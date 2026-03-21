<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Product;
use App\Models\Category;
use App\Models\Transaction;
use App\Models\TransactionItem;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Schema;
use Carbon\Carbon;

class DashboardController extends Controller
{
    public function index()
    {
        $user = auth()->user();

        // Role-based dashboard routing
        switch ($user->role) {
            case 'owner':
                return $this->ownerDashboard();
            case 'kasir':
                return $this->kasirDashboard();
            case 'gudang':
                return $this->gudangDashboard();
            case 'logistik':
                return $this->logistikDashboard();
            case 'kurir':
            case 'driver':
                return redirect()->route('delivery.my-deliveries');
            default:
                return $this->ownerDashboard();
        }
    }

    // =====================
    // OWNER DASHBOARD
    // =====================
    public function ownerDashboard()
    {
        $totalUsers = User::count();
        $totalProducts = Product::count();

        // TOTAL PENDAPATAN
        $totalRevenue = Transaction::sum('total_amount') ?? 0;

        // TOTAL TRANSAKSI (TAMBAHKAN INI)
        $totalTransactions = Transaction::count();

        // RATA-RATA TRANSAKSI
        $avgTransaction = $totalTransactions > 0 ? $totalRevenue / $totalTransactions : 0;

        // TRANSAKSI HARI INI
        $todayTransactions = Transaction::whereDate('created_at', today())->count();
        $todayRevenue = Transaction::whereDate('created_at', today())->sum('total_amount') ?? 0;

        // PRODUK TERLARIS
        $topProducts = TransactionItem::select(
            'products.id',
            'products.name',
            'products.code',
            'categories.name as category_name',
            DB::raw('SUM(transaction_items.qty) as total_sold'),
            DB::raw('SUM(transaction_items.qty * transaction_items.price) as revenue')
        )
            ->join('products', 'products.id', '=', 'transaction_items.product_id')
            ->leftJoin('categories', 'categories.id', '=', 'products.category_id')
            ->groupBy('products.id', 'products.name', 'products.code', 'categories.name')
            ->orderByDesc('total_sold')
            ->limit(5)
            ->get();

        // TRANSAKSI TERAKHIR
        $recentTransactions = Transaction::with(['user', 'items.product'])
            ->select('id', 'invoice_number', 'customer_name', 'total_amount', 'created_at', 'user_id')
            ->latest()
            ->limit(5)
            ->get()
            ->map(function ($transaction) {
                $transaction->customer_name = $transaction->customer_name ?? 'Pelanggan Umum';
                $transaction->formatted_date = $transaction->created_at->format('d M Y H:i');
                $transaction->invoice_display = $transaction->invoice_number ?? 'TRX-' . str_pad($transaction->id, 6, '0', STR_PAD_LEFT);
                return $transaction;
            });

        // ========== DATA STOK DAN KADALUARSA ==========

        // Statistik stok
        $normalStockCount = Product::where('stock', '>=', 10)->count();
        $lowStockCount = Product::where('stock', '>', 0)->where('stock', '<', 10)->count();
        $criticalStockCount = Product::where('stock', '<=', 0)->count();

        // Pastikan tidak ada nilai negatif
        $normalStockCount = max(0, $normalStockCount);
        $lowStockCount = max(0, $lowStockCount);
        $criticalStockCount = max(0, $criticalStockCount);

        // Statistik kadaluarsa
        $expiringSoonCount = Product::whereNotNull('expiry_date')
            ->where('expiry_date', '<=', now()->addDays(30))
            ->where('expiry_date', '>', now())
            ->count();

        $expiredCount = Product::whereNotNull('expiry_date')
            ->where('expiry_date', '<', now())
            ->count();

        // Daftar produk stok rendah
        $lowStockProducts = Product::with('category')
            ->where('stock', '<', 10)
            ->orderBy('stock', 'asc')
            ->limit(10)
            ->get()
            ->map(function ($product) {
                $product->min_stock = 10;
                return $product;
            });

        // Daftar produk akan kadaluarsa
        $expiringProducts = Product::with('category')
            ->whereNotNull('expiry_date')
            ->where('expiry_date', '<=', now()->addDays(30))
            ->orderBy('expiry_date', 'asc')
            ->limit(10)
            ->get()
            ->map(function ($product) {
                if ($product->expiry_date) {
                    $expiryDate = Carbon::parse($product->expiry_date);
                    $now = Carbon::now();

                    if ($expiryDate < $now) {
                        $product->days_left = -1;
                        $product->expiry_status = 'expired';
                    } else {
                        $product->days_left = (int) $now->diffInDays($expiryDate);
                        if ($product->days_left <= 7) {
                            $product->expiry_status = 'critical';
                        } elseif ($product->days_left <= 30) {
                            $product->expiry_status = 'warning';
                        } else {
                            $product->expiry_status = 'good';
                        }
                    }
                } else {
                    $product->days_left = null;
                    $product->expiry_status = 'no_date';
                }
                return $product;
            });

        // Ringkasan stok
        $totalStock = Product::sum('stock') ?? 0;
        $totalStockValue = Product::select(DB::raw('SUM(stock * price) as total'))->value('total') ?? 0;
        $productCategories = Category::count();

        // Data chart - default 7 hari
        $chartRange = 7;
        $chartData = $this->getChartDataInternal($chartRange);

        return view('dashboard.owner', compact(
            'totalUsers',
            'totalProducts',
            'totalTransactions', // TAMBAHKAN INI
            'totalRevenue',
            'avgTransaction',
            'todayTransactions',
            'topProducts',
            'recentTransactions',
            'todayRevenue',
            // Data stok dan kadaluarsa
            'lowStockCount',
            'criticalStockCount',
            'normalStockCount',
            'expiringSoonCount',
            'expiredCount',
            'lowStockProducts',
            'expiringProducts',
            'totalStock',
            'totalStockValue',
            'productCategories',
            'chartData',
            'chartRange'
        ));
    }

    /**
     * Method internal untuk mendapatkan data chart
     */
    private function getChartDataInternal($days = 7)
    {
        $labels = [];
        $salesData = [];
        $stockOutData = [];

        for ($i = $days - 1; $i >= 0; $i--) {
            $date = now()->subDays($i);
            $dateString = $date->format('Y-m-d');

            // Label untuk chart
            if ($days <= 7) {
                $labels[] = $date->isoFormat('dddd'); // Senin, Selasa, etc
            } elseif ($days <= 30) {
                $labels[] = $date->format('d M'); // 01 Jan, 02 Jan
            } else {
                $labels[] = $date->format('d/m'); // 01/01, 02/01
            }

            // Data penjualan per hari (dalam Rupiah)
            $dailySales = Transaction::whereDate('created_at', $dateString)
                ->sum('total_amount');
            $salesData[] = $dailySales ?: rand(500000, 2000000); // Gunakan random untuk demo jika tidak ada data

            // Data stok keluar per hari (dalam unit)
            $dailyStockOut = TransactionItem::whereHas('transaction', function ($query) use ($dateString) {
                $query->whereDate('created_at', $dateString);
            })
                ->sum('qty');
            $stockOutData[] = $dailyStockOut ?: rand(10, 50); // Gunakan random untuk demo jika tidak ada data
        }

        return [
            'labels' => $labels,
            'sales' => $salesData,
            'stock_out' => $stockOutData,
        ];
    }

    /**
     * API endpoint untuk mendapatkan data chart berdasarkan range
     */
    public function getChartData($range = 7)
    {
        try {
            $data = $this->getChartDataInternal($range);
            return response()->json([
                'success' => true,
                'data' => $data
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * API: Stats untuk mobile dashboard
     * GET /api/v1/dashboard/stats
     */
    public function getDashboardStats()
    {
        try {
            $totalKaryawan   = User::count();
            $totalProduk     = Product::count();

            // Stok hampir habis: stok <= min_stock dan stok > 0
            $stokHampirHabis = Product::whereRaw('stock <= min_stock AND stock > 0')->count();

            // Stok kritis: stok = 0
            $stokKritis      = Product::where('stock', '<=', 0)->count();

            // Stok normal: stok > min_stock
            $stokNormal      = Product::whereRaw('stock > min_stock')->count();

            // Akan kadaluarsa dalam 30 hari
            $akanKadaluarsa  = Product::whereNotNull('expiry_date')
                ->where('expiry_date', '<=', now()->addDays(30))
                ->where('expiry_date', '>', now())
                ->count();

            return response()->json([
                'status' => true,
                'data'   => [
                    'total_karyawan'    => $totalKaryawan,
                    'total_produk'      => $totalProduk,
                    'stok_hampir_habis' => $stokHampirHabis,
                    'akan_kadaluarsa'   => $akanKadaluarsa,
                    'stok_normal'       => $stokNormal,
                    'stok_kritis'       => $stokKritis,
                ],
            ]);
        } catch (\Exception $e) {
            return response()->json(['status' => false, 'message' => $e->getMessage()], 500);
        }
    }


    /**
     * API: Chart penjualan & stok keluar
     * GET /api/v1/dashboard/chart-data?days=7
     */
    // public function getChartData(Request $request)
    // {
    //     try {
    //         $days = (int) $request->get('days', 7);
    //         // Batasi maksimal 90 hari
    //         $days = min(max($days, 7), 90);

    //         $data = $this->getChartDataInternal($days);

    //         return response()->json([
    //             'status' => true,
    //             'data'   => [
    //                 'labels'     => $data['labels'],
    //                 'penjualan'  => $data['sales'],      // alias untuk Flutter
    //                 'stok_keluar' => $data['stock_out'],  // alias untuk Flutter
    //             ],
    //         ]);
    //     } catch (\Exception $e) {
    //         return response()->json(['status' => false, 'message' => $e->getMessage()], 500);
    //     }
    // }


    /**
     * API: Notifikasi — produk akan kadaluarsa
     * GET /api/v1/dashboard/notifications
     */
    public function getNotifications()
    {
        try {
            $expiring = Product::with('category')
                ->whereNotNull('expiry_date')
                ->where('expiry_date', '<=', now()->addDays(30))
                ->orderBy('expiry_date', 'asc')
                ->limit(15)
                ->get()
                ->map(function ($product) {
                    $expiry   = Carbon::parse($product->expiry_date);
                    $now      = Carbon::now();
                    $daysLeft = $expiry < $now ? -1 : (int) $now->diffInDays($expiry);

                    return [
                        'id'           => $product->id,
                        'name'         => $product->name,
                        'stock'        => $product->stock,
                        'expiry_date'  => $product->expiry_date->format('Y-m-d'),
                        'days_left'    => $daysLeft,
                        'is_expired'   => $expiry < $now,
                        'category'     => [
                            'name' => $product->category?->name ?? '-',
                        ],
                    ];
                });

            return response()->json([
                'status' => true,
                'data'   => [
                    'expiring' => $expiring,
                ],
            ]);
        } catch (\Exception $e) {
            return response()->json(['status' => false, 'message' => $e->getMessage()], 500);
        }
    }

    // =====================
    // KASIR DASHBOARD
    // =====================
    public function kasirDashboard()
    {
        $user = auth()->user();
        $today = now()->toDateString();
        $yesterday = now()->subDay()->toDateString();

        // STATISTIK HARI INI
        $todayTransactions = Transaction::where('user_id', $user->id)
            ->whereDate('created_at', $today)
            ->count();

        $todayRevenue = Transaction::where('user_id', $user->id)
            ->whereDate('created_at', $today)
            ->sum('total_amount');

        $todayItemsSold = TransactionItem::whereHas('transaction', function ($query) use ($user, $today) {
            $query->where('user_id', $user->id)
                ->whereDate('created_at', $today);
        })
            ->sum('qty');

        // RATA-RATA TRANSAKSI HARI INI
        $avgTransaction = $todayTransactions > 0 ? $todayRevenue / $todayTransactions : 0;

        // PERBANDINGAN DENGAN KEMARIN
        $yesterdayTransactions = Transaction::where('user_id', $user->id)
            ->whereDate('created_at', $yesterday)
            ->count();

        $yesterdayRevenue = Transaction::where('user_id', $user->id)
            ->whereDate('created_at', $yesterday)
            ->sum('total_amount');

        $transactionGrowth = $yesterdayTransactions > 0
            ? round((($todayTransactions - $yesterdayTransactions) / $yesterdayTransactions) * 100, 1)
            : ($todayTransactions > 0 ? 100 : 0);

        // PRODUK POPULER HARI INI
        $popularProducts = Product::select('products.*')
            ->selectSub(function ($query) use ($user, $today) {
                $query->from('transaction_items')
                    ->join('transactions', 'transactions.id', '=', 'transaction_items.transaction_id')
                    ->whereColumn('transaction_items.product_id', 'products.id')
                    ->where('transactions.user_id', $user->id)
                    ->whereDate('transactions.created_at', $today)
                    ->select(DB::raw('COALESCE(SUM(transaction_items.qty), 0)'));
            }, 'sold_today')
            ->orderByDesc('sold_today')
            ->limit(6)
            ->get();

        // TRANSAKSI TERAKHIR
        $recentTransactions = Transaction::where('user_id', $user->id)
            ->with(['items.product'])
            ->select('id', 'invoice_number', 'customer_name', 'total_amount', 'created_at')
            ->latest()
            ->limit(5)
            ->get();

        $topProductToday = $popularProducts->isNotEmpty() ? $popularProducts->first()->name : '-';

        return view('dashboard.kasir', compact(
            'todayTransactions',
            'todayRevenue',
            'todayItemsSold',
            'avgTransaction',
            'transactionGrowth',
            'popularProducts',
            'recentTransactions',
            'topProductToday'
        ));
    }

    // =====================
    // GUDANG DASHBOARD
    // =====================
    public function gudangDashboard()
    {
        // STATISTIK PRODUK
        $totalProducts = Product::count();
        $totalCategories = Category::count();
        $activeProducts = Product::where('is_active', true)->count();

        // STOK RENDAH (stok < 10)
        $lowStockProducts = Product::where('stock', '<', 10)->count();

        // NILAI INVENTORI
        $inventoryValue = Product::sum(DB::raw('stock * price')) ?? 0;

        // STATISTIK HARI INI
        $today = now()->toDateString();
        $todayItemsSold = TransactionItem::whereHas('transaction', function ($query) use ($today) {
            $query->whereDate('created_at', $today);
        })
            ->sum('qty');

        $todayRevenue = Transaction::whereDate('created_at', $today)
            ->sum('total_amount');

        // PRODUK STOK RENDAH DETAIL
        $lowStockItems = Product::where('stock', '<', 10)
            ->with('category')
            ->select('id', 'name', 'code', 'stock', 'min_stock', 'category_id')
            ->orderBy('stock')
            ->limit(5)
            ->get();

        // PRODUK PER KATEGORI
        $productCategories = Category::withCount(['products as products_count'])
            ->withCount(['products as low_stock_count' => function ($query) {
                $query->where('stock', '<', 10);
            }])
            ->orderBy('products_count', 'desc')
            ->limit(6)
            ->get();

        // UPDATE STOK TERBARU
        $recentStockUpdates = collect([]);

        // PRODUK TERLARIS HARI INI
        $topSellingProduct = DB::table('transaction_items')
            ->join('products', 'products.id', '=', 'transaction_items.product_id')
            ->join('transactions', 'transactions.id', '=', 'transaction_items.transaction_id')
            ->whereDate('transactions.created_at', $today)
            ->select(
                'products.name',
                DB::raw('SUM(transaction_items.qty) as total_sold')
            )
            ->groupBy('products.id', 'products.name')
            ->orderByDesc('total_sold')
            ->first();

        $topSellingProductName = $topSellingProduct ? $topSellingProduct->name : '-';

        return view('dashboard.gudang', compact(
            'totalProducts',
            'totalCategories',
            'activeProducts',
            'lowStockProducts',
            'inventoryValue',
            'todayItemsSold',
            'todayRevenue',
            'lowStockItems',
            'productCategories',
            'recentStockUpdates',
            'topSellingProductName'
        ));
    }

    // =====================
    // LOGISTIK DASHBOARD
    // =====================
    public function logistikDashboard()
    {
        // Jika belum ada tabel deliveries, return default view
        if (!Schema::hasTable('deliveries')) {
            return view('dashboard.logistik', [
                'todayDeliveries' => 0,
                'completedDeliveries' => 0,
                'ongoingDeliveries' => 0,
                'delayedDeliveries' => 0,
                'totalItemsShipped' => 0,
                'totalWeight' => 0,
                'totalVolume' => 0,
                'onTimeRate' => 0,
                'activeDeliveries' => collect(),
                'fleetStatus' => collect(),
                'totalFleet' => 0,
                'activeRoutes' => 0,
                'totalDrivers' => 0
            ]);
        }

        // STATISTIK UMUM
        $today = now()->toDateString();

        $todayDeliveries = \App\Models\Delivery::whereDate('created_at', $today)->count();
        $completedDeliveries = \App\Models\Delivery::whereDate('created_at', $today)
            ->where('status', 'delivered')
            ->count();

        $ongoingDeliveries = \App\Models\Delivery::whereIn('status', ['processing', 'on_delivery'])
            ->whereDate('created_at', $today)
            ->count();

        $delayedDeliveries = \App\Models\Delivery::where('status', 'delayed')
            ->whereDate('created_at', $today)
            ->count();

        // TOTAL BARANG DIKIRIM
        $totalItemsShipped = \App\Models\Delivery::whereDate('created_at', $today)
            ->sum('total_items');

        $totalWeight = \App\Models\Delivery::whereDate('created_at', $today)
            ->sum('total_weight');

        $totalVolume = \App\Models\Delivery::whereDate('created_at', $today)
            ->sum('total_volume');

        // ON-TIME DELIVERY RATE
        $totalDeliveriesThisWeek = \App\Models\Delivery::whereBetween('created_at', [now()->startOfWeek(), now()->endOfWeek()])
            ->count();

        $onTimeDeliveries = \App\Models\Delivery::whereBetween('created_at', [now()->startOfWeek(), now()->endOfWeek()])
            ->where('status', 'delivered')
            ->whereRaw('delivered_at <= estimated_delivery_time')
            ->count();

        $onTimeRate = $totalDeliveriesThisWeek > 0
            ? round(($onTimeDeliveries / $totalDeliveriesThisWeek) * 100, 0)
            : 100;

        // PENGIRIMAN AKTIF
        $activeDeliveries = \App\Models\Delivery::with('driver', 'vehicle')
            ->whereIn('status', ['processing', 'on_delivery'])
            ->orderBy('created_at', 'desc')
            ->limit(5)
            ->get()
            ->map(function ($delivery) {
                // Simulasi progress berdasarkan status
                $progress = [
                    'processing' => 30,
                    'on_delivery' => 70,
                    'delivered' => 100
                ];

                $delivery->progress = $progress[$delivery->status] ?? 0;
                $delivery->items_count = $delivery->total_items ?? 0;
                $delivery->driver_name = $delivery->driver->name ?? 'N/A';
                $delivery->vehicle_number = $delivery->vehicle->license_plate ?? 'N/A';
                $delivery->from_location = $delivery->origin ?? 'Gudang Utama';
                $delivery->to_location = $delivery->destination ?? 'Toko Cabang';
                $delivery->eta = $delivery->estimated_delivery_time
                    ? Carbon::parse($delivery->estimated_delivery_time)->format('H:i')
                    : '15:00';

                return $delivery;
            });

        // STATUS ARMADA
        if (Schema::hasTable('vehicles')) {
            $fleetStatus = \App\Models\Vehicle::select('id', 'name', 'type', 'license_plate', 'status')
                ->orderBy('status')
                ->limit(5)
                ->get()
                ->map(function ($vehicle) {
                    // Status kendaraan
                    $statusMap = [
                        'available' => 'Available',
                        'on_delivery' => 'On Delivery',
                        'maintenance' => 'Maintenance'
                    ];

                    $vehicle->status = $statusMap[$vehicle->status] ?? 'Unknown';
                    return $vehicle;
                });
        } else {
            $fleetStatus = collect();
        }

        // DATA STATISTIK TAMBAHAN
        $totalFleet = Schema::hasTable('vehicles') ? \App\Models\Vehicle::count() : 0;
        $activeRoutes = 0;
        $totalDrivers = Schema::hasTable('drivers') ? \App\Models\Driver::count() : 0;

        return view('dashboard.logistik', compact(
            'todayDeliveries',
            'completedDeliveries',
            'ongoingDeliveries',
            'delayedDeliveries',
            'totalItemsShipped',
            'totalWeight',
            'totalVolume',
            'onTimeRate',
            'activeDeliveries',
            'fleetStatus',
            'totalFleet',
            'activeRoutes',
            'totalDrivers'
        ));
    }
}
