@extends('layouts.app')

@section('title', 'Dashboard Checker Barang')
@section('page-title', 'Dashboard Checker Barang')
@section('page-subtitle', 'Pantau stok dan kualitas produk')

@section('content')
<div class="min-h-screen bg-gradient-to-br from-blue-50/50 to-indigo-50/30 p-4 md:p-6">
    <!-- Welcome Header -->
    <div class="glass-effect rounded-3xl p-6 md:p-8 shadow-elegant mb-6 md:mb-8 animate-fade-in">
        <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-4 md:gap-6">
            <div class="flex-1">
                <div class="flex items-center gap-3 md:gap-4 mb-4">
                    <div class="relative">
                        <div class="w-12 h-12 md:w-16 md:h-16 rounded-2xl bg-gradient-to-br from-indigo-500 to-purple-600 flex items-center justify-center shadow-lg">
                            <i class="fas fa-clipboard-check text-xl md:text-2xl text-white"></i>
                        </div>
                        <div class="absolute -inset-1 md:-inset-2 bg-gradient-to-r from-indigo-500 to-purple-600 rounded-2xl blur-xl opacity-20"></div>
                    </div>
                    <div>
                        <h1 class="text-xl md:text-3xl font-bold text-gray-800">Halo, <span class="gradient-text">{{ Auth::user()->name }}!</span> 👋</h1>
                        <p class="text-sm md:text-base text-gray-600 mt-1 md:mt-2">Pantau stok dan kualitas produk</p>
                    </div>
                </div>
                <div class="flex flex-wrap gap-3">
                    <div class="flex items-center gap-2 px-3 py-2 bg-indigo-50 rounded-lg">
                        <i class="fas fa-calendar-day text-indigo-600"></i>
                        <span class="text-sm font-medium text-gray-700">{{ now()->translatedFormat('l, d F Y') }}</span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Stats Grid -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 md:gap-6 mb-6 md:mb-8">
        <!-- Total Produk -->
        <div class="stat-card group">
            <div class="stat-card-glow bg-gradient-to-r from-blue-500 to-indigo-500"></div>
            <div class="stat-card-content">
                <div class="flex items-center gap-3">
                    <div class="p-3 rounded-xl bg-gradient-to-br from-blue-500 to-indigo-600 shadow-lg">
                        <i class="fas fa-boxes text-lg text-white"></i>
                    </div>
                    <div>
                        <p class="text-xs md:text-sm text-gray-500 font-medium">Total Produk</p>
                        <h3 class="text-xl md:text-2xl font-bold text-gray-800 mt-1">{{ $totalProducts ?? 0 }}</h3>
                    </div>
                </div>
                <div class="mt-2 text-xs text-gray-500">
                    {{ $totalCategories ?? 0 }} kategori
                </div>
            </div>
        </div>

        <!-- Stok Rendah -->
        <div class="stat-card group">
            <div class="stat-card-glow bg-gradient-to-r from-amber-500 to-orange-500"></div>
            <div class="stat-card-content">
                <div class="flex items-center gap-3">
                    <div class="p-3 rounded-xl bg-gradient-to-br from-amber-500 to-orange-600 shadow-lg">
                        <i class="fas fa-exclamation-triangle text-lg text-white"></i>
                    </div>
                    <div>
                        <p class="text-xs md:text-sm text-gray-500 font-medium">Stok Rendah</p>
                        <h3 class="text-xl md:text-2xl font-bold text-gray-800 mt-1">{{ $lowStockProducts ?? 0 }}</h3>
                    </div>
                </div>
                <div class="mt-2 text-xs text-amber-600">
                    Perlu perhatian
                </div>
            </div>
        </div>

        <!-- Akan Kadaluarsa -->
        <div class="stat-card group">
            <div class="stat-card-glow bg-gradient-to-r from-yellow-500 to-amber-500"></div>
            <div class="stat-card-content">
                <div class="flex items-center gap-3">
                    <div class="p-3 rounded-xl bg-gradient-to-br from-yellow-500 to-amber-600 shadow-lg">
                        <i class="fas fa-clock text-lg text-white"></i>
                    </div>
                    <div>
                        <p class="text-xs md:text-sm text-gray-500 font-medium">Akan Kadaluarsa</p>
                        <h3 class="text-xl md:text-2xl font-bold text-gray-800 mt-1">{{ $expiringSoonCount ?? 0 }}</h3>
                    </div>
                </div>
                <div class="mt-2 text-xs text-yellow-600">
                    30 hari ke depan
                </div>
            </div>
        </div>

        <!-- Sudah Kadaluarsa -->
        <div class="stat-card group">
            <div class="stat-card-glow bg-gradient-to-r from-red-500 to-rose-500"></div>
            <div class="stat-card-content">
                <div class="flex items-center gap-3">
                    <div class="p-3 rounded-xl bg-gradient-to-br from-red-500 to-rose-600 shadow-lg">
                        <i class="fas fa-calendar-times text-lg text-white"></i>
                    </div>
                    <div>
                        <p class="text-xs md:text-sm text-gray-500 font-medium">Kadaluarsa</p>
                        <h3 class="text-xl md:text-2xl font-bold text-gray-800 mt-1">{{ $expiredCount ?? 0 }}</h3>
                    </div>
                </div>
                <div class="mt-2 text-xs text-red-600">
                    Segera tindak lanjuti
                </div>
            </div>
        </div>
    </div>

    <!-- Main Content Grid -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-4 md:gap-6 mb-6">
        <!-- Stok Rendah -->
        <div class="glass-effect rounded-3xl overflow-hidden shadow-elegant">
            <div class="p-4 md:p-6 border-b border-gray-100/50">
                <div class="flex items-center justify-between">
                    <div>
                        <h3 class="text-lg md:text-xl font-bold text-gray-800">Stok Rendah</h3>
                        <p class="text-xs md:text-sm text-gray-600 mt-1">Produk dengan stok menipis</p>
                    </div>
                    <a href="{{ route('products.index') }}?stock=low" class="text-indigo-600 hover:text-indigo-700 text-sm font-medium">
                        Lihat Semua
                    </a>
                </div>
            </div>
            <div class="divide-y divide-gray-100/50 max-h-96 overflow-y-auto elegant-scrollbar">
                @forelse($lowStockItems ?? [] as $product)
                <div class="p-4 md:p-6 hover:bg-white/30 transition-colors duration-200">
                    <div class="flex items-start gap-3">
                        <div class="relative flex-shrink-0">
                            <div class="w-12 h-12 rounded-xl bg-gradient-to-br from-amber-100 to-orange-100 flex items-center justify-center">
                                <i class="fas fa-box text-amber-600"></i>
                            </div>
                            @if($product->stock <= ($product->min_stock ?? 5))
                            <div class="absolute -top-1 -right-1 w-5 h-5 bg-red-500 rounded-full flex items-center justify-center text-white text-xs animate-pulse">
                                !
                            </div>
                            @endif
                        </div>
                        <div class="flex-1 min-w-0">
                            <div class="flex items-start justify-between gap-2">
                                <div>
                                    <h4 class="font-semibold text-gray-900 truncate">{{ $product->name }}</h4>
                                    <p class="text-xs text-gray-500 mt-1">{{ $product->category->name ?? '-' }}</p>
                                </div>
                                <div class="text-right">
                                    <span class="inline-block px-2 py-1 rounded-lg text-xs font-medium bg-amber-100 text-amber-800">
                                        Stok: {{ $product->stock }}
                                    </span>
                                </div>
                            </div>
                            <div class="flex items-center justify-between mt-3">
                                <div class="flex-1 mr-3">
                                    <div class="w-full bg-gray-200 rounded-full h-2.5">
                                        <div class="h-2.5 rounded-full bg-amber-500" style="width: {{ min(100, $product->stock_percentage) }}%"></div>
                                    </div>
                                </div>
                                <button onclick="showReportModal({{ $product->id }}, '{{ $product->name }}', 'low_stock')"
                                        class="px-3 py-1.5 bg-indigo-600 hover:bg-indigo-700 text-white text-xs font-medium rounded-lg transition-colors">
                                    <i class="fas fa-flag mr-1"></i> Laporkan
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
                @empty
                <div class="p-8 text-center text-gray-400">
                    <i class="fas fa-check-circle text-3xl mb-2"></i>
                    <p>Tidak ada produk dengan stok rendah</p>
                </div>
                @endforelse
            </div>
        </div>

        <!-- Produk Akan Kadaluarsa -->
        <div class="glass-effect rounded-3xl overflow-hidden shadow-elegant">
            <div class="p-4 md:p-6 border-b border-gray-100/50">
                <div class="flex items-center justify-between">
                    <div>
                        <h3 class="text-lg md:text-xl font-bold text-gray-800">Akan Kadaluarsa</h3>
                        <p class="text-xs md:text-sm text-gray-600 mt-1">30 hari ke depan</p>
                    </div>
                    <a href="{{ route('products.index') }}?expiry=expiring" class="text-indigo-600 hover:text-indigo-700 text-sm font-medium">
                        Lihat Semua
                    </a>
                </div>
            </div>
            <div class="divide-y divide-gray-100/50 max-h-96 overflow-y-auto elegant-scrollbar">
                @forelse($expiringProducts ?? [] as $product)
                <div class="p-4 md:p-6 hover:bg-white/30 transition-colors duration-200">
                    <div class="flex items-start gap-3">
                        <div class="relative flex-shrink-0">
                            <div class="w-12 h-12 rounded-xl bg-gradient-to-br from-yellow-100 to-amber-100 flex items-center justify-center">
                                <i class="fas fa-clock text-yellow-600"></i>
                            </div>
                        </div>
                        <div class="flex-1 min-w-0">
                            <div class="flex items-start justify-between gap-2">
                                <div>
                                    <h4 class="font-semibold text-gray-900 truncate">{{ $product->name }}</h4>
                                    <p class="text-xs text-gray-500 mt-1">{{ $product->category->name ?? '-' }}</p>
                                </div>
                                <div class="text-right">
                                    <span class="inline-block px-2 py-1 rounded-lg text-xs font-medium 
                                        {{ $product->days_left <= 7 ? 'bg-red-100 text-red-800' : 'bg-yellow-100 text-yellow-800' }}">
                                        {{ $product->days_display }}
                                    </span>
                                </div>
                            </div>
                            <div class="flex items-center justify-between mt-3">
                                <p class="text-xs text-gray-500">{{ Carbon\Carbon::parse($product->expiry_date)->format('d M Y') }}</p>
                                <button onclick="showReportModal({{ $product->id }}, '{{ $product->name }}', 'expiring')"
                                        class="px-3 py-1.5 bg-indigo-600 hover:bg-indigo-700 text-white text-xs font-medium rounded-lg transition-colors">
                                    <i class="fas fa-flag mr-1"></i> Laporkan
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
                @empty
                <div class="p-8 text-center text-gray-400">
                    <i class="fas fa-calendar-check text-3xl mb-2"></i>
                    <p>Tidak ada produk yang akan kadaluarsa</p>
                </div>
                @endforelse
            </div>
        </div>
    </div>

    <!-- Produk Kadaluarsa & Laporan Terbaru -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-4 md:gap-6">
        <!-- Produk Kadaluarsa -->
        <div class="glass-effect rounded-3xl overflow-hidden shadow-elegant">
            <div class="p-4 md:p-6 border-b border-gray-100/50">
                <div class="flex items-center justify-between">
                    <div>
                        <h3 class="text-lg md:text-xl font-bold text-gray-800">Produk Kadaluarsa</h3>
                        <p class="text-xs md:text-sm text-gray-600 mt-1">Perlu tindakan segera</p>
                    </div>
                    <a href="{{ route('products.index') }}?expiry=expired" class="text-indigo-600 hover:text-indigo-700 text-sm font-medium">
                        Lihat Semua
                    </a>
                </div>
            </div>
            <div class="divide-y divide-gray-100/50 max-h-80 overflow-y-auto elegant-scrollbar">
                @forelse($expiredProducts ?? [] as $product)
                <div class="p-4 md:p-6 hover:bg-white/30 transition-colors duration-200">
                    <div class="flex items-start gap-3">
                        <div class="flex-shrink-0 w-10 h-10 rounded-xl bg-red-100 flex items-center justify-center">
                            <i class="fas fa-exclamation-triangle text-red-600"></i>
                        </div>
                        <div class="flex-1 min-w-0">
                            <div class="flex items-start justify-between gap-2">
                                <div>
                                    <h4 class="font-semibold text-gray-900 truncate">{{ $product->name }}</h4>
                                    <p class="text-xs text-gray-500 mt-1">{{ $product->category->name ?? '-' }}</p>
                                </div>
                                <div class="text-right">
                                    <span class="inline-block px-2 py-1 rounded-lg text-xs font-medium bg-red-100 text-red-800">
                                        {{ $product->days_expired }} hari expired
                                    </span>
                                </div>
                            </div>
                            <div class="flex items-center justify-between mt-3">
                                <p class="text-xs text-gray-500">Stok: {{ $product->stock }}</p>
                                <button onclick="showReportModal({{ $product->id }}, '{{ $product->name }}', 'expired')"
                                        class="px-3 py-1.5 bg-red-600 hover:bg-red-700 text-white text-xs font-medium rounded-lg transition-colors">
                                    <i class="fas fa-flag mr-1"></i> Laporkan
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
                @empty
                <div class="p-8 text-center text-gray-400">
                    <i class="fas fa-check-circle text-3xl mb-2"></i>
                    <p>Tidak ada produk kadaluarsa</p>
                </div>
                @endforelse
            </div>
        </div>

        <!-- Laporan Terbaru -->
        <div class="glass-effect rounded-3xl overflow-hidden shadow-elegant">
            <div class="p-4 md:p-6 border-b border-gray-100/50">
                <div class="flex items-center justify-between">
                    <div>
                        <h3 class="text-lg md:text-xl font-bold text-gray-800">Laporan Terbaru</h3>
                        <p class="text-xs md:text-sm text-gray-600 mt-1">Riwayat laporan Anda</p>
                    </div>
                    <a href="{{ route('checker.index') }}" class="text-indigo-600 hover:text-indigo-700 text-sm font-medium">
                        Lihat Semua
                    </a>
                </div>
            </div>
            <div class="divide-y divide-gray-100/50 max-h-80 overflow-y-auto elegant-scrollbar">
                @forelse($recentReports ?? [] as $report)
                <div class="p-4 md:p-6 hover:bg-white/30 transition-colors duration-200">
                    <div class="flex items-start gap-3">
                        <div class="flex-shrink-0">
                            <div class="w-10 h-10 rounded-xl {{ $report->status_badge_class }} flex items-center justify-center">
                                <i class="fas {{ $report->status == 'pending' ? 'fa-clock' : ($report->status == 'resolved' ? 'fa-check-circle' : 'fa-spinner') }}"></i>
                            </div>
                        </div>
                        <div class="flex-1 min-w-0">
                            <div class="flex items-start justify-between gap-2">
                                <div>
                                    <h4 class="font-medium text-gray-900 truncate">{{ $report->product->name ?? 'Produk' }}</h4>
                                    <p class="text-xs text-gray-500 mt-1">{{ $report->report_type_label }}</p>
                                </div>
                                <span class="inline-block px-2 py-1 rounded-lg text-xs font-medium {{ $report->status_badge_class }}">
                                    {{ ucfirst($report->status) }}
                                </span>
                            </div>
                            <p class="text-xs text-gray-600 mt-2 line-clamp-2">{{ $report->notes }}</p>
                            <p class="text-xs text-gray-400 mt-2">{{ $report->created_at->diffForHumans() }}</p>
                        </div>
                    </div>
                </div>
                @empty
                <div class="p-8 text-center text-gray-400">
                    <i class="fas fa-flag text-3xl mb-2"></i>
                    <p>Belum ada laporan</p>
                </div>
                @endforelse
            </div>
        </div>
    </div>
</div>

<!-- Report Modal -->
<div id="reportModal" class="fixed inset-0 bg-black/50 backdrop-blur-sm z-50 hidden items-center justify-center">
    <div class="bg-white rounded-2xl max-w-md w-full mx-4 animate-slide-up">
        <div class="p-6">
            <div class="flex items-center justify-between mb-4">
                <h3 class="text-xl font-bold text-gray-900">Laporkan Produk</h3>
                <button onclick="closeReportModal()" class="text-gray-400 hover:text-gray-600">
                    <i class="fas fa-times"></i>
                </button>
            </div>
            
            <form id="reportForm" method="POST">
                @csrf
                <input type="hidden" id="product_id" name="product_id">
                <input type="hidden" id="report_type" name="report_type">
                
                <div class="mb-4">
                    <p class="text-sm text-gray-700 mb-2" id="productNameDisplay"></p>
                </div>
                
                <div class="mb-4">
                    <label for="notes" class="block text-sm font-medium text-gray-700 mb-2">
                        Catatan Laporan <span class="text-red-500">*</span>
                    </label>
                    <textarea id="notes" name="notes" rows="4" required
                        class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition-colors"
                        placeholder="Jelaskan kondisi produk..."></textarea>
                </div>
                
                <div class="mb-4">
                    <label for="quantity" class="block text-sm font-medium text-gray-700 mb-2">
                        Jumlah Produk (Opsional)
                    </label>
                    <input type="number" id="quantity" name="quantity" min="1"
                        class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition-colors"
                        placeholder="Masukkan jumlah">
                </div>
                
                <div class="flex gap-3">
                    <button type="button" onclick="closeReportModal()"
                        class="flex-1 px-4 py-3 border border-gray-300 rounded-xl text-gray-700 font-medium hover:bg-gray-50 transition-colors">
                        Batal
                    </button>
                    <button type="submit"
                        class="flex-1 px-4 py-3 bg-indigo-600 hover:bg-indigo-700 text-white font-medium rounded-xl transition-colors">
                        <i class="fas fa-paper-plane mr-2"></i> Kirim Laporan
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<style>
    .stat-card {
        @apply relative bg-white/80 backdrop-blur-sm rounded-2xl p-4 shadow-soft border border-white/50;
        transition: all 0.3s ease;
    }

    .stat-card:hover {
        @apply shadow-lg -translate-y-1 border-indigo-200/50;
    }

    .stat-card-glow {
        @apply absolute -inset-1 rounded-2xl blur-xl opacity-0 transition-opacity duration-300;
    }

    .stat-card:hover .stat-card-glow {
        @apply opacity-50;
    }

    .glass-effect {
        background: rgba(255, 255, 255, 0.7);
        backdrop-filter: blur(10px);
        border: 1px solid rgba(99, 102, 241, 0.1);
    }

    .gradient-text {
        background: linear-gradient(135deg, #6366f1 0%, #8b5cf6 100%);
        -webkit-background-clip: text;
        -webkit-text-fill-color: transparent;
    }

    .animate-fade-in {
        animation: fadeIn 0.6s ease-out;
    }

    @keyframes fadeIn {
        from { opacity: 0; transform: translateY(10px); }
        to { opacity: 1; transform: translateY(0); }
    }

    .animate-slide-up {
        animation: slideUp 0.3s ease-out;
    }

    @keyframes slideUp {
        from { opacity: 0; transform: translateY(20px); }
        to { opacity: 1; transform: translateY(0); }
    }

    .elegant-scrollbar::-webkit-scrollbar {
        width: 6px;
    }

    .elegant-scrollbar::-webkit-scrollbar-track {
        background: rgba(99, 102, 241, 0.1);
        border-radius: 10px;
    }

    .elegant-scrollbar::-webkit-scrollbar-thumb {
        background: linear-gradient(135deg, #6366f1 0%, #8b5cf6 100%);
        border-radius: 10px;
    }

    .elegant-scrollbar::-webkit-scrollbar-thumb:hover {
        background: linear-gradient(135deg, #4f46e5 0%, #7c3aed 100%);
    }

    .line-clamp-2 {
        display: -webkit-box;
        -webkit-line-clamp: 2;
        -webkit-box-orient: vertical;
        overflow: hidden;
    }
</style>

<script>
    function showReportModal(productId, productName, reportType) {
        document.getElementById('product_id').value = productId;
        document.getElementById('report_type').value = reportType;
        document.getElementById('productNameDisplay').innerHTML = `<strong>Produk:</strong> ${productName}`;
        document.getElementById('reportForm').action = `/products/${productId}/report`;
        document.getElementById('reportModal').classList.remove('hidden');
        document.getElementById('reportModal').classList.add('flex');
    }

    function closeReportModal() {
        document.getElementById('reportModal').classList.add('hidden');
        document.getElementById('reportModal').classList.remove('flex');
        document.getElementById('notes').value = '';
        document.getElementById('quantity').value = '';
    }
</script>
@endsection