<?php
// app/Http/Controllers/Api/ReportApiController.php
// Add these methods to your existing ReportController

namespace App\Http\Controllers;

use App\Models\Transaction;
use App\Models\Product;
use App\Models\Member;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class ReportController extends Controller
{
    /**
     * Get sales chart data
     */
    public function getSalesChartData(Request $request)
    {
        try {
            $period = $request->get('period', 'month'); // week, month, year
            $type = $request->get('type', 'amount'); // amount, count
            $year = $request->get('year', now()->year);

            $chartData = [];

            switch ($period) {
                case 'week':
                    // Daily for last 7 days
                    for ($i = 6; $i >= 0; $i--) {
                        $date = now()->subDays($i)->format('Y-m-d');
                        $chartData['labels'][] = now()->subDays($i)->format('l');

                        if ($type === 'amount') {
                            $chartData['data'][] = (float) Transaction::whereDate('created_at', $date)
                                ->where('status', 'completed')
                                ->sum('total_amount');
                        } else {
                            $chartData['data'][] = Transaction::whereDate('created_at', $date)
                                ->where('status', 'completed')
                                ->count();
                        }
                    }
                    break;

                case 'month':
                    // Daily for current month
                    $daysInMonth = now()->daysInMonth;
                    for ($i = 1; $i <= $daysInMonth; $i++) {
                        $date = now()->setDay($i)->format('Y-m-d');
                        $chartData['labels'][] = $i;

                        if ($type === 'amount') {
                            $chartData['data'][] = (float) Transaction::whereDate('created_at', $date)
                                ->where('status', 'completed')
                                ->sum('total_amount');
                        } else {
                            $chartData['data'][] = Transaction::whereDate('created_at', $date)
                                ->where('status', 'completed')
                                ->count();
                        }
                    }
                    break;

                case 'year':
                    // Monthly for selected year
                    for ($i = 1; $i <= 12; $i++) {
                        $month = now()->setYear($year)->setMonth($i);
                        $startOfMonth = $month->copy()->startOfMonth()->format('Y-m-d');
                        $endOfMonth = $month->copy()->endOfMonth()->format('Y-m-d');

                        $chartData['labels'][] = $month->format('M');

                        if ($type === 'amount') {
                            $chartData['data'][] = (float) Transaction::whereBetween('created_at', [$startOfMonth, $endOfMonth])
                                ->where('status', 'completed')
                                ->sum('total_amount');
                        } else {
                            $chartData['data'][] = Transaction::whereBetween('created_at', [$startOfMonth, $endOfMonth])
                                ->where('status', 'completed')
                                ->count();
                        }
                    }
                    break;

                case 'custom':
                    // Custom date range
                    $startDate = $request->get('start_date', now()->startOfMonth()->format('Y-m-d'));
                    $endDate = $request->get('end_date', now()->format('Y-m-d'));

                    $transactions = Transaction::whereBetween('created_at', [$startDate . ' 00:00:00', $endDate . ' 23:59:59'])
                        ->where('status', 'completed')
                        ->select(
                            DB::raw('DATE(created_at) as date'),
                            DB::raw('SUM(total_amount) as total'),
                            DB::raw('COUNT(*) as count')
                        )
                        ->groupBy('date')
                        ->orderBy('date')
                        ->get();

                    foreach ($transactions as $transaction) {
                        $chartData['labels'][] = \Carbon\Carbon::parse($transaction->date)->format('d/m');
                        $chartData['data'][] = $type === 'amount' ? (float) $transaction->total : $transaction->count;
                    }
                    break;
            }

            // Add additional data for comparison
            if ($type === 'amount') {
                $chartData['total'] = array_sum($chartData['data']);
                $chartData['average'] = count($chartData['data']) > 0 ? array_sum($chartData['data']) / count($chartData['data']) : 0;
                $chartData['formatted_total'] = 'Rp ' . number_format($chartData['total'], 0, ',', '.');
                $chartData['formatted_average'] = 'Rp ' . number_format($chartData['average'], 0, ',', '.');
            } else {
                $chartData['total'] = array_sum($chartData['data']);
                $chartData['average'] = count($chartData['data']) > 0 ? round(array_sum($chartData['data']) / count($chartData['data']), 2) : 0;
            }

            return response()->json([
                'success' => true,
                'message' => 'Sales chart data retrieved successfully',
                'data' => $chartData
            ], 200);
        } catch (\Exception $e) {
            Log::error('Sales chart data error: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Failed to get sales chart data',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
