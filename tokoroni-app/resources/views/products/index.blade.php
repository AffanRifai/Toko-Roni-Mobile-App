@extends('layouts.app')

@section('title', 'Daftar Produk')
@section('page-title', 'Manajemen Produk')
@section('page-subtitle', 'Kelola semua produk dan kategori dalam satu dashboard')

@section('content')
    <div class="min-h-screen bg-gradient-to-br from-blue-50/50 to-purple-50/30 p-4 md:p-6">
        <!-- Header with Gradient -->
        <div class="glass-effect rounded-3xl p-6 md:p-8 shadow-elegant mb-6 md:mb-8 animate-fade-in">
            <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-4 md:gap-6">
                <div class="flex items-center gap-3 md:gap-4">
                    <div class="relative">
                        <div
                            class="w-14 h-14 md:w-16 md:h-16 rounded-2xl bg-gradient-to-br from-blue-500 to-purple-600 flex items-center justify-center shadow-lg">
                            <i class="fas fa-boxes text-xl md:text-2xl text-white"></i>
                        </div>
                        <div
                            class="absolute -inset-1 md:-inset-2 bg-gradient-to-r from-blue-500 to-purple-600 rounded-2xl blur-xl opacity-20">
                        </div>
                    </div>
                    <div>
                        <h1 class="text-2xl md:text-3xl lg:text-4xl font-bold text-gray-800">Daftar Produk</h1>
                        <p class="text-sm md:text-base text-gray-600 mt-1">Kelola semua produk dalam satu dashboard</p>
                    </div>
                </div>
                <div class="flex flex-wrap gap-3">
                    <!-- Tambah Kategori Button -->
                    <button onclick="showAddCategoryModal()"
                        class="group flex items-center gap-2 px-5 py-3 bg-white/80 backdrop-blur-sm border border-gray-200 rounded-xl hover:bg-white hover:shadow-lg transition-all duration-300">
                        <i class="fas fa-tags text-purple-600 group-hover:scale-110 transition-transform"></i>
                        <span class="text-gray-700 font-medium">Tambah Kategori</span>
                    </button>
                    <!-- Tambah Produk Button -->
                    <a href="{{ route('products.create') }}"
                        class="group flex items-center gap-2 px-6 py-3 bg-gradient-to-r from-blue-500 to-purple-600 text-white rounded-xl hover:shadow-lg hover:-translate-y-0.5 transition-all duration-300">
                        <i class="fas fa-plus-circle group-hover:rotate-90 transition-transform"></i>
                        <span class="font-medium">Tambah Produk</span>
                    </a>
                </div>
            </div>
        </div>

        <!-- Advanced Stats Cards -->
        <div class="grid grid-cols-2 lg:grid-cols-4 gap-4 md:gap-6 mb-6">
            @php
                $statsConfig = [
                    [
                        'value' => $stats['total'] ?? 0,
                        'label' => 'Total Produk',
                        'icon' => 'box',
                        'gradient' => 'from-blue-500 to-cyan-500',
                        'bg' => 'from-blue-100 to-cyan-100',
                        'iconBg' => 'from-blue-500 to-cyan-600',
                    ],
                    [
                        'value' => $stats['active'] ?? 0,
                        'label' => 'Produk Aktif',
                        'icon' => 'check-circle',
                        'gradient' => 'from-emerald-500 to-green-500',
                        'bg' => 'from-emerald-100 to-green-100',
                        'iconBg' => 'from-emerald-500 to-green-600',
                    ],
                    [
                        'value' => $stats['low_stock'] ?? 0,
                        'label' => 'Stok Rendah',
                        'icon' => 'exclamation-triangle',
                        'gradient' => 'from-amber-500 to-orange-500',
                        'bg' => 'from-amber-100 to-orange-100',
                        'iconBg' => 'from-amber-500 to-orange-600',
                    ],
                    [
                        'value' => $stats['out_of_stock'] ?? 0,
                        'label' => 'Stok Habis',
                        'icon' => 'times-circle',
                        'gradient' => 'from-rose-500 to-pink-500',
                        'bg' => 'from-rose-100 to-pink-100',
                        'iconBg' => 'from-rose-500 to-pink-600',
                    ],
                ];
            @endphp

            @foreach ($statsConfig as $stat)
                <div class="stat-card group">
                    <div class="stat-card-glow bg-gradient-to-r {{ $stat['gradient'] }}"></div>
                    <div class="stat-card-content">
                        <div class="flex items-center justify-between mb-4">
                            <div class="flex items-center gap-3">
                                <div class="p-3 rounded-xl bg-gradient-to-br {{ $stat['iconBg'] }} shadow-lg">
                                    <i class="fas fa-{{ $stat['icon'] }} text-white"></i>
                                </div>
                                <div>
                                    <p class="text-sm text-gray-500">{{ $stat['label'] }}</p>
                                    <h3 class="text-2xl font-bold text-gray-800">{{ number_format($stat['value']) }}</h3>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>

        <!-- Expired & Expiring Products Section -->
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-4 md:gap-6 mb-6">
            <!-- Expiring Soon Products -->
            <div class="glass-effect rounded-2xl p-5">
                <div class="flex items-center justify-between mb-4">
                    <div class="flex items-center gap-2">
                        <div class="w-10 h-10 rounded-xl bg-gradient-to-br from-amber-500 to-orange-500 flex items-center justify-center">
                            <i class="fas fa-clock text-white"></i>
                        </div>
                        <div>
                            <h3 class="font-semibold text-gray-800">Produk Akan Kadaluarsa</h3>
                            <p class="text-xs text-gray-500">30 hari ke depan</p>
                        </div>
                    </div>
                    <span class="text-2xl font-bold text-amber-600">{{ $expiringSoonCount ?? 0 }}</span>
                </div>

                @if(isset($expiringProducts) && $expiringProducts->count() > 0)
                    <div class="space-y-3 max-h-80 overflow-y-auto pr-2 custom-scrollbar">
                        @foreach($expiringProducts as $product)
                            @php
                                $daysLeft = (int) floor($product->days_left);
                                $expiryClass = $daysLeft <= 7 ? 'text-red-600 bg-red-50' : 'text-amber-600 bg-amber-50';
                            @endphp
                            <div class="flex items-center justify-between p-3 bg-white/50 rounded-xl hover:bg-white transition-all">
                                <div class="flex items-center gap-3 min-w-0">
                                    <div class="w-8 h-8 rounded-lg bg-gray-100 flex items-center justify-center flex-shrink-0">
                                        <i class="fas fa-box text-gray-500"></i>
                                    </div>
                                    <div class="min-w-0">
                                        <h4 class="font-medium text-gray-900 truncate">{{ $product->name }}</h4>
                                        <p class="text-xs text-gray-500">Stok: {{ number_format($product->stock) }} {{ $product->unit }}</p>
                                    </div>
                                </div>
                                <div class="text-right flex-shrink-0 ml-2">
                                    <span class="text-xs font-medium px-2 py-1 rounded-full {{ $expiryClass }}">
                                        {{ $daysLeft == 0 ? 'Hari ini' : $daysLeft . ' hari lagi' }}
                                    </span>
                                    <p class="text-xs text-gray-500 mt-1">{{ Carbon\Carbon::parse($product->expiry_date)->format('d/m/Y') }}</p>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @else
                    <div class="text-center py-8">
                        <div class="w-16 h-16 bg-green-100 rounded-full flex items-center justify-center mx-auto mb-3">
                            <i class="fas fa-check-circle text-2xl text-green-600"></i>
                        </div>
                        <p class="text-gray-600">Tidak ada produk yang akan kadaluarsa</p>
                    </div>
                @endif
            </div>

            <!-- Expired Products -->
            <div class="glass-effect rounded-2xl p-5">
                <div class="flex items-center justify-between mb-4">
                    <div class="flex items-center gap-2">
                        <div class="w-10 h-10 rounded-xl bg-gradient-to-br from-red-500 to-rose-500 flex items-center justify-center">
                            <i class="fas fa-calendar-times text-white"></i>
                        </div>
                        <div>
                            <h3 class="font-semibold text-gray-800">Produk Kadaluarsa</h3>
                            <p class="text-xs text-gray-500">Perlu tindakan segera</p>
                        </div>
                    </div>
                    <span class="text-2xl font-bold text-red-600">{{ $expiredCount ?? 0 }}</span>
                </div>

                @if(isset($expiredProducts) && $expiredProducts->count() > 0)
                    <div class="space-y-3 max-h-80 overflow-y-auto pr-2 custom-scrollbar">
                        @foreach($expiredProducts as $product)
                            @php
                                $daysExpired = (int) floor($product->days_expired);
                            @endphp
                            <div class="flex items-center justify-between p-3 bg-red-50/50 rounded-xl hover:bg-red-50 transition-all">
                                <div class="flex items-center gap-3 min-w-0">
                                    <div class="w-8 h-8 rounded-lg bg-red-100 flex items-center justify-center flex-shrink-0">
                                        <i class="fas fa-exclamation-triangle text-red-600"></i>
                                    </div>
                                    <div class="min-w-0">
                                        <h4 class="font-medium text-gray-900 truncate">{{ $product->name }}</h4>
                                        <p class="text-xs text-gray-500">Stok: {{ number_format($product->stock) }} {{ $product->unit }}</p>
                                    </div>
                                </div>
                                <div class="text-right flex-shrink-0 ml-2">
                                    <span class="text-xs font-medium px-2 py-1 rounded-full bg-red-100 text-red-800">
                                        Expired {{ $daysExpired == 0 ? 'hari ini' : $daysExpired . ' hari lalu' }}
                                    </span>
                                    <p class="text-xs text-gray-500 mt-1">{{ Carbon\Carbon::parse($product->expiry_date)->format('d/m/Y') }}</p>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @else
                    <div class="text-center py-8">
                        <div class="w-16 h-16 bg-green-100 rounded-full flex items-center justify-center mx-auto mb-3">
                            <i class="fas fa-check-circle text-2xl text-green-600"></i>
                        </div>
                        <p class="text-gray-600">Tidak ada produk kadaluarsa</p>
                    </div>
                @endif
            </div>
        </div>

        <!-- Alert Messages -->
        @if (session('success'))
            <div class="alert alert-success mb-6" id="successAlert">
                <i class="fas fa-check-circle"></i>
                <span>{{ session('success') }}</span>
                <button onclick="this.parentElement.remove()"><i class="fas fa-times"></i></button>
            </div>
        @endif

        @if (session('error'))
            <div class="alert alert-error mb-6" id="errorAlert">
                <i class="fas fa-exclamation-circle"></i>
                <span>{{ session('error') }}</span>
                <button onclick="this.parentElement.remove()"><i class="fas fa-times"></i></button>
            </div>
        @endif

        <!-- Filter Section -->
        <div class="glass-effect rounded-2xl p-5 mb-6">
            <div class="flex items-center justify-between mb-4">
                <h3 class="font-semibold text-gray-800 flex items-center gap-2">
                    <i class="fas fa-filter text-blue-500"></i>
                    Filter Produk
                </h3>
                <div class="flex items-center gap-2 text-sm">
                    <span class="text-gray-500" id="filterResultCount">{{ $products->total() }} produk ditemukan</span>
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
                <!-- Search -->
                <div class="relative group">
                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                        <i class="fas fa-search text-gray-400 group-focus-within:text-blue-500 transition-colors"></i>
                    </div>
                    <input type="text" id="searchInput" placeholder="Cari nama, kode, atau deskripsi..."
                        class="w-full pl-10 pr-4 py-3 bg-white/50 backdrop-blur-sm border border-gray-200 rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all">
                </div>

                <!-- Category Filter -->
                <div class="relative">
                    <select id="categoryFilter"
                        class="w-full px-4 py-3 bg-white/50 backdrop-blur-sm border border-gray-200 rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-transparent appearance-none">
                        <option value="">Semua Kategori</option>
                        @foreach ($categories ?? [] as $category)
                            <option value="{{ $category->id }}">{{ $category->name }} ({{ $category->products_count ?? 0 }})</option>
                        @endforeach
                    </select>
                    <i class="fas fa-chevron-down absolute right-4 top-1/2 transform -translate-y-1/2 text-gray-400 pointer-events-none"></i>
                </div>

                <!-- Status Filter -->
                <div class="relative">
                    <select id="statusFilter"
                        class="w-full px-4 py-3 bg-white/50 backdrop-blur-sm border border-gray-200 rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-transparent appearance-none">
                        <option value="">Semua Status</option>
                        <option value="1">Aktif</option>
                        <option value="0">Nonaktif</option>
                    </select>
                    <i class="fas fa-chevron-down absolute right-4 top-1/2 transform -translate-y-1/2 text-gray-400 pointer-events-none"></i>
                </div>

                <!-- Stock Filter -->
                <div class="relative">
                    <select id="stockFilter"
                        class="w-full px-4 py-3 bg-white/50 backdrop-blur-sm border border-gray-200 rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-transparent appearance-none">
                        <option value="">Semua Stok</option>
                        <option value="low">Stok Rendah (&lt; Min Stock)</option>
                        <option value="normal">Stok Normal</option>
                        <option value="out">Stok Habis</option>
                    </select>
                    <i class="fas fa-chevron-down absolute right-4 top-1/2 transform -translate-y-1/2 text-gray-400 pointer-events-none"></i>
                </div>
            </div>

            <!-- Clear Filters Button -->
            <div class="flex justify-end mt-4">
                <button id="clearFilters"
                    class="px-4 py-2 border border-gray-200 rounded-lg hover:bg-gray-50 transition-colors text-sm">
                    <i class="fas fa-redo mr-2"></i>Reset Filter
                </button>
            </div>
        </div>

        <!-- Products Grid -->
        @php
            $stockBadgeClasses = [
                'low' => 'bg-amber-100 text-amber-800 border border-amber-200',
                'out' => 'bg-red-100 text-red-800 border border-red-200',
                'normal' => 'bg-emerald-100 text-emerald-800 border border-emerald-200',
            ];

            $statusBadgeClasses = [
                '1' => 'bg-emerald-100 text-emerald-800 border border-emerald-200',
                '0' => 'bg-gray-100 text-gray-800 border border-gray-200',
            ];
        @endphp

        <div id="productsContainer" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-5">
            @forelse($products as $product)
                @php
                    $stockStatus = $product->stock <= 0 ? 'out' : ($product->stock <= ($product->min_stock ?? 5) ? 'low' : 'normal');
                    $today = \Carbon\Carbon::today();
                    $expiryDate = $product->expiry_date ? \Carbon\Carbon::parse($product->expiry_date) : null;
                    $isExpired = $expiryDate ? $expiryDate < $today : false;
                    $daysLeft = $expiryDate ? (int) floor($today->diffInDays($expiryDate, false)) : null;
                @endphp

                <div class="product-card group bg-white/80 backdrop-blur-sm rounded-2xl shadow-soft border border-white/50 overflow-hidden hover:shadow-xl hover:-translate-y-1 transition-all duration-300"
                    data-category="{{ $product->category_id ?? '' }}" data-status="{{ $product->is_active }}"
                    data-stock="{{ $stockStatus }}">

                    <!-- Image Container with Hover Effects -->
                    <div class="relative h-48 bg-gradient-to-br from-gray-100 to-gray-200 overflow-hidden">
                        @if ($product->image && Storage::exists('public/' . $product->image))
                            <img src="{{ Storage::url($product->image) }}" alt="{{ $product->name }}"
                                class="w-full h-full object-cover group-hover:scale-110 transition-transform duration-700">
                        @else
                            <div class="w-full h-full flex items-center justify-center bg-gradient-to-br from-blue-100 to-purple-100">
                                <i class="fas fa-box text-6xl text-blue-300 group-hover:scale-110 transition-transform duration-700"></i>
                            </div>
                        @endif

                        <!-- Overlay with Quick Actions -->
                        <div class="absolute inset-0 bg-gradient-to-t from-black/50 via-transparent to-transparent opacity-0 group-hover:opacity-100 transition-opacity duration-300">
                            <div class="absolute bottom-3 left-3 right-3 flex justify-center gap-2">
                                <button onclick="showQuickView({{ $product->id }})"
                                    class="w-10 h-10 bg-white/90 backdrop-blur-sm rounded-xl flex items-center justify-center text-blue-600 hover:bg-blue-600 hover:text-white transition-all transform hover:scale-110"
                                    title="Quick View">
                                    <i class="fas fa-eye"></i>
                                </button>
                                <a href="{{ route('products.edit', $product->id) }}"
                                    class="w-10 h-10 bg-white/90 backdrop-blur-sm rounded-xl flex items-center justify-center text-amber-600 hover:bg-amber-600 hover:text-white transition-all transform hover:scale-110"
                                    title="Edit">
                                    <i class="fas fa-edit"></i>
                                </a>
                                <button onclick="confirmDelete({{ $product->id }}, '{{ $product->name }}')"
                                    class="w-10 h-10 bg-white/90 backdrop-blur-sm rounded-xl flex items-center justify-center text-red-600 hover:bg-red-600 hover:text-white transition-all transform hover:scale-110"
                                    title="Delete">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </div>
                        </div>

                        <!-- Badges -->
                        <div class="absolute top-3 left-3 flex flex-col gap-1">
                            <span class="badge {{ $statusBadgeClasses[$product->is_active] }}">
                                <i class="fas fa-{{ $product->is_active ? 'check-circle' : 'ban' }} mr-1 text-xs"></i>
                                {{ $product->is_active ? 'Aktif' : 'Nonaktif' }}
                            </span>
                            <span class="badge {{ $stockBadgeClasses[$stockStatus] }}">
                                <i class="fas fa-{{ $stockStatus == 'out' ? 'times-circle' : ($stockStatus == 'low' ? 'exclamation-circle' : 'check-circle') }} mr-1 text-xs"></i>
                                {{ $stockStatus == 'out' ? 'Habis' : ($stockStatus == 'low' ? 'Stok Rendah' : 'Tersedia') }}
                            </span>
                            @if($product->expiry_date)
                                @if($isExpired)
                                    <span class="badge bg-red-100 text-red-800 border border-red-200">
                                        <i class="fas fa-calendar-times mr-1 text-xs"></i>
                                        Expired
                                    </span>
                                @elseif($daysLeft <= 30)
                                    <span class="badge bg-amber-100 text-amber-800 border border-amber-200">
                                        <i class="fas fa-clock mr-1 text-xs"></i>
                                        {{ $daysLeft == 0 ? 'Hari ini' : $daysLeft . ' hari' }}
                                    </span>
                                @endif
                            @endif
                        </div>
                    </div>

                    <!-- Content -->
                    <div class="p-5">
                        <!-- Code and Category -->
                        <div class="flex items-center justify-between mb-2">
                            <span class="text-xs font-mono text-gray-500 bg-gray-100 px-2 py-1 rounded-lg">{{ $product->code }}</span>
                            <span class="text-xs text-gray-500 flex items-center gap-1">
                                <i class="fas fa-tag"></i>
                                {{ $product->category->name ?? '-' }}
                            </span>
                        </div>

                        <!-- Name -->
                        <h3 class="font-semibold text-gray-900 mb-2 line-clamp-1 group-hover:text-blue-600 transition-colors">
                            {{ $product->name }}
                        </h3>

                        <!-- Price and Stock -->
                        <div class="flex items-end justify-between mt-3 pt-3 border-t border-gray-100">
                            <div>
                                <p class="text-xs text-gray-500 mb-1">Harga Jual</p>
                                <p class="text-xl font-bold text-transparent bg-clip-text bg-gradient-to-r from-blue-600 to-purple-600">
                                    Rp {{ number_format($product->price, 0, ',', '.') }}
                                </p>
                            </div>
                            <div class="text-right">
                                <p class="text-xs text-gray-500 mb-1">Stok</p>
                                <p class="text-lg font-semibold {{ $stockStatus == 'out' ? 'text-red-600' : ($stockStatus == 'low' ? 'text-amber-600' : 'text-emerald-600') }}">
                                    {{ number_format($product->stock) }}
                                    <span class="text-xs font-normal text-gray-500">{{ $product->unit }}</span>
                                </p>
                            </div>
                        </div>

                        <!-- Expiry Date if exists -->
                        @if($product->expiry_date)
                            <div class="mt-2 text-xs text-gray-500 flex items-center gap-1">
                                <i class="fas fa-calendar-alt"></i>
                                Exp: {{ Carbon\Carbon::parse($product->expiry_date)->format('d/m/Y') }}
                            </div>
                        @endif
                    </div>
                </div>
            @empty
                <div class="col-span-full">
                    <div class="glass-effect rounded-3xl p-12 text-center">
                        <div class="relative w-24 h-24 mx-auto mb-6">
                            <div class="absolute inset-0 bg-gradient-to-r from-blue-500 to-purple-600 rounded-full blur-xl opacity-20"></div>
                            <div class="relative w-24 h-24 bg-white rounded-full flex items-center justify-center">
                                <i class="fas fa-box-open text-4xl text-gray-400"></i>
                            </div>
                        </div>
                        <h3 class="text-2xl font-bold text-gray-800 mb-2">Belum ada produk</h3>
                        <p class="text-gray-600 mb-6">Mulai dengan menambahkan produk pertama Anda</p>
                        <a href="{{ route('products.create') }}"
                            class="inline-flex items-center gap-2 px-6 py-3 bg-gradient-to-r from-blue-500 to-purple-600 text-white rounded-xl hover:shadow-lg hover:-translate-y-0.5 transition-all duration-300">
                            <i class="fas fa-plus-circle"></i>
                            Tambah Produk Pertama
                        </a>
                    </div>
                </div>
            @endforelse
        </div>

        <!-- Pagination -->
        @if ($products->hasPages())
            <div class="mt-8 flex flex-col sm:flex-row items-center justify-between gap-4">
                <div class="text-sm text-gray-600">
                    Menampilkan {{ $products->firstItem() }} - {{ $products->lastItem() }} dari {{ $products->total() }} produk
                </div>
                <div class="flex items-center gap-2">
                    {{ $products->links() }}
                </div>
            </div>
        @endif

        <!-- Categories Section -->
        <div class="mt-8">
            <div class="glass-effect rounded-3xl p-6">
                <div class="flex items-center justify-between mb-6">
                    <div>
                        <h3 class="text-xl font-bold text-gray-800 flex items-center gap-2">
                            <i class="fas fa-tags text-purple-500"></i>
                            Daftar Kategori
                        </h3>
                        <p class="text-sm text-gray-600 mt-1">{{ $categories->count() }} kategori tersedia</p>
                    </div>
                </div>

                @if ($categories->count() > 0)
                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                        @foreach ($categories as $category)
                            <div class="group bg-white/50 backdrop-blur-sm rounded-xl p-4 border border-gray-200/50 hover:border-purple-200 hover:shadow-md transition-all">
                                <div class="flex items-start justify-between">
                                    <div class="flex items-center gap-3">
                                        <div class="w-10 h-10 rounded-xl bg-gradient-to-br from-purple-100 to-pink-100 flex items-center justify-center group-hover:scale-110 transition-transform">
                                            <i class="fas fa-tag text-purple-600"></i>
                                        </div>
                                        <div>
                                            <h4 class="font-semibold text-gray-900 group-hover:text-purple-600 transition-colors">
                                                {{ $category->name }}
                                            </h4>
                                            <div class="flex items-center gap-3 mt-1 text-xs text-gray-500">
                                                <span class="flex items-center gap-1">
                                                    <i class="fas fa-box"></i>
                                                    {{ $category->products_count ?? 0 }} produk
                                                </span>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="flex gap-1">
                                        <button onclick="editCategory({{ $category->id }}, '{{ $category->name }}')"
                                            class="w-8 h-8 rounded-lg hover:bg-amber-50 text-amber-600 transition-all"
                                            title="Edit Kategori">
                                            <i class="fas fa-edit"></i>
                                        </button>
                                        @if (($category->products_count ?? 0) == 0)
                                            <button onclick="confirmDeleteCategory({{ $category->id }}, '{{ $category->name }}')"
                                                class="w-8 h-8 rounded-lg hover:bg-red-50 text-red-600 transition-all"
                                                title="Hapus Kategori">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        @else
                                            <button class="w-8 h-8 rounded-lg bg-gray-100 text-gray-400 cursor-not-allowed"
                                                title="Tidak dapat dihapus (masih digunakan)" disabled>
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @else
                    <div class="text-center py-8">
                        <div class="relative w-20 h-20 mx-auto mb-4">
                            <div class="absolute inset-0 bg-gradient-to-r from-purple-500 to-pink-500 rounded-full blur-xl opacity-20"></div>
                            <div class="relative w-20 h-20 bg-white rounded-full flex items-center justify-center">
                                <i class="fas fa-tags text-3xl text-gray-400"></i>
                            </div>
                        </div>
                        <p class="text-gray-600 mb-4">Belum ada kategori</p>
                        <button onclick="showAddCategoryModal()"
                            class="inline-flex items-center gap-2 px-4 py-2 bg-gradient-to-r from-purple-500 to-pink-500 text-white rounded-lg hover:shadow-md transition-all">
                            <i class="fas fa-plus-circle"></i>
                            Tambah Kategori Pertama
                        </button>
                    </div>
                @endif
            </div>
        </div>
    </div>

    <!-- Quick View Modal -->
    <div id="quickViewModal" class="modal hidden">
        <div class="modal-content max-w-2xl">
            <div class="modal-header">
                <h3 class="text-xl font-bold text-gray-800">Detail Produk</h3>
                <button onclick="closeModal('quickViewModal')" class="modal-close">
                    <i class="fas fa-times"></i>
                </button>
            </div>
            <div class="modal-body p-6" id="quickViewContent">
                <!-- Content will be loaded here -->
            </div>
        </div>
    </div>

    <!-- Delete Product Modal -->
    <div id="deleteModal" class="modal hidden">
        <div class="modal-content max-w-md">
            <div class="p-6 text-center">
                <div class="relative w-20 h-20 mx-auto mb-4">
                    <div class="absolute inset-0 bg-gradient-to-r from-red-500 to-pink-500 rounded-full blur-xl opacity-20"></div>
                    <div class="relative w-20 h-20 bg-gradient-to-br from-red-100 to-pink-100 rounded-full flex items-center justify-center">
                        <i class="fas fa-exclamation-triangle text-3xl text-red-600"></i>
                    </div>
                </div>
                <h3 class="text-xl font-bold text-gray-800 mb-2">Hapus Produk?</h3>
                <p class="text-gray-600 mb-6" id="deleteProductName"></p>
                <form id="deleteForm" method="POST" class="space-y-4">
                    @csrf
                    @method('DELETE')
                    <div class="flex gap-3">
                        <button type="button" onclick="closeModal('deleteModal')"
                            class="flex-1 py-3 border border-gray-200 rounded-xl hover:bg-gray-50 transition-colors font-medium">
                            Batal
                        </button>
                        <button type="submit"
                            class="flex-1 py-3 bg-gradient-to-r from-red-600 to-pink-600 text-white rounded-xl hover:shadow-lg transition-all font-medium">
                            <i class="fas fa-trash mr-2"></i>
                            Hapus
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Add/Edit Category Modal -->
    <div id="categoryModal" class="modal hidden">
        <div class="modal-content max-w-md">
            <div class="modal-header">
                <h3 class="text-xl font-bold text-gray-800" id="categoryModalTitle">Tambah Kategori</h3>
                <button onclick="closeModal('categoryModal')" class="modal-close">
                    <i class="fas fa-times"></i>
                </button>
            </div>
            <div class="modal-body p-6">
                <form id="categoryForm" method="POST" action="{{ route('categories.store') }}" class="space-y-4">
                    @csrf
                    <input type="hidden" id="categoryId" name="category_id" value="">

                    <div>
                        <label for="categoryName" class="block text-sm font-medium text-gray-700 mb-1">
                            Nama Kategori <span class="text-red-500">*</span>
                        </label>
                        <input type="text" id="categoryName" name="name" required
                            class="w-full px-4 py-3 border border-gray-200 rounded-xl focus:ring-2 focus:ring-purple-500 focus:border-transparent transition-all"
                            placeholder="Contoh: Makanan, Minuman, Elektronik">
                    </div>

                    <div>
                        <label for="categoryDescription" class="block text-sm font-medium text-gray-700 mb-1">
                            Deskripsi
                        </label>
                        <textarea id="categoryDescription" name="description" rows="3"
                            class="w-full px-4 py-3 border border-gray-200 rounded-xl focus:ring-2 focus:ring-purple-500 focus:border-transparent transition-all"
                            placeholder="Deskripsi kategori..."></textarea>
                    </div>

                    <div class="flex gap-3 pt-4 border-t border-gray-200">
                        <button type="button" onclick="closeModal('categoryModal')"
                            class="flex-1 py-3 border border-gray-200 rounded-xl hover:bg-gray-50 transition-colors font-medium">
                            Batal
                        </button>
                        <button type="submit"
                            class="flex-1 py-3 bg-gradient-to-r from-purple-600 to-pink-600 text-white rounded-xl hover:shadow-lg transition-all font-medium">
                            <i class="fas fa-save mr-2"></i>
                            Simpan
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Delete Category Modal -->
    <div id="deleteCategoryModal" class="modal hidden">
        <div class="modal-content max-w-md">
            <div class="p-6 text-center">
                <div class="relative w-20 h-20 mx-auto mb-4">
                    <div class="absolute inset-0 bg-gradient-to-r from-red-500 to-pink-500 rounded-full blur-xl opacity-20"></div>
                    <div class="relative w-20 h-20 bg-gradient-to-br from-red-100 to-pink-100 rounded-full flex items-center justify-center">
                        <i class="fas fa-exclamation-triangle text-3xl text-red-600"></i>
                    </div>
                </div>
                <h3 class="text-xl font-bold text-gray-800 mb-2">Hapus Kategori?</h3>
                <p class="text-gray-600 mb-2" id="deleteCategoryName"></p>
                <p class="text-sm text-gray-500 mb-6">Kategori akan dihapus secara permanen</p>
                <form id="deleteCategoryForm" method="POST" class="space-y-4">
                    @csrf
                    @method('DELETE')
                    <div class="flex gap-3">
                        <button type="button" onclick="closeModal('deleteCategoryModal')"
                            class="flex-1 py-3 border border-gray-200 rounded-xl hover:bg-gray-50 transition-colors font-medium">
                            Batal
                        </button>
                        <button type="submit"
                            class="flex-1 py-3 bg-gradient-to-r from-red-600 to-pink-600 text-white rounded-xl hover:shadow-lg transition-all font-medium">
                            <i class="fas fa-trash mr-2"></i>
                            Hapus
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script>
        // Modal functions
        function showModal(id) {
            document.getElementById(id).classList.remove('hidden');
            document.body.style.overflow = 'hidden';
        }

        function closeModal(id) {
            document.getElementById(id).classList.add('hidden');
            document.body.style.overflow = 'auto';
        }

        // Category CRUD
        function showAddCategoryModal() {
            document.getElementById('categoryModalTitle').textContent = 'Tambah Kategori';
            document.getElementById('categoryForm').action = "{{ route('categories.store') }}";
            document.getElementById('categoryId').value = '';
            document.getElementById('categoryName').value = '';
            document.getElementById('categoryDescription').value = '';

            // Reset method
            const form = document.getElementById('categoryForm');
            const methodInput = form.querySelector('input[name="_method"]');
            if (methodInput) methodInput.remove();
            form.method = 'POST';

            showModal('categoryModal');
        }

        function editCategory(categoryId, categoryName) {
            document.getElementById('categoryModalTitle').textContent = 'Edit Kategori';
            document.getElementById('categoryForm').action = `/categories/${categoryId}`;
            document.getElementById('categoryId').value = categoryId;
            document.getElementById('categoryName').value = categoryName;

            // Change method to PUT
            const form = document.getElementById('categoryForm');
            if (!form.querySelector('input[name="_method"]')) {
                const methodInput = document.createElement('input');
                methodInput.type = 'hidden';
                methodInput.name = '_method';
                methodInput.value = 'PUT';
                form.appendChild(methodInput);
            }

            showModal('categoryModal');
        }

        function confirmDeleteCategory(categoryId, categoryName) {
            document.getElementById('deleteCategoryName').textContent = `"${categoryName}" akan dihapus?`;
            document.getElementById('deleteCategoryForm').action = `/categories/${categoryId}`;
            showModal('deleteCategoryModal');
        }

        // Product CRUD
        async function showQuickView(productId) {
            const content = document.getElementById('quickViewContent');
            content.innerHTML = `
                <div class="animate-pulse">
                    <div class="flex flex-col md:flex-row gap-6">
                        <div class="md:w-1/2">
                            <div class="bg-gray-200 h-64 rounded-xl"></div>
                        </div>
                        <div class="md:w-1/2 space-y-4">
                            <div class="h-8 bg-gray-200 rounded w-3/4"></div>
                            <div class="h-4 bg-gray-200 rounded w-1/2"></div>
                            <div class="h-6 bg-gray-200 rounded w-1/4"></div>
                            <div class="h-4 bg-gray-200 rounded w-full"></div>
                        </div>
                    </div>
                </div>
            `;

            showModal('quickViewModal');

            try {
                const response = await fetch(`/products/${productId}/quick-view`, {
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest',
                        'Accept': 'application/json'
                    }
                });

                if (!response.ok) throw new Error('Network response was not ok');

                const product = await response.json();

                const expiryDate = product.expiry_date ? new Date(product.expiry_date) : null;
                const today = new Date();
                today.setHours(0, 0, 0, 0);
                
                let expiryHtml = '';
                if (expiryDate) {
                    const isExpired = expiryDate < today;
                    const daysLeft = Math.floor((expiryDate - today) / (1000 * 60 * 60 * 24));
                    expiryHtml = `
                        <div class="flex justify-between items-center py-2 border-b border-gray-100">
                            <span class="text-gray-600">Tanggal Kadaluarsa</span>
                            <span class="font-medium ${isExpired ? 'text-red-600' : 'text-amber-600'}">
                                ${expiryDate.toLocaleDateString('id-ID')}
                                ${isExpired ? ' (Expired)' : daysLeft == 0 ? ' (Hari ini)' : ` (${daysLeft} hari lagi)`}
                            </span>
                        </div>
                    `;
                }

                content.innerHTML = `
                    <div class="flex flex-col md:flex-row gap-6">
                        <div class="md:w-1/2">
                            ${product.image_url ?
                                `<img src="${product.image_url}" alt="${product.name}" class="w-full h-64 object-cover rounded-xl">` :
                                `<div class="bg-gradient-to-br from-gray-100 to-gray-200 h-64 rounded-xl flex items-center justify-center">
                                    <i class="fas fa-box text-6xl text-gray-400"></i>
                                </div>`
                            }
                        </div>
                        <div class="md:w-1/2">
                            <h4 class="text-2xl font-bold text-gray-900 mb-4">${product.name}</h4>
                            <div class="space-y-3">
                                <div class="flex justify-between items-center py-2 border-b border-gray-100">
                                    <span class="text-gray-600">Kode Produk</span>
                                    <span class="font-mono font-medium">${product.code}</span>
                                </div>
                                <div class="flex justify-between items-center py-2 border-b border-gray-100">
                                    <span class="text-gray-600">Kategori</span>
                                    <span class="font-medium">${product.category_name || '-'}</span>
                                </div>
                                <div class="flex justify-between items-center py-2 border-b border-gray-100">
                                    <span class="text-gray-600">Harga Jual</span>
                                    <span class="font-bold text-green-600 text-xl">Rp ${parseInt(product.price).toLocaleString('id-ID')}</span>
                                </div>
                                <div class="flex justify-between items-center py-2 border-b border-gray-100">
                                    <span class="text-gray-600">Stok Tersedia</span>
                                    <span class="font-semibold">${parseInt(product.stock).toLocaleString('id-ID')} ${product.unit}</span>
                                </div>
                                ${expiryHtml}
                                ${product.description ? `
                                    <div class="pt-3">
                                        <p class="text-gray-600 mb-2"><strong>Deskripsi:</strong></p>
                                        <p class="text-gray-600 bg-gray-50 p-3 rounded-lg">${product.description}</p>
                                    </div>
                                ` : ''}
                            </div>
                        </div>
                    </div>
                `;
            } catch (error) {
                console.error('Error:', error);
                content.innerHTML = `
                    <div class="text-center py-12">
                        <div class="text-red-500 text-5xl mb-4">
                            <i class="fas fa-exclamation-circle"></i>
                        </div>
                        <p class="text-gray-600 mb-4">Gagal memuat data produk</p>
                        <button onclick="showQuickView(${productId})" class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors">
                            <i class="fas fa-redo mr-2"></i>
                            Coba Lagi
                        </button>
                    </div>
                `;
            }
        }

        function confirmDelete(productId, productName) {
            document.getElementById('deleteProductName').textContent = `"${productName}" akan dihapus?`;
            document.getElementById('deleteForm').action = `/products/${productId}`;
            showModal('deleteModal');
        }

        // Filter functionality
        let searchTimeout;

        function filterProducts() {
            const searchTerm = document.getElementById('searchInput').value.toLowerCase();
            const category = document.getElementById('categoryFilter').value;
            const status = document.getElementById('statusFilter').value;
            const stock = document.getElementById('stockFilter').value;

            const productCards = document.querySelectorAll('.product-card');
            let visibleCount = 0;

            productCards.forEach(card => {
                const name = card.querySelector('h3').textContent.toLowerCase();
                const code = card.querySelector('.text-xs.font-mono').textContent.toLowerCase();
                const cardCategory = card.dataset.category;
                const cardStatus = card.dataset.status;
                const cardStock = card.dataset.stock;

                const matchesSearch = !searchTerm ||
                    name.includes(searchTerm) ||
                    code.includes(searchTerm);

                const matchesCategory = !category || cardCategory == category;
                const matchesStatus = !status || cardStatus == status;
                const matchesStock = !stock || cardStock == stock;

                const isVisible = matchesSearch && matchesCategory && matchesStatus && matchesStock;

                if (isVisible) {
                    card.classList.remove('hidden');
                    visibleCount++;
                } else {
                    card.classList.add('hidden');
                }
            });

            // Update result count
            document.getElementById('filterResultCount').textContent = `${visibleCount} produk ditemukan`;

            // Show empty state if no products
            const container = document.getElementById('productsContainer');
            const existingEmptyState = container.querySelector('.empty-state');

            if (visibleCount === 0 && productCards.length > 0) {
                if (!existingEmptyState) {
                    const emptyState = document.createElement('div');
                    emptyState.className = 'empty-state col-span-full text-center py-12';
                    emptyState.innerHTML = `
                        <div class="relative w-20 h-20 mx-auto mb-4">
                            <div class="absolute inset-0 bg-gradient-to-r from-gray-400 to-gray-500 rounded-full blur-xl opacity-20"></div>
                            <div class="relative w-20 h-20 bg-white rounded-full flex items-center justify-center">
                                <i class="fas fa-search text-3xl text-gray-400"></i>
                            </div>
                        </div>
                        <h3 class="text-xl font-semibold text-gray-800 mb-2">Produk tidak ditemukan</h3>
                        <p class="text-gray-600 mb-4">Coba kata kunci atau filter lain</p>
                        <button onclick="resetAllFilters()"
                                class="px-4 py-2 bg-gradient-to-r from-blue-500 to-purple-600 text-white rounded-lg hover:shadow-md transition-all">
                            <i class="fas fa-redo mr-2"></i>
                            Reset Filter
                        </button>
                    `;
                    container.appendChild(emptyState);
                }
            } else if (existingEmptyState) {
                existingEmptyState.remove();
            }
        }

        function resetAllFilters() {
            document.getElementById('searchInput').value = '';
            document.getElementById('categoryFilter').value = '';
            document.getElementById('statusFilter').value = '';
            document.getElementById('stockFilter').value = '';
            filterProducts();
        }

        // Event Listeners
        document.addEventListener('DOMContentLoaded', function() {
            // Search with debounce
            const searchInput = document.getElementById('searchInput');
            if (searchInput) {
                searchInput.addEventListener('input', () => {
                    clearTimeout(searchTimeout);
                    searchTimeout = setTimeout(filterProducts, 300);
                });
            }

            // Filter changes
            document.getElementById('categoryFilter').addEventListener('change', filterProducts);
            document.getElementById('statusFilter').addEventListener('change', filterProducts);
            document.getElementById('stockFilter').addEventListener('change', filterProducts);

            // Clear filters button
            const clearFilters = document.getElementById('clearFilters');
            if (clearFilters) {
                clearFilters.addEventListener('click', resetAllFilters);
            }

            // Close modals on ESC
            document.addEventListener('keydown', (e) => {
                if (e.key === 'Escape') {
                    ['quickViewModal', 'deleteModal', 'categoryModal', 'deleteCategoryModal']
                        .forEach(modal => {
                            const modalElement = document.getElementById(modal);
                            if (modalElement && !modalElement.classList.contains('hidden')) {
                                closeModal(modal);
                            }
                        });
                }
            });
        });
    </script>

    <style>
        /* Modal Styles */
        .modal {
            position: fixed;
            inset: 0;
            background: rgba(0, 0, 0, 0.5);
            backdrop-filter: blur(4px);
            z-index: 50;
            overflow-y: auto;
            animation: fadeIn 0.3s ease-out;
        }

        .modal:not(.hidden) {
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .modal-content {
            background: white;
            border-radius: 1.5rem;
            width: 90%;
            max-width: 500px;
            margin: 2rem auto;
            box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.25);
            animation: slideUp 0.3s ease-out;
        }

        .modal-header {
            padding: 1.5rem;
            border-bottom: 1px solid #f0f0f0;
            display: flex;
            align-items: center;
            justify-content: space-between;
        }

        .modal-body {
            padding: 1.5rem;
        }

        .modal-close {
            width: 2rem;
            height: 2rem;
            border-radius: 0.5rem;
            display: flex;
            align-items: center;
            justify-content: center;
            color: #6b7280;
            transition: all 0.3s;
        }

        .modal-close:hover {
            background: #f3f4f6;
            color: #1f2937;
        }

        /* Stat Card Styles */
        .stat-card {
            position: relative;
            background: rgba(255, 255, 255, 0.8);
            backdrop-filter: blur(8px);
            -webkit-backdrop-filter: blur(8px);
            border-radius: 1.5rem;
            padding: 1.5rem;
            border: 1px solid rgba(255, 255, 255, 0.5);
            transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
        }

        .stat-card:hover {
            box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.1), 0 10px 10px -5px rgba(0, 0, 0, 0.04);
            transform: translateY(-4px);
            border-color: rgba(59, 130, 246, 0.3);
        }

        .stat-card-glow {
            position: absolute;
            inset: -0.25rem;
            border-radius: 1.75rem;
            filter: blur(12px);
            opacity: 0;
            transition: opacity 0.5s;
            z-index: -1;
        }

        .stat-card:hover .stat-card-glow {
            opacity: 0.5;
        }

        /* Badge Styles */
        .badge {
            display: inline-flex;
            align-items: center;
            padding: 0.25rem 0.75rem;
            border-radius: 9999px;
            font-size: 0.75rem;
            font-weight: 500;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.05);
        }

        /* Glass Effect */
        .glass-effect {
            background: rgba(255, 255, 255, 0.7);
            backdrop-filter: blur(10px);
            -webkit-backdrop-filter: blur(10px);
            border: 1px solid rgba(59, 130, 246, 0.1);
        }

        /* Alert Styles */
        .alert {
            padding: 1rem;
            border-radius: 1rem;
            display: flex;
            align-items: center;
            gap: 0.75rem;
            animation: slideIn 0.3s ease-out;
        }

        .alert-success {
            background: #ecfdf5;
            border: 1px solid #a7f3d0;
            color: #065f46;
        }

        .alert-error {
            background: #fef2f2;
            border: 1px solid #fecaca;
            color: #991b1b;
        }

        .alert button {
            margin-left: auto;
            opacity: 0.5;
            transition: opacity 0.3s;
        }

        .alert button:hover {
            opacity: 1;
        }

        /* Custom Scrollbar */
        .custom-scrollbar::-webkit-scrollbar {
            width: 6px;
        }

        .custom-scrollbar::-webkit-scrollbar-track {
            background: #f1f1f1;
            border-radius: 10px;
        }

        .custom-scrollbar::-webkit-scrollbar-thumb {
            background: #cbd5e1;
            border-radius: 10px;
        }

        .custom-scrollbar::-webkit-scrollbar-thumb:hover {
            background: #94a3b8;
        }

        /* Animations */
        .animate-fade-in {
            animation: fadeIn 0.6s ease-out;
        }

        .animate-pulse {
            animation: pulse 2s cubic-bezier(0.4, 0, 0.6, 1) infinite;
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

        @keyframes slideUp {
            from {
                opacity: 0;
                transform: translateY(30px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        @keyframes slideIn {
            from {
                opacity: 0;
                transform: translateX(-20px);
            }
            to {
                opacity: 1;
                transform: translateX(0);
            }
        }

        @keyframes pulse {
            0%, 100% {
                opacity: 1;
            }
            50% {
                opacity: .5;
            }
        }

        /* Text truncation */
        .line-clamp-1 {
            overflow: hidden;
            display: -webkit-box;
            -webkit-line-clamp: 1;
            -webkit-box-orient: vertical;
        }

        /* Responsive adjustments */
        @media (max-width: 640px) {
            .modal-content {
                width: 95%;
                margin: 1rem auto;
            }

            .stat-card {
                padding: 1rem;
            }

            .stat-card h3 {
                font-size: 1.5rem;
            }
        }

        .hidden {
            display: none !important;
        }
    </style>
@endsection