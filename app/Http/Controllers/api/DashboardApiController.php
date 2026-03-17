<?php
// app/Http/Controllers/Api/DashboardApiController.php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Transaction;
use App\Models\TransactionItem;
use App\Models\Product;
use App\Models\Member;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class DashboardApiController extends Controller
{
    /**
     * GET /api/v1/dashboard/stats
     */
    public function getDashboardStats(Request $request): JsonResponse
    {
        try {
            $today        = now()->toDateString();
            $startOfMonth = now()->startOfMonth()->toDateString();
            $endOfMonth   = now()->endOfMonth()->toDateString();

            // ── Products ─────────────────────────────────────────────
            $totalProduk    = Product::count();
            $lowStock       = Product::whereRaw('stock <= min_stock AND stock > 0')->count();
            $outOfStock     = Product::where('stock', '<=', 0)->count();
            $normalStock    = max(0, $totalProduk - $lowStock - $outOfStock);
            $akanKadaluarsa = Product::whereNotNull('expiry_date')
                ->where('expiry_date', '<=', now()->addDays(30))
                ->where('expiry_date', '>', now())
                ->count();

            // ── Users ────────────────────────────────────────────────
            $totalKaryawan = User::count();

            // ── Transaksi (hanya LUNAS) ──────────────────────────────
            $todayCount  = Transaction::whereDate('created_at', $today)
                ->where('payment_status', 'LUNAS')->count();
            $todayAmount = (float) Transaction::whereDate('created_at', $today)
                ->where('payment_status', 'LUNAS')->sum('total_amount');
            $monthCount  = Transaction::whereBetween('created_at', [$startOfMonth, $endOfMonth])
                ->where('payment_status', 'LUNAS')->count();
            $monthAmount = (float) Transaction::whereBetween('created_at', [$startOfMonth, $endOfMonth])
                ->where('payment_status', 'LUNAS')->sum('total_amount');

            // ── Produk akan kadaluarsa (list untuk tabel Flutter) ────
            $expiringList = Product::with('category')
                ->whereNotNull('expiry_date')
                ->where('expiry_date', '<=', now()->addDays(30))
                ->orderBy('expiry_date', 'asc')
                ->limit(15)
                ->get()
                ->map(function ($p) {
                    $expiry   = Carbon::parse($p->expiry_date);
                    $now      = Carbon::now();
                    $daysLeft = $expiry < $now ? -1 : (int) $now->diffInDays($expiry);
                    return [
                        'id'          => $p->id,
                        'name'        => $p->name,
                        'stock'       => $p->stock,
                        'expiry_date' => $p->expiry_date->format('Y-m-d'),
                        'days_left'   => $daysLeft,
                        'is_expired'  => $expiry < $now,
                        'category'    => ['name' => $p->category?->name ?? '-'],
                    ];
                });

            return response()->json([
                'success' => true,
                'data'    => [
                    // Summary cards Flutter (flat keys)
                    'total_karyawan'    => $totalKaryawan,
                    'total_produk'      => $totalProduk,
                    'stok_hampir_habis' => $lowStock,
                    'akan_kadaluarsa'   => $akanKadaluarsa,

                    // Tabel kadaluarsa Flutter
                    'expiring_products' => $expiringList,

                    // Nested untuk kompatibilitas
                    'products' => [
                        'total'        => $totalProduk,
                        'low_stock'    => $lowStock,
                        'out_of_stock' => $outOfStock,
                        'normal'       => $normalStock,
                        'active'       => Product::where('is_active', true)->count(),
                        'total_value'  => (float) Product::sum(DB::raw('price * stock')),
                    ],
                    'users'    => ['total' => $totalKaryawan, 'active' => User::where('is_active', true)->count()],
                    'members'  => ['total' => Member::count(), 'active' => Member::where('is_active', true)->count()],
                    'transactions' => [
                        'today'      => ['count' => $todayCount,  'amount' => $todayAmount],
                        'this_month' => ['count' => $monthCount,  'amount' => $monthAmount],
                    ],
                ],
            ]);
        } catch (\Exception $e) {
            Log::error('Dashboard Stats Error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve dashboard statistics',
                'error'   => config('app.debug') ? $e->getMessage() : 'Internal server error',
            ], 500);
        }
    }

    /**
     * GET /api/v1/dashboard/chart-data?period=week|month|year
     *
     * Response:
     * {
     *   "data": {
     *     "labels": ["Sen","Sel",...],
     *     "penjualan":  [500000, 1200000, ...],   ← rupiah mentah
     *     "stok_keluar": [5, 12, ...]              ← unit terjual
     *   }
     * }
     */
    public function getChartData(Request $request): JsonResponse
    {
        try {
            $period = $request->get('period', 'week');

            $labels      = [];
            $penjualan   = [];
            $stokKeluar  = [];

            switch ($period) {
                case 'month': // 5 minggu terakhir
                    for ($i = 4; $i >= 0; $i--) {
                        $start = now()->subWeeks($i)->startOfWeek()->toDateTimeString();
                        $end   = now()->subWeeks($i)->endOfWeek()->toDateTimeString();
                        $labels[]     = 'Minggu ' . (5 - $i);
                        $penjualan[]  = (float) Transaction::whereBetween('created_at', [$start, $end])
                            ->where('payment_status', 'LUNAS')
                            ->sum('total_amount');
                        $stokKeluar[] = (int) TransactionItem::whereHas('transaction', function ($q) use ($start, $end) {
                            $q->whereBetween('created_at', [$start, $end])
                                ->where('payment_status', 'LUNAS');
                        })->sum('qty');
                    }
                    break;

                case 'year': // 90 hari terakhir — dikelompokkan per 10 hari (9 titik)
                    for ($i = 8; $i >= 0; $i--) {
                        $end   = now()->subDays($i * 10)->endOfDay()->toDateTimeString();
                        $start = now()->subDays($i * 10 + 9)->startOfDay()->toDateTimeString();
                        $endDate = now()->subDays($i * 10);
                        // Label: "dd/mm" dari hari terakhir di periode tersebut
                        $labels[]     = $endDate->format('d/m');
                        $penjualan[]  = (float) Transaction::whereBetween('created_at', [$start, $end])
                            ->where('payment_status', 'LUNAS')
                            ->sum('total_amount');
                        $stokKeluar[] = (int) TransactionItem::whereHas('transaction', function ($q) use ($start, $end) {
                            $q->whereBetween('created_at', [$start, $end])
                                ->where('payment_status', 'LUNAS');
                        })->sum('qty');
                    }
                    break;

                default: // week — 7 hari terakhir
                    $dayNames = ['Min', 'Sen', 'Sel', 'Rab', 'Kam', 'Jum', 'Sab'];
                    for ($i = 6; $i >= 0; $i--) {
                        $date         = now()->subDays($i)->toDateString();
                        $dayOfWeek    = now()->subDays($i)->dayOfWeek;
                        $labels[]     = $dayNames[$dayOfWeek];
                        $penjualan[]  = (float) Transaction::whereDate('created_at', $date)
                            ->where('payment_status', 'LUNAS')
                            ->sum('total_amount');
                        $stokKeluar[] = (int) TransactionItem::whereHas('transaction', function ($q) use ($date) {
                            $q->whereDate('created_at', $date)
                                ->where('payment_status', 'LUNAS');
                        })->sum('qty');
                    }
                    break;
            }

            return response()->json([
                'success' => true,
                'data'    => [
                    'labels'     => $labels,
                    'penjualan'  => $penjualan,   // rupiah mentah (Flutter handle formatting)
                    'stok_keluar' => $stokKeluar,  // unit
                ],
            ]);
        } catch (\Exception $e) {
            Log::error('Chart Data Error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve chart data',
                'error'   => config('app.debug') ? $e->getMessage() : 'Internal server error',
            ], 500);
        }
    }

    // Role-specific dashboards — semua delegate ke getDashboardStats
    public function ownerDashboard(Request $request): JsonResponse
    {
        return $this->getDashboardStats($request);
    }
    public function kasirDashboard(Request $request): JsonResponse
    {
        return $this->getDashboardStats($request);
    }
    public function gudangDashboard(Request $request): JsonResponse
    {
        return $this->getDashboardStats($request);
    }
    public function logistikDashboard(Request $request): JsonResponse
    {
        return $this->getDashboardStats($request);
    }
    public function kurirDashboard(Request $request): JsonResponse
    {
        return $this->getDashboardStats($request);
    }
}
