<?php

namespace App\Services;

use App\Models\Transaction;
use App\Models\TransactionItem;
use App\Models\Product;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class ForecastService
{
    /**
     * Forecast sales for the next X days based on historical data.
     */
    public function getSalesForecast($days = 7)
    {
        // Get historical daily sales for the last 30 days
        $history = Transaction::select(
                DB::raw('DATE(created_at) as date'),
                DB::raw('SUM(total_amount) as total')
            )
            ->where('created_at', '>=', now()->subDays(30))
            ->groupBy('date')
            ->orderBy('date')
            ->get();

        if ($history->count() < 3) {
            return $this->generateFallBackForecast($days);
        }

        // Simple Linear Regression: y = mx + b
        $n = $history->count();
        $sumX = 0;
        $sumY = 0;
        $sumXY = 0;
        $sumX2 = 0;

        foreach ($history as $index => $data) {
            $x = $index;
            $y = (float) $data->total;
            $sumX += $x;
            $sumY += $y;
            $sumXY += ($x * $y);
            $sumX2 += ($x * $x);
        }

        $denominator = ($n * $sumX2 - $sumX * $sumX);
        if ($denominator == 0) return $this->generateFallBackForecast($days);

        $m = ($n * $sumXY - $sumX * $sumY) / $denominator;
        $b = ($sumY - $m * $sumX) / $n;

        $forecast = [];
        $lastDate = Carbon::parse($history->last()->date);

        for ($i = 1; $i <= $days; $i++) {
            $date = $lastDate->copy()->addDays($i);
            $projectedValue = max(0, $m * ($n + $i - 1) + $b);
            
            $forecast[] = [
                'date' => $date->format('Y-m-d'),
                'label' => $date->isoFormat('dddd, D MMM'),
                'value' => round($projectedValue, 2),
                'confidence' => $this->calculateConfidence($m, $n)
            ];
        }

        return $forecast;
    }

    /**
     * Forecast product demand.
     */
    public function getProductDemandForecast($days = 7)
    {
        $topProducts = TransactionItem::select('product_id', DB::raw('SUM(qty) as total_qty'))
            ->where('created_at', '>=', now()->subDays(14))
            ->groupBy('product_id')
            ->orderByDesc('total_qty')
            ->limit(10)
            ->get();

        $forecast = [];
        foreach ($topProducts as $item) {
            $product = Product::find($item->product_id);
            if (!$product) continue;

            $avgDaily = $item->total_qty / 14;
            $projected = $avgDaily * $days;

            $forecast[] = [
                'product_id' => $product->id,
                'name' => $product->name,
                'code' => $product->code,
                'current_stock' => $product->stock,
                'projected_demand' => ceil($projected),
                'stock_status' => ($product->stock < $projected) ? 'danger' : 'safe',
                'recommendation' => ($product->stock < $projected) ? 'Restock Segera' : 'Stok Cukup'
            ];
        }

        return $forecast;
    }


    /**
     * Forecast sales for the next X months based on historical data.
     */
    public function getMonthlySalesForecast($months = 3)
    {
        // Get historical monthly sales for the last 12 months
        $history = Transaction::select(
                DB::raw("DATE_FORMAT(created_at, '%Y-%m') as month"),
                DB::raw('SUM(total_amount) as total')
            )
            ->where('created_at', '>=', now()->subMonths(12))
            ->groupBy('month')
            ->orderBy('month')
            ->get();

        if ($history->count() < 2) {
            return $this->generateFallBackMonthlyForecast($months);
        }

        $n = $history->count();
        $sumX = 0;
        $sumY = 0;
        $sumXY = 0;
        $sumX2 = 0;

        foreach ($history as $index => $data) {
            $x = $index;
            $y = (float) $data->total;
            $sumX += $x;
            $sumY += $y;
            $sumXY += ($x * $y);
            $sumX2 += ($x * $x);
        }

        $denominator = ($n * $sumX2 - $sumX * $sumX);
        if ($denominator == 0) return $this->generateFallBackMonthlyForecast($months);

        $m = ($n * $sumXY - $sumX * $sumY) / $denominator;
        $b = ($sumY - $m * $sumX) / $n;

        $forecast = [];
        $lastMonth = Carbon::parse($history->last()->month . '-01');

        for ($i = 1; $i <= $months; $i++) {
            $date = $lastMonth->copy()->addMonths($i);
            $projectedValue = max(0, $m * ($n + $i - 1) + $b);
            
            $forecast[] = [
                'month' => $date->format('Y-m'),
                'label' => $date->isoFormat('MMMM Y'),
                'value' => round($projectedValue, 2),
                'growth' => round($m, 2),
                'confidence' => $this->calculateConfidence($m, $n)
            ];
        }

        return [
            'history' => $history->map(fn($h) => [
                'label' => Carbon::parse($h->month . '-01')->isoFormat('MMMM'),
                'value' => $h->total
            ]),
            'prediction' => $forecast
        ];
    }

    private function generateFallBackMonthlyForecast($months)
    {
        $forecast = [];
        $avg = Transaction::avg('total_amount') * 30 ?: 15000000;
        
        for ($i = 1; $i <= $months; $i++) {
            $date = now()->addMonths($i);
            $forecast[] = [
                'month' => $date->format('Y-m'),
                'label' => $date->isoFormat('MMMM Y'),
                'value' => $avg * (1 + (rand(-5, 10) / 100)),
                'growth' => 0,
                'confidence' => 40
            ];
        }
        return [
            'history' => [],
            'prediction' => $forecast
        ];
    }

    private function calculateConfidence($m, $n)
    {
        // Simple heuristic: higher sample size and positive trend increases confidence
        $base = min(90, 50 + ($n * 1.5));
        return $m >= 0 ? min(98, $base + 5) : max(40, $base - 10);
    }

    private function generateFallBackForecast($days)
    {
        $forecast = [];
        $avg = Transaction::avg('total_amount') ?: 500000;
        
        for ($i = 1; $i <= $days; $i++) {
            $date = now()->addDays($i);
            $forecast[] = [
                'date' => $date->format('Y-m-d'),
                'label' => $date->isoFormat('dddd, D MMM'),
                'value' => $avg * (1 + (rand(-10, 10) / 100)),
                'confidence' => 50
            ];
        }
        return $forecast;
    }
}
