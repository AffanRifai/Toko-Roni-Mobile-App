<?php $__env->startSection('title', 'Manajemen Pengiriman'); ?>
<?php $__env->startSection('page-title', 'Manajemen Pengiriman'); ?>
<?php $__env->startSection('page-subtitle', 'Kelola semua pengiriman barang dalam satu dashboard'); ?>

<?php $__env->startSection('content'); ?>
    <div class="min-h-screen bg-gradient-to-br from-blue-50 via-white to-purple-50 p-4 md:p-6 lg:p-8">

        <!-- Header dengan Statistik dan Tombol Aksi -->
        <div class="mb-8">
            <!-- Welcome Section -->
            <div class="flex flex-col md:flex-row md:items-center md:justify-between mb-6">
                <div>
                    <h1 class="text-3xl font-bold text-gray-900">Dashboard Pengiriman</h1>
                    <p class="text-gray-600 mt-1">Selamat datang kembali, <?php echo e(Auth::user()->name); ?>!</p>
                </div>
                <div class="mt-4 md:mt-0 flex flex-wrap gap-3">
                    <div class="bg-white/80 backdrop-blur-sm rounded-xl px-4 py-2 shadow-sm border border-gray-200">
                        <span class="text-sm text-gray-600"><?php echo e(now()->format('l, d F Y')); ?></span>
                    </div>
                </div>
            </div>

            <!-- Statistik Cards -->
            <div class="grid grid-cols-2 lg:grid-cols-5 gap-4 mb-6">
                <?php
                    $totalStats = max($stats['total'] ?? 1, 1);
                ?>
                
                <!-- Total Card -->
                <div class="bg-white rounded-2xl p-6 shadow-lg hover:shadow-xl transition-all duration-300 border border-gray-100">
                    <div class="flex items-center justify-between mb-3">
                        <div class="w-12 h-12 rounded-xl bg-blue-100 flex items-center justify-center">
                            <i class="fas fa-box text-blue-600 text-xl"></i>
                        </div>
                        <span class="text-xs font-semibold text-blue-600 bg-blue-50 px-2 py-1 rounded-full">Total</span>
                    </div>
                    <h3 class="text-3xl font-bold text-gray-900 mb-1"><?php echo e(number_format($stats['total'] ?? 0)); ?></h3>
                    <p class="text-sm text-gray-600">Total Pengiriman</p>
                    <div class="mt-3 h-1 w-full bg-blue-100 rounded-full">
                        <div class="h-1 bg-blue-600 rounded-full" style="width: 100%"></div>
                    </div>
                </div>

                <!-- Pending Card -->
                <div class="bg-white rounded-2xl p-6 shadow-lg hover:shadow-xl transition-all duration-300 border border-gray-100">
                    <div class="flex items-center justify-between mb-3">
                        <div class="w-12 h-12 rounded-xl bg-yellow-100 flex items-center justify-center">
                            <i class="fas fa-clock text-yellow-600 text-xl"></i>
                        </div>
                        <span class="text-xs font-semibold text-yellow-600 bg-yellow-50 px-2 py-1 rounded-full">Pending</span>
                    </div>
                    <h3 class="text-3xl font-bold text-gray-900 mb-1"><?php echo e(number_format($stats['pending'] ?? 0)); ?></h3>
                    <p class="text-sm text-gray-600">Menunggu Diproses</p>
                    <div class="mt-3 h-1 w-full bg-yellow-100 rounded-full">
                        <?php
                            $pendingPercentage = $totalStats > 0 ? (($stats['pending'] ?? 0) / $totalStats) * 100 : 0;
                        ?>
                        <div class="h-1 bg-yellow-600 rounded-full" style="width: <?php echo e($pendingPercentage); ?>%"></div>
                    </div>
                </div>

                <!-- Aktif/On Delivery Card -->
                <div class="bg-white rounded-2xl p-6 shadow-lg hover:shadow-xl transition-all duration-300 border border-gray-100">
                    <div class="flex items-center justify-between mb-3">
                        <div class="w-12 h-12 rounded-xl bg-purple-100 flex items-center justify-center">
                            <i class="fas fa-truck text-purple-600 text-xl"></i>
                        </div>
                        <span class="text-xs font-semibold text-purple-600 bg-purple-50 px-2 py-1 rounded-full">Aktif</span>
                    </div>
                    <h3 class="text-3xl font-bold text-gray-900 mb-1"><?php echo e(number_format($stats['on_delivery'] ?? 0)); ?></h3>
                    <p class="text-sm text-gray-600">Dalam Perjalanan</p>
                    <div class="mt-3 h-1 w-full bg-purple-100 rounded-full">
                        <?php
                            $activePercentage = $totalStats > 0 ? (($stats['on_delivery'] ?? 0) / $totalStats) * 100 : 0;
                        ?>
                        <div class="h-1 bg-purple-600 rounded-full" style="width: <?php echo e($activePercentage); ?>%"></div>
                    </div>
                </div>

                <!-- Sukses/Delivered Card -->
                <div class="bg-white rounded-2xl p-6 shadow-lg hover:shadow-xl transition-all duration-300 border border-gray-100">
                    <div class="flex items-center justify-between mb-3">
                        <div class="w-12 h-12 rounded-xl bg-green-100 flex items-center justify-center">
                            <i class="fas fa-check-circle text-green-600 text-xl"></i>
                        </div>
                        <span class="text-xs font-semibold text-green-600 bg-green-50 px-2 py-1 rounded-full">Sukses</span>
                    </div>
                    <h3 class="text-3xl font-bold text-gray-900 mb-1"><?php echo e(number_format($stats['delivered'] ?? 0)); ?></h3>
                    <p class="text-sm text-gray-600">Terkirim</p>
                    <div class="mt-3 h-1 w-full bg-green-100 rounded-full">
                        <?php
                            $successPercentage = $totalStats > 0 ? (($stats['delivered'] ?? 0) / $totalStats) * 100 : 0;
                        ?>
                        <div class="h-1 bg-green-600 rounded-full" style="width: <?php echo e($successPercentage); ?>%"></div>
                    </div>
                </div>

                <!-- Gagal/Failed Card -->
                <div class="bg-white rounded-2xl p-6 shadow-lg hover:shadow-xl transition-all duration-300 border border-gray-100">
                    <div class="flex items-center justify-between mb-3">
                        <div class="w-12 h-12 rounded-xl bg-red-100 flex items-center justify-center">
                            <i class="fas fa-exclamation-circle text-red-600 text-xl"></i>
                        </div>
                        <span class="text-xs font-semibold text-red-600 bg-red-50 px-2 py-1 rounded-full">Dibatalkan</span>
                    </div>
                    <h3 class="text-3xl font-bold text-gray-900 mb-1"><?php echo e(number_format($stats['failed'] ?? 0)); ?></h3>
                    <p class="text-sm text-gray-600">Pengiriman Dibatalkan</p>
                    <div class="mt-3 h-1 w-full bg-red-100 rounded-full">
                        <?php
                            $failedPercentage = $totalStats > 0 ? (($stats['failed'] ?? 0) / $totalStats) * 100 : 0;
                        ?>
                        <div class="h-1 bg-red-600 rounded-full" style="width: <?php echo e($failedPercentage); ?>%"></div>
                    </div>
                </div>
            </div>

            <!-- Tombol Aksi Utama - Hanya untuk role yang memiliki akses -->
            <?php
                $userRole = Auth::user()->role;
                $canManage = in_array($userRole, ['owner', 'manager', 'kepala_gudang']);
                $isStaffLogistik = in_array($userRole, ['logistik', 'staff_logistik']);
            ?>

            <?php if($canManage): ?>
                <div class="flex flex-wrap gap-3 mb-6">
                    <a href="<?php echo e(route('delivery.create')); ?>"
                        class="inline-flex items-center px-6 py-3 bg-gradient-to-r from-blue-600 to-indigo-600 text-white rounded-xl font-semibold hover:from-blue-700 hover:to-indigo-700 hover:shadow-lg transform hover:-translate-y-0.5 transition-all duration-200">
                        <i class="fas fa-plus-circle mr-2"></i>
                        Buat Pengiriman Baru
                    </a>

                    <button onclick="openModal('courierModal')"
                        class="inline-flex items-center px-6 py-3 bg-gradient-to-r from-orange-500 to-orange-600 text-white rounded-xl font-semibold hover:from-orange-600 hover:to-orange-700 hover:shadow-lg transform hover:-translate-y-0.5 transition-all duration-200">
                        <i class="fas fa-user-plus mr-2"></i>
                        Tambah Kurir
                    </button>

                    <button onclick="openModal('vehicleModal')"
                        class="inline-flex items-center px-6 py-3 bg-gradient-to-r from-green-500 to-green-600 text-white rounded-xl font-semibold hover:from-green-600 hover:to-green-700 hover:shadow-lg transform hover:-translate-y-0.5 transition-all duration-200">
                        <i class="fas fa-truck mr-2"></i>
                        Tambah Kendaraan
                    </button>

                    <button onclick="exportData()"
                        class="inline-flex items-center px-6 py-3 bg-white text-gray-700 rounded-xl font-semibold hover:bg-gray-50 hover:shadow-lg transform hover:-translate-y-0.5 transition-all duration-200 border border-gray-200">
                        <i class="fas fa-download mr-2"></i>
                        Export Data
                    </button>
                </div>
            <?php elseif($isStaffLogistik): ?>
                <!-- Staff Logistik melihat pengiriman mereka sendiri -->
                <div class="mb-6">
                    <div class="bg-blue-50 border border-blue-200 rounded-xl p-4">
                        <div class="flex items-center gap-3">
                            <i class="fas fa-info-circle text-blue-600 text-xl"></i>
                            <p class="text-blue-800 text-sm">Menampilkan pengiriman yang ditugaskan kepada <strong><?php echo e(Auth::user()->name); ?></strong> (<?php echo e($stats['total'] ?? 0); ?> pengiriman)</p>
                        </div>
                    </div>
                </div>
            <?php endif; ?>
        </div>

        <!-- Filter Section Modern -->
        <div class="bg-white/80 backdrop-blur-sm rounded-2xl shadow-lg border border-gray-100 mb-6 overflow-hidden">
            <div class="p-5 border-b border-gray-100 bg-gray-50/50">
                <h3 class="font-semibold text-gray-900 flex items-center">
                    <i class="fas fa-filter mr-2 text-blue-600"></i>
                    Filter Pengiriman
                </h3>
            </div>

            <div class="p-5">
                <form method="GET" action="<?php echo e(route('delivery.index')); ?>" class="space-y-4" id="filterForm">
                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
                        <!-- Search -->
                        <div class="relative">
                            <i class="fas fa-search absolute left-3 top-1/2 transform -translate-y-1/2 text-gray-400"></i>
                            <input type="text" name="search" value="<?php echo e(request('search')); ?>"
                                placeholder="Cari kode, invoice, tujuan..."
                                class="w-full pl-10 pr-4 py-3 bg-gray-50 border border-gray-200 rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-transparent outline-none transition"
                                onchange="this.form.submit()">
                        </div>

                        <!-- Status Filter -->
                        <div class="relative">
                            <select name="status" onchange="this.form.submit()"
                                class="w-full px-4 py-3 bg-gray-50 border border-gray-200 rounded-xl appearance-none focus:ring-2 focus:ring-blue-500 focus:border-transparent outline-none transition cursor-pointer">
                                <option value="all" <?php echo e(request('status') == 'all' || !request('status') ? 'selected' : ''); ?>>Semua Status</option>
                                <option value="pending" <?php echo e(request('status') == 'pending' ? 'selected' : ''); ?>>Pending</option>
                                <option value="processing" <?php echo e(request('status') == 'processing' ? 'selected' : ''); ?>>Processing</option>
                                <option value="assigned" <?php echo e(request('status') == 'assigned' ? 'selected' : ''); ?>>Assigned</option>
                                <option value="picked_up" <?php echo e(request('status') == 'picked_up' ? 'selected' : ''); ?>>Picked Up</option>
                                <option value="on_delivery" <?php echo e(request('status') == 'on_delivery' ? 'selected' : ''); ?>>Dalam Perjalanan</option>
                                <option value="delivered" <?php echo e(request('status') == 'delivered' ? 'selected' : ''); ?>>Terkirim</option>
                                <option value="failed" <?php echo e(request('status') == 'failed' ? 'selected' : ''); ?>>Gagal</option>
                                <option value="cancelled" <?php echo e(request('status') == 'cancelled' ? 'selected' : ''); ?>>Dibatalkan</option>
                            </select>
                            <i class="fas fa-chevron-down absolute right-4 top-1/2 transform -translate-y-1/2 text-gray-400 pointer-events-none"></i>
                        </div>

                        <!-- Kurir Filter - Hanya untuk role yang memiliki akses -->
                        <?php if($canManage): ?>
                            <div class="relative">
                                <select name="driver_id" onchange="this.form.submit()"
                                    class="w-full px-4 py-3 bg-gray-50 border border-gray-200 rounded-xl appearance-none focus:ring-2 focus:ring-blue-500 focus:border-transparent outline-none transition cursor-pointer">
                                    <option value="all" <?php echo e(request('driver_id') == 'all' || !request('driver_id') ? 'selected' : ''); ?>>Semua Kurir</option>
                                    <?php $__currentLoopData = $drivers ?? []; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $driver): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                        <option value="<?php echo e($driver->id); ?>"
                                            <?php echo e(request('driver_id') == $driver->id ? 'selected' : ''); ?>>
                                            <?php echo e($driver->name); ?>

                                        </option>
                                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                </select>
                                <i class="fas fa-chevron-down absolute right-4 top-1/2 transform -translate-y-1/2 text-gray-400 pointer-events-none"></i>
                            </div>
                        <?php else: ?>
                            <!-- Untuk staff logistik, tampilkan info bahwa hanya menampilkan pengiriman mereka -->
                            <div class="bg-gray-50 rounded-xl px-4 py-3 text-gray-600 text-sm flex items-center">
                                <i class="fas fa-user-check mr-2 text-blue-500"></i>
                                Menampilkan pengiriman untuk: <strong class="ml-1"><?php echo e(Auth::user()->name); ?></strong>
                                <input type="hidden" name="driver_id" value="<?php echo e(Auth::id()); ?>">
                            </div>
                        <?php endif; ?>

                        <!-- Tombol Filter & Reset -->
                        <div class="flex gap-2">
                            <button type="submit"
                                class="flex-1 px-6 py-3 bg-blue-600 text-white rounded-xl hover:bg-blue-700 transition-all font-semibold">
                                <i class="fas fa-filter mr-2"></i>
                                Filter
                            </button>
                            <a href="<?php echo e(route('delivery.index')); ?>"
                                class="px-6 py-3 bg-gray-200 text-gray-700 rounded-xl hover:bg-gray-300 transition-all inline-flex items-center justify-center"
                                title="Reset Filter">
                                <i class="fas fa-redo-alt"></i>
                            </a>
                        </div>
                    </div>

                    <!-- Date Range Filter -->
                    <div class="flex flex-wrap items-center gap-4 pt-2">
                        <div class="flex items-center gap-3">
                            <span class="text-sm font-medium text-gray-700">Dari:</span>
                            <div class="relative">
                                <i class="fas fa-calendar absolute left-3 top-1/2 transform -translate-y-1/2 text-gray-400"></i>
                                <input type="date" name="start_date" value="<?php echo e(request('start_date')); ?>"
                                    class="pl-10 pr-4 py-2.5 bg-gray-50 border border-gray-200 rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-transparent outline-none transition cursor-pointer"
                                    onchange="this.form.submit()">
                            </div>
                        </div>
                        <div class="flex items-center gap-3">
                            <span class="text-sm font-medium text-gray-700">Sampai:</span>
                            <div class="relative">
                                <i class="fas fa-calendar absolute left-3 top-1/2 transform -translate-y-1/2 text-gray-400"></i>
                                <input type="date" name="end_date" value="<?php echo e(request('end_date')); ?>"
                                    class="pl-10 pr-4 py-2.5 bg-gray-50 border border-gray-200 rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-transparent outline-none transition cursor-pointer"
                                    onchange="this.form.submit()">
                            </div>
                        </div>
                        <?php if(request('start_date') || request('end_date')): ?>
                            <a href="<?php echo e(route('delivery.index')); ?>" class="text-sm text-red-600 hover:text-red-700">
                                <i class="fas fa-times-circle mr-1"></i> Hapus filter tanggal
                            </a>
                        <?php endif; ?>
                    </div>
                </form>
            </div>
        </div>

        <!-- Tabel Pengiriman Modern -->
        <div class="bg-white rounded-2xl shadow-lg border border-gray-100 overflow-hidden mb-8">
            <div class="overflow-x-auto">
                <table class="w-full">
                    <thead class="bg-gray-50 border-b border-gray-200">
                        <tr>
                            <th class="px-6 py-4 text-left">
                                <div class="flex items-center space-x-1 text-xs font-semibold text-gray-600 uppercase tracking-wider">
                                    <i class="fas fa-hashtag text-gray-400"></i>
                                    <span>Kode</span>
                                </div>
                            </th>
                            <th class="px-6 py-4 text-left">
                                <div class="flex items-center space-x-1 text-xs font-semibold text-gray-600 uppercase tracking-wider">
                                    <i class="fas fa-file-invoice text-gray-400"></i>
                                    <span>Invoice</span>
                                </div>
                            </th>
                            <th class="px-6 py-4 text-left">
                                <div class="flex items-center space-x-1 text-xs font-semibold text-gray-600 uppercase tracking-wider">
                                    <i class="fas fa-map-marker-alt text-gray-400"></i>
                                    <span>Tujuan</span>
                                </div>
                            </th>
                            <th class="px-6 py-4 text-left">
                                <div class="flex items-center space-x-1 text-xs font-semibold text-gray-600 uppercase tracking-wider">
                                    <i class="fas fa-user text-gray-400"></i>
                                    <span>Kurir</span>
                                </div>
                            </th>
                            <th class="px-6 py-4 text-left">
                                <div class="flex items-center space-x-1 text-xs font-semibold text-gray-600 uppercase tracking-wider">
                                    <i class="fas fa-info-circle text-gray-400"></i>
                                    <span>Status</span>
                                </div>
                            </th>
                            <?php if($canManage): ?>
                                <th class="px-6 py-4 text-left">
                                    <div class="flex items-center space-x-1 text-xs font-semibold text-gray-600 uppercase tracking-wider">
                                        <i class="fas fa-cog text-gray-400"></i>
                                        <span>Aksi</span>
                                    </div>
                                </th>
                            <?php elseif($isStaffLogistik): ?>
                                <th class="px-6 py-4 text-left">
                                    <div class="flex items-center space-x-1 text-xs font-semibold text-gray-600 uppercase tracking-wider">
                                        <i class="fas fa-eye text-gray-400"></i>
                                        <span>Detail</span>
                                    </div>
                                </th>
                            <?php endif; ?>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        <?php $__empty_1 = true; $__currentLoopData = $deliveries; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $delivery): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                            <tr class="hover:bg-blue-50/30 transition-colors group">
                                <td class="px-6 py-4">
                                    <div class="flex items-center">
                                        <div class="w-8 h-8 rounded-lg bg-gradient-to-br from-blue-500 to-indigo-600 flex items-center justify-center text-white text-xs font-bold mr-3">
                                            <?php echo e(substr($delivery->delivery_code, 0, 3)); ?>

                                        </div>
                                        <span class="font-medium text-gray-900"><?php echo e($delivery->delivery_code); ?></span>
                                    </div>
                                </td>
                                <td class="px-6 py-4">
                                    <span class="text-gray-700"><?php echo e($delivery->transaction->invoice_number ?? '-'); ?></span>
                                </td>
                                <td class="px-6 py-4">
                                    <div class="flex items-center">
                                        <i class="fas fa-map-pin text-gray-400 mr-2 text-sm"></i>
                                        <span class="text-gray-700"><?php echo e(Str::limit($delivery->destination, 30)); ?></span>
                                    </div>
                                </td>
                                <td class="px-6 py-4">
                                    <?php if($delivery->user): ?>
                                        <div>
                                            <span class="text-gray-900 font-medium"><?php echo e($delivery->user->name); ?></span>
                                            <?php if($delivery->vehicle): ?>
                                                <span class="text-xs text-gray-500 block"><?php echo e($delivery->vehicle->name); ?> - <?php echo e($delivery->vehicle->license_plate); ?></span>
                                            <?php endif; ?>
                                        </div>
                                    <?php else: ?>
                                        <?php if($canManage): ?>
                                            <button onclick="openAssignModal(<?php echo e($delivery->id); ?>, '<?php echo e($delivery->delivery_code); ?>', '<?php echo e(addslashes($delivery->destination)); ?>', <?php echo e($delivery->total_items); ?>)"
                                                class="inline-flex items-center px-3 py-1.5 bg-blue-50 text-blue-700 rounded-lg hover:bg-blue-100 transition text-sm font-medium">
                                                <i class="fas fa-user-plus mr-1"></i>
                                                Assign Kurir
                                            </button>
                                        <?php else: ?>
                                            <span class="text-gray-400 text-sm">Belum ditugaskan</span>
                                        <?php endif; ?>
                                    <?php endif; ?>
                                </td>
                                <td class="px-6 py-4">
                                    <?php
                                        $statusColors = [
                                            'pending' => ['bg' => 'bg-yellow-100', 'text' => 'text-yellow-800', 'icon' => 'fa-clock'],
                                            'processing' => ['bg' => 'bg-blue-100', 'text' => 'text-blue-800', 'icon' => 'fa-cog fa-spin'],
                                            'assigned' => ['bg' => 'bg-purple-100', 'text' => 'text-purple-800', 'icon' => 'fa-user-check'],
                                            'picked_up' => ['bg' => 'bg-indigo-100', 'text' => 'text-indigo-800', 'icon' => 'fa-box-open'],
                                            'on_delivery' => ['bg' => 'bg-orange-100', 'text' => 'text-orange-800', 'icon' => 'fa-truck'],
                                            'delivered' => ['bg' => 'bg-green-100', 'text' => 'text-green-800', 'icon' => 'fa-check-circle'],
                                            'failed' => ['bg' => 'bg-red-100', 'text' => 'text-red-800', 'icon' => 'fa-exclamation-circle'],
                                            'cancelled' => ['bg' => 'bg-gray-100', 'text' => 'text-gray-800', 'icon' => 'fa-times-circle'],
                                        ];
                                        $color = $statusColors[$delivery->status] ?? ['bg' => 'bg-gray-100', 'text' => 'text-gray-800', 'icon' => 'fa-question-circle'];
                                    ?>
                                    <span class="inline-flex items-center px-3 py-1.5 rounded-full text-xs font-medium <?php echo e($color['bg']); ?> <?php echo e($color['text']); ?>">
                                        <i class="fas <?php echo e($color['icon']); ?> mr-1"></i>
                                        <?php echo e(ucwords(str_replace('_', ' ', $delivery->status))); ?>

                                    </span>
                                </td>
                                <?php if($canManage): ?>
                                    <td class="px-6 py-4">
                                        <div class="flex items-center space-x-2">
                                            <a href="<?php echo e(route('delivery.show', $delivery)); ?>"
                                                class="p-2 text-blue-600 hover:bg-blue-50 rounded-lg transition"
                                                title="Detail">
                                                <i class="fas fa-eye"></i>
                                            </a>

                                            <?php if(in_array($delivery->status, ['pending', 'processing'])): ?>
                                                <button onclick="openAssignModal(<?php echo e($delivery->id); ?>, '<?php echo e($delivery->delivery_code); ?>', '<?php echo e(addslashes($delivery->destination)); ?>', <?php echo e($delivery->total_items); ?>)"
                                                    class="p-2 text-purple-600 hover:bg-purple-50 rounded-lg transition"
                                                    title="Assign Kurir">
                                                    <i class="fas fa-user-plus"></i>
                                                </button>
                                            <?php endif; ?>

                                            <a href="<?php echo e(route('delivery.print.note', $delivery)); ?>" target="_blank"
                                                class="p-2 text-green-600 hover:bg-green-50 rounded-lg transition"
                                                title="Cetak Surat Jalan">
                                                <i class="fas fa-print"></i>
                                            </a>

                                            <?php if($delivery->status == 'pending'): ?>
                                                <button onclick="openCancelModal(<?php echo e($delivery->id); ?>, '<?php echo e($delivery->delivery_code); ?>')"
                                                    class="p-2 text-red-600 hover:bg-red-50 rounded-lg transition"
                                                    title="Batalkan">
                                                    <i class="fas fa-times"></i>
                                                </button>
                                            <?php endif; ?>
                                        </div>
                                    </td>
                                <?php elseif($isStaffLogistik): ?>
                                    <td class="px-6 py-4">
                                        <div class="flex items-center space-x-2">
                                            <a href="<?php echo e(route('delivery.show', $delivery)); ?>"
                                                class="p-2 text-blue-600 hover:bg-blue-50 rounded-lg transition"
                                                title="Detail Pengiriman">
                                                <i class="fas fa-eye"></i>
                                            </a>
                                            
                                            <a href="<?php echo e(route('delivery.print.note', $delivery)); ?>" target="_blank"
                                                class="p-2 text-green-600 hover:bg-green-50 rounded-lg transition"
                                                title="Cetak Surat Jalan">
                                                <i class="fas fa-print"></i>
                                            </a>
                                        </div>
                                    </td>
                                <?php endif; ?>
                            </tr>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                            <tr>
                                <td colspan="<?php echo e($canManage ? 6 : ($isStaffLogistik ? 6 : 5)); ?>" class="px-6 py-16 text-center">
                                    <div class="flex flex-col items-center">
                                        <div class="w-20 h-20 rounded-full bg-gray-100 flex items-center justify-center mb-4">
                                            <i class="fas fa-box-open text-gray-400 text-3xl"></i>
                                        </div>
                                        <h3 class="text-lg font-semibold text-gray-900 mb-1">Belum Ada Pengiriman</h3>
                                        <p class="text-gray-500 mb-4">
                                            <?php if($isStaffLogistik): ?>
                                                Anda belum memiliki pengiriman yang ditugaskan
                                            <?php else: ?>
                                                Mulai dengan membuat pengiriman baru
                                            <?php endif; ?>
                                        </p>
                                        <?php if($canManage): ?>
                                            <a href="<?php echo e(route('delivery.create')); ?>"
                                                class="inline-flex items-center px-6 py-3 bg-blue-600 text-white rounded-xl hover:bg-blue-700 transition font-medium">
                                                <i class="fas fa-plus-circle mr-2"></i>
                                                Buat Pengiriman Baru
                                            </a>
                                        <?php endif; ?>
                                    </div>
                                </td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>

            <!-- Pagination -->
            <?php if(method_exists($deliveries, 'links') && $deliveries->hasPages()): ?>
                <div class="px-6 py-4 border-t border-gray-100 bg-gray-50/50">
                    <?php echo e($deliveries->links()); ?>

                </div>
            <?php endif; ?>
        </div>

        <!-- Daftar Kurir & Kendaraan - Hanya untuk role yang memiliki akses -->
        <?php if($canManage): ?>
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-8">
            <!-- Daftar Kurir -->
            <div class="bg-white rounded-2xl shadow-lg border border-gray-100 overflow-hidden">
                <div class="p-5 border-b border-gray-100 bg-gradient-to-r from-orange-500 to-orange-600">
                    <div class="flex items-center justify-between">
                        <h3 class="font-semibold text-white flex items-center">
                            <i class="fas fa-users mr-2"></i>
                            Daftar Kurir Aktif (<?php echo e($drivers->count()); ?>)
                        </h3>
                    </div>
                </div>
                <div class="p-5">
                    <?php if($drivers->count() > 0): ?>
                        <div class="space-y-3">
                            <?php $__currentLoopData = $drivers; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $driver): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <div class="flex items-center justify-between p-3 bg-gray-50 rounded-xl hover:bg-gray-100 transition">
                                    <div class="flex items-center">
                                        <div class="w-10 h-10 rounded-full bg-gradient-to-r from-orange-500 to-pink-500 flex items-center justify-center text-white font-bold mr-3">
                                            <?php echo e(substr($driver->name, 0, 1)); ?>

                                        </div>
                                        <div>
                                            <p class="font-medium text-gray-900"><?php echo e($driver->name); ?></p>
                                            <p class="text-xs text-gray-500"><?php echo e($driver->email); ?></p>
                                        </div>
                                    </div>
                                    <span class="px-2 py-1 bg-green-100 text-green-800 text-xs rounded-full">Aktif</span>
                                </div>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                        </div>
                    <?php else: ?>
                        <div class="text-center py-8">
                            <i class="fas fa-users text-gray-300 text-4xl mb-2"></i>
                            <p class="text-gray-500">Belum ada kurir</p>
                            <button onclick="openModal('courierModal')"
                                class="mt-2 text-orange-600 hover:text-orange-700 text-sm font-medium">
                                + Tambah Kurir
                            </button>
                        </div>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Daftar Kendaraan -->
            <div class="bg-white rounded-2xl shadow-lg border border-gray-100 overflow-hidden">
                <div class="p-5 border-b border-gray-100 bg-gradient-to-r from-green-500 to-green-600">
                    <div class="flex items-center justify-between">
                        <h3 class="font-semibold text-white flex items-center">
                            <i class="fas fa-truck mr-2"></i>
                            Daftar Kendaraan Tersedia (<?php echo e($vehicles->count()); ?>)
                        </h3>
                    </div>
                </div>
                <div class="p-5">
                    <?php if($vehicles->count() > 0): ?>
                        <div class="space-y-3">
                            <?php $__currentLoopData = $vehicles; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $vehicle): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <div class="flex items-center justify-between p-3 bg-gray-50 rounded-xl hover:bg-gray-100 transition">
                                    <div class="flex items-center">
                                        <div class="w-10 h-10 rounded-full bg-gradient-to-r from-green-500 to-teal-500 flex items-center justify-center text-white mr-3">
                                            <?php
                                                $icon = match($vehicle->type) {
                                                    'truck' => 'fa-truck',
                                                    'van' => 'fa-shuttle-van',
                                                    'motorcycle' => 'fa-motorcycle',
                                                    'pickup' => 'fa-truck-pickup',
                                                    default => 'fa-car'
                                                };
                                                $typeLabel = match($vehicle->type) {
                                                    'truck' => 'Truck',
                                                    'van' => 'Van',
                                                    'motorcycle' => 'Motor',
                                                    'pickup' => 'Pickup',
                                                    default => ucfirst($vehicle->type)
                                                };
                                            ?>
                                            <i class="fas <?php echo e($icon); ?>"></i>
                                        </div>
                                        <div>
                                            <p class="font-medium text-gray-900"><?php echo e($vehicle->name); ?></p>
                                            <p class="text-xs text-gray-500">
                                                <?php echo e($vehicle->license_plate); ?> • <?php echo e($typeLabel); ?>

                                            </p>
                                        </div>
                                    </div>
                                    <span class="px-2 py-1 bg-green-100 text-green-800 text-xs rounded-full">
                                        <?php echo e($typeLabel); ?>

                                    </span>
                                </div>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                        </div>
                    <?php else: ?>
                        <div class="text-center py-8">
                            <i class="fas fa-truck text-gray-300 text-4xl mb-2"></i>
                            <p class="text-gray-500">Belum ada kendaraan</p>
                            <button onclick="openModal('vehicleModal')"
                                class="mt-2 text-green-600 hover:text-green-700 text-sm font-medium">
                                + Tambah Kendaraan
                            </button>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
        <?php endif; ?>
    </div>

    <!-- Modal Assign Professional -->
    <div id="assignModal" class="fixed inset-0 bg-black/60 hidden items-center justify-center z-[9999] backdrop-blur-sm">
        <div class="bg-white rounded-2xl max-w-md w-full mx-4 shadow-2xl transform transition-all">
            <div class="flex justify-between items-center p-6 border-b">
                <h3 class="text-xl font-bold text-gray-900 flex items-center">
                    <div class="w-8 h-8 rounded-full bg-blue-100 flex items-center justify-center mr-3">
                        <i class="fas fa-user-plus text-blue-600"></i>
                    </div>
                    Assign Kurir & Kendaraan
                </h3>
                <button onclick="closeModal('assignModal')" class="text-gray-400 hover:text-gray-600 transition">
                    <i class="fas fa-times text-xl"></i>
                </button>
            </div>

            <div class="p-6">
                <div id="deliveryInfo" class="bg-blue-50 p-4 rounded-xl mb-6 text-sm">
                    <div class="flex items-center text-blue-800 mb-2">
                        <i class="fas fa-info-circle mr-2"></i>
                        <span class="font-medium">Informasi Pengiriman</span>
                    </div>
                    <div id="deliveryInfoContent" class="space-y-1 text-blue-700"></div>
                </div>

                <form id="assignForm" method="POST" action="">
                    <?php echo csrf_field(); ?>

                    <div class="mb-5">
                        <label class="block text-sm font-medium text-gray-700 mb-2">
                            <i class="fas fa-user mr-1 text-blue-600"></i>
                            Pilih Kurir <span class="text-red-500">*</span>
                        </label>
                        <select name="driver_id" required
                            class="w-full p-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-transparent outline-none transition">
                            <option value="">-- Pilih Kurir --</option>
                            <?php $__currentLoopData = $drivers; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $driver): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <option value="<?php echo e($driver->id); ?>"><?php echo e($driver->name); ?></option>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                        </select>
                    </div>

                    <div class="mb-6">
                        <label class="block text-sm font-medium text-gray-700 mb-2">
                            <i class="fas fa-truck mr-1 text-green-600"></i>
                            Pilih Kendaraan <span class="text-red-500">*</span>
                        </label>
                        <select name="vehicle_id" required
                            class="w-full p-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-transparent outline-none transition">
                            <option value="">-- Pilih Kendaraan --</option>
                            <?php $__currentLoopData = $vehicles; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $vehicle): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <option value="<?php echo e($vehicle->id); ?>"><?php echo e($vehicle->name); ?> -
                                    <?php echo e($vehicle->license_plate ?? $vehicle->plate_number); ?></option>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                        </select>
                    </div>

                    <div class="flex gap-3">
                        <button type="submit"
                            class="flex-1 bg-gradient-to-r from-blue-600 to-indigo-600 text-white py-3 rounded-xl hover:from-blue-700 hover:to-indigo-700 transition-all font-medium shadow-lg hover:shadow-xl">
                            <i class="fas fa-check mr-2"></i>
                            Assign Sekarang
                        </button>
                        <button type="button" onclick="closeModal('assignModal')"
                            class="flex-1 bg-gray-200 text-gray-700 py-3 rounded-xl hover:bg-gray-300 transition-all font-medium">
                            Batal
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Modal Tambah Kurir Professional -->
    <div id="courierModal" class="fixed inset-0 bg-black/60 hidden items-center justify-center z-[9999] backdrop-blur-sm">
        <div class="bg-white rounded-2xl max-w-md w-full mx-4 shadow-2xl max-h-[90vh] flex flex-col">
            <div class="flex justify-between items-center p-6 border-b flex-shrink-0">
                <h3 class="text-xl font-bold text-gray-900 flex items-center">
                    <div class="w-8 h-8 rounded-full bg-orange-100 flex items-center justify-center mr-3">
                        <i class="fas fa-user-plus text-orange-600"></i>
                    </div>
                    Tambah Kurir Baru
                </h3>
                <button onclick="closeModal('courierModal')" class="text-gray-400 hover:text-gray-600 transition">
                    <i class="fas fa-times text-xl"></i>
                </button>
            </div>

            <div class="p-6 overflow-y-auto flex-1">
                <form action="<?php echo e(route('delivery.kurir.store')); ?>" method="POST" id="courierForm">
                    <?php echo csrf_field(); ?>
                    
                    <div class="mb-4">
                        <label class="block text-sm font-medium text-gray-700 mb-2">
                            Nama Lengkap <span class="text-red-500">*</span>
                        </label>
                        <input type="text" name="name" required
                            class="w-full p-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-orange-500 focus:border-transparent outline-none transition"
                            placeholder="Masukkan nama lengkap" value="<?php echo e(old('name')); ?>">
                    </div>

                    <div class="mb-4">
                        <label class="block text-sm font-medium text-gray-700 mb-2">
                            Email <span class="text-red-500">*</span>
                        </label>
                        <input type="email" name="email" required
                            class="w-full p-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-orange-500 focus:border-transparent outline-none transition"
                            placeholder="nama@email.com" value="<?php echo e(old('email')); ?>">
                    </div>

                    <div class="mb-4">
                        <label class="block text-sm font-medium text-gray-700 mb-2">
                            Password <span class="text-red-500">*</span>
                        </label>
                        <input type="password" name="password" required
                            class="w-full p-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-orange-500 focus:border-transparent outline-none transition"
                            placeholder="Minimal 8 karakter">
                    </div>

                    <div class="mb-4">
                        <label class="block text-sm font-medium text-gray-700 mb-2">
                            Konfirmasi Password <span class="text-red-500">*</span>
                        </label>
                        <input type="password" name="password_confirmation" required
                            class="w-full p-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-orange-500 focus:border-transparent outline-none transition"
                            placeholder="Ulangi password">
                    </div>

                    <div class="mb-4">
                        <label class="block text-sm font-medium text-gray-700 mb-2">
                            No. Telepon
                        </label>
                        <input type="text" name="phone" id="phoneInput"
                            class="w-full p-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-orange-500 focus:border-transparent outline-none transition"
                            placeholder="08xx xxxx xxxx" value="<?php echo e(old('phone')); ?>" maxlength="15">
                        <p class="text-xs text-gray-500 mt-1">Format: 08xx xxxx xxxx (spasi otomatis)</p>
                    </div>

                    <div class="mb-4">
                        <label class="block text-sm font-medium text-gray-700 mb-2">
                            Alamat
                        </label>
                        <textarea name="address" rows="2"
                            class="w-full p-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-orange-500 focus:border-transparent outline-none transition resize-none"
                            placeholder="Masukkan alamat lengkap"><?php echo e(old('address')); ?></textarea>
                    </div>

                    <div class="bg-blue-50 p-3 rounded-xl mb-4">
                        <p class="text-xs text-blue-700 flex items-center gap-2">
                            <i class="fas fa-info-circle"></i>
                            Kurir akan memiliki role <strong>Staff Logistik</strong> dan dapat mengakses dashboard pengiriman.
                        </p>
                    </div>
                </form>
            </div>

            <div class="p-6 border-t flex gap-3 flex-shrink-0">
                <button type="submit" form="courierForm"
                    class="flex-1 bg-gradient-to-r from-orange-500 to-orange-600 text-white py-3 rounded-xl hover:from-orange-600 hover:to-orange-700 transition-all font-medium shadow-lg">
                    <i class="fas fa-save mr-2"></i>
                    Simpan Kurir
                </button>
                <button type="button" onclick="closeModal('courierModal')"
                    class="flex-1 bg-gray-200 text-gray-700 py-3 rounded-xl hover:bg-gray-300 transition-all font-medium">
                    Batal
                </button>
            </div>
        </div>
    </div>

    <!-- Modal Tambah Kendaraan Professional -->
    <div id="vehicleModal" class="fixed inset-0 bg-black/60 hidden items-center justify-center z-[9999] backdrop-blur-sm">
        <div class="bg-white rounded-2xl max-w-md w-full mx-4 shadow-2xl max-h-[90vh] flex flex-col">
            <div class="flex justify-between items-center p-6 border-b flex-shrink-0">
                <h3 class="text-xl font-bold text-gray-900 flex items-center">
                    <div class="w-8 h-8 rounded-full bg-green-100 flex items-center justify-center mr-3">
                        <i class="fas fa-truck text-green-600"></i>
                    </div>
                    Tambah Kendaraan
                </h3>
                <button onclick="closeModal('vehicleModal')" class="text-gray-400 hover:text-gray-600 transition">
                    <i class="fas fa-times text-xl"></i>
                </button>
            </div>

            <div class="p-6 overflow-y-auto flex-1">
                <form action="<?php echo e(route('vehicles.store')); ?>" method="POST" id="vehicleForm">
                    <?php echo csrf_field(); ?>
                    
                    <div class="mb-4">
                        <label class="block text-sm font-medium text-gray-700 mb-2">
                            Nama Kendaraan <span class="text-red-500">*</span>
                        </label>
                        <input type="text" name="name" id="vehicle_name" required
                            class="w-full p-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-green-500 focus:border-transparent outline-none transition"
                            placeholder="Contoh: Honda Beat, Toyota Avanza" value="<?php echo e(old('name')); ?>">
                    </div>

                    <div class="mb-4">
                        <label class="block text-sm font-medium text-gray-700 mb-2">
                            Plat Nomor <span class="text-red-500">*</span>
                        </label>
                        <input type="text" name="license_plate" id="license_plate" required
                            class="w-full p-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-green-500 focus:border-transparent outline-none transition uppercase"
                            placeholder="Contoh: B 1234 XYZ" value="<?php echo e(old('license_plate')); ?>">
                    </div>

                    <div class="mb-4">
                        <label class="block text-sm font-medium text-gray-700 mb-2">
                            Jenis Kendaraan <span class="text-red-500">*</span>
                        </label>
                        <select name="type" id="vehicle_type" required
                            class="w-full p-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-green-500 focus:border-transparent outline-none transition">
                            <option value="">-- Pilih Jenis Kendaraan --</option>
                            <option value="truck">Truck</option>
                            <option value="van">Van</option>
                            <option value="motorcycle">Motorcycle</option>
                            <option value="pickup">Pickup</option>
                        </select>
                    </div>

                    <div class="mb-4">
                        <label class="block text-sm font-medium text-gray-700 mb-2">
                            Kapasitas Berat (kg)
                        </label>
                        <input type="number" name="capacity_weight" id="capacity_weight" 
                            min="0" step="0.1" value="<?php echo e(old('capacity_weight')); ?>"
                            class="w-full p-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-green-500 focus:border-transparent outline-none transition"
                            placeholder="Contoh: 1000">
                    </div>

                    <div class="mb-4">
                        <label class="block text-sm font-medium text-gray-700 mb-2">
                            Kapasitas Volume (m³)
                        </label>
                        <input type="number" name="capacity_volume" id="capacity_volume" 
                            min="0" step="0.1" value="<?php echo e(old('capacity_volume')); ?>"
                            class="w-full p-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-green-500 focus:border-transparent outline-none transition"
                            placeholder="Contoh: 5.5">
                    </div>

                    <div class="mb-4">
                        <label class="block text-sm font-medium text-gray-700 mb-2">
                            Status
                        </label>
                        <select name="status" id="vehicle_status"
                            class="w-full p-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-green-500 focus:border-transparent outline-none transition">
                            <option value="available" selected>Tersedia</option>
                            <option value="in_use">Sedang Digunakan</option>
                            <option value="maintenance">Servis/Maintenance</option>
                        </select>
                    </div>

                    <div class="mb-4">
                        <label class="block text-sm font-medium text-gray-700 mb-2">
                            Terakhir Maintenance
                        </label>
                        <input type="date" name="last_maintenance" id="last_maintenance" 
                            value="<?php echo e(old('last_maintenance')); ?>"
                            class="w-full p-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-green-500 focus:border-transparent outline-none transition">
                    </div>

                    <div class="mb-4">
                        <label class="block text-sm font-medium text-gray-700 mb-2">
                            Catatan
                        </label>
                        <textarea name="notes" id="notes" rows="2"
                            class="w-full p-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-green-500 focus:border-transparent outline-none transition resize-none"
                            placeholder="Catatan tambahan tentang kendaraan (kondisi, kelengkapan, dll)"><?php echo e(old('notes')); ?></textarea>
                    </div>
                </form>
            </div>

            <div class="p-6 border-t flex gap-3 flex-shrink-0">
                <button type="submit" form="vehicleForm"
                    class="flex-1 bg-gradient-to-r from-green-500 to-green-600 text-white py-3 rounded-xl hover:from-green-600 hover:to-green-700 transition-all font-medium shadow-lg">
                    <i class="fas fa-save mr-2"></i>
                    Simpan Kendaraan
                </button>
                <button type="button" onclick="closeModal('vehicleModal')"
                    class="flex-1 bg-gray-200 text-gray-700 py-3 rounded-xl hover:bg-gray-300 transition-all font-medium">
                    Batal
                </button>
            </div>
        </div>
    </div>

    <!-- Modal Cancel Professional -->
    <div id="cancelModal" class="fixed inset-0 bg-black/60 hidden items-center justify-center z-[9999] backdrop-blur-sm">
        <div class="bg-white rounded-2xl max-w-md w-full mx-4 shadow-2xl">
            <div class="flex justify-between items-center p-6 border-b">
                <h3 class="text-xl font-bold text-gray-900 flex items-center">
                    <div class="w-8 h-8 rounded-full bg-red-100 flex items-center justify-center mr-3">
                        <i class="fas fa-times-circle text-red-600"></i>
                    </div>
                    Batalkan Pengiriman
                </h3>
                <button onclick="closeModal('cancelModal')" class="text-gray-400 hover:text-gray-600 transition">
                    <i class="fas fa-times text-xl"></i>
                </button>
            </div>

            <div class="p-6">
                <div class="bg-red-50 p-4 rounded-xl mb-6">
                    <p class="text-red-800 text-sm" id="cancelDeliveryCode"></p>
                </div>

                <form id="cancelForm" method="POST" action="">
                    <?php echo csrf_field(); ?>

                    <div class="mb-6">
                        <label class="block text-sm font-medium text-gray-700 mb-2">Alasan Pembatalan <span class="text-red-500">*</span></label>
                        <textarea name="cancellation_reason" rows="3" required
                            class="w-full p-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-red-500 focus:border-transparent outline-none transition"
                            placeholder="Jelaskan alasan pembatalan..."></textarea>
                    </div>

                    <div class="flex gap-3">
                        <button type="submit"
                            class="flex-1 bg-gradient-to-r from-red-500 to-red-600 text-white py-3 rounded-xl hover:from-red-600 hover:to-red-700 transition-all font-medium shadow-lg">
                            <i class="fas fa-check mr-2"></i>
                            Batalkan Pengiriman
                        </button>
                        <button type="button" onclick="closeModal('cancelModal')"
                            class="flex-1 bg-gray-200 text-gray-700 py-3 rounded-xl hover:bg-gray-300 transition-all font-medium">
                            Tutup
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Script untuk modal -->
    <?php $__env->startPush('scripts'); ?>
    <script>
        // Fungsi untuk membuka modal
        function openModal(modalId) {
            const modal = document.getElementById(modalId);
            if (modal) {
                modal.classList.remove('hidden');
                modal.classList.add('flex');
                document.body.style.overflow = 'hidden';
            }
        }

        // Fungsi untuk menutup modal
        function closeModal(modalId) {
            const modal = document.getElementById(modalId);
            if (modal) {
                modal.classList.add('hidden');
                modal.classList.remove('flex');
                document.body.style.overflow = 'auto';
            }
        }

        // Fungsi untuk membuka modal assign
        function openAssignModal(id, code, destination, items) {
            const form = document.getElementById('assignForm');
            if (form) {
                form.action = '/delivery/' + id + '/assign';
            }

            const infoContent = document.getElementById('deliveryInfoContent');
            if (infoContent) {
                infoContent.innerHTML = `
                    <div class="flex justify-between"><span class="font-medium">Kode:</span> <span>${code}</span></div>
                    <div class="flex justify-between"><span class="font-medium">Tujuan:</span> <span>${destination}</span></div>
                    <div class="flex justify-between"><span class="font-medium">Total Item:</span> <span>${items} barang</span></div>
                `;
            }

            const formElement = document.getElementById('assignForm');
            if (formElement) {
                formElement.reset();
            }

            openModal('assignModal');
        }

        // Fungsi untuk membuka modal cancel
        function openCancelModal(id, code) {
            const form = document.getElementById('cancelForm');
            if (form) {
                form.action = '/delivery/' + id + '/cancel';
            }

            const cancelInfo = document.getElementById('cancelDeliveryCode');
            if (cancelInfo) {
                cancelInfo.innerHTML = `<i class="fas fa-exclamation-triangle mr-2"></i> Yakin ingin membatalkan pengiriman <strong>${code}</strong>?`;
            }

            openModal('cancelModal');
        }

        // Fungsi export
        function exportData() {
            const urlParams = new URLSearchParams(window.location.search);
            let exportUrl = '<?php echo e(route("reports.delivery.pdf")); ?>?' + urlParams.toString();
            window.open(exportUrl, '_blank');
        }

        // Tutup modal jika klik di luar
        window.addEventListener('click', function(event) {
            if (event.target.classList.contains('fixed')) {
                const modalId = event.target.id;
                if (modalId) {
                    closeModal(modalId);
                    document.body.style.overflow = 'auto';
                }
            }
        });

        // Escape key untuk tutup modal
        document.addEventListener('keydown', function(event) {
            if (event.key === 'Escape') {
                const modals = ['assignModal', 'courierModal', 'vehicleModal', 'cancelModal'];
                modals.forEach(modalId => {
                    const modal = document.getElementById(modalId);
                    if (modal && !modal.classList.contains('hidden')) {
                        closeModal(modalId);
                        document.body.style.overflow = 'auto';
                    }
                });
            }
        });

        // Format nomor telepon
        document.addEventListener('DOMContentLoaded', function() {
            const phoneInput = document.getElementById('phoneInput');
            if (phoneInput) {
                phoneInput.addEventListener('input', function(e) {
                    let value = e.target.value.replace(/\D/g, '');
                    if (value.length > 12) {
                        value = value.substring(0, 12);
                    }
                    if (value.length > 0) {
                        if (value.length <= 4) {
                            e.target.value = value;
                        } else if (value.length <= 8) {
                            e.target.value = value.substring(0, 4) + ' ' + value.substring(4);
                        } else {
                            e.target.value = value.substring(0, 4) + ' ' + value.substring(4, 8) + ' ' + value.substring(8, 12);
                        }
                    } else {
                        e.target.value = '';
                    }
                });
            }

            // Format plat nomor menjadi uppercase otomatis
            const licensePlate = document.getElementById('license_plate');
            if (licensePlate) {
                licensePlate.addEventListener('input', function(e) {
                    e.target.value = e.target.value.toUpperCase();
                });
            }
        });
    </script>
    <?php $__env->stopPush(); ?>
<?php $__env->stopSection(); ?>
<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\laragon\www\Toko-Roni-Mobile-App\resources\views/delivery/index.blade.php ENDPATH**/ ?>