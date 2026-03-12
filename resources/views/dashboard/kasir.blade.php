{{-- resources/views/dashboard/kasir.blade.php --}}
@extends('layouts.app')

@section('title', 'Dashboard Kasir')
@section('page-title', 'Dashboard Kasir')
@section('page-subtitle', 'Transaksi & Penjualan Hari Ini')

@section('content')
<div class="min-h-screen bg-gradient-to-br from-blue-50/50 to-emerald-50/30 p-4 md:p-6">
    <!-- Welcome Header Kasir -->
    <div class="glass-effect rounded-3xl p-6 md:p-8 shadow-elegant mb-6 md:mb-8 animate-fade-in">
        <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-4 md:gap-6">
            <div class="flex-1">
                <div class="flex items-center gap-3 md:gap-4 mb-4">
                    <div class="relative">
                        <div class="w-12 h-12 md:w-16 md:h-16 rounded-2xl bg-gradient-to-br from-emerald-500 to-green-600 flex items-center justify-center shadow-lg">
                            <i class="fas fa-cash-register text-xl md:text-2xl text-white"></i>
                        </div>
                        <div class="absolute -inset-1 md:-inset-2 bg-gradient-to-r from-emerald-500 to-green-600 rounded-2xl blur-xl opacity-20"></div>
                    </div>
                    <div>
                        <h1 class="text-xl md:text-3xl font-bold text-gray-800">Halo, <span class="gradient-text">{{ Auth::user()->name }}!</span> 💰</h1>
                        <p class="text-sm md:text-base text-gray-600 mt-1 md:mt-2">Siap melayani transaksi hari ini?</p>
                    </div>
                </div>
                <div class="flex flex-col sm:flex-row gap-3">
                    <div class="flex items-center gap-2">
                        <div class="w-8 h-8 rounded-lg bg-emerald-100 flex items-center justify-center">
                            <i class="fas fa-user-tie text-emerald-600 text-sm"></i>
                        </div>
                        <div>
                            <p class="text-xs text-gray-500">Shift</p>
                            <p class="text-sm font-semibold text-gray-800">Pagi (08:00-16:00)</p>
                        </div>
                    </div>
                    <div class="flex items-center gap-2">
                        <div class="w-8 h-8 rounded-lg bg-blue-100 flex items-center justify-center">
                            <i class="fas fa-clock text-blue-600 text-sm"></i>
                        </div>
                        <div>
                            <p class="text-xs text-gray-500">Kasir ID</p>
                            <p class="text-sm font-semibold text-gray-800">KSR-{{ str_pad(Auth::id(), 3, '0', STR_PAD_LEFT) }}</p>
                        </div>
                    </div>
                </div>
            </div>
            <div class="mt-4 lg:mt-0">
                <a href="{{ route('transactions.create') }}"
                   class="inline-flex items-center gap-2 px-6 py-3 md:py-4 bg-gradient-to-r from-emerald-500 to-green-600 text-white font-semibold rounded-xl hover:shadow-lg transition-all duration-300 hover:-translate-y-1">
                    <i class="fas fa-plus-circle"></i>
                    <span>Transaksi Baru</span>
                </a>
            </div>
        </div>
    </div>

    <!-- Stats Grid Kasir -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 md:gap-6 mb-6 md:mb-8">
        <!-- Total Transaksi Hari Ini -->
        <div class="stat-card group">
            <div class="stat-card-glow bg-gradient-to-r from-emerald-500 to-green-500"></div>
            <div class="stat-card-content">
                <div class="flex items-center justify-between mb-4">
                    <div class="flex items-center gap-3">
                        <div class="p-3 rounded-xl bg-gradient-to-br from-emerald-500 to-green-600 shadow-lg">
                            <i class="fas fa-receipt text-lg text-white"></i>
                        </div>
                        <div>
                            <p class="text-xs md:text-sm text-gray-500 font-medium">Transaksi Hari Ini</p>
                            <h3 class="text-xl md:text-2xl font-bold text-gray-800 mt-1">{{ $todayTransactions ?? 0 }}</h3>
                        </div>
                    </div>
                </div>
                <div class="flex items-center justify-between">
                    <div class="flex items-center text-xs">
                        <div class="w-6 h-6 rounded-lg bg-emerald-100 flex items-center justify-center mr-2">
                            <i class="fas fa-arrow-up text-emerald-600 text-xs"></i>
                        </div>
                        <span class="text-emerald-600 font-semibold">+{{ $transactionGrowth ?? 0 }}%</span>
                        <span class="text-gray-500 ml-2">dari kemarin</span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Total Pendapatan Hari Ini -->
        <div class="stat-card group">
            <div class="stat-card-glow bg-gradient-to-r from-blue-500 to-cyan-500"></div>
            <div class="stat-card-content">
                <div class="flex items-center justify-between mb-4">
                    <div class="flex items-center gap-3">
                        <div class="p-3 rounded-xl bg-gradient-to-br from-blue-500 to-cyan-600 shadow-lg">
                            <i class="fas fa-money-bill-wave text-lg text-white"></i>
                        </div>
                        <div>
                            <p class="text-xs md:text-sm text-gray-500 font-medium">Pendapatan Hari Ini</p>
                            <h3 class="text-xl md:text-2xl font-bold text-gray-800 mt-1">Rp {{ number_format($todayRevenue ?? 0, 0, ',', '.') }}</h3>
                        </div>
                    </div>
                </div>
                <div class="flex items-center justify-between">
                    <div class="flex items-center text-xs">
                        <span class="text-gray-500">Target: Rp 5.000.000</span>
                        <div class="w-16 h-2 bg-gray-200 rounded-full overflow-hidden">
                            <div class="h-full bg-emerald-500 rounded-full"
                                 style="width: {{ min(100, (($todayRevenue ?? 0) / 5000000) * 100) }}%"></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Rata-rata Transaksi -->
        <div class="stat-card group">
            <div class="stat-card-glow bg-gradient-to-r from-purple-500 to-pink-500"></div>
            <div class="stat-card-content">
                <div class="flex items-center justify-between mb-4">
                    <div class="flex items-center gap-3">
                        <div class="p-3 rounded-xl bg-gradient-to-br from-purple-500 to-pink-600 shadow-lg">
                            <i class="fas fa-chart-bar text-lg text-white"></i>
                        </div>
                        <div>
                            <p class="text-xs md:text-sm text-gray-500 font-medium">Rata-rata Transaksi</p>
                            <h3 class="text-xl md:text-2xl font-bold text-gray-800 mt-1">Rp {{ number_format($avgTransaction ?? 0, 0, ',', '.') }}</h3>
                        </div>
                    </div>
                </div>
                <div class="flex items-center justify-between">
                    <div class="flex items-center text-xs">
                        <div class="w-6 h-6 rounded-lg bg-emerald-100 flex items-center justify-center mr-2">
                            <i class="fas fa-arrow-up text-emerald-600 text-xs"></i>
                        </div>
                        <span class="text-emerald-600 font-semibold">+8%</span>
                        <span class="text-gray-500 ml-2">lebih tinggi</span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Produk Terjual Hari Ini -->
        <div class="stat-card group">
            <div class="stat-card-glow bg-gradient-to-r from-amber-500 to-orange-500"></div>
            <div class="stat-card-content">
                <div class="flex items-center justify-between mb-4">
                    <div class="flex items-center gap-3">
                        <div class="p-3 rounded-xl bg-gradient-to-br from-amber-500 to-orange-600 shadow-lg">
                            <i class="fas fa-shopping-cart text-lg text-white"></i>
                        </div>
                        <div>
                            <p class="text-xs md:text-sm text-gray-500 font-medium">Produk Terjual</p>
                            <h3 class="text-xl md:text-2xl font-bold text-gray-800 mt-1">{{ $todayItemsSold ?? 0 }}</h3>
                        </div>
                    </div>
                </div>
                <div class="flex items-center justify-between">
                    <div class="flex items-center text-xs">
                        <span class="text-gray-500">{{ $topProductToday ?? 'Produk terlaris' }}</span>
                        <span class="text-amber-600 font-semibold ml-2">#1</span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Main Content Area -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-4 md:gap-6">
        <!-- Transaksi Terbaru -->
        <div class="lg:col-span-2">
            <div class="glass-effect rounded-3xl overflow-hidden shadow-elegant h-full">
                <div class="p-4 md:p-6 border-b border-gray-100/50">
                    <div class="flex items-center justify-between">
                        <div>
                            <h3 class="text-lg md:text-xl font-bold text-gray-800">Transaksi Terbaru</h3>
                            <p class="text-xs md:text-sm text-gray-600 mt-1">5 transaksi terakhir Anda</p>
                        </div>
                        <a href="{{ route('transactions.index') }}"
                           class="text-emerald-600 hover:text-emerald-700 font-medium text-sm flex items-center gap-1">
                            Lihat Semua <i class="fas fa-arrow-right text-xs"></i>
                        </a>
                    </div>
                </div>
                <div class="divide-y divide-gray-100/50">
                    @forelse($recentTransactions ?? [] as $transaction)
                    <div class="p-4 md:p-6 hover:bg-white/30 transition-colors duration-200">
                        <div class="flex items-center justify-between">
                            <div class="flex items-center gap-3">
                                <div class="w-10 h-10 rounded-xl flex items-center justify-center
                                    {{ $transaction->payment_method === 'cash' ? 'bg-emerald-100 text-emerald-600' :
                                       ($transaction->payment_method === 'debit_card' ? 'bg-blue-100 text-blue-600' : 'bg-purple-100 text-purple-600') }}">
                                    @if($transaction->payment_method === 'cash')
                                        <i class="fas fa-money-bill-wave"></i>
                                    @elseif($transaction->payment_method === 'debit_card')
                                        <i class="fas fa-credit-card"></i>
                                    @else
                                        <i class="fas fa-qrcode"></i>
                                    @endif
                                </div>
                                <div>
                                    <h4 class="font-semibold text-gray-900">#{{ $transaction->transaction_code }}</h4>
                                    <p class="text-xs text-gray-500 mt-1">
                                        {{ $transaction->created_at->format('H:i') }} •
                                        {{ $transaction->items->count() }} items
                                    </p>
                                </div>
                            </div>
                            <div class="text-right">
                                <div class="text-lg font-bold text-gray-900">Rp {{ number_format($transaction->total_amount, 0, ',', '.') }}</div>
                                <span class="inline-block mt-1 px-2 py-1 rounded-full text-xs font-medium
                                    {{ $transaction->status === 'completed' ? 'bg-emerald-100 text-emerald-800' : 'bg-amber-100 text-amber-800' }}">
                                    {{ ucfirst($transaction->status) }}
                                </span>
                            </div>
                        </div>
                    </div>
                    @empty
                    <div class="p-8 text-center">
                        <div class="flex flex-col items-center justify-center text-gray-400">
                            <i class="fas fa-receipt text-3xl mb-3"></i>
                            <p class="text-sm">Belum ada transaksi hari ini</p>
                        </div>
                    </div>
                    @endforelse
                </div>
            </div>
        </div>

        <!-- Quick Actions & Product Search -->
        <div class="space-y-4 md:space-y-6">
            <!-- Quick Transaction -->
            <div class="glass-effect rounded-3xl p-4 md:p-6 shadow-elegant">
                <h3 class="text-lg md:text-xl font-bold text-gray-800 mb-4">Transaksi Cepat</h3>
                <div class="space-y-3">
                    <a href="{{ route('transactions.create') }}?type=regular"
                       class="flex items-center gap-3 p-3 rounded-xl bg-emerald-50 hover:bg-emerald-100 transition-colors">
                        <div class="w-10 h-10 rounded-lg bg-emerald-500 flex items-center justify-center">
                            <i class="fas fa-shopping-cart text-white"></i>
                        </div>
                        <div>
                            <h4 class="font-semibold text-gray-900">Transaksi Regular</h4>
                            <p class="text-xs text-gray-600">Pelanggan umum</p>
                        </div>
                    </a>
                    <a href="{{ route('transactions.create') }}?type=member"
                       class="flex items-center gap-3 p-3 rounded-xl bg-blue-50 hover:bg-blue-100 transition-colors">
                        <div class="w-10 h-10 rounded-lg bg-blue-500 flex items-center justify-center">
                            <i class="fas fa-user-friends text-white"></i>
                        </div>
                        <div>
                            <h4 class="font-semibold text-gray-900">Transaksi Member</h4>
                            <p class="text-xs text-gray-600">Member terdaftar</p>
                        </div>
                    </a>
                    <a href="{{ route('transactions.create') }}?type=wholesale"
                       class="flex items-center gap-3 p-3 rounded-xl bg-purple-50 hover:bg-purple-100 transition-colors">
                        <div class="w-10 h-10 rounded-lg bg-purple-500 flex items-center justify-center">
                            <i class="fas fa-boxes text-white"></i>
                        </div>
                        <div>
                            <h4 class="font-semibold text-gray-900">Transaksi Grosir</h4>
                            <p class="text-xs text-gray-600">Minimal 10 item</p>
                        </div>
                    </a>
                </div>
            </div>

            <!-- Product Search -->
            <div class="glass-effect rounded-3xl p-4 md:p-6 shadow-elegant">
                <h3 class="text-lg md:text-xl font-bold text-gray-800 mb-4">Cari Produk Cepat</h3>
                <div class="relative">
                    <input type="text"
                           placeholder="Scan barcode atau ketik nama produk..."
                           class="w-full px-4 py-3 bg-white/50 border border-gray-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:border-transparent">
                    <button class="absolute right-3 top-3 text-gray-400">
                        <i class="fas fa-search"></i>
                    </button>
                </div>
                <div class="mt-4 grid grid-cols-2 gap-2">
                    <button class="p-3 rounded-lg bg-blue-50 hover:bg-blue-100 transition-colors text-center">
                        <i class="fas fa-barcode text-blue-600 text-lg mb-1"></i>
                        <p class="text-xs font-medium text-gray-700">Scan Barcode</p>
                    </button>
                    <button class="p-3 rounded-lg bg-emerald-50 hover:bg-emerald-100 transition-colors text-center">
                        <i class="fas fa-keyboard text-emerald-600 text-lg mb-1"></i>
                        <p class="text-xs font-medium text-gray-700">Manual Entry</p>
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Popular Products -->
    <div class="mt-6 md:mt-8">
        <div class="glass-effect rounded-3xl p-4 md:p-6 shadow-elegant">
            <div class="flex items-center justify-between mb-4">
                <div>
                    <h3 class="text-lg md:text-xl font-bold text-gray-800">Produk Populer</h3>
                    <p class="text-xs md:text-sm text-gray-600 mt-1">Sering dibeli hari ini</p>
                </div>
                <a href="{{ route('products.index') }}" class="text-emerald-600 hover:text-emerald-700 text-sm font-medium">
                    Lihat semua produk
                </a>
            </div>
            <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-6 gap-3">
                @forelse($popularProducts ?? [] as $product)
                <div class="group cursor-pointer">
                    <div class="aspect-square rounded-2xl bg-gradient-to-br from-gray-50 to-gray-100 flex items-center justify-center p-4 mb-2 group-hover:shadow-md transition-shadow">
                        <i class="fas fa-box text-2xl text-gray-400 group-hover:text-emerald-500 transition-colors"></i>
                    </div>
                    <div class="text-center">
                        <p class="text-xs font-medium text-gray-900 truncate">{{ $product->name }}</p>
                        <p class="text-xs text-gray-500 mt-1">Rp {{ number_format($product->price, 0, ',', '.') }}</p>
                        <div class="flex items-center justify-center mt-1">
                            <i class="fas fa-shopping-cart text-xs text-emerald-500 mr-1"></i>
                            <span class="text-xs text-gray-600">{{ $product->sold_today ?? 0 }} terjual</span>
                        </div>
                    </div>
                </div>
                @empty
                <div class="col-span-full p-6 text-center text-gray-400">
                    <i class="fas fa-box-open text-2xl mb-2"></i>
                    <p class="text-sm">Belum ada data produk populer</p>
                </div>
                @endforelse
            </div>
        </div>
    </div>
</div>

<script>
    // Update current time
    function updateTime() {
        const now = new Date();
        const options = {
            hour: '2-digit',
            minute: '2-digit',
            hour12: false
        };
        const timeString = now.toLocaleTimeString('id-ID', options);
        document.querySelectorAll('.current-time').forEach(el => {
            el.textContent = timeString;
        });
    }

    updateTime();
    setInterval(updateTime, 1000);

    // Quick product search
    document.getElementById('productSearch')?.addEventListener('input', function(e) {
        const searchTerm = e.target.value.toLowerCase();
        // Implement search functionality here
    });
</script>

<style>
    .stat-card {
        @apply relative bg-white/80 backdrop-blur-sm rounded-2xl p-4 shadow-soft border border-white/50;
        transition: all 0.3s ease;
    }

    .stat-card:hover {
        @apply shadow-lg -translate-y-1;
    }

    .stat-card-glow {
        @apply absolute -inset-1 rounded-2xl blur-xl opacity-0 transition-opacity duration-300;
    }

    .glass-effect {
        background: rgba(255, 255, 255, 0.7);
        backdrop-filter: blur(10px);
        border: 1px solid rgba(16, 185, 129, 0.1);
    }

    .gradient-text {
        background: linear-gradient(135deg, #10b981 0%, #059669 100%);
        -webkit-background-clip: text;
        -webkit-text-fill-color: transparent;
    }
</style>
@endsection
