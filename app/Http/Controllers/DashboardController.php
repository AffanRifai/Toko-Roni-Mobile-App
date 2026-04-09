<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Product;
use App\Models\Category;
use App\Models\CheckerReport;
use App\Models\Transaction;
use App\Models\TransactionItem;
use App\Models\Delivery;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Schema;
use Illuminate\Http\Request;
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
            case 'manager':
                return $this->managerDashboard();
            case 'kasir':
                return $this->kasirDashboard();
            case 'kepala_gudang':
                return $this->gudangDashboard();
            case 'checker_barang':
                return $this->checkerDashboard();
            case 'logistik':
                return $this->logistikDashboard(request());
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

        // TOTAL TRANSAKSI
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
            ->map(function($transaction) {
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
            ->map(function($product) {
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
            ->map(function($product) {
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
            'totalTransactions',
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

    // =====================
    // MANAGER DASHBOARD (DENGAN TOTAL USERS)
    // =====================
    public function managerDashboard()
    {
        // TAMBAHKAN TOTAL USERS
        $totalUsers = User::count();
        
        // STATISTIK UTAMA
        $totalProducts = Product::count();
        $totalTransactions = Transaction::count();
        $totalRevenue = Transaction::sum('total_amount') ?? 0;
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
            ->map(function($transaction) {
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
            ->map(function($product) {
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
            ->map(function($product) {
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

        // KIRIM SEMUA DATA KE VIEW, TERMASUK TOTAL USERS
        return view('dashboard.manager', compact(
            'totalUsers',           // <-- TAMBAHKAN INI
            'totalProducts',
            'totalTransactions',
            'totalRevenue',
            'avgTransaction',
            'todayTransactions',
            'todayRevenue',
            'topProducts',
            'recentTransactions',
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
     * (HANYA SATU DEKLARASI - TIDAK DUPLIKAT)
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
            } elseif ($days <= 90) {
                $labels[] = $date->format('d/m'); // 01/01, 02/01
            } else {
                $labels[] = $date->format('M Y'); // Jan 2024, Feb 2024
            }

            // Data penjualan per hari (dalam Rupiah)
            $dailySales = Transaction::whereDate('created_at', $dateString)
                ->sum('total_amount');
            $salesData[] = (float) ($dailySales ?? 0);

            // Data stok keluar per hari (dalam unit)
            $dailyStockOut = TransactionItem::whereHas('transaction', function($query) use ($dateString) {
                $query->whereDate('created_at', $dateString);
            })->sum('qty');
            $stockOutData[] = (int) ($dailyStockOut ?? 0);
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
            // Validasi range yang diizinkan
            $allowedRanges = [7, 30, 90, 180, 360];
            if (!in_array($range, $allowedRanges)) {
                $range = 7;
            }

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
     * DITAMBAHKAN DARI KODE (2)
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
     * API: Notifikasi — produk akan kadaluarsa
     * GET /api/v1/dashboard/notifications
     * DITAMBAHKAN DARI KODE (2)
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
        })->sum('qty');

        // RATA-RATA TRANSAKSI HARI INI
        $avgTransaction = $todayTransactions > 0 ? $todayRevenue / $todayTransactions : 0;

        // PERBANDINGAN DENGAN KEMARIN
        $yesterdayTransactions = Transaction::where('user_id', $user->id)
            ->whereDate('created_at', $yesterday)
            ->count();

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
            ->having('sold_today', '>', 0)
            ->orderByDesc('sold_today')
            ->limit(6)
            ->get();

        // TRANSAKSI TERAKHIR - PERBAIKAN: gunakan 'invoice_number' bukan 'transaction_code'
        $recentTransactions = Transaction::where('user_id', $user->id)
            ->with(['items'])
            ->select('id', 'invoice_number', 'customer_name', 'customer_phone', 'total_amount', 'payment_method', 'created_at')
            ->latest()
            ->limit(5)
            ->get()
            ->map(function($transaction) {
                $transaction->customer_name = $transaction->customer_name ?? 'Pelanggan Umum';
                $transaction->transaction_code = $transaction->invoice_number ?? 'INV-' . str_pad($transaction->id, 6, '0', STR_PAD_LEFT);
                $transaction->status = 'completed'; // Default status
                return $transaction;
            });

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
        
        // Produk Terjual Hari Ini (total unit dari TransactionItem)
        $todayItemsSold = TransactionItem::whereHas('transaction', function ($query) use ($today) {
            $query->whereDate('created_at', $today);
        })->sum('qty') ?? 0;

        // Total Pendapatan Hari Ini (dari TransactionItem subtotal)
        $todayRevenue = TransactionItem::whereHas('transaction', function ($query) use ($today) {
            $query->whereDate('created_at', $today);
        })->sum('subtotal') ?? 0;

        // ===== STATISTIK KESELURUHAN (ALL TIME) =====
        
        // Total Semua Produk Terjual (keseluruhan qty)
        $totalItemsSoldAllTime = TransactionItem::sum('qty') ?? 0;
        
        // Total Pendapatan Keseluruhan (dari subtotal)
        $totalRevenueAllTime = TransactionItem::sum('subtotal') ?? 0;
        
        // Rata-rata nilai per transaksi item
        $avgItemValue = $totalItemsSoldAllTime > 0 
            ? $totalRevenueAllTime / $totalItemsSoldAllTime 
            : 0;

        // PRODUK STOK RENDAH DETAIL
        $lowStockItems = Product::where('stock', '<', 10)
            ->with('category')
            ->select('id', 'name', 'code', 'stock', 'min_stock', 'category_id')
            ->orderBy('stock')
            ->limit(5)
            ->get();

        // PRODUK TERLARIS HARI INI
        $topSellingToday = TransactionItem::select(
                'products.id',
                'products.name',
                'products.code',
                DB::raw('COALESCE(SUM(transaction_items.qty), 0) as total_sold'),
                DB::raw('COALESCE(SUM(transaction_items.subtotal), 0) as total_revenue')
            )
            ->join('products', 'products.id', '=', 'transaction_items.product_id')
            ->join('transactions', 'transactions.id', '=', 'transaction_items.transaction_id')
            ->whereDate('transactions.created_at', $today)
            ->groupBy('products.id', 'products.name', 'products.code')
            ->orderByDesc('total_sold')
            ->first();

        // PRODUK TERLARIS ALL TIME
        $topSellingAllTime = TransactionItem::select(
                'products.id',
                'products.name',
                'products.code',
                DB::raw('COALESCE(SUM(transaction_items.qty), 0) as total_sold'),
                DB::raw('COALESCE(SUM(transaction_items.subtotal), 0) as total_revenue')
            )
            ->join('products', 'products.id', '=', 'transaction_items.product_id')
            ->groupBy('products.id', 'products.name', 'products.code')
            ->orderByDesc('total_sold')
            ->first();

        $topSellingProductName = $topSellingToday ? $topSellingToday->name : '-';
        $topSellingProductQty = $topSellingToday ? $topSellingToday->total_sold : 0;
        $topSellingProductRevenue = $topSellingToday ? $topSellingToday->total_revenue : 0;

        // PRODUK PER KATEGORI
        $productCategories = Category::withCount(['products as products_count'])
            ->withCount(['products as low_stock_count' => function ($query) {
                $query->where('stock', '<', 10);
            }])
            ->orderBy('products_count', 'desc')
            ->limit(6)
            ->get();

        // UPDATE STOK TERBARU
        $recentStockUpdates = DB::table('stock_histories')
            ->join('products', 'products.id', '=', 'stock_histories.product_id')
            ->select(
                'stock_histories.type',
                'stock_histories.quantity',
                'stock_histories.new_stock',
                'stock_histories.created_at',
                'products.name as product_name'
            )
            ->orderBy('stock_histories.created_at', 'desc')
            ->limit(5)
            ->get()
            ->map(function ($item) {
                // Konversi string created_at ke objek Carbon
                if ($item->created_at && is_string($item->created_at)) {
                    $item->created_at = \Carbon\Carbon::parse($item->created_at);
                }
                return $item;
            });

        return view('dashboard.gudang', compact(
            'totalProducts',
            'totalCategories',
            'activeProducts',
            'lowStockProducts',
            'inventoryValue',
            'todayItemsSold',
            'todayRevenue',
            'totalItemsSoldAllTime',
            'totalRevenueAllTime',
            'avgItemValue',
            'lowStockItems',
            'productCategories',
            'recentStockUpdates',
            'topSellingProductName',
            'topSellingProductQty',
            'topSellingProductRevenue',
            'topSellingAllTime'
        ));
    }

    // =====================
    // LOGISTIK DASHBOARD
    // =====================
    public function logistikDashboard(Request $request)  // <-- Tambahkan Request $request sebagai parameter
    {
        try {
            $user = auth()->user();
            $userRole = $user->role;
            $isStaffLogistik = in_array($userRole, ['logistik', 'staff_logistik']);
            
            // Ambil filter range dari request (default: 7)
            $chartRange = $request->get('range', 7);
            $allowedRanges = [7, 30, 90];
            if (!in_array($chartRange, $allowedRanges)) {
                $chartRange = 7;
            }
            
            // ========== DATA CHART DENGAN FILTER RANGE ==========
            $chartData = $this->getDeliveryChartData($isStaffLogistik ? $user->id : null, $chartRange);
            
            // ========== STATISTIK UMUM (tetap 7 hari terakhir untuk statistik ringkasan) ==========
            $today = now()->toDateString();
            
            // Total pengiriman hari ini
            if ($isStaffLogistik) {
                $todayDeliveries = Delivery::where('user_id', $user->id)
                    ->whereDate('created_at', $today)
                    ->count();
            } else {
                $todayDeliveries = Delivery::whereDate('created_at', $today)->count();
            }
            
            // Pengiriman selesai hari ini
            if ($isStaffLogistik) {
                $completedDeliveries = Delivery::where('user_id', $user->id)
                    ->whereDate('created_at', $today)
                    ->where('status', 'delivered')
                    ->count();
            } else {
                $completedDeliveries = Delivery::whereDate('created_at', $today)
                    ->where('status', 'delivered')
                    ->count();
            }
            
            // Pengiriman dalam proses
            if ($isStaffLogistik) {
                $ongoingDeliveries = Delivery::where('user_id', $user->id)
                    ->whereIn('status', ['assigned', 'picked_up', 'on_delivery'])
                    ->count();
            } else {
                $ongoingDeliveries = Delivery::whereIn('status', ['assigned', 'picked_up', 'on_delivery'])->count();
            }
            
            // Pengiriman tertunda (melebihi estimated time)
            if ($isStaffLogistik) {
                $delayedDeliveries = Delivery::where('user_id', $user->id)
                    ->where('status', '!=', 'delivered')
                    ->where('status', '!=', 'cancelled')
                    ->where('status', '!=', 'failed')
                    ->whereDate('estimated_delivery_time', '<', now())
                    ->count();
            } else {
                $delayedDeliveries = Delivery::where('status', '!=', 'delivered')
                    ->where('status', '!=', 'cancelled')
                    ->where('status', '!=', 'failed')
                    ->whereDate('estimated_delivery_time', '<', now())
                    ->count();
            }
            
            // TOTAL BARANG DIKIRIM (berdasarkan range filter)
            if ($chartRange == 7) {
                $startDate = now()->subDays(7);
            } elseif ($chartRange == 30) {
                $startDate = now()->subDays(30);
            } else {
                $startDate = now()->subDays(90);
            }
            
            if ($isStaffLogistik) {
                $totalItemsShipped = Delivery::where('user_id', $user->id)
                    ->where('created_at', '>=', $startDate)
                    ->sum('total_items') ?? 0;
                $totalWeight = Delivery::where('user_id', $user->id)
                    ->where('created_at', '>=', $startDate)
                    ->sum('total_weight') ?? 0;
                $totalVolume = Delivery::where('user_id', $user->id)
                    ->where('created_at', '>=', $startDate)
                    ->sum('total_volume') ?? 0;
            } else {
                $totalItemsShipped = Delivery::where('created_at', '>=', $startDate)->sum('total_items') ?? 0;
                $totalWeight = Delivery::where('created_at', '>=', $startDate)->sum('total_weight') ?? 0;
                $totalVolume = Delivery::where('created_at', '>=', $startDate)->sum('total_volume') ?? 0;
            }
            
            // ON-TIME DELIVERY RATE (berdasarkan range filter)
            if ($isStaffLogistik) {
                $totalDeliveriesInRange = Delivery::where('user_id', $user->id)
                    ->where('created_at', '>=', $startDate)
                    ->count();
                
                $onTimeDeliveries = Delivery::where('user_id', $user->id)
                    ->where('created_at', '>=', $startDate)
                    ->where('status', 'delivered')
                    ->where(function($query) {
                        $query->whereNull('estimated_delivery_time')
                            ->orWhereRaw('delivered_at <= estimated_delivery_time');
                    })
                    ->count();
            } else {
                $totalDeliveriesInRange = Delivery::where('created_at', '>=', $startDate)->count();
                
                $onTimeDeliveries = Delivery::where('created_at', '>=', $startDate)
                    ->where('status', 'delivered')
                    ->where(function($query) {
                        $query->whereNull('estimated_delivery_time')
                            ->orWhereRaw('delivered_at <= estimated_delivery_time');
                    })
                    ->count();
            }
            
            $onTimeRate = $totalDeliveriesInRange > 0
                ? round(($onTimeDeliveries / $totalDeliveriesInRange) * 100, 0)
                : 100;
            
            // ========== PENGIRIMAN AKTIF ==========
            if ($isStaffLogistik) {
                $activeDeliveries = Delivery::with(['user', 'vehicle', 'transaction'])
                    ->where('user_id', $user->id)
                    ->whereIn('status', ['assigned', 'picked_up', 'on_delivery'])
                    ->orderBy('created_at', 'desc')
                    ->limit(5)
                    ->get()
                    ->map(function ($delivery) {
                        $progress = [
                            'assigned' => 30,
                            'picked_up' => 50,
                            'on_delivery' => 75,
                            'delivered' => 100
                        ];
                        
                        $statusLabels = [
                            'assigned' => 'Ditugaskan',
                            'picked_up' => 'Diambil',
                            'on_delivery' => 'Dalam Perjalanan',
                            'delivered' => 'Terkirim'
                        ];
                        
                        $delivery->progress = $progress[$delivery->status] ?? 0;
                        $delivery->status_label = $statusLabels[$delivery->status] ?? ucfirst(str_replace('_', ' ', $delivery->status));
                        $delivery->items_count = $delivery->total_items ?? 0;
                        $delivery->driver_name = $delivery->user->name ?? 'N/A';
                        $delivery->vehicle_number = $delivery->vehicle->license_plate ?? 'N/A';
                        $delivery->from_location = $delivery->origin ?? 'Gudang Utama';
                        $delivery->to_location = $delivery->destination ?? 'Toko Cabang';
                        $delivery->eta = $delivery->estimated_delivery_time
                            ? \Carbon\Carbon::parse($delivery->estimated_delivery_time)->format('H:i')
                            : '15:00';
                        
                        return $delivery;
                    });
            } else {
                $activeDeliveries = Delivery::with(['user', 'vehicle', 'transaction'])
                    ->whereIn('status', ['assigned', 'picked_up', 'on_delivery'])
                    ->orderBy('created_at', 'desc')
                    ->limit(5)
                    ->get()
                    ->map(function ($delivery) {
                        $progress = [
                            'assigned' => 30,
                            'picked_up' => 50,
                            'on_delivery' => 75,
                            'delivered' => 100
                        ];
                        
                        $statusLabels = [
                            'assigned' => 'Ditugaskan',
                            'picked_up' => 'Diambil',
                            'on_delivery' => 'Dalam Perjalanan',
                            'delivered' => 'Terkirim'
                        ];
                        
                        $delivery->progress = $progress[$delivery->status] ?? 0;
                        $delivery->status_label = $statusLabels[$delivery->status] ?? ucfirst(str_replace('_', ' ', $delivery->status));
                        $delivery->items_count = $delivery->total_items ?? 0;
                        $delivery->driver_name = $delivery->user->name ?? 'N/A';
                        $delivery->vehicle_number = $delivery->vehicle->license_plate ?? 'N/A';
                        $delivery->from_location = $delivery->origin ?? 'Gudang Utama';
                        $delivery->to_location = $delivery->destination ?? 'Toko Cabang';
                        $delivery->eta = $delivery->estimated_delivery_time
                            ? \Carbon\Carbon::parse($delivery->estimated_delivery_time)->format('H:i')
                            : '15:00';
                        
                        return $delivery;
                    });
            }
            
            // ========== STATUS ARMADA ==========
            if (Schema::hasTable('vehicles') && class_exists(\App\Models\Vehicle::class)) {
                $fleetStatus = \App\Models\Vehicle::select('id', 'name', 'type', 'license_plate', 'status')
                    ->orderByRaw("FIELD(status, 'available', 'in_use', 'maintenance')")
                    ->limit(5)
                    ->get()
                    ->map(function ($vehicle) {
                        $statusMap = [
                            'available' => 'Tersedia',
                            'in_use' => 'Sedang Digunakan',
                            'maintenance' => 'Servis'
                        ];
                        
                        $statusColors = [
                            'available' => 'text-green-600',
                            'in_use' => 'text-blue-600',
                            'maintenance' => 'text-amber-600'
                        ];
                        
                        $vehicle->status_display = $statusMap[$vehicle->status] ?? 'Unknown';
                        $vehicle->status_color = $statusColors[$vehicle->status] ?? 'text-gray-600';
                        return $vehicle;
                    });
            } else {
                $fleetStatus = collect();
            }
            
            // ========== DATA STATISTIK TAMBAHAN ==========
            $totalFleet = Schema::hasTable('vehicles') ? \App\Models\Vehicle::count() : 0;
            $availableFleet = Schema::hasTable('vehicles') ? \App\Models\Vehicle::where('status', 'available')->count() : 0;
            $activeRoutes = Delivery::whereIn('status', ['assigned', 'picked_up', 'on_delivery'])->count();
            $totalDrivers = User::whereIn('role', ['logistik', 'staff_logistik'])->count();
            $activeDrivers = User::whereIn('role', ['logistik', 'staff_logistik'])
                ->whereHas('deliveries', function($query) {
                    $query->whereIn('status', ['assigned', 'picked_up', 'on_delivery']);
                })
                ->count();
            
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
            'availableFleet',
            'activeRoutes',
            'totalDrivers',
            'activeDrivers',
            'isStaffLogistik',
            'chartData',
            'chartRange'
        ));
        
        } catch (\Exception $e) {
            \Log::error('Logistik Dashboard Error: ' . $e->getMessage());
            \Log::error('Stack trace: ' . $e->getTraceAsString());
            
            // Return default view dengan data kosong
            return view('dashboard.logistik', [
                'todayDeliveries' => 0,
                'completedDeliveries' => 0,
                'ongoingDeliveries' => 0,
                'delayedDeliveries' => 0,
                'totalItemsShipped' => 0,
                'totalWeight' => 0,
                'totalVolume' => 0,
                'onTimeRate' => 100,
                'activeDeliveries' => collect(),
                'fleetStatus' => collect(),
                'totalFleet' => 0,
                'availableFleet' => 0,
                'activeRoutes' => 0,
                'totalDrivers' => 0,
                'activeDrivers' => 0,
                'isStaffLogistik' => false,
                'chartRange' => 7,
                'chartData' => [
                    'labels' => [],
                    'total_deliveries' => [],
                    'on_time_deliveries' => []
                ]
            ]);
        }
    }

    /**
     * Get chart data for delivery dashboard dengan range
     * 
     * @param int|null $userId
     * @param int $days
     * @return array
     */
    private function getDeliveryChartData($userId = null, $days = 7)
    {
        $labels = [];
        $totalDeliveries = [];
        $onTimeDeliveries = [];
        
        // Tentukan format label berdasarkan range
        $useDateLabels = $days > 30;
        
        for ($i = $days - 1; $i >= 0; $i--) {
            $date = now()->subDays($i);
            $dateString = $date->format('Y-m-d');
            
            // Label untuk chart berdasarkan range
            if ($days <= 7) {
                $labels[] = $date->isoFormat('dddd'); // Senin, Selasa, Rabu
            } elseif ($days <= 30) {
                $labels[] = $date->format('d M'); // 01 Jan, 02 Jan
            } else {
                $labels[] = $date->format('d/m'); // 01/01, 02/01
            }
            
            // Query untuk total pengiriman per hari (SEMUA STATUS)
            $totalQuery = Delivery::whereDate('created_at', $dateString);
            if ($userId) {
                $totalQuery->where('user_id', $userId);
            }
            $totalDeliveries[] = (clone $totalQuery)->count();
            
            // Query untuk pengiriman yang selesai (status = 'delivered')
            $onTimeQuery = Delivery::whereDate('created_at', $dateString)
                ->where('status', 'delivered');
            if ($userId) {
                $onTimeQuery->where('user_id', $userId);
            }
            $onTimeDeliveries[] = (clone $onTimeQuery)->count();
        }
        
        return [
            'labels' => $labels,
            'total_deliveries' => $totalDeliveries,
            'on_time_deliveries' => $onTimeDeliveries,
        ];
    }

    /**
     * API endpoint untuk mendapatkan data chart logistik berdasarkan range
     * 
     * @param int $range
     * @return \Illuminate\Http\JsonResponse
     */
    public function getLogistikChartData($range = 7)
    {
        try {
            $user = auth()->user();
            $isStaffLogistik = in_array($user->role, ['logistik', 'staff_logistik']);
            
            // Validasi range
            $allowedRanges = [7, 30, 90];
            if (!in_array($range, $allowedRanges)) {
                $range = 7;
            }
            
            // Ambil data chart
            $chartData = $this->getDeliveryChartData(
                $isStaffLogistik ? $user->id : null, 
                $range
            );
            
            // Hitung ringkasan statistik berdasarkan range
            $startDate = now()->subDays($range);
            
            if ($isStaffLogistik) {
                $totalItemsShipped = Delivery::where('user_id', $user->id)
                    ->where('created_at', '>=', $startDate)
                    ->sum('total_items') ?? 0;
                    
                $totalDeliveries = Delivery::where('user_id', $user->id)
                    ->where('created_at', '>=', $startDate)
                    ->count();
                    
                $onTimeDeliveries = Delivery::where('user_id', $user->id)
                    ->where('created_at', '>=', $startDate)
                    ->where('status', 'delivered')
                    ->where(function($query) {
                        $query->whereNull('estimated_delivery_time')
                            ->orWhereRaw('delivered_at <= estimated_delivery_time');
                    })
                    ->count();
            } else {
                $totalItemsShipped = Delivery::where('created_at', '>=', $startDate)
                    ->sum('total_items') ?? 0;
                    
                $totalDeliveries = Delivery::where('created_at', '>=', $startDate)->count();
                
                $onTimeDeliveries = Delivery::where('created_at', '>=', $startDate)
                    ->where('status', 'delivered')
                    ->where(function($query) {
                        $query->whereNull('estimated_delivery_time')
                            ->orWhereRaw('delivered_at <= estimated_delivery_time');
                    })
                    ->count();
            }
            
            $onTimeRate = $totalDeliveries > 0 
                ? round(($onTimeDeliveries / $totalDeliveries) * 100, 0) 
                : 100;
            
            return response()->json([
                'success' => true,
                'data' => $chartData,
                'summary' => [
                    'total_items_shipped' => $totalItemsShipped,
                    'total_deliveries' => $totalDeliveries,
                    'on_time_deliveries' => $onTimeDeliveries,
                    'on_time_rate' => $onTimeRate
                ]
            ]);
            
        } catch (\Exception $e) {
            \Log::error('Error getting logistik chart data: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }
        
    // =====================
    // CHECKER BARANG DASHBOARD
    // =====================
    public function checkerDashboard()
    {
        // STATISTIK UMUM
        $totalProducts = Product::count();
        $totalCategories = Category::count();
        $activeProducts = Product::where('is_active', true)->count();

        // STOK RENDAH
        $lowStockProducts = Product::where(function ($query) {
            $query->where('stock', '<', 10)
                ->orWhereRaw('stock < COALESCE(min_stock, 10)');
        })->count();

        // PRODUK AKAN KADALUARSA (HANYA YANG MASIH > 0 HARI)
        $expiringSoonCount = Product::whereNotNull('expiry_date')
            ->where('expiry_date', '>', now())  // HANYA YANG BELUM EXPIRED
            ->where('expiry_date', '<=', now()->addDays(30))
            ->count();

        $expiredCount = Product::whereNotNull('expiry_date')
            ->where('expiry_date', '<', now())
            ->count();

        // DAFTAR PRODUK STOK RENDAH
        $lowStockItems = Product::with('category')
            ->where(function ($query) {
                $query->where('stock', '<', 10)
                    ->orWhereRaw('stock < COALESCE(min_stock, 10)');
            })
            ->orderBy('stock', 'asc')
            ->limit(10)
            ->get()
            ->map(function ($product) {
                $product->stock_percentage = $product->min_stock > 0 
                    ? min(100, ($product->stock / $product->min_stock) * 100) 
                    : 0;
                return $product;
            });

        // DAFTAR PRODUK AKAN KADALUARSA (HANYA YANG MASIH > 0 HARI)
        $expiringProducts = Product::with('category')
            ->whereNotNull('expiry_date')
            ->where('expiry_date', '>', now())  // HANYA YANG BELUM EXPIRED
            ->where('expiry_date', '<=', now()->addDays(30))
            ->orderBy('expiry_date', 'asc')
            ->limit(10)
            ->get()
            ->map(function ($product) {
                $expiryDate = Carbon::parse($product->expiry_date);
                $now = Carbon::now();
                
                // Hitung selisih hari
                $daysLeft = (int) $now->diffInDays($expiryDate, false);
                
                // Pastikan tidak negatif (sudah expired)
                if ($daysLeft < 0) {
                    $daysLeft = 0;
                }
                
                $product->days_left = $daysLeft;
                
                // Tentukan tampilan hari
                if ($daysLeft == 0) {
                    $product->days_display = 'Hari ini';
                } else {
                    $product->days_display = $daysLeft . ' hari lagi';
                }
                
                // Tentukan kelas warna berdasarkan sisa hari
                if ($daysLeft <= 3) {
                    $product->status_class = 'bg-red-100 text-red-800';
                    $product->status_icon = 'fas fa-exclamation-circle';
                } elseif ($daysLeft <= 7) {
                    $product->status_class = 'bg-orange-100 text-orange-800';
                    $product->status_icon = 'fas fa-clock';
                } else {
                    $product->status_class = 'bg-yellow-100 text-yellow-800';
                    $product->status_icon = 'fas fa-calendar-day';
                }
                
                return $product;
            });

        // DAFTAR PRODUK EXPIRED
        $expiredProducts = Product::with('category')
            ->whereNotNull('expiry_date')
            ->where('expiry_date', '<', now())
            ->orderBy('expiry_date', 'desc')
            ->limit(10)
            ->get()
            ->map(function ($product) {
                $daysExpired = (int) floor(Carbon::parse($product->expiry_date)->startOfDay()->diffInDays(now()->startOfDay(), false));
                $product->days_expired = abs($daysExpired);
                return $product;
            });

        // RIWAYAT LAPORAN CHECKER
        $recentReports = CheckerReport::with(['product', 'reportedBy'])
            ->where('reported_by', auth()->id())
            ->orWhereIn('status', ['pending', 'in_progress'])
            ->latest()
            ->limit(10)
            ->get();

        return view('dashboard.checker', compact(
            'totalProducts',
            'totalCategories',
            'activeProducts',
            'lowStockProducts',
            'expiringSoonCount',
            'expiredCount',
            'lowStockItems',
            'expiringProducts',
            'expiredProducts',
            'recentReports'
        ));
    }
}