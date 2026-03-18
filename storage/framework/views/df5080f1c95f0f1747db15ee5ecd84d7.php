<?php $__env->startSection('title', 'Tugas Pengiriman Saya'); ?>
<?php $__env->startSection('page-title', 'Tugas Pengiriman Saya'); ?>
<?php $__env->startSection('page-subtitle', 'Daftar pengiriman yang ditugaskan kepada Anda'); ?>

<?php $__env->startSection('content'); ?>
<div class="min-h-screen bg-gradient-to-br from-blue-50 via-white to-purple-50 p-4 md:p-6 lg:p-8">

    <!-- Header dengan Statistik -->
    <div class="mb-8">
        <h1 class="text-3xl font-bold text-gray-900 mb-6">Tugas Pengiriman Saya</h1>
        
        <!-- Statistik Cards -->
        <div class="grid grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
            <!-- Total Card -->
            <div class="bg-white rounded-2xl p-6 shadow-lg border border-gray-100">
                <div class="flex items-center justify-between mb-3">
                    <div class="w-12 h-12 rounded-xl bg-blue-100 flex items-center justify-center">
                        <i class="fas fa-box text-blue-600 text-xl"></i>
                    </div>
                </div>
                <h3 class="text-3xl font-bold text-gray-900 mb-1"><?php echo e(number_format($stats['total'] ?? 0)); ?></h3>
                <p class="text-sm text-gray-600">Total Tugas</p>
            </div>
            
            <!-- Assigned Card -->
            <div class="bg-white rounded-2xl p-6 shadow-lg border border-gray-100">
                <div class="flex items-center justify-between mb-3">
                    <div class="w-12 h-12 rounded-xl bg-purple-100 flex items-center justify-center">
                        <i class="fas fa-user-check text-purple-600 text-xl"></i>
                    </div>
                </div>
                <h3 class="text-3xl font-bold text-gray-900 mb-1"><?php echo e(number_format($stats['assigned'] ?? 0)); ?></h3>
                <p class="text-sm text-gray-600">Perlu Diterima</p>
            </div>
            
            <!-- On Delivery Card -->
            <div class="bg-white rounded-2xl p-6 shadow-lg border border-gray-100">
                <div class="flex items-center justify-between mb-3">
                    <div class="w-12 h-12 rounded-xl bg-orange-100 flex items-center justify-center">
                        <i class="fas fa-truck text-orange-600 text-xl"></i>
                    </div>
                </div>
                <h3 class="text-3xl font-bold text-gray-900 mb-1"><?php echo e(number_format($stats['on_delivery'] ?? 0)); ?></h3>
                <p class="text-sm text-gray-600">Sedang Diantar</p>
            </div>
            
            <!-- Completed Card -->
            <div class="bg-white rounded-2xl p-6 shadow-lg border border-gray-100">
                <div class="flex items-center justify-between mb-3">
                    <div class="w-12 h-12 rounded-xl bg-green-100 flex items-center justify-center">
                        <i class="fas fa-check-circle text-green-600 text-xl"></i>
                    </div>
                </div>
                <h3 class="text-3xl font-bold text-gray-900 mb-1"><?php echo e(number_format($stats['completed'] ?? 0)); ?></h3>
                <p class="text-sm text-gray-600">Selesai</p>
            </div>
        </div>
    </div>

    <!-- Tabel Pengiriman -->
    <div class="bg-white rounded-2xl shadow-lg border border-gray-100 overflow-hidden mb-8">
        <div class="p-5 border-b border-gray-100 bg-gray-50/50">
            <h3 class="font-semibold text-gray-900 flex items-center">
                <i class="fas fa-list mr-2 text-blue-600"></i>
                Daftar Tugas Pengiriman
            </h3>
        </div>
        
        <div class="overflow-x-auto">
            <table class="w-full">
                <thead class="bg-gray-50 border-b border-gray-200">
                    <tr>
                        <th class="px-6 py-4 text-left text-xs font-semibold text-gray-600 uppercase">Kode</th>
                        <th class="px-6 py-4 text-left text-xs font-semibold text-gray-600 uppercase">Tujuan</th>
                        <th class="px-6 py-4 text-left text-xs font-semibold text-gray-600 uppercase">Status</th>
                        <th class="px-6 py-4 text-left text-xs font-semibold text-gray-600 uppercase">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    <?php $__empty_1 = true; $__currentLoopData = $deliveries; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $delivery): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                        <tr class="hover:bg-blue-50/30 transition-colors">
                            <td class="px-6 py-4 font-medium text-gray-900"><?php echo e($delivery->delivery_code); ?></td>
                            <td class="px-6 py-4 text-gray-700"><?php echo e(Str::limit($delivery->destination, 40)); ?></td>
                            <td class="px-6 py-4">
                                <?php
                                    $statusColors = [
                                        'assigned' => 'bg-purple-100 text-purple-800',
                                        'picked_up' => 'bg-indigo-100 text-indigo-800',
                                        'on_delivery' => 'bg-orange-100 text-orange-800',
                                        'delivered' => 'bg-green-100 text-green-800',
                                        'failed' => 'bg-red-100 text-red-800',
                                        'cancelled' => 'bg-gray-100 text-gray-800',
                                    ];
                                    $color = $statusColors[$delivery->status] ?? 'bg-gray-100 text-gray-800';
                                ?>
                                <span class="px-3 py-1 text-xs font-medium rounded-full <?php echo e($color); ?>">
                                    <?php echo e(ucwords(str_replace('_', ' ', $delivery->status))); ?>

                                </span>
                            </td>
                            <td class="px-6 py-4 space-x-2">
                                <a href="<?php echo e(route('delivery.show', $delivery->id)); ?>" class="inline-flex items-center px-3 py-1.5 bg-blue-100 text-blue-700 rounded hover:bg-blue-200 text-sm font-medium">
                                    <i class="fas fa-eye mr-1"></i> Detail
                                </a>
                                
                                <?php if($delivery->status === 'assigned'): ?>
                                    <form action="<?php echo e(route('delivery.accept', $delivery->id)); ?>" method="POST" class="inline-block">
                                        <?php echo csrf_field(); ?>
                                        <button type="submit" class="inline-flex items-center px-3 py-1.5 bg-indigo-100 text-indigo-700 rounded hover:bg-indigo-200 text-sm font-medium">
                                            <i class="fas fa-truck-loading mr-1"></i> Mulai Pengiriman
                                        </button>
                                    </form>
                                <?php endif; ?>
                                
                                <?php if(in_array($delivery->status, ['picked_up', 'on_delivery'])): ?>
                                    <form action="<?php echo e(route('delivery.update-status', $delivery->id)); ?>" method="POST" class="inline-flex flex-col gap-2">
                                        <?php echo csrf_field(); ?>
                                        <div class="flex items-center gap-2">
                                            <select name="status" class="text-sm p-1.5 border border-gray-300 rounded outline-none ring-blue-500 focus:ring-2" required>
                                                <?php if($delivery->status === 'picked_up'): ?>
                                                    <option value="on_delivery">Dalam Perjalanan</option>
                                                <?php endif; ?>
                                                <option value="delivered">Berhasil (Delivered)</option>
                                                <option value="failed">Gagal (Failed)</option>
                                            </select>
                                            <button type="submit" class="px-3 py-1.5 bg-green-600 text-white rounded hover:bg-green-700 text-sm font-medium">
                                                Update
                                            </button>
                                        </div>
                                    </form>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                        <tr>
                            <td colspan="4" class="px-6 py-8 text-center text-gray-500">
                                <div class="flex flex-col items-center justify-center">
                                    <i class="fas fa-clipboard-check text-4xl text-gray-300 mb-3"></i>
                                    <p>Tidak ada tugas pengiriman saat ini.</p>
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
</div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH D:\PROJECT3\Toko-Roni-Mobile-App\tokoroni-app\resources\views/delivery/my-deliveries.blade.php ENDPATH**/ ?>