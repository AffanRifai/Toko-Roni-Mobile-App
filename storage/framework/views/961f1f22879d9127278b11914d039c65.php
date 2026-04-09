<?php $__env->startSection('title', 'Manajemen Kendaraan'); ?>
<?php $__env->startSection('page-title', 'Manajemen Kendaraan'); ?>
<?php $__env->startSection('page-subtitle', 'Kelola semua kendaraan untuk pengiriman'); ?>

<?php $__env->startSection('content'); ?>
<div class="min-h-screen bg-gradient-to-br from-blue-50/50 to-purple-50/30 p-4 md:p-6">

    <!-- Header -->
    <div class="glass-effect rounded-3xl p-6 md:p-8 shadow-elegant mb-6">
        <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-4">
            <div class="flex items-center gap-4">
                <div class="relative">
                    <div class="w-16 h-16 rounded-2xl bg-gradient-to-br from-green-500 to-emerald-600 flex items-center justify-center shadow-lg">
                        <i class="fas fa-car text-2xl text-white"></i>
                    </div>
                    <div class="absolute -inset-1 bg-gradient-to-r from-green-500 to-emerald-600 rounded-2xl blur-xl opacity-20"></div>
                </div>
                <div>
                    <h1 class="text-3xl font-bold text-gray-800">Manajemen Kendaraan</h1>
                    <p class="text-gray-600 mt-1">Kelola semua kendaraan untuk pengiriman</p>
                </div>
            </div>
            <?php
                $userRole = Auth::user()->role;
                $canManage = in_array($userRole, ['owner', 'manager', 'kepala_gudang']);
            ?>
            
            <?php if($canManage): ?>
            <div class="flex gap-3">
                <a href="<?php echo e(route('vehicles.create')); ?>"
                   class="flex items-center gap-2 px-6 py-3 bg-gradient-to-r from-green-500 to-emerald-600 text-white rounded-xl hover:shadow-lg hover:-translate-y-0.5 transition-all">
                    <i class="fas fa-plus-circle"></i>
                    <span>Tambah Kendaraan</span>
                </a>
            </div>
            <?php endif; ?>
        </div>
    </div>

    <!-- Stats Cards -->
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
        <div class="stat-card group">
            <div class="stat-card-content">
                <p class="text-sm text-gray-500">Total Kendaraan</p>
                <h3 class="text-2xl font-bold text-gray-800"><?php echo e($stats['total'] ?? 0); ?></h3>
            </div>
        </div>
        <div class="stat-card group">
            <div class="stat-card-content">
                <p class="text-sm text-gray-500">Tersedia</p>
                <h3 class="text-2xl font-bold text-green-600"><?php echo e($stats['available'] ?? 0); ?></h3>
            </div>
        </div>
        <div class="stat-card group">
            <div class="stat-card-content">
                <p class="text-sm text-gray-500">Sedang Digunakan</p>
                <h3 class="text-2xl font-bold text-blue-600"><?php echo e($stats['in_use'] ?? 0); ?></h3>
            </div>
        </div>
        <div class="stat-card group">
            <div class="stat-card-content">
                <p class="text-sm text-gray-500">Servis</p>
                <h3 class="text-2xl font-bold text-red-600"><?php echo e($stats['maintenance'] ?? 0); ?></h3>
            </div>
        </div>
    </div>

    <!-- Vehicles Table -->
    <div class="glass-effect rounded-2xl overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full">
                <thead class="bg-gray-50/50">
                     <tr>
                        <th class="px-6 py-4 text-left text-sm font-semibold text-gray-600">ID</th>
                        <th class="px-6 py-4 text-left text-sm font-semibold text-gray-600">Nama Kendaraan</th>
                        <th class="px-6 py-4 text-left text-sm font-semibold text-gray-600">Plat Nomor</th>
                        <th class="px-6 py-4 text-left text-sm font-semibold text-gray-600">Jenis</th>
                        <th class="px-6 py-4 text-left text-sm font-semibold text-gray-600">Kapasitas</th>
                        <th class="px-6 py-4 text-left text-sm font-semibold text-gray-600">Last Maintenance</th>
                        <th class="px-6 py-4 text-left text-sm font-semibold text-gray-600">Status</th>
                        <?php if($canManage): ?>
                        <th class="px-6 py-4 text-left text-sm font-semibold text-gray-600">Aksi</th>
                        <?php endif; ?>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    <?php $__empty_1 = true; $__currentLoopData = $vehicles; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $vehicle): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                    <tr class="hover:bg-white/30 transition-colors">
                        <td class="px-6 py-4 text-sm text-gray-600">#<?php echo e($vehicle->id); ?></td>
                        <td class="px-6 py-4">
                            <div class="font-medium text-gray-900"><?php echo e($vehicle->name); ?></div>
                        </td>
                        <td class="px-6 py-4">
                            <span class="font-mono text-sm font-medium"><?php echo e($vehicle->license_plate); ?></span>
                        </td>
                        <td class="px-6 py-4">
                            <?php
                                $typeLabels = [
                                    'truck' => 'Truck',
                                    'van' => 'Van',
                                    'motorcycle' => 'Motor',
                                    'pickup' => 'Pickup'
                                ];
                                $typeColors = [
                                    'truck' => 'bg-orange-100 text-orange-800',
                                    'van' => 'bg-blue-100 text-blue-800',
                                    'motorcycle' => 'bg-purple-100 text-purple-800',
                                    'pickup' => 'bg-green-100 text-green-800'
                                ];
                            ?>
                            <span class="px-2 py-1 text-xs rounded-full <?php echo e($typeColors[$vehicle->type] ?? 'bg-gray-100 text-gray-800'); ?>">
                                <?php echo e($typeLabels[$vehicle->type] ?? ucfirst($vehicle->type)); ?>

                            </span>
                        </td>
                        <td class="px-6 py-4 text-sm">
                            <?php if($vehicle->capacity_weight > 0 || $vehicle->capacity_volume > 0): ?>
                                <div><?php echo e(number_format($vehicle->capacity_weight, 0)); ?> kg</div>
                                <div class="text-xs text-gray-500"><?php echo e(number_format($vehicle->capacity_volume, 1)); ?> m³</div>
                            <?php else: ?>
                                <span class="text-gray-400">-</span>
                            <?php endif; ?>
                        </td>
                        <td class="px-6 py-4 text-sm">
                            <?php if($vehicle->last_maintenance): ?>
                                <?php
                                    try {
                                        $lastMaintenance = \Carbon\Carbon::parse($vehicle->last_maintenance);
                                        // Gunakan diffInDays dengan floor untuk mendapatkan integer
                                        $daysSince = floor($lastMaintenance->diffInDays(now()));
                                        $maintenanceDate = $lastMaintenance->format('d/m/Y');
                                    } catch (\Exception $e) {
                                        $maintenanceDate = '-';
                                        $daysSince = null;
                                    }
                                ?>
                                <div><?php echo e($maintenanceDate); ?></div>
                                <?php if($daysSince !== null): ?>
                                    <?php if($daysSince > 30): ?>
                                        <span class="text-xs text-red-600 font-medium"><?php echo e((int)$daysSince); ?> hari lalu (Perlu servis!)</span>
                                    <?php elseif($daysSince > 0): ?>
                                        <span class="text-xs text-gray-500"><?php echo e((int)$daysSince); ?> hari lalu</span>
                                    <?php elseif($daysSince == 0): ?>
                                        <span class="text-xs text-green-600">Hari ini</span>
                                    <?php endif; ?>
                                <?php endif; ?>
                            <?php else: ?>
                                <span class="text-gray-400">Belum pernah</span>
                            <?php endif; ?>
                        </td>
                        <td class="px-6 py-4">
                            <?php
                                $statusBadge = [
                                    'available' => 'bg-green-100 text-green-800',
                                    'in_use' => 'bg-blue-100 text-blue-800',
                                    'maintenance' => 'bg-red-100 text-red-800'
                                ];
                                $statusIcon = [
                                    'available' => 'fa-check-circle',
                                    'in_use' => 'fa-truck',
                                    'maintenance' => 'fa-tools'
                                ];
                                $statusText = [
                                    'available' => 'Tersedia',
                                    'in_use' => 'Digunakan',
                                    'maintenance' => 'Servis'
                                ];
                            ?>
                            <span class="badge <?php echo e($statusBadge[$vehicle->status] ?? 'bg-gray-100 text-gray-800'); ?>">
                                <i class="fas <?php echo e($statusIcon[$vehicle->status] ?? 'fa-circle'); ?> mr-1 text-[8px]"></i>
                                <?php echo e($statusText[$vehicle->status] ?? ucfirst($vehicle->status)); ?>

                            </span>
                        </td>
                        <?php if($canManage): ?>
                        <td class="px-6 py-4">
                            <div class="flex items-center gap-2">
                                <a href="<?php echo e(route('vehicles.show', $vehicle)); ?>"
                                   class="w-8 h-8 rounded-lg hover:bg-blue-50 flex items-center justify-center text-blue-600 transition-colors"
                                   title="Detail">
                                    <i class="fas fa-eye"></i>
                                </a>
                                <a href="<?php echo e(route('vehicles.edit', $vehicle)); ?>"
                                   class="w-8 h-8 rounded-lg hover:bg-yellow-50 flex items-center justify-center text-yellow-600 transition-colors"
                                   title="Edit">
                                    <i class="fas fa-edit"></i>
                                </a>
                                <?php if($vehicle->status != 'in_use'): ?>
                                <form action="<?php echo e(route('vehicles.destroy', $vehicle)); ?>" method="POST" class="inline" onsubmit="return confirm('Yakin ingin menghapus kendaraan ini?')">
                                    <?php echo csrf_field(); ?>
                                    <?php echo method_field('DELETE'); ?>
                                    <button type="submit"
                                            class="w-8 h-8 rounded-lg hover:bg-red-50 flex items-center justify-center text-red-600 transition-colors"
                                            title="Hapus">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </form>
                                <?php endif; ?>
                            </div>
                        </td>
                        <?php endif; ?>
                    </tr>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                    <tr>
                        <td colspan="<?php echo e($canManage ? 8 : 7); ?>" class="px-6 py-12 text-center">
                            <div class="flex flex-col items-center">
                                <div class="w-16 h-16 bg-gray-100 rounded-full flex items-center justify-center mb-4">
                                    <i class="fas fa-car text-3xl text-gray-400"></i>
                                </div>
                                <h3 class="text-lg font-medium text-gray-900 mb-2">Belum ada kendaraan</h3>
                                <p class="text-gray-600 mb-4">Mulai dengan menambahkan kendaraan baru</p>
                                <?php if($canManage): ?>
                                <a href="<?php echo e(route('vehicles.create')); ?>"
                                   class="px-4 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700 transition-colors">
                                    <i class="fas fa-plus mr-2"></i>Tambah Kendaraan
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
        <?php if(method_exists($vehicles, 'hasPages') && $vehicles->hasPages()): ?>
        <div class="px-6 py-4 border-t border-gray-100">
            <div class="flex items-center justify-between">
                <div class="text-sm text-gray-600">
                    Menampilkan <?php echo e($vehicles->firstItem()); ?> - <?php echo e($vehicles->lastItem()); ?>

                    dari <?php echo e($vehicles->total()); ?> kendaraan
                </div>
                <div class="flex items-center gap-2">
                    <?php echo e($vehicles->links()); ?>

                </div>
            </div>
        </div>
        <?php endif; ?>
    </div>
</div>

<style>
.badge {
    display: inline-flex;
    align-items: center;
    padding: 0.25rem 0.75rem;
    border-radius: 9999px;
    font-size: 0.75rem;
    font-weight: 500;
    border-width: 1px;
}

.stat-card {
    position: relative;
    background: rgba(255, 255, 255, 0.8);
    backdrop-filter: blur(8px);
    border-radius: 1.5rem;
    padding: 1.5rem;
    border: 1px solid rgba(255, 255, 255, 0.5);
    transition: all 0.3s;
}

.stat-card:hover {
    transform: translateY(-4px);
    box-shadow: 0 20px 25px -5px rgba(0,0,0,0.1);
}

.glass-effect {
    background: rgba(255, 255, 255, 0.7);
    backdrop-filter: blur(10px);
    border: 1px solid rgba(59, 130, 246, 0.1);
}
</style>
<?php $__env->stopSection(); ?>
<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\laragon\www\Toko-Roni-Mobile-App\resources\views/vehicles/index.blade.php ENDPATH**/ ?>