<?php $__env->startSection('title', 'Dashboard Owner'); ?>
<?php $__env->startSection('page-title', 'Dashboard'); ?>
<?php $__env->startSection('page-subtitle', 'Ringkasan dan statistik bisnis Anda'); ?>

<?php $__env->startSection('content'); ?>
<div class="min-h-screen bg-gradient-to-br from-blue-50/50 to-blue-100/30 p-4 md:p-6">
    <!-- Welcome Header -->
    <div class="bg-white/80 backdrop-blur-sm rounded-3xl p-6 md:p-8 shadow-lg mb-6 md:mb-8 border border-white/50">
        <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-4 md:gap-6">
            <div class="flex-1">
                <div class="flex items-center gap-3 md:gap-4">
                    <div class="relative">
                        <div class="w-12 h-12 md:w-16 md:h-16 rounded-2xl bg-gradient-to-br from-blue-500 to-blue-600 flex items-center justify-center shadow-lg">
                            <i class="fas fa-chart-line text-xl md:text-2xl text-white"></i>
                        </div>
                        <div class="absolute -inset-1 md:-inset-2 bg-gradient-to-r from-blue-500 to-blue-600 rounded-2xl blur-xl opacity-20"></div>
                    </div>
                    <div>
                        <h1 class="text-xl md:text-3xl lg:text-4xl font-bold text-gray-800">Selamat datang, <span class="text-blue-600"><?php echo e(Auth::user()->name); ?>!</span> 👋</h1>
                        <p class="text-sm md:text-base text-gray-600 mt-1 md:mt-2">Berikut adalah ringkasan performa bisnis Anda hari ini</p>
                    </div>
                </div>
            </div>
            <div class="flex flex-col sm:flex-row gap-3 md:gap-4">
                <div class="flex items-center gap-2 md:gap-3 px-4 md:px-6 py-3 md:py-4 bg-white/80 backdrop-blur-sm rounded-2xl shadow-sm border border-white/50">
                    <div class="w-10 h-10 md:w-12 md:h-12 rounded-xl bg-gradient-to-br from-blue-100 to-blue-200 flex items-center justify-center">
                        <i class="fas fa-calendar-alt text-blue-600 text-sm md:text-lg"></i>
                    </div>
                    <div>
                        <p class="text-xs md:text-sm text-gray-500 font-medium">Tanggal</p>
                        <p class="text-sm md:text-base font-semibold text-gray-800"><?php echo e(now()->translatedFormat('l, d F Y')); ?></p>
                    </div>
                </div>
                <div class="flex items-center gap-2 md:gap-3 px-4 md:px-6 py-3 md:py-4 bg-white/80 backdrop-blur-sm rounded-2xl shadow-sm border border-white/50">
                    <div class="w-10 h-10 md:w-12 md:h-12 rounded-xl bg-gradient-to-br from-emerald-100 to-emerald-200 flex items-center justify-center">
                        <i class="fas fa-clock text-emerald-600 text-sm md:text-lg"></i>
                    </div>
                    <div>
                        <p class="text-xs md:text-sm text-gray-500 font-medium">Waktu</p>
                        <p class="text-sm md:text-base font-semibold text-gray-800" id="currentTime"><?php echo e(now()->format('H:i')); ?></p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Stats Grid -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 md:gap-6 mb-6 md:mb-8">
        <!-- Total Users -->
        <div class="bg-white/80 backdrop-blur-sm rounded-2xl p-5 md:p-6 shadow-sm border border-white/50 hover:shadow-md transition-all">
            <div class="flex items-center justify-between">
                <div class="flex items-center gap-3 md:gap-4">
                    <div class="p-3 md:p-4 rounded-2xl bg-gradient-to-br from-blue-500 to-blue-600 shadow-lg">
                        <i class="fas fa-users text-lg md:text-xl text-white"></i>
                    </div>
                    <div>
                        <p class="text-xs md:text-sm text-gray-500 font-medium">Total Pengguna</p>
                        <h3 class="text-xl md:text-3xl font-bold text-gray-800 mt-1"><?php echo e($totalUsers ?? 0); ?></h3>
                    </div>
                </div>
                <a href="<?php echo e(route('users.index')); ?>" class="w-8 h-8 rounded-lg bg-gray-100 flex items-center justify-center text-gray-600 hover:bg-blue-500 hover:text-white transition-all">
                    <i class="fas fa-arrow-right text-xs"></i>
                </a>
            </div>
            <div class="flex items-center justify-between mt-4 pt-3 border-t border-gray-100">
                <div class="flex items-center text-xs md:text-sm">
                    <div class="w-6 h-6 md:w-8 md:h-8 rounded-lg bg-emerald-100 flex items-center justify-center mr-2">
                        <i class="fas fa-arrow-up text-emerald-600 text-xs"></i>
                    </div>
                    <span class="text-emerald-600 font-semibold">+12%</span>
                    <span class="text-gray-500 ml-2 hidden sm:inline">dari bulan lalu</span>
                </div>
                <div class="text-xs text-gray-400">Active</div>
            </div>
        </div>

        <!-- Total Products -->
        <div class="bg-white/80 backdrop-blur-sm rounded-2xl p-5 md:p-6 shadow-sm border border-white/50 hover:shadow-md transition-all">
            <div class="flex items-center justify-between">
                <div class="flex items-center gap-3 md:gap-4">
                    <div class="p-3 md:p-4 rounded-2xl bg-gradient-to-br from-emerald-500 to-green-600 shadow-lg">
                        <i class="fas fa-box text-lg md:text-xl text-white"></i>
                    </div>
                    <div>
                        <p class="text-xs md:text-sm text-gray-500 font-medium">Total Produk</p>
                        <h3 class="text-xl md:text-3xl font-bold text-gray-800 mt-1"><?php echo e($totalProducts ?? 0); ?></h3>
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
                    <span class="text-emerald-600 font-semibold">+5%</span>
                    <span class="text-gray-500 ml-2 hidden sm:inline">dari bulan lalu</span>
                </div>
                <div class="text-xs text-gray-400">In Stock</div>
            </div>
        </div>

        <!-- Stock Statistics -->
        <div class="bg-white/80 backdrop-blur-sm rounded-2xl p-5 md:p-6 shadow-sm border border-white/50 hover:shadow-md transition-all">
            <div class="flex items-center justify-between">
                <div class="flex items-center gap-3 md:gap-4">
                    <div class="p-3 md:p-4 rounded-2xl bg-gradient-to-br from-amber-500 to-orange-600 shadow-lg">
                        <i class="fas fa-exclamation-triangle text-lg md:text-xl text-white"></i>
                    </div>
                    <div>
                        <p class="text-xs md:text-sm text-gray-500 font-medium">Stok Hampir Habis</p>
                        <h3 class="text-xl md:text-3xl font-bold text-gray-800 mt-1"><?php echo e($lowStockCount ?? 0); ?></h3>
                    </div>
                </div>
                <a href="#low-stock" class="w-8 h-8 rounded-lg bg-gray-100 flex items-center justify-center text-gray-600 hover:bg-blue-500 hover:text-white transition-all">
                    <i class="fas fa-arrow-right text-xs"></i>
                </a>
            </div>
            <div class="flex items-center justify-between mt-4 pt-3 border-t border-gray-100">
                <div class="flex items-center text-xs md:text-sm">
                    <div class="w-6 h-6 md:w-8 md:h-8 rounded-lg bg-red-100 flex items-center justify-center mr-2">
                        <i class="fas fa-exclamation text-red-600 text-xs"></i>
                    </div>
                    <span class="text-red-600 font-semibold"><?php echo e($criticalStockCount ?? 0); ?> kritis</span>
                    <span class="text-gray-500 ml-2 hidden sm:inline">perlu restock</span>
                </div>
                <div class="text-xs text-gray-400">Alert</div>
            </div>
        </div>

        <!-- Expired Products -->
        <div class="bg-white/80 backdrop-blur-sm rounded-2xl p-5 md:p-6 shadow-sm border border-white/50 hover:shadow-md transition-all">
            <div class="flex items-center justify-between">
                <div class="flex items-center gap-3 md:gap-4">
                    <div class="p-3 md:p-4 rounded-2xl bg-gradient-to-br from-rose-500 to-pink-600 shadow-lg">
                        <i class="fas fa-clock text-lg md:text-xl text-white"></i>
                    </div>
                    <div>
                        <p class="text-xs md:text-sm text-gray-500 font-medium">Akan Kadaluarsa</p>
                        <h3 class="text-xl md:text-3xl font-bold text-gray-800 mt-1"><?php echo e($expiringSoonCount ?? 0); ?></h3>
                    </div>
                </div>
                <a href="#expiring" class="w-8 h-8 rounded-lg bg-gray-100 flex items-center justify-center text-gray-600 hover:bg-blue-500 hover:text-white transition-all">
                    <i class="fas fa-arrow-right text-xs"></i>
                </a>
            </div>
            <div class="flex items-center justify-between mt-4 pt-3 border-t border-gray-100">
                <div class="flex items-center text-xs md:text-sm">
                    <div class="w-6 h-6 md:w-8 md:h-8 rounded-lg bg-amber-100 flex items-center justify-center mr-2">
                        <i class="fas fa-calendar text-amber-600 text-xs"></i>
                    </div>
                    <span class="text-amber-600 font-semibold"><?php echo e($expiredCount ?? 0); ?> expired</span>
                    <span class="text-gray-500 ml-2 hidden sm:inline">hari ini</span>
                </div>
                <div class="text-xs text-gray-400">Warning</div>
            </div>
        </div>
    </div>

    <!-- Stock Alert & Expiration Section -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-4 md:gap-6 mb-6 md:mb-8">
        <!-- Low Stock Products -->
        <div class="bg-white/80 backdrop-blur-sm rounded-2xl overflow-hidden shadow-sm border border-white/50" id="low-stock">
            <div class="p-4 md:p-6 border-b border-gray-100">
                <div class="flex items-center justify-between">
                    <div>
                        <h3 class="text-lg md:text-xl font-bold text-gray-800">Stok Menipis</h3>
                        <p class="text-xs md:text-sm text-gray-600 mt-1">Produk dengan stok di bawah minimum</p>
                    </div>
                    <a href="<?php echo e(route('products.index')); ?>" class="text-blue-600 hover:text-blue-700 font-medium text-xs md:text-sm flex items-center gap-1">
                        Kelola Stok <i class="fas fa-arrow-right text-xs"></i>
                    </a>
                </div>
            </div>
            <div class="divide-y divide-gray-100 max-h-96 overflow-y-auto">
                <?php $__empty_1 = true; $__currentLoopData = $lowStockProducts ?? []; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $product): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                    <div class="p-4 md:p-6 hover:bg-gray-50 transition-colors">
                        <div class="flex items-start gap-3 md:gap-4">
                            <div class="relative flex-shrink-0">
                                <div class="w-12 h-12 md:w-16 md:h-16 rounded-2xl bg-gradient-to-br from-amber-100 to-orange-100 flex items-center justify-center">
                                    <i class="fas fa-box text-amber-600 text-lg md:text-xl"></i>
                                </div>
                                <?php if($product->stock <= 5): ?>
                                    <div class="absolute -top-1 -right-1 w-5 h-5 md:w-6 md:h-6 bg-red-500 rounded-full flex items-center justify-center text-white text-xs font-bold animate-pulse">!</div>
                                <?php endif; ?>
                            </div>
                            <div class="flex-1 min-w-0">
                                <div class="flex items-start justify-between gap-2">
                                    <div>
                                        <h4 class="text-sm md:text-base font-semibold text-gray-900 truncate"><?php echo e($product->name); ?></h4>
                                        <p class="text-xs md:text-sm text-gray-500 mt-1"><?php echo e($product->category->name ?? '-'); ?></p>
                                    </div>
                                    <div class="text-right">
                                        <span class="inline-block px-2 py-1 rounded-lg text-xs font-medium <?php echo e($product->stock <= 5 ? 'bg-red-100 text-red-800' : 'bg-amber-100 text-amber-800'); ?>">
                                            Stok: <?php echo e($product->stock); ?>

                                        </span>
                                    </div>
                                </div>
                                <div class="flex items-center gap-4 mt-3">
                                    <div class="flex-1">
                                        <div class="w-full bg-gray-200 rounded-full h-2.5">
                                            <?php $percentage = min(($product->stock / 10) * 100, 100); ?>
                                            <div class="h-2.5 rounded-full <?php echo e($product->stock <= 5 ? 'bg-red-500' : 'bg-amber-500'); ?>" style="width: <?php echo e($percentage); ?>%"></div>
                                        </div>
                                    </div>
                                    <span class="text-xs text-gray-500">Min: 10</span>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                    <div class="p-8 md:p-12 text-center">
                        <div class="flex flex-col items-center justify-center text-gray-400">
                            <i class="fas fa-check-circle text-2xl md:text-4xl mb-3 md:mb-4 text-emerald-400"></i>
                            <p class="text-xs md:text-sm">Semua produk dalam stok yang cukup</p>
                        </div>
                    </div>
                <?php endif; ?>
            </div>
        </div>

        <!-- Expiring Products Section -->
        <div class="bg-white/80 backdrop-blur-sm rounded-2xl overflow-hidden shadow-sm border border-white/50" id="expiring">
            <div class="p-4 md:p-6 border-b border-gray-100">
                <div class="flex items-center justify-between">
                    <div>
                        <h3 class="text-lg md:text-xl font-bold text-gray-800">Produk Akan Kadaluarsa</h3>
                        <p class="text-xs md:text-sm text-gray-600 mt-1">30 hari ke depan</p>
                    </div>
                    <a href="<?php echo e(route('products.index')); ?>" class="text-blue-600 hover:text-blue-700 font-medium text-xs md:text-sm flex items-center gap-1">
                        Lihat Semua <i class="fas fa-arrow-right text-xs"></i>
                    </a>
                </div>
            </div>
            <div class="divide-y divide-gray-100 max-h-96 overflow-y-auto">
                <?php $__empty_1 = true; $__currentLoopData = $expiringProducts ?? []; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $product): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                    <?php
                        $daysLeft = $product->days_left ?? null;
                        $expiryStatus = $product->expiry_status ?? 'no_date';
                    ?>
                    <div class="p-4 md:p-6 hover:bg-gray-50 transition-colors">
                        <div class="flex items-start gap-3 md:gap-4">
                            <div class="relative flex-shrink-0">
                                <div class="w-12 h-12 md:w-16 md:h-16 rounded-2xl bg-gradient-to-br
                                    <?php if($expiryStatus == 'expired'): ?> from-red-100 to-rose-100
                                    <?php elseif($expiryStatus == 'critical'): ?> from-orange-100 to-amber-100
                                    <?php elseif($expiryStatus == 'warning'): ?> from-yellow-100 to-amber-50
                                    <?php else: ?> from-blue-100 to-cyan-100 <?php endif; ?>
                                    flex items-center justify-center">
                                    <i class="fas fa-calendar-alt text-lg md:text-xl
                                        <?php if($expiryStatus == 'expired'): ?> text-red-600
                                        <?php elseif($expiryStatus == 'critical'): ?> text-orange-600
                                        <?php elseif($expiryStatus == 'warning'): ?> text-amber-600
                                        <?php else: ?> text-blue-600 <?php endif; ?>">
                                    </i>
                                </div>
                            </div>
                            <div class="flex-1 min-w-0">
                                <div class="flex items-start justify-between gap-2">
                                    <div>
                                        <h4 class="text-sm md:text-base font-semibold text-gray-900 truncate"><?php echo e($product->name); ?></h4>
                                        <p class="text-xs md:text-sm text-gray-500 mt-1"><?php echo e($product->category->name ?? '-'); ?></p>
                                    </div>
                                    <div class="text-right">
                                        <?php if($expiryStatus == 'expired'): ?>
                                            <span class="inline-block px-2 py-1 rounded-lg text-xs font-medium bg-red-100 text-red-800">
                                                <i class="fas fa-exclamation-circle mr-1"></i>Expired
                                            </span>
                                        <?php elseif($expiryStatus == 'critical'): ?>
                                            <span class="inline-block px-2 py-1 rounded-lg text-xs font-medium bg-orange-100 text-orange-800">
                                                <i class="fas fa-clock mr-1"></i><?php echo e($daysLeft); ?> hari lagi
                                            </span>
                                        <?php elseif($expiryStatus == 'warning'): ?>
                                            <span class="inline-block px-2 py-1 rounded-lg text-xs font-medium bg-amber-100 text-amber-800">
                                                <i class="fas fa-calendar mr-1"></i><?php echo e($daysLeft); ?> hari
                                            </span>
                                        <?php else: ?>
                                            <span class="inline-block px-2 py-1 rounded-lg text-xs font-medium bg-blue-100 text-blue-800">
                                                <?php echo e($product->expiry_date ? \Carbon\Carbon::parse($product->expiry_date)->format('d/m/Y') : 'Tidak Ada'); ?>

                                            </span>
                                        <?php endif; ?>
                                    </div>
                                </div>
                                <div class="flex flex-wrap items-center gap-4 mt-3">
                                    <div class="flex items-center gap-2">
                                        <i class="fas fa-box text-gray-400 text-xs"></i>
                                        <span class="text-xs text-gray-600">Stok: <?php echo e($product->stock); ?></span>
                                    </div>
                                    <?php if($product->expiry_date): ?>
                                        <div class="flex items-center gap-2">
                                            <i class="fas fa-calendar text-gray-400 text-xs"></i>
                                            <span class="text-xs text-gray-600"><?php echo e(\Carbon\Carbon::parse($product->expiry_date)->format('d M Y')); ?></span>
                                        </div>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                    <div class="p-8 md:p-12 text-center">
                        <div class="flex flex-col items-center justify-center text-gray-400">
                            <i class="fas fa-calendar-check text-2xl md:text-4xl mb-3 md:mb-4 text-emerald-400"></i>
                            <p class="text-xs md:text-sm">Tidak ada produk yang akan kadaluarsa dalam 30 hari ke depan</p>
                        </div>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- Charts Section -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-4 md:gap-6 mb-6 md:mb-8">
        <!-- Sales Chart -->
        <div class="lg:col-span-2">
            <div class="bg-white/80 backdrop-blur-sm rounded-2xl p-4 md:p-6 shadow-sm border border-white/50 h-full">
                <div class="flex flex-col sm:flex-row sm:items-center justify-between mb-4 md:mb-6">
                    <div>
                        <h3 class="text-lg md:text-xl font-bold text-gray-800">Grafik Penjualan & Pengeluaran Stok</h3>
                        <p class="text-xs md:text-sm text-gray-600 mt-1">Performa penjualan dan pergerakan stok harian</p>
                    </div>
                    <div class="flex items-center gap-3 mt-4 sm:mt-0">
                        <div class="flex items-center gap-2">
                            <div class="w-3 h-3 rounded-full bg-blue-500"></div>
                            <span class="text-xs md:text-sm text-gray-600">Penjualan</span>
                        </div>
                        <div class="flex items-center gap-2">
                            <div class="w-3 h-3 rounded-full bg-orange-500"></div>
                            <span class="text-xs md:text-sm text-gray-600">Stok Keluar</span>
                        </div>
                        <select id="chartRange" class="px-3 md:px-4 py-1.5 md:py-2 bg-white border border-gray-200 rounded-xl text-xs md:text-sm text-gray-700 focus:outline-none focus:ring-2 focus:ring-blue-500">
                            <option value="7">7 Hari</option>
                            <option value="30">30 Hari</option>
                            <option value="90">90 Hari</option>
                        </select>
                    </div>
                </div>
                <div class="h-64 md:h-80 w-full">
                    <canvas id="salesChart"></canvas>
                </div>
            </div>
        </div>

        <!-- Stock Distribution -->
        <div class="bg-white/80 backdrop-blur-sm rounded-2xl p-4 md:p-6 shadow-sm border border-white/50">
            <h3 class="text-lg md:text-xl font-bold text-gray-800 mb-4">Distribusi Stok</h3>
            <div class="h-48 md:h-56 w-full">
                <canvas id="stockChart"></canvas>
            </div>
            <div class="mt-4 space-y-3">
                <div class="flex items-center justify-between">
                    <div class="flex items-center gap-2">
                        <div class="w-3 h-3 rounded-full bg-emerald-500"></div>
                        <span class="text-xs md:text-sm text-gray-600">Stok Normal</span>
                    </div>
                    <span class="text-xs md:text-sm font-semibold"><?php echo e($normalStockCount ?? 0); ?> produk</span>
                </div>
                <div class="flex items-center justify-between">
                    <div class="flex items-center gap-2">
                        <div class="w-3 h-3 rounded-full bg-amber-500"></div>
                        <span class="text-xs md:text-sm text-gray-600">Hampir Habis</span>
                    </div>
                    <span class="text-xs md:text-sm font-semibold"><?php echo e($lowStockCount ?? 0); ?> produk</span>
                </div>
                <div class="flex items-center justify-between">
                    <div class="flex items-center gap-2">
                        <div class="w-3 h-3 rounded-full bg-red-500"></div>
                        <span class="text-xs md:text-sm text-gray-600">Kritis</span>
                    </div>
                    <span class="text-xs md:text-sm font-semibold"><?php echo e($criticalStockCount ?? 0); ?> produk</span>
                </div>
            </div>
        </div>
    </div>

    <!-- Recent Transactions & Quick Actions -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-4 md:gap-6">
        <!-- Recent Transactions -->
        <div class="lg:col-span-2">
            <div class="bg-white/80 backdrop-blur-sm rounded-2xl overflow-hidden shadow-sm border border-white/50">
                <div class="p-4 md:p-6 border-b border-gray-100">
                    <div class="flex flex-col sm:flex-row sm:items-center justify-between">
                        <div>
                            <h3 class="text-lg md:text-xl font-bold text-gray-800">Transaksi Terbaru</h3>
                            <p class="text-xs md:text-sm text-gray-600 mt-1">Transaksi 7 hari terakhir</p>
                        </div>
                        <a href="<?php echo e(route('transactions.index')); ?>" class="mt-4 sm:mt-0 px-4 md:px-5 py-2 md:py-2.5 bg-gradient-to-r from-blue-500 to-blue-600 text-white rounded-xl text-xs md:text-sm font-medium hover:shadow-lg transition-all inline-flex items-center gap-2">
                            <i class="fas fa-list"></i> Lihat Semua
                        </a>
                    </div>
                </div>
                <div class="divide-y divide-gray-100">
                    <?php $__empty_1 = true; $__currentLoopData = $recentTransactions ?? []; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $transaction): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                        <div class="p-4 md:p-6 hover:bg-gray-50 transition-colors group">
                            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
                                <div class="flex items-center gap-3 md:gap-4">
                                    <div class="w-12 h-12 md:w-14 md:h-14 rounded-2xl bg-blue-100 flex items-center justify-center text-blue-600 flex-shrink-0">
                                        <i class="fas fa-receipt text-lg md:text-xl"></i>
                                    </div>
                                    <div>
                                        <h4 class="font-semibold text-gray-900 text-sm md:text-base">Transaksi #<?php echo e($transaction->invoice_display ?? $transaction->id); ?></h4>
                                        <div class="flex flex-col sm:flex-row sm:items-center gap-1 sm:gap-3 mt-1">
                                            <span class="text-xs md:text-sm text-gray-500 flex items-center gap-1">
                                                <i class="fas fa-user"></i> <?php echo e($transaction->customer_name ?? 'Pelanggan Umum'); ?>

                                            </span>
                                            <span class="text-xs md:text-sm text-gray-500 flex items-center gap-1">
                                                <i class="fas fa-clock"></i> <?php echo e($transaction->created_at->format('d M Y H:i')); ?>

                                            </span>
                                        </div>
                                    </div>
                                </div>
                                <div class="flex items-center justify-between sm:justify-end gap-4">
                                    <div class="text-right">
                                        <div class="text-lg md:text-2xl font-bold text-gray-900">Rp <?php echo e(number_format($transaction->total_amount, 0, ',', '.')); ?></div>
                                    </div>
                                    <a href="<?php echo e(route('transactions.show', $transaction)); ?>" class="w-8 h-8 rounded-lg bg-gray-100 flex items-center justify-center text-gray-600 hover:bg-blue-500 hover:text-white transition-all">
                                        <i class="fas fa-chevron-right text-xs"></i>
                                    </a>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                        <div class="p-8 md:p-12 text-center">
                            <div class="flex flex-col items-center justify-center text-gray-400">
                                <i class="fas fa-receipt text-2xl md:text-4xl mb-3 md:mb-4"></i>
                                <h4 class="text-sm md:text-lg font-medium text-gray-600 mb-1">Belum Ada Transaksi</h4>
                                <p class="text-xs md:text-sm">Belum ada transaksi yang tercatat</p>
                            </div>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <!-- Quick Actions -->
        <div>
            <div class="bg-white/80 backdrop-blur-sm rounded-2xl p-4 md:p-6 shadow-sm border border-white/50 h-full">
                <h3 class="text-lg md:text-xl font-bold text-gray-800 mb-4 md:mb-6">Quick Actions</h3>
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 md:gap-4">
                    <a href="<?php echo e(route('transactions.create')); ?>" class="group p-4 rounded-2xl bg-gradient-to-br from-emerald-50 to-green-50 border border-emerald-100 hover:shadow-md transition-all">
                        <div class="w-10 h-10 md:w-12 md:h-12 rounded-xl bg-gradient-to-br from-emerald-500 to-green-600 flex items-center justify-center mb-3 group-hover:scale-110 transition-transform">
                            <i class="fas fa-cash-register text-white text-base md:text-lg"></i>
                        </div>
                        <h4 class="text-sm md:text-base font-semibold text-gray-900 mb-1">Transaksi Baru</h4>
                        <p class="text-xs md:text-sm text-gray-600">Buat transaksi penjualan baru</p>
                    </a>

                    <a href="<?php echo e(route('products.create')); ?>" class="group p-4 rounded-2xl bg-gradient-to-br from-blue-50 to-cyan-50 border border-blue-100 hover:shadow-md transition-all">
                        <div class="w-10 h-10 md:w-12 md:h-12 rounded-xl bg-gradient-to-br from-blue-500 to-cyan-600 flex items-center justify-center mb-3 group-hover:scale-110 transition-transform">
                            <i class="fas fa-plus-circle text-white text-base md:text-lg"></i>
                        </div>
                        <h4 class="text-sm md:text-base font-semibold text-gray-900 mb-1">Tambah Produk</h4>
                        <p class="text-xs md:text-sm text-gray-600">Tambah produk baru ke katalog</p>
                    </a>

                    <a href="<?php echo e(route('products.index')); ?>" class="group p-4 rounded-2xl bg-gradient-to-br from-amber-50 to-orange-50 border border-amber-100 hover:shadow-md transition-all">
                        <div class="w-10 h-10 md:w-12 md:h-12 rounded-xl bg-gradient-to-br from-amber-500 to-orange-600 flex items-center justify-center mb-3 group-hover:scale-110 transition-transform">
                            <i class="fas fa-truck text-white text-base md:text-lg"></i>
                        </div>
                        <h4 class="text-sm md:text-base font-semibold text-gray-900 mb-1">Kelola Stok</h4>
                        <p class="text-xs md:text-sm text-gray-600">Atur stok produk</p>
                    </a>

                    <a href="<?php echo e(route('reports.sales')); ?>" class="group p-4 rounded-2xl bg-gradient-to-br from-purple-50 to-pink-50 border border-purple-100 hover:shadow-md transition-all">
                        <div class="w-10 h-10 md:w-12 md:h-12 rounded-xl bg-gradient-to-br from-purple-500 to-pink-600 flex items-center justify-center mb-3 group-hover:scale-110 transition-transform">
                            <i class="fas fa-chart-pie text-white text-base md:text-lg"></i>
                        </div>
                        <h4 class="text-sm md:text-base font-semibold text-gray-900 mb-1">Laporan Penjualan</h4>
                        <p class="text-xs md:text-sm text-gray-600">Analisis data penjualan</p>
                    </a>
                </div>

                <!-- Stock Summary -->
                <div class="mt-6 pt-4 border-t border-gray-100">
                    <h4 class="text-sm md:text-base font-semibold text-gray-800 mb-3">Ringkasan Stok</h4>
                    <div class="space-y-2">
                        <div class="flex items-center justify-between">
                            <span class="text-xs md:text-sm text-gray-600">Total Stok</span>
                            <span class="font-semibold text-gray-800"><?php echo e($totalStock ?? 0); ?> unit</span>
                        </div>
                        <div class="flex items-center justify-between">
                            <span class="text-xs md:text-sm text-gray-600">Nilai Stok</span>
                            <span class="font-semibold text-gray-800">Rp <?php echo e(number_format($totalStockValue ?? 0, 0, ',', '.')); ?></span>
                        </div>
                        <div class="flex items-center justify-between">
                            <span class="text-xs md:text-sm text-gray-600">Produk per Kategori</span>
                            <span class="font-semibold text-emerald-600"><?php echo e($productCategories ?? 0); ?> kategori</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
    // Update current time
    function updateTime() {
        const now = new Date();
        const timeElement = document.getElementById('currentTime');
        if (timeElement) {
            const options = {
                hour: '2-digit',
                minute: '2-digit',
                hour12: false
            };
            timeElement.textContent = now.toLocaleTimeString('id-ID', options);
        }
    }

    updateTime();
    setInterval(updateTime, 1000);

    // Charts
    document.addEventListener('DOMContentLoaded', function() {
        console.log('DOM loaded, initializing charts...');

        let salesChart = null;
        let stockChart = null;

        // Data awal dari PHP dengan validasi
        <?php
            $chartLabels = isset($chartData['labels']) && is_array($chartData['labels']) ? $chartData['labels'] : ['Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu', 'Minggu'];
            $chartSales = isset($chartData['sales']) && is_array($chartData['sales']) ? $chartData['sales'] : [500000, 750000, 600000, 900000, 1200000, 1500000, 800000];
            $chartStockOut = isset($chartData['stock_out']) && is_array($chartData['stock_out']) ? $chartData['stock_out'] : [25, 30, 28, 35, 42, 55, 38];

            $normalStock = $normalStockCount ?? 0;
            $lowStock = $lowStockCount ?? 0;
            $criticalStock = $criticalStockCount ?? 0;
        ?>

        const initialLabels = <?php echo json_encode($chartLabels); ?>;
        const initialSales = <?php echo json_encode($chartSales); ?>;
        const initialStockOut = <?php echo json_encode($chartStockOut); ?>;

        const normalStock = <?php echo e($normalStock); ?>;
        const lowStock = <?php echo e($lowStock); ?>;
        const criticalStock = <?php echo e($criticalStock); ?>;

        console.log('Initial Data:', {
            labels: initialLabels,
            sales: initialSales,
            stockOut: initialStockOut,
            stockDistribution: {
                normal: normalStock,
                low: lowStock,
                critical: criticalStock
            }
        });

        // Fungsi untuk inisialisasi chart penjualan
        function initSalesChart(labels, salesData, stockOutData) {
            const salesChartCtx = document.getElementById('salesChart');
            if (!salesChartCtx) {
                console.error('Sales chart canvas not found');
                return;
            }

            // Hapus chart lama jika ada
            if (salesChart) {
                salesChart.destroy();
            }

            const ctx = salesChartCtx.getContext('2d');
            
            // Gradient untuk area fill
            const gradientSales = ctx.createLinearGradient(0, 0, 0, 400);
            gradientSales.addColorStop(0, 'rgba(59, 130, 246, 0.25)');
            gradientSales.addColorStop(1, 'rgba(59, 130, 246, 0.05)');

            const gradientStock = ctx.createLinearGradient(0, 0, 0, 400);
            gradientStock.addColorStop(0, 'rgba(249, 115, 22, 0.25)');
            gradientStock.addColorStop(1, 'rgba(249, 115, 22, 0.05)');

            salesChart = new Chart(salesChartCtx, {
                type: 'line',
                data: {
                    labels: labels,
                    datasets: [
                        {
                            label: 'Penjualan (Rp)',
                            data: salesData,
                            backgroundColor: gradientSales,
                            borderColor: 'rgba(59, 130, 246, 1)',
                            borderWidth: 3,
                            fill: true,
                            tension: 0.4,
                            pointBackgroundColor: 'rgba(59, 130, 246, 1)',
                            pointBorderColor: '#ffffff',
                            pointBorderWidth: 2,
                            pointRadius: 4,
                            pointHoverRadius: 6,
                            yAxisID: 'y'
                        },
                        {
                            label: 'Stok Keluar (Unit)',
                            data: stockOutData,
                            backgroundColor: gradientStock,
                            borderColor: 'rgba(249, 115, 22, 1)',
                            borderWidth: 3,
                            fill: true,
                            tension: 0.4,
                            pointBackgroundColor: 'rgba(249, 115, 22, 1)',
                            pointBorderColor: '#ffffff',
                            pointBorderWidth: 2,
                            pointRadius: 4,
                            pointHoverRadius: 6,
                            yAxisID: 'y1'
                        }
                    ]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    interaction: {
                        mode: 'index',
                        intersect: false
                    },
                    plugins: {
                        legend: {
                            display: true,
                            position: 'top',
                            labels: {
                                usePointStyle: true,
                                boxWidth: 6,
                                font: {
                                    size: 12
                                }
                            }
                        },
                        tooltip: {
                            backgroundColor: 'white',
                            titleColor: '#1f2937',
                            bodyColor: '#4b5563',
                            borderColor: '#e5e7eb',
                            borderWidth: 1,
                            padding: 10,
                            callbacks: {
                                label: function(context) {
                                    let label = context.dataset.label || '';
                                    let value = context.raw;
                                    if (label.includes('Penjualan')) {
                                        return label + ': Rp ' + new Intl.NumberFormat('id-ID').format(value);
                                    } else {
                                        return label + ': ' + value + ' unit';
                                    }
                                }
                            }
                        }
                    },
                    scales: {
                        y: {
                            type: 'linear',
                            display: true,
                            position: 'left',
                            beginAtZero: true,
                            grid: {
                                color: 'rgba(229, 231, 235, 0.5)',
                                drawBorder: false
                            },
                            ticks: {
                                color: '#3b82f6',
                                callback: function(value) {
                                    if (value >= 1000000) {
                                        return 'Rp ' + (value / 1000000).toFixed(1) + 'Jt';
                                    }
                                    return 'Rp ' + new Intl.NumberFormat('id-ID').format(value);
                                }
                            }
                        },
                        y1: {
                            type: 'linear',
                            display: true,
                            position: 'right',
                            beginAtZero: true,
                            grid: {
                                drawOnChartArea: false
                            },
                            ticks: {
                                color: '#f97316',
                                callback: function(value) {
                                    return value + ' unit';
                                }
                            }
                        },
                        x: {
                            grid: {
                                display: false
                            },
                            ticks: {
                                color: '#6b7280',
                                maxRotation: 45,
                                minRotation: 45
                            }
                        }
                    }
                }
            });

            console.log('Sales chart initialized successfully');
        }

        // Fungsi untuk inisialisasi chart distribusi stok
        function initStockChart() {
            const stockChartCtx = document.getElementById('stockChart');
            if (!stockChartCtx) {
                console.error('Stock chart canvas not found');
                return;
            }

            // Hapus chart lama jika ada
            if (stockChart) {
                stockChart.destroy();
            }

            // Cek apakah ada data
            const totalStock = normalStock + lowStock + criticalStock;
            
            if (totalStock === 0) {
                // Tampilkan pesan jika tidak ada data
                const ctx = stockChartCtx.getContext('2d');
                ctx.clearRect(0, 0, stockChartCtx.width, stockChartCtx.height);
                ctx.font = '14px Arial';
                ctx.fillStyle = '#9ca3af';
                ctx.textAlign = 'center';
                ctx.fillText('Tidak ada data stok', stockChartCtx.width / 2, stockChartCtx.height / 2);
                console.log('No stock data available');
                return;
            }

            stockChart = new Chart(stockChartCtx, {
                type: 'doughnut',
                data: {
                    labels: ['Stok Normal', 'Hampir Habis', 'Kritis'],
                    datasets: [{
                        data: [normalStock, lowStock, criticalStock],
                        backgroundColor: [
                            'rgba(16, 185, 129, 0.9)',
                            'rgba(245, 158, 11, 0.9)',
                            'rgba(239, 68, 68, 0.9)'
                        ],
                        borderColor: [
                            'rgb(16, 185, 129)',
                            'rgb(245, 158, 11)',
                            'rgb(239, 68, 68)'
                        ],
                        borderWidth: 2,
                        hoverOffset: 4
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    cutout: '65%',
                    plugins: {
                        legend: {
                            display: true,
                            position: 'bottom',
                            labels: {
                                usePointStyle: true,
                                pointStyle: 'circle',
                                padding: 15,
                                font: {
                                    size: 11
                                }
                            }
                        },
                        tooltip: {
                            callbacks: {
                                label: function(context) {
                                    const label = context.label || '';
                                    const value = context.raw || 0;
                                    const total = context.dataset.data.reduce((a, b) => a + b, 0);
                                    const percentage = total > 0 ? ((value / total) * 100).toFixed(1) : 0;
                                    return `${label}: ${value} produk (${percentage}%)`;
                                }
                            }
                        }
                    }
                }
            });

            console.log('Stock chart initialized with data:', {normalStock, lowStock, criticalStock});
        }

        // Inisialisasi semua chart
        initSalesChart(initialLabels, initialSales, initialStockOut);
        initStockChart();

        // Chart range changer
        const chartRange = document.getElementById('chartRange');
        
        if (chartRange) {
            chartRange.addEventListener('change', function(e) {
                const range = e.target.value;
                console.log('Range changed to:', range);

                // Tampilkan loading
                Swal.fire({
                    title: 'Memuat Data...',
                    text: 'Mohon tunggu sebentar',
                    allowOutsideClick: false,
                    didOpen: () => {
                        Swal.showLoading();
                    }
                });

                // PERBAIKAN: Gunakan URL absolut dengan base URL
                const baseUrl = window.location.origin;
                const url = `${baseUrl}/dashboard/chart-data/${range}`;
                
                fetch(url, {
                    method: 'GET',
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest',
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content')
                    }
                })
                .then(response => {
                    if (!response.ok) {
                        throw new Error('Network response was not ok: ' + response.status);
                    }
                    return response.json();
                })
                .then(result => {
                    console.log('Received chart data:', result);
                    
                    Swal.close();

                    if (result.success && result.data) {
                        const data = result.data;
                        
                        // Update chart dengan data baru
                        if (salesChart) {
                            salesChart.data.labels = data.labels;
                            salesChart.data.datasets[0].data = data.sales;
                            salesChart.data.datasets[1].data = data.stock_out;
                            salesChart.update();
                            
                            Swal.fire({
                                icon: 'success',
                                title: 'Berhasil',
                                text: 'Data chart diperbarui',
                                timer: 1500,
                                showConfirmButton: false
                            });
                        }
                    } else {
                        throw new Error('Data format tidak valid');
                    }
                })
                .catch(error => {
                    console.error('Error fetching chart data:', error);
                    Swal.close();
                    Swal.fire({
                        icon: 'error',
                        title: 'Oops...',
                        text: 'Gagal memuat data chart: ' + error.message
                    });
                });
            });
        }

        // Inisialisasi ulang chart saat window resize
        window.addEventListener('resize', function() {
            if (salesChart) {
                salesChart.resize();
            }
            if (stockChart) {
                stockChart.resize();
            }
        });
    });
</script>

<style>
    /* Custom scrollbar */
    .overflow-y-auto::-webkit-scrollbar {
        width: 4px;
    }
    .overflow-y-auto::-webkit-scrollbar-track {
        background: rgba(219, 234, 254, 0.5);
        border-radius: 10px;
    }
    .overflow-y-auto::-webkit-scrollbar-thumb {
        background: linear-gradient(135deg, #3b82f6 0%, #1d4ed8 100%);
        border-radius: 10px;
    }
    
    /* Animations */
    .animate-fade-in {
        animation: fadeIn 0.6s ease-out;
    }
    @keyframes fadeIn {
        from { opacity: 0; transform: translateY(10px); }
        to { opacity: 1; transform: translateY(0); }
    }
    
    .animate-pulse {
        animation: pulse 2s cubic-bezier(0.4, 0, 0.6, 1) infinite;
    }
    @keyframes pulse {
        0%, 100% { opacity: 1; }
        50% { opacity: 0.5; }
    }
    
    /* Chart containers */
    #salesChart, #stockChart {
        max-width: 100%;
        height: auto !important;
    }
</style>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH D:\laravel project 3\Toko-Roni-Mobile-App\resources\views/dashboard/owner.blade.php ENDPATH**/ ?>