<?php $__env->startSection('title', 'Dashboard Gudang'); ?>
<?php $__env->startSection('page-title', 'Dashboard Gudang'); ?>
<?php $__env->startSection('page-subtitle', 'Manajemen Inventori & Stok'); ?>

<?php $__env->startSection('content'); ?>
<div class="min-h-screen bg-gradient-to-br from-blue-50/50 to-amber-50/30 p-4 md:p-6">
    <!-- Welcome Header Gudang -->
    <div class="glass-effect rounded-3xl p-6 md:p-8 shadow-elegant mb-6 md:mb-8 animate-fade-in">
        <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-4 md:gap-6">
            <div class="flex-1">
                <div class="flex items-center gap-3 md:gap-4 mb-4">
                    <div class="relative">
                        <div class="w-12 h-12 md:w-16 md:h-16 rounded-2xl bg-gradient-to-br from-amber-500 to-orange-600 flex items-center justify-center shadow-lg">
                            <i class="fas fa-warehouse text-xl md:text-2xl text-white"></i>
                        </div>
                        <div class="absolute -inset-1 md:-inset-2 bg-gradient-to-r from-amber-500 to-orange-600 rounded-2xl blur-xl opacity-20"></div>
                    </div>
                    <div>
                        <h1 class="text-xl md:text-3xl font-bold text-gray-800">Halo, <span class="gradient-text"><?php echo e(Auth::user()->name); ?>!</span> 📦</h1>
                        <p class="text-sm md:text-base text-gray-600 mt-1 md:mt-2">Status inventori gudang hari ini</p>
                    </div>
                </div>
                <div class="flex flex-wrap gap-3">
                    <div class="flex items-center gap-2 px-3 py-2 bg-amber-50 rounded-lg">
                        <i class="fas fa-map-marker-alt text-amber-600"></i>
                        <span class="text-sm font-medium text-gray-700">Gudang Utama</span>
                    </div>
                    <div class="flex items-center gap-2 px-3 py-2 bg-blue-50 rounded-lg">
                        <i class="fas fa-calendar-day text-blue-600"></i>
                        <span class="text-sm font-medium text-gray-700"><?php echo e(now()->translatedFormat('l, d F Y')); ?></span>
                    </div>
                </div>
            </div>
            <div class="mt-4 lg:mt-0">
                <a href="<?php echo e(route('products.create')); ?>"
                   class="inline-flex items-center gap-2 px-6 py-3 md:py-4 bg-gradient-to-r from-amber-500 to-orange-600 text-white font-semibold rounded-xl hover:shadow-lg transition-all duration-300 hover:-translate-y-1">
                    <i class="fas fa-plus-circle"></i>
                    <span>Tambah Produk</span>
                </a>
            </div>
        </div>
    </div>

    <!-- Stats Grid Gudang - Baris Pertama (4 Card) -->
    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 md:gap-6 mb-6 md:mb-8">
        <!-- Total Produk -->
        <div class="bg-white/80 backdrop-blur-sm rounded-2xl p-5 md:p-6 shadow-sm border border-white/50 hover:shadow-md transition-all">
            <div class="flex items-center justify-between">
                <div class="flex items-center gap-3 md:gap-4">
                    <div class="p-3 md:p-4 rounded-2xl bg-gradient-to-br from-indigo-500 to-blue-600 shadow-lg">
                        <i class="fas fa-boxes text-lg md:text-xl text-white"></i>
                    </div>
                    <div>
                        <p class="text-xs md:text-sm text-gray-500 font-medium">Total Produk</p>
                        <h3 class="text-xl md:text-3xl font-bold text-gray-800 mt-1"><?php echo e(number_format($totalProducts ?? 0)); ?></h3>
                    </div>
                </div>
                <a href="<?php echo e(route('products.index')); ?>" class="w-8 h-8 rounded-lg bg-gray-100 flex items-center justify-center text-gray-600 hover:bg-blue-500 hover:text-white transition-all">
                    <i class="fas fa-arrow-right text-xs"></i>
                </a>
            </div>
            <div class="flex items-center justify-between mt-4 pt-3 border-t border-gray-100">
                <div class="flex items-center text-xs md:text-sm">
                    <div class="w-6 h-6 md:w-8 md:h-8 rounded-lg bg-emerald-100 flex items-center justify-center mr-2">
                        <i class="fas fa-arrow-up text-emerald-600 text-xs"></i>
                    </div>
                    <span class="text-emerald-600 font-semibold"><?php echo e($totalCategories ?? 0); ?> kategori</span>
                    <span class="text-gray-500 ml-2 hidden sm:inline"><?php echo e($activeProducts ?? 0); ?> aktif</span>
                </div>
                <div class="text-xs text-gray-400">Products</div>
            </div>
        </div>

        <!-- Stok Rendah -->
        <div class="bg-white/80 backdrop-blur-sm rounded-2xl p-5 md:p-6 shadow-sm border border-white/50 hover:shadow-md transition-all">
            <div class="flex items-center justify-between">
                <div class="flex items-center gap-3 md:gap-4">
                    <div class="p-3 md:p-4 rounded-2xl bg-gradient-to-br from-indigo-500 to-blue-600 shadow-lg">
                        <i class="fas fa-exclamation-triangle text-lg md:text-xl text-white"></i>
                    </div>
                    <div>
                        <p class="text-xs md:text-sm text-gray-500 font-medium">Stok Rendah</p>
                        <h3 class="text-xl md:text-3xl font-bold text-gray-800 mt-1"><?php echo e(number_format($lowStockProducts ?? 0)); ?></h3>
                    </div>
                </div>
                <a href="<?php echo e(route('products.index')); ?>?filter=low_stock" class="w-8 h-8 rounded-lg bg-gray-100 flex items-center justify-center text-gray-600 hover:bg-red-500 hover:text-white transition-all">
                    <i class="fas fa-arrow-right text-xs"></i>
                </a>
            </div>
            <div class="flex items-center justify-between mt-4 pt-3 border-t border-gray-100">
                <div class="flex items-center text-xs md:text-sm">
                    <div class="w-6 h-6 md:w-8 md:h-8 rounded-lg bg-red-100 flex items-center justify-center mr-2">
                        <i class="fas fa-exclamation text-red-600 text-xs"></i>
                    </div>
                    <span class="text-red-600 font-semibold">Perlu Restock</span>
                    <span class="text-gray-500 ml-2 hidden sm:inline">segera</span>
                </div>
                <div class="text-xs text-gray-400">Alert</div>
            </div>
        </div>

        <!-- Nilai Inventori -->
        <div class="bg-white/80 backdrop-blur-sm rounded-2xl p-5 md:p-2 shadow-sm border border-white/50 hover:shadow-md transition-all">
            <div class="flex items-center justify-between">
                <div class="flex items-center gap-3 md:gap-4">
                    <div class="p-3 md:p-4 rounded-2xl bg-gradient-to-br from-emerald-500 to-green-600 shadow-lg">
                        <i class="fas fa-coins text-lg md:text-xl text-white"></i>
                    </div>
                    <div>
                        <p class="text-xs md:text-sm text-gray-500 font-medium">Nilai Inventori</p>
                        <h3 class="text-xl md:text-3xl font-bold text-gray-800 mt-1">Rp <?php echo e(number_format($inventoryValue ?? 0, 0, ',', '.')); ?></h3>
                    </div>
                </div>
                
                <!-- FORM UNTUK EXPORT PDF INVENTORY -->
                <form action="<?php echo e(route('reports.export.pdf')); ?>" method="POST" target="_blank">
                    <?php echo csrf_field(); ?>
                    <input type="hidden" name="type" value="inventory">
                    <button type="submit" class="w-8 h-8 rounded-lg bg-gray-100 flex items-center justify-center text-gray-600 hover:bg-emerald-500 hover:text-white transition-all">
                        <i class="fas fa-arrow-right text-xs"></i>
                    </button>
                </form>
            </div>
            <div class="flex items-center justify-between mt-4 pt-3 border-t border-gray-100">
                <div class="flex items-center text-xs md:text-sm">
                    <div class="w-6 h-6 md:w-8 md:h-8 rounded-lg bg-emerald-100 flex items-center justify-center mr-2">
                        <i class="fas fa-arrow-up text-emerald-600 text-xs"></i>
                    </div>
                    <span class="text-emerald-600 font-semibold">+5%</span>
                    <span class="text-gray-500 ml-2 hidden sm:inline">dari bulan lalu</span>
                </div>
                <div class="text-xs text-gray-400">Value</div>
            </div>
        </div>
    <!-- Stats Grid Gudang - Baris Kedua (3 Card All Time) -->
        <!-- Total Semua Produk Terjual (All Time) -->
        <div class="bg-white/80 backdrop-blur-sm rounded-2xl p-5 md:p-6 shadow-sm border border-white/50 hover:shadow-md transition-all">
            <div class="flex items-center justify-between">
                <div class="flex items-center gap-3 md:gap-4">
                    <div class="p-3 md:p-4 rounded-2xl bg-gradient-to-br from-indigo-500 to-blue-600 shadow-lg">
                        <i class="fas fa-chart-bar text-lg md:text-xl text-white"></i>
                    </div>
                    <div>
                        <p class="text-xs md:text-sm text-gray-500 font-medium">Total Terjual</p>
                        <h3 class="text-xl md:text-3xl font-bold text-gray-800 mt-1"><?php echo e(number_format($totalItemsSoldAllTime ?? 0)); ?> unit</h3>
                    </div>
                </div>
                <div class="w-8 h-8 rounded-lg bg-gray-100 flex items-center justify-center text-gray-400">
                    <i class="fas fa-infinity text-xs"></i>
                </div>
            </div>
            <div class="flex items-center justify-between mt-4 pt-3 border-t border-gray-100">
                <div class="flex items-center text-xs md:text-sm">
                    <div class="w-6 h-6 md:w-8 md:h-8 rounded-lg bg-indigo-100 flex items-center justify-center mr-2">
                        <i class="fas fa-calendar-alt text-indigo-600 text-xs"></i>
                    </div>
                    <span class="text-indigo-600 font-semibold">Sepanjang waktu</span>
                </div>
                <div class="text-xs text-gray-400">All Time</div>
            </div>
        </div>

        <!-- Total Pendapatan All Time -->
        <div class="bg-white/80 backdrop-blur-sm rounded-2xl p-5 md:p-6 shadow-sm border border-white/50 hover:shadow-md transition-all">
            <div class="flex items-center justify-between">
                <div class="flex items-center gap-3 md:gap-4">
                    <div class="p-3 md:p-4 rounded-2xl bg-gradient-to-br from-green-500 to-emerald-600 shadow-lg">
                        <i class="fas fa-chart-pie text-lg md:text-xl text-white"></i>
                    </div>
                    <div>
                        <p class="text-xs md:text-sm text-gray-500 font-medium">Total Pendapatan</p>
                        <h3 class="text-xl md:text-3xl font-bold text-gray-800 mt-1">Rp <?php echo e(number_format($totalRevenueAllTime ?? 0, 0, ',', '.')); ?></h3>
                    </div>
                </div>
                <div class="w-8 h-8 rounded-lg bg-gray-100 flex items-center justify-center text-gray-400">
                    <i class="fas fa-infinity text-xs"></i>
                </div>
            </div>
            <div class="flex items-center justify-between mt-4 pt-3 border-t border-gray-100">
                <div class="flex items-center text-xs md:text-sm">
                    <div class="w-6 h-6 md:w-8 md:h-8 rounded-lg bg-green-100 flex items-center justify-center mr-2">
                        <i class="fas fa-chart-line text-green-600 text-xs"></i>
                    </div>
                    <span class="text-green-600 font-semibold">Dari semua transaksi</span>
                </div>
                <div class="text-xs text-gray-400">Revenue</div>
            </div>
        </div>

        <!-- Rata-rata Nilai Item -->
        <div class="bg-white/80 backdrop-blur-sm rounded-2xl p-5 md:p-6 shadow-sm border border-white/50 hover:shadow-md transition-all">
            <div class="flex items-center justify-between">
                <div class="flex items-center gap-3 md:gap-4">
                    <div class="p-3 md:p-4 rounded-2xl bg-gradient-to-br from-amber-500 to-orange-600 shadow-lg">
                        <i class="fas fa-calculator text-lg md:text-xl text-white"></i>
                    </div>
                    <div>
                        <p class="text-xs md:text-sm text-gray-500 font-medium">Rata-rata per Item</p>
                        <h3 class="text-xl md:text-3xl font-bold text-gray-800 mt-1">Rp <?php echo e(number_format($avgItemValue ?? 0, 0, ',', '.')); ?></h3>
                    </div>
                </div>
                <div class="w-8 h-8 rounded-lg bg-gray-100 flex items-center justify-center text-gray-400">
                    <i class="fas fa-chart-simple text-xs"></i>
                </div>
            </div>
            <div class="flex items-center justify-between mt-4 pt-3 border-t border-gray-100">
                <div class="flex items-center text-xs md:text-sm">
                    <div class="w-6 h-6 md:w-8 md:h-8 rounded-lg bg-amber-100 flex items-center justify-center mr-2">
                        <i class="fas fa-tag text-amber-600 text-xs"></i>
                    </div>
                    <span class="text-amber-600 font-semibold">Nilai rata-rata</span>
                </div>
                <div class="text-xs text-gray-400">Average</div>
            </div>
        </div>
    </div>

    <!-- Main Content Area -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-4 md:gap-6">
       <!-- Produk Stok Rendah -->
        <div class="lg:col-span-2">
            <div class="glass-effect rounded-3xl overflow-hidden shadow-elegant h-full">
                <div class="p-4 md:p-6 border-b border-gray-100/50">
                    <div class="flex items-center justify-between">
                        <div>
                            <h3 class="text-lg md:text-xl font-bold text-gray-800">Produk Stok Rendah</h3>
                            <p class="text-xs md:text-sm text-gray-600 mt-1">Perlu restock segera</p>
                        </div>
                        <a href="<?php echo e(route('products.index')); ?>?filter=low_stock"
                        class="text-red-600 hover:text-red-700 font-medium text-sm flex items-center gap-1">
                            Lihat Semua <i class="fas fa-arrow-right text-xs"></i>
                        </a>
                    </div>
                </div>
                <div class="divide-y divide-gray-100/50 max-h-96 overflow-y-auto">
                    <?php $__empty_1 = true; $__currentLoopData = $lowStockItems ?? []; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $product): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                    <div class="p-4 md:p-6 hover:bg-white/30 transition-colors duration-200">
                        <div class="flex items-center justify-between">
                            <div class="flex items-center gap-3">
                                <div class="relative">
                                    <div class="w-12 h-12 rounded-xl bg-gradient-to-br from-red-100 to-rose-100 flex items-center justify-center">
                                        <i class="fas fa-box text-red-600"></i>
                                    </div>
                                    <div class="absolute -top-1 -right-1 w-5 h-5 bg-red-500 rounded-full flex items-center justify-center text-white text-xs">
                                        <i class="fas fa-exclamation"></i>
                                    </div>
                                </div>
                                <div class="flex-1 min-w-0">
                                    <h4 class="font-semibold text-gray-900 truncate"><?php echo e($product->name); ?></h4>
                                    <p class="text-xs text-gray-500 mt-1"><?php echo e($product->category->name ?? '-'); ?> • SKU: <?php echo e($product->code ?? '-'); ?></p>
                                </div>
                            </div>
                            <div class="text-right">
                                <div class="text-lg font-bold text-gray-900"><?php echo e($product->stock); ?></div>
                                <div class="text-xs text-red-600 font-medium mt-1">
                                    Min: <?php echo e($product->min_stock ?? 10); ?>

                                </div>
                            </div>
                        </div>
                        <div class="mt-3 flex items-center gap-2">
                            <div class="flex-1 h-2 bg-gray-200 rounded-full overflow-hidden">
                                <?php
                                    $minStock = $product->min_stock ?? 10;
                                    $percentage = $minStock > 0 ? min(100, ($product->stock / $minStock) * 100) : 0;
                                ?>
                                <div class="h-full bg-red-500 rounded-full" style="width: <?php echo e($percentage); ?>%"></div>
                            </div>
                            
                            <!-- Tombol Restock dengan modal -->
                            <button type="button" 
                                    onclick="openRestockModal(<?php echo e($product->id); ?>, '<?php echo e($product->name); ?>', <?php echo e($product->stock); ?>, <?php echo e($product->min_stock ?? 10); ?>)"
                                    class="px-3 py-1 bg-blue-500 text-white text-xs rounded-lg hover:bg-blue-600 transition-colors">
                                 Restock
                            </button>
                        </div>
                    </div>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                    <div class="p-8 text-center">
                        <div class="flex flex-col items-center justify-center text-gray-400">
                            <i class="fas fa-check-circle text-3xl mb-3"></i>
                            <p class="text-sm">Semua stok dalam kondisi baik</p>
                        </div>
                    </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <!-- Quick Actions -->
        <div class="space-y-4 md:space-y-6">
            <!-- Inventory Actions -->
            <div class="glass-effect rounded-3xl p-4 md:p-6 shadow-elegant">
                <h3 class="text-lg md:text-xl font-bold text-gray-800 mb-4">Aksi Cepat</h3>
                <div class="grid grid-cols-2 gap-3">
                    <a href="<?php echo e(route('products.create')); ?>"
                       class="group p-4 rounded-xl bg-blue-50 hover:bg-blue-100 transition-colors text-center">
                        <div class="w-10 h-10 rounded-lg bg-blue-500 flex items-center justify-center mx-auto mb-2 group-hover:scale-110 transition-transform">
                            <i class="fas fa-plus text-white"></i>
                        </div>
                        <p class="text-sm font-medium text-gray-900">Tambah Produk</p>
                    </a>
                    <a href="<?php echo e(route('categories.index')); ?>"
                       class="group p-4 rounded-xl bg-emerald-50 hover:bg-emerald-100 transition-colors text-center">
                        <div class="w-10 h-10 rounded-lg bg-emerald-500 flex items-center justify-center mx-auto mb-2 group-hover:scale-110 transition-transform">
                            <i class="fas fa-tags text-white"></i>
                        </div>
                        <p class="text-sm font-medium text-gray-900">Kelola Kategori</p>
                    </a>
                    <a href="<?php echo e(route('products.index')); ?>?filter=low_stock"
                       class="group p-4 rounded-xl bg-red-50 hover:bg-red-100 transition-colors text-center">
                        <div class="w-10 h-10 rounded-lg bg-red-500 flex items-center justify-center mx-auto mb-2 group-hover:scale-110 transition-transform">
                            <i class="fas fa-exclamation text-white"></i>
                        </div>
                        <p class="text-sm font-medium text-gray-900">Stok Rendah</p>
                    </a>
                    <a href="<?php echo e(route('reports.index')); ?>" 
                    class="group p-4 rounded-xl bg-purple-50 hover:bg-purple-100 transition-colors text-center">
                        <div class="w-10 h-10 rounded-lg bg-purple-500 flex items-center justify-center mx-auto mb-2 group-hover:scale-110 transition-transform">
                            <i class="fas fa-file-export text-white"></i>
                        </div>
                        <p class="text-sm font-medium text-gray-900">Ekstrak Laporan</p>
                    </a>
                </div>
            </div>

            <!-- Recent Stock Updates -->
            <div class="glass-effect rounded-3xl p-4 md:p-6 shadow-elegant">
                <h3 class="text-lg md:text-xl font-bold text-gray-800 mb-4">Update Stok Terbaru</h3>
                <div class="space-y-3">
                    <?php $__empty_1 = true; $__currentLoopData = $recentStockUpdates ?? []; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $update): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                    <div class="flex items-center justify-between p-3 rounded-lg bg-gray-50">
                        <div class="flex items-center gap-2">
                            <div class="w-8 h-8 rounded-lg <?php echo e($update->type === 'in' ? 'bg-emerald-100 text-emerald-600' : 'bg-blue-100 text-blue-600'); ?> flex items-center justify-center">
                                <i class="fas fa-<?php echo e($update->type === 'in' ? 'arrow-down' : 'arrow-up'); ?> text-xs"></i>
                            </div>
                            <div>
                                <p class="text-xs font-medium text-gray-900"><?php echo e($update->product_name); ?></p>
                                <p class="text-xs text-gray-500">
                                    <?php
                                        $createdAt = $update->created_at;
                                        if (is_string($createdAt)) {
                                            try {
                                                $time = \Carbon\Carbon::parse($createdAt)->format('H:i');
                                            } catch (\Exception $e) {
                                                $time = '-';
                                            }
                                        } elseif ($createdAt instanceof \Carbon\Carbon) {
                                            $time = $createdAt->format('H:i');
                                        } else {
                                            $time = '-';
                                        }
                                    ?>
                                    <?php echo e($time); ?>

                                </p>
                            </div>
                        </div>
                        <div class="text-right">
                            <span class="text-sm font-bold <?php echo e($update->type === 'in' ? 'text-emerald-600' : 'text-blue-600'); ?>">
                                <?php echo e($update->type === 'in' ? '+' : '-'); ?><?php echo e($update->quantity); ?>

                            </span>
                            <p class="text-xs text-gray-500">Stok: <?php echo e($update->new_stock); ?></p>
                        </div>
                    </div>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                    <div class="text-center py-4 text-gray-400">
                        <i class="fas fa-history text-lg mb-2"></i>
                        <p class="text-sm">Belum ada update stok</p>
                    </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    <!-- Product Categories Overview -->
    <div class="mt-6 md:mt-8">
        <div class="glass-effect rounded-3xl p-4 md:p-6 shadow-elegant">
            <div class="flex items-center justify-between mb-4">
                <div>
                    <h3 class="text-lg md:text-xl font-bold text-gray-800">Kategori Produk</h3>
                    <p class="text-xs md:text-sm text-gray-600 mt-1">Distribusi produk per kategori</p>
                </div>
                <a href="<?php echo e(route('categories.index')); ?>" class="text-amber-600 hover:text-amber-700 text-sm font-medium">
                    Kelola Kategori
                </a>
            </div>
            <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-6 gap-3">
                <?php $__empty_1 = true; $__currentLoopData = $productCategories ?? []; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $category): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                <div class="group cursor-pointer">
                    <div class="aspect-square rounded-2xl bg-gradient-to-br from-amber-50 to-orange-50 flex flex-col items-center justify-center p-4 mb-2 group-hover:shadow-md transition-shadow">
                        <i class="fas fa-tag text-2xl text-amber-500 mb-2"></i>
                        <span class="text-sm font-bold text-gray-800"><?php echo e($category->products_count ?? 0); ?></span>
                    </div>
                    <div class="text-center">
                        <p class="text-xs font-medium text-gray-900 truncate"><?php echo e($category->name); ?></p>
                        <p class="text-xs text-gray-500 mt-1"><?php echo e($category->low_stock_count ?? 0); ?> low stock</p>
                    </div>
                </div>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                <div class="col-span-full p-6 text-center text-gray-400">
                    <i class="fas fa-tags text-2xl mb-2"></i>
                    <p class="text-sm">Belum ada kategori</p>
                </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<!-- Modal Restock -->
<div id="restockModal" class="fixed inset-0 bg-black/60 hidden items-center justify-center z-[9999] backdrop-blur-sm">
    <div class="bg-white rounded-2xl max-w-md w-full mx-4 shadow-2xl">
        <div class="flex justify-between items-center p-6 border-b">
            <h3 class="text-xl font-bold text-gray-900 flex items-center">
                <div class="w-8 h-8 rounded-full bg-blue-100 flex items-center justify-center mr-3">
                    <i class="fas fa-boxes text-blue-600"></i>
                </div>
                Restock Produk
            </h3>
            <button onclick="closeRestockModal()" class="text-gray-400 hover:text-gray-600 transition">
                <i class="fas fa-times text-xl"></i>
            </button>
        </div>

        <div class="p-6">
            <div id="productInfo" class="bg-blue-50 p-4 rounded-xl mb-6">
                <div class="flex items-center text-blue-800 mb-2">
                    <i class="fas fa-info-circle mr-2"></i>
                    <span class="font-medium">Informasi Produk</span>
                </div>
                <div id="productInfoContent" class="space-y-1 text-blue-700 text-sm">
                    <div class="flex justify-between">
                        <span class="font-medium">Nama:</span>
                        <span id="productName"></span>
                    </div>
                    <div class="flex justify-between">
                        <span class="font-medium">Stok Saat Ini:</span>
                        <span id="currentStock" class="font-bold"></span>
                    </div>
                    <div class="flex justify-between">
                        <span class="font-medium">Stok Minimum:</span>
                        <span id="minStock"></span>
                    </div>
                </div>
            </div>

            <form id="restockForm" method="POST">
                <?php echo csrf_field(); ?>
                <input type="hidden" id="productId" name="product_id">

                <div class="mb-5">
                    <label class="block text-sm font-medium text-gray-700 mb-2">
                        <i class="fas fa-cubes mr-1 text-blue-600"></i>
                        Jumlah Restock <span class="text-red-500">*</span>
                    </label>
                    <input type="number" name="quantity" id="quantity" 
                           min="1" value="10" required
                           class="w-full p-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-transparent outline-none transition"
                           placeholder="Masukkan jumlah">
                </div>

                <div class="mb-5">
                    <label class="block text-sm font-medium text-gray-700 mb-2">
                        <i class="fas fa-sticky-note mr-1 text-gray-600"></i>
                        Catatan (Opsional)
                    </label>
                    <textarea name="note" id="note" rows="2"
                              class="w-full p-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-transparent outline-none transition"
                              placeholder="Catatan restock..."></textarea>
                </div>

                <div class="bg-yellow-50 p-3 rounded-xl mb-5">
                    <div class="flex items-start gap-2">
                        <i class="fas fa-lightbulb text-yellow-600 mt-1"></i>
                        <p class="text-xs text-yellow-700">
                            Stok akan bertambah sesuai jumlah yang dimasukkan. Pastikan jumlah sesuai dengan barang yang diterima.
                        </p>
                    </div>
                </div>

                <div class="flex gap-3">
                    <button type="submit"
                        class="flex-1 bg-gradient-to-r from-blue-600 to-indigo-600 text-white py-3 rounded-xl hover:from-blue-700 hover:to-indigo-700 transition-all font-medium shadow-lg">
                        <i class="fas fa-check mr-2"></i>
                        Proses Restock
                    </button>
                    <button type="button" onclick="closeRestockModal()"
                        class="flex-1 bg-gray-200 text-gray-700 py-3 rounded-xl hover:bg-gray-300 transition-all font-medium">
                        Batal
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Loading Spinner -->
<div id="loadingSpinner" class="fixed inset-0 bg-black/30 hidden items-center justify-center z-[10000] backdrop-blur-sm">
    <div class="bg-white p-6 rounded-2xl shadow-2xl flex items-center gap-4">
        <div class="spinner"></div>
        <span class="text-gray-700 font-medium">Memproses...</span>
    </div>
</div>

<!-- Success Toast -->
<div id="successToast" class="fixed bottom-4 right-4 bg-green-500 text-white px-6 py-3 rounded-lg shadow-lg z-[10001] hidden">
    <div class="flex items-center gap-3">
        <i class="fas fa-check-circle"></i>
        <span id="toastMessage"></span>
    </div>
</div>

<!-- Error Toast -->
<div id="errorToast" class="fixed bottom-4 right-4 bg-red-500 text-white px-6 py-3 rounded-lg shadow-lg z-[10001] hidden">
    <div class="flex items-center gap-3">
        <i class="fas fa-exclamation-circle"></i>
        <span id="errorToastMessage"></span>
    </div>
</div>

<script>
    // Stock alert functionality
    document.addEventListener('DOMContentLoaded', function() {
        // Check for critical stock levels
        const lowStockItems = <?php echo e($lowStockProducts ?? 0); ?>;
        if (lowStockItems > 0) {
            showStockAlert(lowStockItems);
        }

        // Auto-refresh stock data every 5 minutes
        setInterval(() => {
            fetch('/api/stock-updates')
                .then(response => response.json())
                .then(data => {
                    updateStockDisplay(data);
                });
        }, 300000);
    });

    function showStockAlert(count) {
        if (count > 3) {
            const alert = document.createElement('div');
            alert.className = 'fixed bottom-4 right-4 bg-red-50 border border-red-200 rounded-xl p-4 shadow-lg z-50';
            alert.innerHTML = `
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-lg bg-red-500 flex items-center justify-center">
                        <i class="fas fa-exclamation-triangle text-white"></i>
                    </div>
                    <div>
                        <h4 class="font-semibold text-gray-900">Perhatian!</h4>
                        <p class="text-sm text-gray-600">${count} produk stok rendah</p>
                    </div>
                    <button onclick="this.parentElement.parentElement.remove()" class="ml-4 text-gray-400 hover:text-gray-600">
                        <i class="fas fa-times"></i>
                    </button>
                </div>
            `;
            document.body.appendChild(alert);

            // Remove alert after 10 seconds
            setTimeout(() => {
                if (alert.parentElement) {
                    alert.remove();
                }
            }, 10000);
        }
    }

    // Fungsi untuk membuka modal restock
    function openRestockModal(productId, productName, currentStock, minStock) {
        document.getElementById('productId').value = productId;
        document.getElementById('productName').textContent = productName;
        document.getElementById('currentStock').textContent = currentStock + ' unit';
        document.getElementById('minStock').textContent = minStock + ' unit';
        document.getElementById('quantity').value = minStock; // Default = min stock
        document.getElementById('note').value = '';
        
        // Set form action
        document.getElementById('restockForm').action = '/products/' + productId + '/restock';
        
        document.getElementById('restockModal').classList.remove('hidden');
        document.getElementById('restockModal').classList.add('flex');
        document.body.style.overflow = 'hidden';
    }

    // Fungsi untuk menutup modal restock
    function closeRestockModal() {
        document.getElementById('restockModal').classList.add('hidden');
        document.getElementById('restockModal').classList.remove('flex');
        document.body.style.overflow = 'auto';
    }

    // Fungsi untuk menampilkan loading
    function showLoading() {
        document.getElementById('loadingSpinner').classList.remove('hidden');
        document.getElementById('loadingSpinner').classList.add('flex');
    }

    // Fungsi untuk menyembunyikan loading
    function hideLoading() {
        document.getElementById('loadingSpinner').classList.add('hidden');
        document.getElementById('loadingSpinner').classList.remove('flex');
    }

    // Fungsi untuk menampilkan toast sukses
    function showSuccess(message) {
        document.getElementById('toastMessage').textContent = message;
        document.getElementById('successToast').classList.remove('hidden');
        
        setTimeout(() => {
            document.getElementById('successToast').classList.add('hidden');
        }, 3000);
    }

    // Fungsi untuk menampilkan toast error
    function showError(message) {
        document.getElementById('errorToastMessage').textContent = message;
        document.getElementById('errorToast').classList.remove('hidden');
        
        setTimeout(() => {
            document.getElementById('errorToast').classList.add('hidden');
        }, 3000);
    }

    // Handle form submission dengan AJAX
    document.addEventListener('DOMContentLoaded', function() {
        const restockForm = document.getElementById('restockForm');
        
        if (restockForm) {
            restockForm.addEventListener('submit', function(e) {
                e.preventDefault();
                
                const formData = new FormData(this);
                const action = this.action;
                const productId = document.getElementById('productId').value;
                const productName = document.getElementById('productName').textContent;
                
                showLoading();
                
                fetch(action, {
                    method: 'POST',
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest',
                        'Accept': 'application/json'
                    },
                    body: formData
                })
                .then(response => response.json())
                .then(data => {
                    hideLoading();
                    closeRestockModal();
                    
                    if (data.success) {
                        showSuccess(data.message);
                        
                        // Refresh halaman setelah 2 detik untuk melihat perubahan
                        setTimeout(() => {
                            location.reload();
                        }, 2000);
                    } else {
                        showError(data.message || 'Gagal melakukan restock');
                    }
                })
                .catch(error => {
                    hideLoading();
                    closeRestockModal();
                    showError('Terjadi kesalahan: ' + error.message);
                    console.error('Error:', error);
                });
            });
        }
    });
</script>

<style>
    .glass-effect {
        background: rgba(255, 255, 255, 0.7);
        backdrop-filter: blur(10px);
        border: 1px solid rgba(245, 158, 11, 0.1);
    }

    .gradient-text {
        background: linear-gradient(135deg, #f59e0b 0%, #d97706 100%);
        -webkit-background-clip: text;
        -webkit-text-fill-color: transparent;
    }

    .spinner {
        border: 3px solid #f3f3f3;
        border-top: 3px solid #3b82f6;
        border-radius: 50%;
        width: 30px;
        height: 30px;
        animation: spin 1s linear infinite;
    }

    @keyframes spin {
        0% { transform: rotate(0deg); }
        100% { transform: rotate(360deg); }
    }
</style>
<?php $__env->stopSection(); ?>
<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\laragon\www\Toko-Roni-Mobile-App\resources\views/dashboard/gudang.blade.php ENDPATH**/ ?>