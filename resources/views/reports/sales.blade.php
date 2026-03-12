@extends('layouts.app')

@section('title', 'Laporan Penjualan')

@section('content')
<div class="min-h-screen bg-gray-50 p-4 md:p-6">
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
                        <span class="text-blue-600 font-medium">Penjualan</span>
                    </nav>

                    <div class="flex items-center gap-3">
                        <div class="p-3 bg-gradient-to-r from-blue-600 to-blue-700 rounded-xl shadow-lg">
                            <i class="fas fa-chart-line text-white text-2xl"></i>
                        </div>
                        <div>
                            <h1 class="text-2xl md:text-3xl font-bold text-gray-900">
                                Laporan Penjualan
                            </h1>
                            <p class="text-gray-600 mt-1">
                                Periode: {{ $period }}
                            </p>
                        </div>
                    </div>
                </div>

                <!-- Export Buttons -->
                <div class="flex flex-wrap gap-3">
                    <form action="{{ route('reports.sales.export') }}" method="POST" class="inline">
                        @csrf
                        <input type="hidden" name="date" value="{{ request('date') }}">
                        <input type="hidden" name="month" value="{{ request('month') }}">
                        <input type="hidden" name="sort" value="{{ request('sort') }}">
                        <button type="submit" class="px-4 py-2.5 bg-red-600 text-white font-medium rounded-lg hover:bg-red-700 transition-colors flex items-center gap-2">
                            <i class="fas fa-file-pdf"></i>
                            Export PDF
                        </button>
                    </form>
                </div>
            </div>

            <!-- Summary Cards with Comparison - Margin Adjusted -->
            <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-8">
                <!-- Card 1: Total Omzet -->
                <div class="bg-gradient-to-r from-blue-600 to-blue-700 text-white rounded-xl p-5 shadow-lg relative overflow-hidden">
                    <!-- Background decoration -->
                    <div class="absolute top-0 right-0 w-20 h-20 bg-white/10 rounded-full -mr-6 -mt-6"></div>
                    <div class="absolute bottom-0 left-0 w-16 h-16 bg-white/10 rounded-full -ml-6 -mb-6"></div>

                    <div class="relative">
                        <div class="flex justify-between items-start">
                            <div>
                                <p class="text-blue-100 text-sm mb-1">Total Omzet</p>
                                <h3 class="text-2xl font-bold">Rp {{ number_format($total, 0, ',', '.') }}</h3>
                            </div>
                            <div class="p-2 bg-white/20 rounded-lg">
                                <i class="fas fa-money-bill-wave text-xl"></i>
                            </div>
                        </div>

                        <!-- Comparison with previous month - With better margin -->
                        @if(isset($comparison) && $comparison['total'] != 0)
                        <div class="mt-5 pt-4 border-t border-blue-500/30">
                            <div class="flex items-center justify-between mb-2">
                                <span class="text-blue-100 text-sm flex items-center gap-1">
                                    <i class="fas fa-calendar-alt text-xs"></i>
                                    vs Bulan Lalu
                                </span>
                                <div class="flex items-center gap-1">
                                    @php
                                        $percentageChange = $comparison['total_percentage'];
                                        $isPositive = $percentageChange > 0;
                                    @endphp
                                    <span class="text-{{ $isPositive ? 'green' : 'red' }}-300 text-sm font-medium px-2 py-0.5 bg-white/10 rounded-full">
                                        <i class="fas fa-{{ $isPositive ? 'arrow-up' : 'arrow-down' }} mr-1"></i>
                                        {{ number_format(abs($percentageChange), 1) }}%
                                    </span>
                                </div>
                            </div>
                            <div class="flex justify-between items-center text-xs">
                                <span class="text-blue-200">Periode Sekarang</span>
                                <span class="text-white font-medium">Rp {{ number_format($total, 0, ',', '.') }}</span>
                            </div>
                            <div class="flex justify-between items-center text-xs mt-1">
                                <span class="text-blue-200">Periode Lalu</span>
                                <span class="text-blue-200">Rp {{ number_format($comparison['total'], 0, ',', '.') }}</span>
                            </div>
                        </div>
                        @endif
                    </div>
                </div>

                <!-- Card 2: Rata-rata Transaksi -->
                <div class="bg-white rounded-xl p-5 shadow border border-gray-200 relative overflow-hidden">
                    <div class="absolute top-0 right-0 w-16 h-16 bg-green-50 rounded-full -mr-6 -mt-6"></div>

                    <div class="relative">
                        <div class="flex justify-between items-start">
                            <div>
                                <p class="text-gray-600 text-sm mb-1">Rata-rata Transaksi</p>
                                <h3 class="text-2xl font-bold text-gray-900">Rp {{ number_format($averageTransaction, 0, ',', '.') }}</h3>
                            </div>
                            <div class="p-2 bg-green-100 text-green-600 rounded-lg">
                                <i class="fas fa-calculator text-xl"></i>
                            </div>
                        </div>

                        <!-- Comparison with previous month -->
                        @if(isset($comparison) && $comparison['avg'] != 0)
                        <div class="mt-5 pt-4 border-t border-gray-200">
                            <div class="flex items-center justify-between mb-2">
                                <span class="text-gray-600 text-sm flex items-center gap-1">
                                    <i class="fas fa-calendar-alt text-xs text-blue-600"></i>
                                    vs Bulan Lalu
                                </span>
                                <div class="flex items-center gap-1">
                                    @php
                                        $percentageChange = $comparison['avg_percentage'];
                                        $isPositive = $percentageChange > 0;
                                    @endphp
                                    <span class="text-{{ $isPositive ? 'green' : 'red' }}-600 text-sm font-medium">
                                        <i class="fas fa-{{ $isPositive ? 'arrow-up' : 'arrow-down' }} mr-1"></i>
                                        {{ number_format(abs($percentageChange), 1) }}%
                                    </span>
                                </div>
                            </div>
                            <div class="flex justify-between items-center text-xs">
                                <span class="text-gray-500">Sekarang</span>
                                <span class="font-medium text-gray-900">Rp {{ number_format($averageTransaction, 0, ',', '.') }}</span>
                            </div>
                            <div class="flex justify-between items-center text-xs mt-1">
                                <span class="text-gray-500">Kemarin</span>
                                <span class="text-gray-600">Rp {{ number_format($comparison['avg'], 0, ',', '.') }}</span>
                            </div>
                        </div>
                        @endif
                    </div>
                </div>

                <!-- Card 3: Transaksi Tertinggi -->
                <div class="bg-white rounded-xl p-5 shadow border border-gray-200 relative overflow-hidden">
                    <div class="absolute top-0 right-0 w-16 h-16 bg-yellow-50 rounded-full -mr-6 -mt-6"></div>

                    <div class="relative">
                        <div class="flex justify-between items-start">
                            <div>
                                <p class="text-gray-600 text-sm mb-1">Transaksi Tertinggi</p>
                                <h3 class="text-2xl font-bold text-gray-900">Rp {{ number_format($maxTransaction, 0, ',', '.') }}</h3>
                            </div>
                            <div class="p-2 bg-yellow-100 text-yellow-600 rounded-lg">
                                <i class="fas fa-arrow-up text-xl"></i>
                            </div>
                        </div>

                        <!-- Comparison with previous month -->
                        @if(isset($comparison) && $comparison['max'] != 0)
                        <div class="mt-5 pt-4 border-t border-gray-200">
                            <div class="flex items-center justify-between mb-2">
                                <span class="text-gray-600 text-sm flex items-center gap-1">
                                    <i class="fas fa-calendar-alt text-xs text-blue-600"></i>
                                    vs Bulan Lalu
                                </span>
                                <div class="flex items-center gap-1">
                                    @php
                                        $percentageChange = $comparison['max_percentage'];
                                        $isPositive = $percentageChange > 0;
                                    @endphp
                                    <span class="text-{{ $isPositive ? 'green' : 'red' }}-600 text-sm font-medium">
                                        <i class="fas fa-{{ $isPositive ? 'arrow-up' : 'arrow-down' }} mr-1"></i>
                                        {{ number_format(abs($percentageChange), 1) }}%
                                    </span>
                                </div>
                            </div>
                            <div class="flex justify-between items-center text-xs">
                                <span class="text-gray-500">Sekarang</span>
                                <span class="font-medium text-gray-900">Rp {{ number_format($maxTransaction, 0, ',', '.') }}</span>
                            </div>
                            <div class="flex justify-between items-center text-xs mt-1">
                                <span class="text-gray-500">Kemarin</span>
                                <span class="text-gray-600">Rp {{ number_format($comparison['max'], 0, ',', '.') }}</span>
                            </div>
                        </div>
                        @endif
                    </div>
                </div>

                <!-- Card 4: Jumlah Transaksi -->
                <div class="bg-white rounded-xl p-5 shadow border border-gray-200 relative overflow-hidden">
                    <div class="absolute top-0 right-0 w-16 h-16 bg-purple-50 rounded-full -mr-6 -mt-6"></div>

                    <div class="relative">
                        <div class="flex justify-between items-start">
                            <div>
                                <p class="text-gray-600 text-sm mb-1">Jumlah Transaksi</p>
                                <h3 class="text-2xl font-bold text-gray-900">{{ number_format($totalCount, 0) }}</h3>
                            </div>
                            <div class="p-2 bg-purple-100 text-purple-600 rounded-lg">
                                <i class="fas fa-shopping-cart text-xl"></i>
                            </div>
                        </div>

                        <!-- Comparison with previous month -->
                        @if(isset($comparison) && $comparison['count'] != 0)
                        <div class="mt-5 pt-4 border-t border-gray-200">
                            <div class="flex items-center justify-between mb-2">
                                <span class="text-gray-600 text-sm flex items-center gap-1">
                                    <i class="fas fa-calendar-alt text-xs text-blue-600"></i>
                                    vs Bulan Lalu
                                </span>
                                <div class="flex items-center gap-1">
                                    @php
                                        $percentageChange = $comparison['count_percentage'];
                                        $isPositive = $percentageChange > 0;
                                    @endphp
                                    <span class="text-{{ $isPositive ? 'green' : 'red' }}-600 text-sm font-medium">
                                        <i class="fas fa-{{ $isPositive ? 'arrow-up' : 'arrow-down' }} mr-1"></i>
                                        {{ number_format(abs($percentageChange), 1) }}%
                                    </span>
                                </div>
                            </div>
                            <div class="flex justify-between items-center text-xs">
                                <span class="text-gray-500">Sekarang</span>
                                <span class="font-medium text-gray-900">{{ number_format($totalCount, 0) }} transaksi</span>
                            </div>
                            <div class="flex justify-between items-center text-xs mt-1">
                                <span class="text-gray-500">Kemarin</span>
                                <span class="text-gray-600">{{ number_format($comparison['count'], 0) }} transaksi</span>
                            </div>
                        </div>
                        @endif
                    </div>
                </div>
            </div>

            <!-- Performance Indicators with Better Margin -->
            @if(isset($comparison) && $comparison['total'] != 0)
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-8">
                <!-- Daily Performance Chart -->
                <div class="bg-white rounded-xl p-6 shadow border border-gray-200">
                    <h4 class="font-semibold text-gray-900 mb-5 flex items-center gap-2 text-lg">
                        <i class="fas fa-chart-line text-blue-600"></i>
                        Kinerja Penjualan
                    </h4>

                    <!-- Current vs Previous Month Comparison -->
                    <div class="mb-6">
                        <div class="flex items-center justify-between mb-3">
                            <span class="text-sm font-medium text-gray-700">Perbandingan Omzet</span>
                            <span class="text-xs text-gray-500">Periode: {{ $period }} vs Bulan Lalu</span>
                        </div>

                        <!-- Progress bar comparison -->
                        <div class="space-y-4">
                            <div>
                                <div class="flex justify-between text-xs mb-1">
                                    <span class="text-gray-600">Periode Ini</span>
                                    <span class="font-medium text-blue-600">Rp {{ number_format($total, 0, ',', '.') }}</span>
                                </div>
                                <div class="w-full bg-gray-200 rounded-full h-2.5">
                                    <div class="bg-blue-600 h-2.5 rounded-full" style="width: 100%"></div>
                                </div>
                            </div>
                            <div>
                                <div class="flex justify-between text-xs mb-1">
                                    <span class="text-gray-600">Periode Lalu</span>
                                    <span class="font-medium text-gray-600">Rp {{ number_format($comparison['total'], 0, ',', '.') }}</span>
                                </div>
                                <div class="w-full bg-gray-200 rounded-full h-2.5">
                                    @php
                                        $previousPercentage = $total > 0 ? min(($comparison['total'] / $total) * 100, 100) : 0;
                                    @endphp
                                    <div class="bg-gray-400 h-2.5 rounded-full" style="width: {{ $previousPercentage }}%"></div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="space-y-3">
                        @php
                            $totalChange = $comparison['total_percentage'];
                            $trend = $totalChange > 0 ? 'naik' : ($totalChange < 0 ? 'turun' : 'stabil');
                        @endphp

                        <div class="flex items-center justify-between p-4 bg-gray-50 rounded-lg">
                            <span class="text-sm text-gray-700">Pertumbuhan Omzet</span>
                            <span class="font-semibold text-lg {{ $totalChange > 0 ? 'text-green-600' : ($totalChange < 0 ? 'text-red-600' : 'text-gray-600') }}">
                                {{ $totalChange > 0 ? '+' : '' }}{{ number_format($totalChange, 1) }}%
                                <i class="fas fa-{{ $totalChange > 0 ? 'arrow-up' : ($totalChange < 0 ? 'arrow-down' : 'minus') }} ml-1"></i>
                            </span>
                        </div>

                        <div class="flex items-center justify-between p-4 bg-gray-50 rounded-lg">
                            <span class="text-sm text-gray-700">Kesimpulan</span>
                            <span class="font-medium">
                                @if($trend == 'naik')
                                    <span class="text-green-600 flex items-center gap-1">
                                        <i class="fas fa-smile"></i>
                                        Meningkat dibanding bulan lalu
                                    </span>
                                @elseif($trend == 'turun')
                                    <span class="text-red-600 flex items-center gap-1">
                                        <i class="fas fa-frown"></i>
                                        Menurun dibanding bulan lalu
                                    </span>
                                @else
                                    <span class="text-gray-600 flex items-center gap-1">
                                        <i class="fas fa-meh"></i>
                                        Stabil dibanding bulan lalu
                                    </span>
                                @endif
                            </span>
                        </div>
                    </div>
                </div>

                <!-- Top Products -->
                @if(isset($topProducts) && $topProducts->count() > 0)
                <div class="bg-white rounded-xl p-6 shadow border border-gray-200">
                    <h4 class="font-semibold text-gray-900 mb-5 flex items-center gap-2 text-lg">
                        <i class="fas fa-crown text-yellow-500"></i>
                        Produk Terlaris
                    </h4>

                    <div class="space-y-3">
                        @foreach($topProducts as $index => $product)
                        <div class="flex items-center justify-between p-3 hover:bg-gray-50 rounded-lg transition-colors border-b border-gray-100 last:border-0">
                            <div class="flex items-center gap-3">
                                <span class="w-7 h-7 flex items-center justify-center bg-{{ $index == 0 ? 'yellow' : ($index == 1 ? 'gray' : ($index == 2 ? 'orange' : 'blue')) }}-100 text-{{ $index == 0 ? 'yellow' : ($index == 1 ? 'gray' : ($index == 2 ? 'orange' : 'blue')) }}-600 rounded-full text-xs font-bold">
                                    {{ $index + 1 }}
                                </span>
                                <span class="text-sm font-medium text-gray-900">{{ $product->name }}</span>
                            </div>
                            <div class="flex items-center gap-2">
                                <span class="text-sm text-gray-600">{{ $product->total_sold }} terjual</span>
                                @if($index == 0)
                                    <span class="px-2 py-0.5 bg-yellow-100 text-yellow-700 text-xs rounded-full">Terlaris</span>
                                @endif
                            </div>
                        </div>
                        @endforeach
                    </div>

                    <!-- Summary -->
                    <div class="mt-5 pt-4 border-t border-gray-200">
                        <div class="flex justify-between text-sm">
                            <span class="text-gray-600">Total produk terjual</span>
                            <span class="font-medium text-gray-900">{{ $topProducts->sum('total_sold') }} unit</span>
                        </div>
                    </div>
                </div>
                @endif
            </div>
            @endif
        </div>

        <!-- Filter Section -->
        <div class="bg-white rounded-xl shadow border border-gray-200 mb-6 overflow-hidden">
            <div class="border-b border-gray-200 px-6 py-4">
                <div class="flex items-center gap-3">
                    <div class="p-2 bg-blue-100 text-blue-600 rounded-lg">
                        <i class="fas fa-filter"></i>
                    </div>
                    <div>
                        <h3 class="font-semibold text-gray-900">Filter Laporan</h3>
                        <p class="text-sm text-gray-600">Pilih periode untuk laporan penjualan</p>
                    </div>
                </div>
            </div>
            <div class="p-6">
                <form method="GET" action="{{ route('reports.sales') }}" class="space-y-4">
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">
                                <i class="fas fa-calendar-day text-blue-600 mr-2"></i>
                                Tanggal Spesifik
                            </label>
                            <input type="date" name="date" value="{{ request('date') }}"
                                   class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-colors">
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">
                                <i class="fas fa-calendar-alt text-blue-600 mr-2"></i>
                                Bulan
                            </label>
                            <input type="month" name="month" value="{{ request('month') }}"
                                   class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-colors">
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">
                                <i class="fas fa-sort text-blue-600 mr-2"></i>
                                Urutkan Berdasarkan
                            </label>
                            <select name="sort" class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-colors">
                                <option value="latest" {{ request('sort') == 'latest' ? 'selected' : '' }}>Terbaru</option>
                                <option value="oldest" {{ request('sort') == 'oldest' ? 'selected' : '' }}>Terlama</option>
                                <option value="highest" {{ request('sort') == 'highest' ? 'selected' : '' }}>Total Tertinggi</option>
                                <option value="lowest" {{ request('sort') == 'lowest' ? 'selected' : '' }}>Total Terendah</option>
                            </select>
                        </div>
                    </div>

                    <div class="flex flex-wrap gap-3 pt-2">
                        <button type="submit" class="px-5 py-2.5 bg-gradient-to-r from-blue-600 to-blue-700 text-white font-medium rounded-lg hover:from-blue-700 hover:to-blue-800 transition-all flex items-center gap-2 shadow-sm">
                            <i class="fas fa-filter"></i>
                            Terapkan Filter
                        </button>
                        <a href="{{ route('reports.sales') }}" class="px-5 py-2.5 bg-white border border-gray-300 text-gray-700 font-medium rounded-lg hover:bg-gray-50 transition-colors flex items-center gap-2">
                            <i class="fas fa-redo"></i>
                            Reset
                        </a>
                    </div>
                </form>
            </div>
        </div>

        <!-- Transactions Table -->
        <div class="bg-white rounded-xl shadow border border-gray-200 overflow-hidden">
            <div class="border-b border-gray-200 px-6 py-4">
                <div class="flex items-center justify-between">
                    <div class="flex items-center gap-3">
                        <div class="p-2 bg-blue-100 text-blue-600 rounded-lg">
                            <i class="fas fa-table"></i>
                        </div>
                        <div>
                            <h3 class="font-semibold text-gray-900">Data Transaksi</h3>
                            <p class="text-sm text-gray-600">Detail transaksi penjualan</p>
                        </div>
                    </div>
                    <span class="px-3 py-1 bg-blue-100 text-blue-700 text-sm font-medium rounded-full">
                        {{ $transactions->total() }} transaksi
                    </span>
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
                                    <span class="text-gray-400 text-xs">{{ $transaction->created_at->format('H:i') }}</span>
                                </div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 font-mono">
                                {{ $transaction->invoice_number ?? 'TRX-' . $transaction->id }}
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
                                {{ $transaction->customer_name ?? 'Pelanggan Umum' }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-semibold text-green-600">
                                Rp {{ number_format($transaction->total_amount, 0, ',', '.') }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                @if($transaction->payment_method == 'credit_card' || $transaction->payment_method == 'hutang')
                                <span class="px-2 py-1 text-xs font-medium bg-yellow-100 text-yellow-800 rounded-full">
                                    <i class="fas fa-clock mr-1"></i> {{ ucfirst($transaction->payment_method) }}
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
                                <p class="text-gray-400 text-sm mt-1">Silakan pilih periode lain atau atur filter</p>
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
</div>
@endsection

@push('styles')
<style>
    .report-card {
        transition: transform 0.2s ease;
    }

    .report-card:hover {
        transform: translateY(-2px);
    }

    /* Animasi untuk percentage change */
    .percentage-up {
        animation: pulseGreen 2s infinite;
    }

    .percentage-down {
        animation: pulseRed 2s infinite;
    }

    @keyframes pulseGreen {
        0%, 100% {
            opacity: 1;
        }
        50% {
            opacity: 0.7;
        }
    }

    @keyframes pulseRed {
        0%, 100% {
            opacity: 1;
        }
        50% {
            opacity: 0.7;
        }
    }

    /* Hover effect untuk cards */
    .card-hover {
        transition: all 0.3s ease;
    }

    .card-hover:hover {
        transform: translateY(-4px);
        box-shadow: 0 12px 20px -8px rgba(0, 0, 0, 0.15);
    }
</style>
@endpush

@push('scripts')
<script>
    // Date and month validation
    const dateInput = document.querySelector('input[name="date"]');
    const monthInput = document.querySelector('input[name="month"]');

    if (dateInput && monthInput) {
        dateInput.addEventListener('change', function() {
            if (this.value) {
                monthInput.value = '';
            }
        });

        monthInput.addEventListener('change', function() {
            if (this.value) {
                dateInput.value = '';
            }
        });
    }

    // Tooltip untuk percentage
    document.querySelectorAll('[data-tooltip]').forEach(element => {
        element.addEventListener('mouseenter', function(e) {
            const tooltip = document.createElement('div');
            tooltip.className = 'absolute z-50 px-2 py-1 text-xs text-white bg-gray-800 rounded-lg pointer-events-none';
            tooltip.textContent = this.dataset.tooltip;
            tooltip.style.top = (e.pageY - 30) + 'px';
            tooltip.style.left = (e.pageX + 10) + 'px';
            document.body.appendChild(tooltip);

            this.addEventListener('mouseleave', function() {
                tooltip.remove();
            });
        });
    });
</script>
@endpush
