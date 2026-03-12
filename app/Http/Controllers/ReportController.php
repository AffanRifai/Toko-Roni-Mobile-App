<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Transaction;
use App\Models\Product;
use App\Models\User;
use App\Models\Category;
use App\Models\TransactionItem;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use Barryvdh\DomPDF\Facade\Pdf;

class ReportController extends Controller
{
    /**
     * DASHBOARD LAPORAN UTAMA
     */
    public function index(Request $request)
    {
        // Validasi role
        if (!in_array(auth()->user()->role, ['owner', 'manager', 'admin'])) {
            abort(403, 'Anda tidak memiliki akses ke halaman laporan');
        }

        // Stats for header
        $today = Carbon::today();
        $startOfMonth = Carbon::now()->startOfMonth();
        $startOfWeek = Carbon::now()->startOfWeek();

        // Total Revenue
        $totalRevenue = Transaction::sum('total_amount');
        $transactionCount = Transaction::count();
        $productCount = Product::count();
        $customerCount = Transaction::distinct('customer_name')->count('customer_name');
        $todayCustomers = Transaction::whereDate('created_at', $today)
            ->distinct('customer_name')
            ->count('customer_name');
        $averageTransaction = $transactionCount > 0 ? $totalRevenue / $transactionCount : 0;

        // Low stock products
        $lowStockProducts = Product::where('stock', '<=', 10)->where('stock', '>', 0)->get();
        $lowStockCount = $lowStockProducts->count();
        $outOfStockCount = Product::where('stock', '<=', 0)->count();
        $inStockCount = Product::where('stock', '>', 10)->count();

        // Categories with product count
        $categories = Category::withCount('products')->get();

        // Cashiers for filter
        $cashiers = User::whereIn('role', ['kasir', 'admin', 'owner'])->get();

        // Filter transactions for sales report
        $transactionsQuery = Transaction::with('user')->latest();

        if ($request->filled('start_date')) {
            $transactionsQuery->whereDate('created_at', '>=', $request->start_date);
        }

        if ($request->filled('end_date')) {
            $transactionsQuery->whereDate('created_at', '<=', $request->end_date);
        }

        if ($request->filled('cashier_id') && $request->cashier_id != 'all') {
            $transactionsQuery->where('user_id', $request->cashier_id);
        }

        // Sorting
        if ($request->filled('sort')) {
            switch ($request->sort) {
                case 'oldest':
                    $transactionsQuery->oldest();
                    break;
                case 'highest':
                    $transactionsQuery->orderBy('total_amount', 'desc');
                    break;
                case 'lowest':
                    $transactionsQuery->orderBy('total_amount', 'asc');
                    break;
                default:
                    $transactionsQuery->latest();
            }
        }

        $transactions = $transactionsQuery->paginate(10);

        // Products report
        $products = Product::with('category')
            ->withCount(['transactionItems as sold_count' => function ($query) {
                $query->select(DB::raw('COALESCE(SUM(qty), 0)'));
            }])
            ->withSum(['transactionItems as revenue' => function ($query) {
                $query->select(DB::raw('COALESCE(SUM(qty * price), 0)'));
            }])
            ->latest()
            ->paginate(10);

        // Top selling products (last 30 days)
        $topProducts = TransactionItem::select('product_id', DB::raw('COALESCE(SUM(qty), 0) as total_sold'))
            ->with('product')
            ->whereHas('transaction', function ($q) use ($startOfMonth) {
                $q->where('created_at', '>=', $startOfMonth);
            })
            ->groupBy('product_id')
            ->orderByDesc('total_sold')
            ->take(5)
            ->get();

        // Data Chart 7 Hari Terakhir
        $chartData = $this->getDailyRevenueChart(7);

        return view('reports.index', compact(
            'totalRevenue',
            'transactionCount',
            'productCount',
            'customerCount',
            'todayCustomers',
            'averageTransaction',
            'lowStockProducts',
            'lowStockCount',
            'outOfStockCount',
            'inStockCount',
            'categories',
            'cashiers',
            'transactions',
            'products',
            'topProducts',
            'chartData'
        ));
    }

    /**
     * LAPORAN PENJUALAN DETAIL
     */
    /**
     * LAPORAN PENJUALAN DETAIL
     */
    public function salesReport(Request $request)
    {
        if (!in_array(auth()->user()->role, ['owner', 'manager', 'admin'])) {
            abort(403, 'Anda tidak memiliki akses ke halaman laporan');
        }

        // Query untuk pagination
        $transactionsQuery = Transaction::with(['user', 'items.product']);

        // Query untuk summary (tanpa pagination)
        $summaryQuery = Transaction::query();

        // Filter Tanggal untuk kedua query
        if ($request->has('date') && $request->date) {
            $transactionsQuery->whereDate('created_at', $request->date);
            $summaryQuery->whereDate('created_at', $request->date);
        } elseif ($request->has('month') && $request->month) {
            $yearMonth = explode('-', $request->month);
            if (count($yearMonth) == 2) {
                $year = $yearMonth[0];
                $month = $yearMonth[1];
                $transactionsQuery->whereYear('created_at', $year)
                    ->whereMonth('created_at', $month);
                $summaryQuery->whereYear('created_at', $year)
                    ->whereMonth('created_at', $month);
            }
        } elseif ($request->has('start_date') && $request->start_date && $request->has('end_date') && $request->end_date) {
            $startDate = Carbon::parse($request->start_date)->startOfDay();
            $endDate = Carbon::parse($request->end_date)->endOfDay();

            $transactionsQuery->whereBetween('created_at', [$startDate, $endDate]);
            $summaryQuery->whereBetween('created_at', [$startDate, $endDate]);
        } else {
            // Default: Bulan Ini
            $transactionsQuery->whereBetween('created_at', [
                Carbon::now()->startOfMonth(),
                Carbon::now()->endOfMonth()
            ]);
            $summaryQuery->whereBetween('created_at', [
                Carbon::now()->startOfMonth(),
                Carbon::now()->endOfMonth()
            ]);
        }

        // Filter Kasir
        if ($request->has('cashier_id') && $request->cashier_id != 'all') {
            $transactionsQuery->where('user_id', $request->cashier_id);
            $summaryQuery->where('user_id', $request->cashier_id);
        }

        // Filter Metode Pembayaran
        if ($request->has('payment_method') && $request->payment_method != 'all') {
            $transactionsQuery->where('payment_method', $request->payment_method);
            $summaryQuery->where('payment_method', $request->payment_method);
        }

        // Sorting hanya untuk transactionsQuery
        if ($request->has('sort')) {
            switch ($request->sort) {
                case 'oldest':
                    $transactionsQuery->orderBy('created_at', 'asc');
                    break;
                case 'highest':
                    $transactionsQuery->orderBy('total_amount', 'desc');
                    break;
                case 'lowest':
                    $transactionsQuery->orderBy('total_amount', 'asc');
                    break;
                default:
                    $transactionsQuery->orderBy('created_at', 'desc');
            }
        } else {
            $transactionsQuery->orderBy('created_at', 'desc');
        }

        // Eksekusi query
        $transactions = $transactionsQuery->paginate(30);
        $allTransactions = $summaryQuery->get();

        // Ringkasan - SESUAIKAN DENGAN VIEW
        $total = $allTransactions->sum('total_amount');
        $totalCount = $allTransactions->count(); // Nama variabel sesuai view
        $averageTransaction = $totalCount > 0 ? $total / $totalCount : 0;
        $maxTransaction = $allTransactions->max('total_amount'); // Nama variabel sesuai view
        $minTransaction = $allTransactions->min('total_amount'); // Nama variabel sesuai view

        // Tentukan periode untuk ditampilkan
        $period = 'Bulan Ini'; // default

        if ($request->has('date') && $request->date) {
            $period = Carbon::parse($request->date)->translatedFormat('d F Y');
        } elseif ($request->has('month') && $request->month) {
            $period = Carbon::parse($request->month . '-01')->translatedFormat('F Y');
        } elseif ($request->has('start_date') && $request->start_date && $request->has('end_date') && $request->end_date) {
            $period = Carbon::parse($request->start_date)->translatedFormat('d F Y') . ' - ' .
                Carbon::parse($request->end_date)->translatedFormat('d F Y');
        }

        // Kasir List untuk filter
        $cashiers = User::whereIn('role', ['admin', 'kasir', 'owner'])->get();

        // Sesuaikan nama variabel dengan view
        return view('reports.sales', compact(
            'transactions',
            'cashiers',
            'period',
            'total',          // Nama sesuai view
            'totalCount',     // Nama sesuai view
            'averageTransaction',
            'maxTransaction', // Nama sesuai view
            'minTransaction'  // Nama sesuai view
        ));
    }

    /**
     * LAPORAN PRODUK / INVENTORY
     */
    public function inventoryReport(Request $request)
    {
        if (!in_array(auth()->user()->role, ['owner', 'manager', 'admin'])) {
            abort(403, 'Anda tidak memiliki akses ke halaman laporan');
        }

        // Query untuk pagination
        $productsQuery = Product::with('category');

        // Filter Kategori
        if ($request->has('category_id') && $request->category_id != 'all') {
            $productsQuery->where('category_id', $request->category_id);
        }

        // Filter Status Stok
        if ($request->has('stock_status')) {
            switch ($request->stock_status) {
                case 'low':
                    $productsQuery->where('stock', '<=', 10)->where('stock', '>', 0);
                    break;
                case 'out':
                    $productsQuery->where('stock', '<=', 0);
                    break;
                case 'sufficient':
                    $productsQuery->where('stock', '>', 10);
                    break;
            }
        }

        // Filter Status Aktif
        if ($request->has('status') && $request->status != 'all') {
            $productsQuery->where('is_active', $request->status == 'active' ? 1 : 0);
        }

        // Search
        if ($request->has('search')) {
            $search = $request->search;
            $productsQuery->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('code', 'like', "%{$search}%")
                    ->orWhere('description', 'like', "%{$search}%");
            });
        }

        // Sorting
        $sortBy = $request->get('sort_by', 'name');
        $sortOrder = $request->get('sort_order', 'asc');
        $productsQuery->orderBy($sortBy, $sortOrder);

        $products = $productsQuery->paginate(50);

        // Query terpisah untuk summary yang akurat
        $summaryQuery = Product::query();

        // Apply filters yang sama untuk summary
        if ($request->has('category_id') && $request->category_id != 'all') {
            $summaryQuery->where('category_id', $request->category_id);
        }
        if ($request->has('stock_status')) {
            switch ($request->stock_status) {
                case 'low':
                    $summaryQuery->where('stock', '<=', 10)->where('stock', '>', 0);
                    break;
                case 'out':
                    $summaryQuery->where('stock', '<=', 0);
                    break;
                case 'sufficient':
                    $summaryQuery->where('stock', '>', 10);
                    break;
            }
        }
        if ($request->has('status') && $request->status != 'all') {
            $summaryQuery->where('is_active', $request->status == 'active' ? 1 : 0);
        }
        if ($request->has('search')) {
            $search = $request->search;
            $summaryQuery->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('code', 'like', "%{$search}%")
                    ->orWhere('description', 'like', "%{$search}%");
            });
        }

        $allProducts = $summaryQuery->get();

        // Ringkasan Inventory
        $summary = [
            'total_products' => $allProducts->count(),
            'total_stock' => $allProducts->sum('stock'),
            'total_value' => $allProducts->sum(function ($product) {
                return ($product->price ?? 0) * ($product->stock ?? 0);
            }),
            'low_stock' => $allProducts->where('stock', '<=', 10)->where('stock', '>', 0)->count(),
            'out_of_stock' => $allProducts->where('stock', '<=', 0)->count(),
            'average_price' => $allProducts->avg('price') ?? 0,
            'most_expensive' => $allProducts->max('price') ?? 0,
            'cheapest' => $allProducts->min('price') ?? 0,
        ];

        // Kategori List
        $categories = Category::all();

        // Produk Terlaris (30 Hari)
        $topSellingProducts = TransactionItem::select('product_id', DB::raw('COALESCE(SUM(qty), 0) as total_sold'))
            ->with('product')
            ->whereHas('transaction', function ($q) {
                $q->where('created_at', '>=', Carbon::now()->subDays(30));
            })
            ->groupBy('product_id')
            ->orderByDesc('total_sold')
            ->take(10)
            ->get();

        return view('reports.inventory', compact(
            'products',
            'summary',
            'categories',
            'topSellingProducts'
        ));
    }

    /**
     * LAPORAN PRODUK TERLARIS
     */
    public function bestSellingProducts(Request $request)
    {
        if (!in_array(auth()->user()->role, ['owner', 'manager', 'admin'])) {
            abort(403, 'Anda tidak memiliki akses ke halaman laporan');
        }

        $query = TransactionItem::select(
            'product_id',
            DB::raw('COALESCE(SUM(qty), 0) as total_sold'),
            DB::raw('COALESCE(SUM(subtotal), 0) as total_revenue')
        )
            ->with('product')
            ->groupBy('product_id');

        // Filter Periode
        if ($request->has('period')) {
            switch ($request->period) {
                case 'today':
                    $query->whereDate('created_at', Carbon::today());
                    break;
                case 'week':
                    $query->whereBetween('created_at', [
                        Carbon::now()->startOfWeek(),
                        Carbon::now()->endOfWeek()
                    ]);
                    break;
                case 'month':
                    $query->whereBetween('created_at', [
                        Carbon::now()->startOfMonth(),
                        Carbon::now()->endOfMonth()
                    ]);
                    break;
                case 'year':
                    $query->whereYear('created_at', Carbon::now()->year);
                    break;
            }
        }

        // Filter Kategori
        if ($request->has('category_id') && $request->category_id != 'all') {
            $query->whereHas('product', function ($q) use ($request) {
                $q->where('category_id', $request->category_id);
            });
        }

        $products = $query->orderByDesc('total_sold')
            ->paginate(20);

        $categories = Category::all();

        return view('reports.best-selling', compact('products', 'categories'));
    }

    /**
     * EXPORT PDF
     */
    public function exportPDF(Request $request)
    {
        if (!in_array(auth()->user()->role, ['owner', 'manager', 'admin'])) {
            abort(403, 'Anda tidak memiliki akses ke halaman laporan');
        }

        $type = $request->get('type', 'sales');
        $startDate = $request->get('start_date', Carbon::now()->startOfMonth()->format('Y-m-d'));
        $endDate = $request->get('end_date', Carbon::now()->format('Y-m-d'));

        $data = [];

        switch ($type) {
            case 'sales':
                $transactions = Transaction::with(['user', 'items.product'])
                    ->whereBetween('created_at', [
                        Carbon::parse($startDate)->startOfDay(),
                        Carbon::parse($endDate)->endOfDay()
                    ])
                    ->orderBy('created_at', 'desc')
                    ->get();

                $summary = [
                    'total_transactions' => $transactions->count(),
                    'total_revenue' => $transactions->sum('total_amount') ?? 0,
                    'total_items' => TransactionItem::whereIn('transaction_id', $transactions->pluck('id'))->sum('qty') ?? 0,
                    'average_transaction' => $transactions->avg('total_amount') ?? 0,
                ];

                $data = compact('transactions', 'summary', 'startDate', 'endDate');
                $view = 'reports.exports.sales-pdf';
                break;

            case 'inventory':
                $products = Product::with('category')->get();
                $summary = [
                    'total_products' => $products->count(),
                    'total_stock' => $products->sum('stock') ?? 0,
                    'total_value' => $products->sum(function ($p) {
                        return ($p->price ?? 0) * ($p->stock ?? 0);
                    }) ?? 0,
                    'low_stock' => $products->where('stock', '<=', 10)->where('stock', '>', 0)->count(),
                    'out_of_stock' => $products->where('stock', '<=', 0)->count(),
                ];

                $data = compact('products', 'summary');
                $view = 'reports.exports.inventory-pdf';
                break;

            default:
                abort(400, 'Jenis laporan tidak valid');
        }

        $pdf = Pdf::loadView($view, $data)
            ->setPaper('A4', $type == 'sales' ? 'landscape' : 'portrait');

        $filename = "laporan-{$type}-" . Carbon::now()->format('YmdHis') . '.pdf';

        return $pdf->download($filename);
    }

    /**
     * EXPORT LAPORAN PENJUALAN KE PDF
     */
    public function exportSalesPDF(Request $request)
    {
        if (!in_array(auth()->user()->role, ['owner', 'manager', 'admin'])) {
            abort(403, 'Anda tidak memiliki akses ke halaman laporan');
        }

        // Query yang sama dengan salesReport
        $query = Transaction::with(['user', 'items.product']);

        // Filter Tanggal
        if ($request->has('date') && $request->date) {
            $query->whereDate('created_at', $request->date);
        } elseif ($request->has('month') && $request->month) {
            $yearMonth = explode('-', $request->month);
            if (count($yearMonth) == 2) {
                $year = $yearMonth[0];
                $month = $yearMonth[1];
                $query->whereYear('created_at', $year)
                    ->whereMonth('created_at', $month);
            }
        }

        // Filter lainnya
        if ($request->has('cashier_id') && $request->cashier_id != 'all') {
            $query->where('user_id', $request->cashier_id);
        }
        if ($request->has('payment_method') && $request->payment_method != 'all') {
            $query->where('payment_method', $request->payment_method);
        }

        $transactions = $query->orderBy('created_at', 'desc')->get();

        $summary = [
            'total' => $transactions->sum('total_amount') ?? 0,
            'count' => $transactions->count(),
            'average' => $transactions->avg('total_amount') ?? 0,
            'highest' => $transactions->max('total_amount') ?? 0,
            'lowest' => $transactions->min('total_amount') ?? 0,
        ];

        $pdf = Pdf::loadView('reports.exports.sales-pdf', compact('transactions', 'summary'))
            ->setPaper('A4', 'landscape');

        $filename = 'laporan-penjualan-' . Carbon::now()->format('YmdHis') . '.pdf';

        return $pdf->download($filename);
    }

    /**
     * CHART DATA UNTUK DASHBOARD
     */
    private function getDailyRevenueChart($days = 7)
    {
        $data = [];
        $labels = [];
        $revenues = [];
        $transactionCounts = [];

        for ($i = $days - 1; $i >= 0; $i--) {
            $date = Carbon::now()->subDays($i)->format('Y-m-d');
            $labels[] = Carbon::now()->subDays($i)->format('d M');

            $dailyData = Transaction::whereDate('created_at', $date)
                ->select(
                    DB::raw('COALESCE(SUM(total_amount), 0) as revenue'),
                    DB::raw('COUNT(*) as transactions')
                )
                ->first();

            $revenues[] = $dailyData->revenue ?? 0;
            $transactionCounts[] = $dailyData->transactions ?? 0;
        }

        return [
            'labels' => $labels,
            'datasets' => [
                [
                    'label' => 'Pendapatan (Rp)',
                    'data' => $revenues,
                    'backgroundColor' => 'rgba(59, 130, 246, 0.5)',
                    'borderColor' => 'rgb(59, 130, 246)',
                    'borderWidth' => 2,
                    'yAxisID' => 'y',
                ],
                [
                    'label' => 'Jumlah Transaksi',
                    'data' => $transactionCounts,
                    'backgroundColor' => 'rgba(16, 185, 129, 0.5)',
                    'borderColor' => 'rgb(16, 185, 129)',
                    'borderWidth' => 2,
                    'yAxisID' => 'y1',
                ]
            ]
        ];
    }

    /**
     * API: GET SALES CHART DATA
     */
    public function getSalesChartData(Request $request)
    {
        if (!in_array(auth()->user()->role, ['owner', 'manager', 'admin'])) {
            abort(403, 'Anda tidak memiliki akses ke halaman laporan');
        }

        $period = $request->get('period', 'week');

        switch ($period) {
            case 'day':
                return response()->json($this->getHourlySalesData());
            case 'week':
                return response()->json($this->getDailyRevenueChart(7));
            case 'month':
                return response()->json($this->getDailyRevenueChart(30));
            case 'year':
                return response()->json($this->getMonthlyRevenueChart());
            default:
                return response()->json(['error' => 'Periode tidak valid'], 400);
        }
    }

    /**
     * DATA PENJUALAN PER JAM
     */
    private function getHourlySalesData()
    {
        $labels = [];
        $revenues = [];

        for ($i = 0; $i < 24; $i++) {
            $hour = str_pad($i, 2, '0', STR_PAD_LEFT);
            $labels[] = "{$hour}:00";

            $revenue = Transaction::whereDate('created_at', Carbon::today())
                ->whereRaw('HOUR(created_at) = ?', [$i])
                ->sum('total_amount');

            $revenues[] = $revenue ?? 0;
        }

        return [
            'labels' => $labels,
            'datasets' => [[
                'label' => 'Pendapatan per Jam',
                'data' => $revenues,
                'backgroundColor' => 'rgba(139, 92, 246, 0.5)',
                'borderColor' => 'rgb(139, 92, 246)',
                'borderWidth' => 1
            ]]
        ];
    }

    /**
     * DATA PENJUALAN BULANAN
     */
    private function getMonthlyRevenueChart()
    {
        $labels = [];
        $revenues = [];

        for ($i = 1; $i <= 12; $i++) {
            $monthName = Carbon::create()->month($i)->format('M');
            $labels[] = $monthName;

            $revenue = Transaction::whereYear('created_at', Carbon::now()->year)
                ->whereMonth('created_at', $i)
                ->sum('total_amount');

            $revenues[] = $revenue ?? 0;
        }

        return [
            'labels' => $labels,
            'datasets' => [[
                'label' => 'Pendapatan Bulanan',
                'data' => $revenues,
                'backgroundColor' => 'rgba(245, 158, 11, 0.5)',
                'borderColor' => 'rgb(245, 158, 11)',
                'borderWidth' => 2
            ]]
        ];
    }
}
