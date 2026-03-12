@extends('layouts.app')

@section('title', 'Laporan Sistem')

@section('content')
<div class="min-h-screen bg-gradient-to-br from-blue-50 via-white to-blue-50 p-4 md:p-6">
    <div class="max-w-7xl mx-auto">
        <!-- Header -->
        <div class="mb-8">
            <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 mb-6">
                <div>
                    <nav class="flex items-center space-x-2 text-sm text-gray-600 mb-3">
                        <a href="{{ route('dashboard') }}" class="hover:text-blue-600 transition-colors">
                            <i class="fas fa-home"></i>
                        </a>
                        <i class="fas fa-chevron-right text-xs"></i>
                        <a href="{{ route('reports.index') }}" class="hover:text-blue-600 transition-colors">
                            Laporan
                        </a>
                        <i class="fas fa-chevron-right text-xs"></i>
                        <span class="text-blue-600 font-medium">Semua Laporan</span>
                    </nav>

                    <div class="flex items-center gap-3">
                        <div class="p-3 bg-gradient-to-r from-blue-600 to-blue-700 rounded-xl shadow-lg">
                            <i class="fas fa-chart-pie text-white text-2xl"></i>
                        </div>
                        <div>
                            <h1 class="text-2xl md:text-3xl font-bold text-gray-900">
                                Sistem Laporan
                            </h1>
                            <p class="text-gray-600 mt-1">
                                Dashboard lengkap untuk analisis penjualan, produk, dan inventaris
                            </p>
                        </div>
                    </div>
                </div>

                <!-- Export Buttons -->
                <div class="flex flex-wrap gap-3">
                    <a href="#help"
                       class="px-4 py-2.5 bg-white border border-gray-300 rounded-lg text-gray-700 hover:bg-gray-50 transition-colors flex items-center gap-2">
                        <i class="fas fa-question-circle text-blue-600"></i>
                        Panduan
                    </a>
                </div>
            </div>

            <!-- Stats Overview -->
            <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                <div class="bg-gradient-to-r from-blue-600 to-blue-700 text-white rounded-xl p-5 shadow-lg">
                    <div class="flex justify-between items-start">
                        <div>
                            <p class="text-blue-100 text-sm mb-1">Total Omzet</p>
                            <h3 class="text-2xl font-bold">Rp {{ number_format($totalRevenue ?? 0, 0, ',', '.') }}</h3>
                        </div>
                        <div class="p-2 bg-white/20 rounded-lg">
                            <i class="fas fa-money-bill-wave text-xl"></i>
                        </div>
                    </div>
                    <div class="mt-4 pt-3 border-t border-blue-500/30">
                        <div class="flex items-center justify-between">
                            <span class="text-blue-100 text-sm">Transaksi</span>
                            <span class="font-medium">{{ $transactionCount ?? 0 }}</span>
                        </div>
                    </div>
                </div>

                <div class="bg-white rounded-xl p-5 shadow border border-gray-200">
                    <div class="flex justify-between items-start">
                        <div>
                            <p class="text-gray-600 text-sm mb-1">Total Produk</p>
                            <h3 class="text-2xl font-bold text-gray-900">{{ $productCount ?? 0 }}</h3>
                        </div>
                        <div class="p-2 bg-blue-100 text-blue-600 rounded-lg">
                            <i class="fas fa-box text-xl"></i>
                        </div>
                    </div>
                    <div class="mt-4 pt-3 border-t border-gray-100">
                        <div class="flex items-center justify-between">
                            <span class="text-gray-500 text-sm">Stok Rendah</span>
                            <span class="font-medium text-orange-600">{{ $lowStockCount ?? 0 }}</span>
                        </div>
                    </div>
                </div>

                <div class="bg-white rounded-xl p-5 shadow border border-gray-200">
                    <div class="flex justify-between items-start">
                        <div>
                            <p class="text-gray-600 text-sm mb-1">Rata-rata Transaksi</p>
                            <h3 class="text-2xl font-bold text-gray-900">Rp {{ number_format($averageTransaction ?? 0, 0, ',', '.') }}</h3>
                        </div>
                        <div class="p-2 bg-green-100 text-green-600 rounded-lg">
                            <i class="fas fa-calculator text-xl"></i>
                        </div>
                    </div>
                    <div class="mt-4 pt-3 border-t border-gray-100">
                        <div class="flex items-center justify-between">
                            <span class="text-gray-500 text-sm">Per Transaksi</span>
                            <span class="font-medium text-green-600">+12%</span>
                        </div>
                    </div>
                </div>

                <div class="bg-white rounded-xl p-5 shadow border border-gray-200">
                    <div class="flex justify-between items-start">
                        <div>
                            <p class="text-gray-600 text-sm mb-1">Pelanggan Aktif</p>
                            <h3 class="text-2xl font-bold text-gray-900">{{ $customerCount ?? 0 }}</h3>
                        </div>
                        <div class="p-2 bg-purple-100 text-purple-600 rounded-lg">
                            <i class="fas fa-users text-xl"></i>
                        </div>
                    </div>
                    <div class="mt-4 pt-3 border-t border-gray-100">
                        <div class="flex items-center justify-between">
                            <span class="text-gray-500 text-sm">Hari Ini</span>
                            <span class="font-medium text-blue-600">{{ $todayCustomers ?? 0 }}</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Filter Section -->
        <div class="bg-white rounded-xl shadow border border-gray-200 mb-6 overflow-hidden">
            <div class="border-b border-gray-200 px-6 py-4">
                <div class="flex items-center gap-3">
                    <div class="p-2 bg-blue-100 text-blue-600 rounded-lg">
                        <i class="fas fa-filter"></i>
                    </div>
                    <div>
                        <h3 class="font-semibold text-gray-900">Filter Periode</h3>
                        <p class="text-sm text-gray-600">Pilih rentang waktu untuk laporan</p>
                    </div>
                </div>
            </div>
            <div class="p-6">
                <form method="GET" action="{{ route('reports.index') }}" class="space-y-4">
                    <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">
                                <i class="fas fa-calendar-day text-blue-600 mr-2"></i>
                                Tanggal Mulai
                            </label>
                            <input type="date" name="start_date" value="{{ request('start_date') }}"
                                   class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-colors">
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">
                                <i class="fas fa-calendar-day text-blue-600 mr-2"></i>
                                Tanggal Akhir
                            </label>
                            <input type="date" name="end_date" value="{{ request('end_date') }}"
                                   class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-colors">
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">
                                <i class="fas fa-user text-blue-600 mr-2"></i>
                                Kasir
                            </label>
                            <select name="cashier_id" class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-colors">
                                <option value="all">Semua Kasir</option>
                                @foreach($cashiers as $cashier)
                                <option value="{{ $cashier->id }}" {{ request('cashier_id') == $cashier->id ? 'selected' : '' }}>
                                    {{ $cashier->name }}
                                </option>
                                @endforeach
                            </select>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">
                                <i class="fas fa-sort text-blue-600 mr-2"></i>
                                Urutkan
                            </label>
                            <select name="sort" class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-colors">
                                <option value="latest" {{ request('sort') == 'latest' ? 'selected' : '' }}>Terbaru</option>
                                <option value="oldest" {{ request('sort') == 'oldest' ? 'selected' : '' }}>Terlama</option>
                                <option value="highest" {{ request('sort') == 'highest' ? 'selected' : '' }}>Total Tertinggi</option>
                            </select>
                        </div>
                    </div>

                    <div class="flex flex-wrap gap-3 pt-2">
                        <button type="submit" class="px-5 py-2.5 bg-gradient-to-r from-blue-600 to-blue-700 text-white font-medium rounded-lg hover:from-blue-700 hover:to-blue-800 transition-all flex items-center gap-2 shadow-sm">
                            <i class="fas fa-filter"></i>
                            Terapkan Filter
                        </button>
                        <a href="{{ route('reports.index') }}" class="px-5 py-2.5 bg-white border border-gray-300 text-gray-700 font-medium rounded-lg hover:bg-gray-50 transition-colors flex items-center gap-2">
                            <i class="fas fa-redo"></i>
                            Reset
                        </a>
                        <button type="button" onclick="window.print()" class="px-5 py-2.5 bg-white border border-gray-300 text-gray-700 font-medium rounded-lg hover:bg-gray-50 transition-colors flex items-center gap-2">
                            <i class="fas fa-print"></i>
                            Cetak
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <!-- Report Tabs -->
        <div class="mb-6">
            <div class="border-b border-gray-200">
                <nav class="flex space-x-8">
                    <button onclick="showTab('sales')"
                            id="sales-tab"
                            class="report-tab py-4 px-1 border-b-2 border-transparent font-medium text-gray-500 hover:text-gray-700 hover:border-gray-300 transition-colors flex items-center gap-2">
                        <i class="fas fa-shopping-cart"></i>
                        Laporan Penjualan
                    </button>
                    <button onclick="showTab('products')"
                            id="products-tab"
                            class="report-tab py-4 px-1 border-b-2 border-transparent font-medium text-gray-500 hover:text-gray-700 hover:border-gray-300 transition-colors flex items-center gap-2">
                        <i class="fas fa-box"></i>
                        Laporan Produk
                    </button>
                    <button onclick="showTab('inventory')"
                            id="inventory-tab"
                            class="report-tab py-4 px-1 border-b-2 border-transparent font-medium text-gray-500 hover:text-gray-700 hover:border-gray-300 transition-colors flex items-center gap-2">
                        <i class="fas fa-warehouse"></i>
                        Laporan Inventaris
                    </button>
                </nav>
            </div>
        </div>

        <!-- Sales Report Tab -->
        <div id="sales-report" class="report-content">
            <div class="bg-white rounded-xl shadow border border-gray-200 overflow-hidden mb-6">
                <div class="border-b border-gray-200 px-6 py-4">
                    <div class="flex items-center justify-between">
                        <div class="flex items-center gap-3">
                            <div class="p-2 bg-blue-100 text-blue-600 rounded-lg">
                                <i class="fas fa-chart-line"></i>
                            </div>
                            <div>
                                <h3 class="font-semibold text-gray-900">Laporan Penjualan</h3>
                                <p class="text-sm text-gray-600">Detail transaksi penjualan</p>
                            </div>
                        </div>
                        <form action="{{ route('reports.export.pdf') }}" method="POST">
                            @csrf
                            <input type="hidden" name="type" value="sales">
                            <input type="hidden" name="start_date" value="{{ request('start_date') }}">
                            <input type="hidden" name="end_date" value="{{ request('end_date') }}">
                            <button type="submit" class="px-4 py-2 bg-red-600 text-white font-medium rounded-lg hover:bg-red-700 transition-colors flex items-center gap-2">
                                <i class="fas fa-file-pdf"></i>
                                Export PDF
                            </button>
                        </form>
                    </div>
                </div>

                <div class="overflow-x-auto">
                    <table class="w-full">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">No</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Tanggal</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Invoice</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Kasir</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Customer</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Total</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200">
                            @forelse($transactions as $transaction)
                            <tr class="hover:bg-gray-50 transition-colors">
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                                    {{ ($transactions->currentPage() - 1) * $transactions->perPage() + $loop->iteration }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                    <div class="flex items-center gap-2">
                                        <div class="p-1 bg-blue-100 text-blue-600 rounded">
                                            <i class="fas fa-calendar text-xs"></i>
                                        </div>
                                        {{ $transaction->created_at->format('d/m/Y') }}
                                    </div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 font-mono">
                                    {{ $transaction->invoice_number }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                    <div class="flex items-center gap-2">
                                        <div class="w-8 h-8 bg-blue-100 text-blue-600 rounded-full flex items-center justify-center">
                                            <i class="fas fa-user text-xs"></i>
                                        </div>
                                        {{ $transaction->user->name ?? '-' }}
                                    </div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                    {{ $transaction->customer_name }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-semibold text-green-600">
                                    Rp {{ number_format($transaction->total_amount, 0, ',', '.') }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    @if($transaction->payment_method == 'credit_card')
                                    <span class="px-2 py-1 text-xs font-medium bg-yellow-100 text-yellow-800 rounded-full">
                                        <i class="fas fa-clock mr-1"></i> Hutang
                                    </span>
                                    @else
                                    <span class="px-2 py-1 text-xs font-medium bg-green-100 text-green-800 rounded-full">
                                        <i class="fas fa-check mr-1"></i> Lunas
                                    </span>
                                    @endif
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm">
                                    <a href="{{ route('transactions.show', $transaction) }}"
                                       class="px-3 py-1 bg-blue-600 text-white text-xs font-medium rounded hover:bg-blue-700 transition-colors inline-flex items-center gap-1">
                                        <i class="fas fa-eye text-xs"></i> Detail
                                    </a>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="8" class="px-6 py-12 text-center">
                                    <div class="text-gray-400 mb-3">
                                        <i class="fas fa-inbox text-4xl"></i>
                                    </div>
                                    <p class="text-gray-500">Tidak ada data transaksi</p>
                                </td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                @if($transactions->hasPages())
                <div class="border-t border-gray-200 px-6 py-4">
                    <div class="flex items-center justify-between">
                        <div class="text-sm text-gray-700">
                            Menampilkan {{ $transactions->firstItem() }} - {{ $transactions->lastItem() }} dari {{ $transactions->total() }} transaksi
                        </div>
                        <div>
                            {{ $transactions->links() }}
                        </div>
                    </div>
                </div>
                @endif
            </div>
        </div>

        <!-- Products Report Tab -->
        <div id="products-report" class="report-content hidden">
            <div class="bg-white rounded-xl shadow border border-gray-200 overflow-hidden mb-6">
                <div class="border-b border-gray-200 px-6 py-4">
                    <div class="flex items-center justify-between">
                        <div class="flex items-center gap-3">
                            <div class="p-2 bg-blue-100 text-blue-600 rounded-lg">
                                <i class="fas fa-box"></i>
                            </div>
                            <div>
                                <h3 class="font-semibold text-gray-900">Laporan Produk</h3>
                                <p class="text-sm text-gray-600">Data produk dan penjualan</p>
                            </div>
                        </div>
                        <a href="{{ route('reports.best-selling') }}"
                           class="px-4 py-2 bg-blue-600 text-white font-medium rounded-lg hover:bg-blue-700 transition-colors flex items-center gap-2">
                            <i class="fas fa-chart-bar"></i>
                            Produk Terlaris
                        </a>
                    </div>
                </div>

                <div class="overflow-x-auto">
                    <table class="w-full">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">No</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Produk</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Kategori</th>
                                <th class="px6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Harga</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Stok</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Terjual</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Pendapatan</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200">
                            @forelse($products as $product)
                            <tr class="hover:bg-gray-50 transition-colors">
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                                    {{ $loop->iteration }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="flex items-center gap-3">
                                        <div class="w-10 h-10 bg-gray-100 rounded-lg flex items-center justify-center">
                                            <i class="fas fa-box text-gray-400"></i>
                                        </div>
                                        <div>
                                            <div class="font-medium text-gray-900">{{ $product->name }}</div>
                                            <div class="text-xs text-gray-500">{{ $product->code }}</div>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                    <span class="px-2 py-1 text-xs font-medium bg-gray-100 text-gray-800 rounded">
                                        {{ $product->category->name ?? '-' }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                                    Rp {{ number_format($product->price, 0, ',', '.') }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    @if($product->stock > 20)
                                    <span class="px-2 py-1 text-xs font-medium bg-green-100 text-green-800 rounded-full">
                                        {{ $product->stock }}
                                    </span>
                                    @elseif($product->stock > 0)
                                    <span class="px-2 py-1 text-xs font-medium bg-yellow-100 text-yellow-800 rounded-full">
                                        {{ $product->stock }}
                                    </span>
                                    @else
                                    <span class="px-2 py-1 text-xs font-medium bg-red-100 text-red-800 rounded-full">
                                        Habis
                                    </span>
                                    @endif
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                    {{ $product->sold_count ?? 0 }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-semibold text-blue-600">
                                    Rp {{ number_format($product->revenue ?? 0, 0, ',', '.') }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    @if($product->stock > 20)
                                    <span class="px-2 py-1 text-xs font-medium bg-green-100 text-green-800 rounded-full">
                                        <i class="fas fa-check mr-1"></i> Baik
                                    </span>
                                    @elseif($product->stock > 0)
                                    <span class="px-2 py-1 text-xs font-medium bg-yellow-100 text-yellow-800 rounded-full">
                                        <i class="fas fa-exclamation mr-1"></i> Menipis
                                    </span>
                                    @else
                                    <span class="px-2 py-1 text-xs font-medium bg-red-100 text-red-800 rounded-full">
                                        <i class="fas fa-times mr-1"></i> Habis
                                    </span>
                                    @endif
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="8" class="px-6 py-12 text-center">
                                    <div class="text-gray-400 mb-3">
                                        <i class="fas fa-box-open text-4xl"></i>
                                    </div>
                                    <p class="text-gray-500">Tidak ada data produk</p>
                                </td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                @if($products->hasPages())
                <div class="border-t border-gray-200 px-6 py-4">
                    <div class="flex items-center justify-between">
                        <div class="text-sm text-gray-700">
                            Menampilkan {{ $products->firstItem() }} - {{ $products->lastItem() }} dari {{ $products->total() }} produk
                        </div>
                        <div>
                            {{ $products->links() }}
                        </div>
                    </div>
                </div>
                @endif
            </div>
        </div>

        <!-- Inventory Report Tab -->
        <div id="inventory-report" class="report-content hidden">
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                <!-- Low Stock Products -->
                <div class="lg:col-span-2 bg-white rounded-xl shadow border border-gray-200 overflow-hidden">
                    <div class="border-b border-gray-200 px-6 py-4">
                        <div class="flex items-center justify-between">
                            <div class="flex items-center gap-3">
                                <div class="p-2 bg-orange-100 text-orange-600 rounded-lg">
                                    <i class="fas fa-exclamation-triangle"></i>
                                </div>
                                <div>
                                    <h3 class="font-semibold text-gray-900">Stok Menipis</h3>
                                    <p class="text-sm text-gray-600">Produk yang perlu restok</p>
                                </div>
                            </div>
                            <span class="px-3 py-1 bg-orange-100 text-orange-700 text-sm font-medium rounded-full">
                                {{ $lowStockProducts->count() }} Produk
                            </span>
                        </div>
                    </div>

                    <div class="overflow-x-auto">
                        <table class="w-full">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Produk</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Stok</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Minimum</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-200">
                                @forelse($lowStockProducts as $product)
                                <tr class="hover:bg-orange-50/30 transition-colors">
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="flex items-center gap-3">
                                            <div class="w-10 h-10 bg-orange-100 text-orange-600 rounded-lg flex items-center justify-center">
                                                <i class="fas fa-box"></i>
                                            </div>
                                            <div>
                                                <div class="font-medium text-gray-900">{{ $product->name }}</div>
                                                <div class="text-xs text-gray-500">{{ $product->code }}</div>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <span class="px-3 py-1 bg-orange-100 text-orange-700 text-sm font-medium rounded-full">
                                            {{ $product->stock }}
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                        10
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <span class="px-2 py-1 text-xs font-medium bg-red-100 text-red-800 rounded-full">
                                            <i class="fas fa-exclamation-triangle mr-1"></i> Segera Restok
                                        </span>
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="4" class="px-6 py-8 text-center">
                                        <div class="text-gray-400 mb-2">
                                            <i class="fas fa-check-circle text-2xl"></i>
                                        </div>
                                        <p class="text-gray-500">Semua stok aman</p>
                                    </td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>

                <!-- Inventory Summary -->
                <div class="bg-white rounded-xl shadow border border-gray-200 p-6">
                    <div class="flex items-center gap-3 mb-6">
                        <div class="p-2 bg-blue-100 text-blue-600 rounded-lg">
                            <i class="fas fa-chart-pie"></i>
                        </div>
                        <div>
                            <h3 class="font-semibold text-gray-900">Ringkasan Inventaris</h3>
                            <p class="text-sm text-gray-600">Status keseluruhan stok</p>
                        </div>
                    </div>

                    <div class="space-y-4">
                        <div>
                            <div class="flex justify-between mb-1">
                                <span class="text-sm font-medium text-gray-700">Stok Aman</span>
                                <span class="text-sm font-medium text-green-600">{{ $inStockCount ?? 0 }}</span>
                            </div>
                            <div class="w-full bg-gray-200 rounded-full h-2">
                                <div class="bg-green-600 h-2 rounded-full" style="width: {{ ($inStockCount/$productCount)*100 }}%"></div>
                            </div>
                        </div>

                        <div>
                            <div class="flex justify-between mb-1">
                                <span class="text-sm font-medium text-gray-700">Stok Menipis</span>
                                <span class="text-sm font-medium text-orange-600">{{ $lowStockCount ?? 0 }}</span>
                            </div>
                            <div class="w-full bg-gray-200 rounded-full h-2">
                                <div class="bg-orange-500 h-2 rounded-full" style="width: {{ ($lowStockCount/$productCount)*100 }}%"></div>
                            </div>
                        </div>

                        <div>
                            <div class="flex justify-between mb-1">
                                <span class="text-sm font-medium text-gray-700">Stok Habis</span>
                                <span class="text-sm font-medium text-red-600">{{ $outOfStockCount ?? 0 }}</span>
                            </div>
                            <div class="w-full bg-gray-200 rounded-full h-2">
                                <div class="bg-red-600 h-2 rounded-full" style="width: {{ ($outOfStockCount/$productCount)*100 }}%"></div>
                            </div>
                        </div>
                    </div>

                    <div class="mt-8 pt-6 border-t border-gray-200">
                        <h4 class="text-sm font-medium text-gray-700 mb-3">Kategori Produk</h4>
                        <div class="space-y-2">
                            @foreach($categories as $category)
                            <div class="flex justify-between items-center">
                                <span class="text-sm text-gray-600">{{ $category->name }}</span>
                                <span class="text-sm font-medium text-gray-900">{{ $category->products_count ?? 0 }}</span>
                            </div>
                            @endforeach
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Help Section -->
        <div id="help" class="bg-white rounded-xl shadow border border-gray-200 p-6 mt-8">
            <h3 class="font-semibold text-gray-900 mb-4 flex items-center gap-2">
                <i class="fas fa-question-circle text-blue-600"></i>
                Panduan Penggunaan Laporan
            </h3>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                <div class="p-4 bg-blue-50 rounded-lg">
                    <div class="flex items-center gap-3 mb-3">
                        <div class="p-2 bg-white text-blue-600 rounded-lg">
                            <i class="fas fa-filter"></i>
                        </div>
                        <h4 class="font-medium text-gray-900">Filter Data</h4>
                    </div>
                    <p class="text-sm text-gray-600">
                        Gunakan filter tanggal, kasir, dan urutan untuk mendapatkan data yang spesifik sesuai kebutuhan analisis Anda.
                    </p>
                </div>

                <div class="p-4 bg-green-50 rounded-lg">
                    <div class="flex items-center gap-3 mb-3">
                        <div class="p-2 bg-white text-green-600 rounded-lg">
                            <i class="fas fa-file-pdf"></i>
                        </div>
                        <h4 class="font-medium text-gray-900">Export PDF</h4>
                    </div>
                    <p class="text-sm text-gray-600">
                        Klik tombol Export PDF untuk mengunduh laporan dalam format PDF. Filter yang aktif akan diterapkan pada file PDF.
                    </p>
                </div>

                <div class="p-4 bg-purple-50 rounded-lg">
                    <div class="flex items-center gap-3 mb-3">
                        <div class="p-2 bg-white text-purple-600 rounded-lg">
                            <i class="fas fa-chart-line"></i>
                        </div>
                        <h4 class="font-medium text-gray-900">Analisis Data</h4>
                    </div>
                    <p class="text-sm text-gray-600">
                        Gunakan tab yang berbeda untuk menganalisis penjualan, produk, dan inventaris secara terpisah.
                    </p>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    // Tab functionality
    function showTab(tabName) {
        // Hide all tab contents
        document.querySelectorAll('.report-content').forEach(content => {
            content.classList.add('hidden');
        });

        // Remove active class from all tabs
        document.querySelectorAll('.report-tab').forEach(tab => {
            tab.classList.remove('border-blue-600', 'text-blue-600');
            tab.classList.add('border-transparent', 'text-gray-500');
        });

        // Show selected tab content
        document.getElementById(tabName + '-report').classList.remove('hidden');

        // Add active class to selected tab
        document.getElementById(tabName + '-tab').classList.remove('border-transparent', 'text-gray-500');
        document.getElementById(tabName + '-tab').classList.add('border-blue-600', 'text-blue-600');
    }

    // Initialize first tab as active
    document.addEventListener('DOMContentLoaded', function() {
        document.getElementById('sales-tab').classList.add('border-blue-600', 'text-blue-600');
        document.getElementById('sales-report').classList.remove('hidden');

        // Print functionality
        window.printReport = function() {
            window.print();
        };

        // Date range validation
        const startDate = document.querySelector('input[name="start_date"]');
        const endDate = document.querySelector('input[name="end_date"]');

        if (startDate && endDate) {
            startDate.addEventListener('change', function() {
                if (this.value && endDate.value && this.value > endDate.value) {
                    endDate.value = this.value;
                }
            });

            endDate.addEventListener('change', function() {
                if (this.value && startDate.value && this.value < startDate.value) {
                    startDate.value = this.value;
                }
            });
        }
    });

    // Export to Excel
    function exportToExcel(tableId, filename) {
        const table = document.getElementById(tableId);
        const wb = XLSX.utils.table_to_book(table, {sheet: "Sheet1"});
        XLSX.writeFile(wb, filename + '.xlsx');
    }
</script>
@endpush

@push('styles')
<style>
    .report-tab {
        transition: all 0.2s ease;
        position: relative;
    }

    .report-tab:hover {
        color: #2563eb;
    }

    .report-tab.border-blue-600 {
        border-bottom-width: 2px;
        border-bottom-color: #2563eb;
    }

    .report-content {
        animation: fadeIn 0.3s ease;
    }

    @keyframes fadeIn {
        from {
            opacity: 0;
            transform: translateY(10px);
        }
        to {
            opacity: 1;
            transform: translateY(0);
        }
    }

    /* Custom scrollbar */
    .overflow-x-auto::-webkit-scrollbar {
        height: 6px;
    }

    .overflow-x-auto::-webkit-scrollbar-track {
        background: #f1f1f1;
        border-radius: 3px;
    }

    .overflow-x-auto::-webkit-scrollbar-thumb {
        background: #c1c1c1;
        border-radius: 3px;
    }

    .overflow-x-auto::-webkit-scrollbar-thumb:hover {
        background: #a1a1a1;
    }

    /* Print styles */
    @media print {
        .no-print {
            display: none !important;
        }

        body {
            background: white !important;
        }

        .bg-gradient-to-br {
            background: white !important;
        }

        .shadow, .shadow-lg, .shadow-sm {
            box-shadow: none !important;
        }

        .border, .border-gray-200 {
            border: 1px solid #e5e7eb !important;
        }

        .rounded-xl, .rounded-lg {
            border-radius: 0 !important;
        }

        .p-4, .p-6, .px-6, .py-4 {
            padding: 0 !important;
        }

        .space-y-4 > * + * {
            margin-top: 0 !important;
        }

        .grid {
            display: block !important;
        }

        .gap-4, .gap-6 {
            gap: 0 !important;
        }

        .mb-4, .mb-6, .mb-8 {
            margin-bottom: 0.5rem !important;
        }
    }
</style>
@endpush
