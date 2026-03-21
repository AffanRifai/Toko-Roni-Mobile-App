<?php $__env->startSection('title', 'Semua Notifikasi'); ?>

<?php $__env->startSection('content'); ?>
<div class="container mx-auto px-4 py-6">
    <!-- Header -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 mb-6">
        <div>
            <h1 class="text-2xl font-bold text-gray-800 dark:text-white">Semua Notifikasi</h1>
            <p class="text-sm text-gray-600 dark:text-gray-400 mt-1">
                Riwayat semua notifikasi Anda
            </p>
        </div>
        
        <div class="flex items-center gap-2">
            <form action="<?php echo e(route('notifications.mark-all-as-read')); ?>" method="POST">
                <?php echo csrf_field(); ?>
                <button type="submit" 
                        class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors text-sm font-medium flex items-center gap-2">
                    <i class="fas fa-check-double"></i>
                    <span>Tandai Semua Dibaca</span>
                </button>
            </form>
        </div>
    </div>

    <!-- Stats Cards -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-6">
        <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-4 border border-gray-200 dark:border-gray-700">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm text-gray-500 dark:text-gray-400">Total Notifikasi</p>
                    <p class="text-2xl font-bold text-gray-800 dark:text-white"><?php echo e($notifications->total()); ?></p>
                </div>
                <div class="w-10 h-10 rounded-full bg-blue-100 dark:bg-blue-900/30 flex items-center justify-center">
                    <i class="fas fa-bell text-blue-600 dark:text-blue-400"></i>
                </div>
            </div>
        </div>

        <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-4 border border-gray-200 dark:border-gray-700">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm text-gray-500 dark:text-gray-400">Belum Dibaca</p>
                    <p class="text-2xl font-bold text-gray-800 dark:text-white"><?php echo e(auth()->user()->unreadNotifications->count()); ?></p>
                </div>
                <div class="w-10 h-10 rounded-full bg-yellow-100 dark:bg-yellow-900/30 flex items-center justify-center">
                    <i class="fas fa-circle text-yellow-500"></i>
                </div>
            </div>
        </div>

        <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-4 border border-gray-200 dark:border-gray-700">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm text-gray-500 dark:text-gray-400">Sudah Dibaca</p>
                    <p class="text-2xl font-bold text-gray-800 dark:text-white"><?php echo e($notifications->total() - auth()->user()->unreadNotifications->count()); ?></p>
                </div>
                <div class="w-10 h-10 rounded-full bg-green-100 dark:bg-green-900/30 flex items-center justify-center">
                    <i class="fas fa-check-circle text-green-600 dark:text-green-400"></i>
                </div>
            </div>
        </div>
    </div>

    <!-- Notifications List -->
    <div class="bg-white dark:bg-gray-800 rounded-lg shadow border border-gray-200 dark:border-gray-700 overflow-hidden">
        <?php $__empty_1 = true; $__currentLoopData = $notifications; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $notification): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
            <?php
                $data = $notification->data;
                $isUnread = is_null($notification->read_at);
                $message = $data['message'] ?? 'Notifikasi baru';
                $time = $notification->created_at->diffForHumans();
                $type = $data['type'] ?? 'default';
                
                $iconClass = match($type) {
                    'user_created' => 'fas fa-user-plus text-green-500',
                    'user_updated' => 'fas fa-user-edit text-blue-500',
                    default => 'fas fa-bell text-gray-400'
                };
                
                $bgClass = $isUnread ? 'bg-blue-50 dark:bg-blue-900/20' : '';
            ?>
            
            <div class="p-4 border-b border-gray-100 dark:border-gray-700 <?php echo e($bgClass); ?> hover:bg-gray-50 dark:hover:bg-gray-700/50 transition-colors">
                <div class="flex items-start gap-4">
                    <!-- Icon -->
                    <div class="flex-shrink-0 mt-1">
                        <i class="<?php echo e($iconClass); ?> text-lg"></i>
                    </div>

                    <!-- Content -->
                    <div class="flex-1">
                        <p class="text-gray-900 dark:text-white font-medium">
                            <?php echo e($message); ?>

                        </p>
                        
                        <?php if(isset($data['user_name'])): ?>
                            <p class="text-sm text-gray-600 dark:text-gray-400 mt-1">
                                User: <?php echo e($data['user_name']); ?> 
                                <?php if(isset($data['user_role'])): ?>
                                    (<?php echo e(str_replace('_', ' ', $data['user_role'])); ?>)
                                <?php endif; ?>
                            </p>
                        <?php endif; ?>

                        <?php if(isset($data['updated_by'])): ?>
                            <p class="text-sm text-gray-600 dark:text-gray-400">
                                Diperbarui oleh: <?php echo e($data['updated_by']); ?>

                            </p>
                        <?php endif; ?>

                        <?php if(isset($data['created_by'])): ?>
                            <p class="text-sm text-gray-600 dark:text-gray-400">
                                Dibuat oleh: <?php echo e($data['created_by']); ?>

                            </p>
                        <?php endif; ?>

                        <?php if(isset($data['changed_fields']) && !empty($data['changed_fields'])): ?>
                            <div class="mt-2 flex flex-wrap gap-2">
                                <?php $__currentLoopData = $data['changed_fields']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $field): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                    <span class="inline-flex items-center px-2 py-1 rounded bg-gray-100 dark:bg-gray-700 text-xs text-gray-700 dark:text-gray-300">
                                        <i class="fas fa-edit mr-1 text-blue-500"></i>
                                        <?php echo e($field); ?>

                                    </span>
                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                            </div>
                        <?php endif; ?>

                        <div class="flex items-center gap-4 mt-2">
                            <span class="text-xs text-gray-500 dark:text-gray-400">
                                <i class="far fa-clock mr-1"></i>
                                <?php echo e($notification->created_at->format('d M Y H:i')); ?>

                            </span>
                            <span class="text-xs text-gray-500 dark:text-gray-400">
                                <?php echo e($time); ?>

                            </span>
                            <?php if($notification->read_at): ?>
                                <span class="text-xs text-green-600 dark:text-green-400">
                                    <i class="fas fa-check-circle mr-1"></i>
                                    Dibaca <?php echo e($notification->read_at->diffForHumans()); ?>

                                </span>
                            <?php endif; ?>
                        </div>
                    </div>

                    <!-- Actions -->
                    <div class="flex items-center gap-2">
                        <?php if($isUnread): ?>
                            <form action="<?php echo e(route('notifications.mark-as-read', $notification->id)); ?>" method="POST">
                                <?php echo csrf_field(); ?>
                                <button type="submit" 
                                        class="p-2 text-blue-600 hover:text-blue-800 dark:text-blue-400 hover:bg-blue-50 dark:hover:bg-blue-900/30 rounded-lg transition-colors"
                                        title="Tandai dibaca">
                                    <i class="fas fa-check-circle"></i>
                                </button>
                            </form>
                        <?php endif; ?>
                        
                        <form action="<?php echo e(route('notifications.destroy', $notification->id)); ?>" method="POST">
                            <?php echo csrf_field(); ?>
                            <?php echo method_field('DELETE'); ?>
                            <button type="submit" 
                                    class="p-2 text-red-600 hover:text-red-800 dark:text-red-400 hover:bg-red-50 dark:hover:bg-red-900/30 rounded-lg transition-colors"
                                    title="Hapus"
                                    onclick="return confirm('Hapus notifikasi ini?')">
                                <i class="fas fa-trash"></i>
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
            <div class="p-12 text-center">
                <div class="mb-4">
                    <i class="fas fa-bell-slash text-5xl text-gray-300 dark:text-gray-600"></i>
                </div>
                <h3 class="text-lg font-medium text-gray-900 dark:text-white mb-2">Belum ada notifikasi</h3>
                <p class="text-sm text-gray-500 dark:text-gray-400">
                    Notifikasi akan muncul di sini ketika ada aktivitas yang memerlukan perhatian Anda
                </p>
            </div>
        <?php endif; ?>

        <!-- Pagination -->
        <?php if($notifications->hasPages()): ?>
            <div class="p-4 border-t border-gray-100 dark:border-gray-700">
                <?php echo e($notifications->links()); ?>

            </div>
        <?php endif; ?>
    </div>

    <!-- Back Button -->
    <div class="mt-6">
        <a href="<?php echo e(url()->previous()); ?>" class="text-blue-600 hover:text-blue-800 dark:text-blue-400">
            <i class="fas fa-arrow-left mr-1"></i> Kembali
        </a>
    </div>
</div>
<?php $__env->stopSection(); ?>
<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH D:\laravel project 3\Toko-Roni-Mobile-App\resources\views/notifications/index.blade.php ENDPATH**/ ?>