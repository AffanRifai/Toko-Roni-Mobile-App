
<?php $attributes ??= new \Illuminate\View\ComponentAttributeBag;

$__newAttributes = [];
$__propNames = \Illuminate\View\ComponentAttributeBag::extractPropNames((['align' => 'right']));

foreach ($attributes->all() as $__key => $__value) {
    if (in_array($__key, $__propNames)) {
        $$__key = $$__key ?? $__value;
    } else {
        $__newAttributes[$__key] = $__value;
    }
}

$attributes = new \Illuminate\View\ComponentAttributeBag($__newAttributes);

unset($__propNames);
unset($__newAttributes);

foreach (array_filter((['align' => 'right']), 'is_string', ARRAY_FILTER_USE_KEY) as $__key => $__value) {
    $$__key = $$__key ?? $__value;
}

$__defined_vars = get_defined_vars();

foreach ($attributes->all() as $__key => $__value) {
    if (array_key_exists($__key, $__defined_vars)) unset($$__key);
}

unset($__defined_vars, $__key, $__value); ?>

<?php
$alignmentClasses = match ($align) {
    'left' => 'origin-top-left left-0',
    'top' => 'origin-top',
    default => 'origin-top-right right-0',
};

$user = Auth::user();

// DEBUG: Cek apakah user login dan punya notifikasi
$notifications = $user ? $user->notifications()->latest()->take(5)->get() : collect();
$unreadCount = $user ? $user->unreadNotifications->count() : 0;

// Untuk debugging - hapus setelah berhasil
// \Log::info('Notifikasi:', ['count' => $notifications->count(), 'unread' => $unreadCount]);
?>

<?php if($user): ?>
<div class="relative" x-data="{ open: false }">
    <!-- Tombol Notifikasi -->
    <button @click="open = !open" 
            class="relative p-1.5 rounded-lg hover:bg-gray-100 dark:hover:bg-gray-700 transition-colors group focus:outline-none">
        <i class="fas fa-bell text-gray-600 dark:text-gray-300 group-hover:text-gray-900 dark:group-hover:text-white text-sm"></i>
        
        <?php if($unreadCount > 0): ?>
            <span class="absolute -top-1 -right-1 flex h-4 w-4">
                <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-red-400 opacity-75"></span>
                <span class="relative inline-flex rounded-full h-4 w-4 bg-red-500 text-white text-[10px] items-center justify-center font-bold">
                    <?php echo e($unreadCount > 9 ? '9+' : $unreadCount); ?>

                </span>
            </span>
        <?php else: ?>
            <span class="absolute top-0.5 right-0.5 w-2 h-2 bg-red-500 rounded-full"></span>
        <?php endif; ?>
    </button>

    <!-- Dropdown Menu -->
    <div x-show="open"
         @click.away="open = false"
         x-transition:enter="transition ease-out duration-200"
         x-transition:enter-start="transform opacity-0 scale-95"
         x-transition:enter-end="transform opacity-100 scale-100"
         x-transition:leave="transition ease-in duration-75"
         x-transition:leave-start="transform opacity-100 scale-100"
         x-transition:leave-end="transform opacity-0 scale-95"
         class="absolute z-50 <?php echo e($alignmentClasses); ?> mt-2 w-80 rounded-lg shadow-lg bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700"
         style="display: none;">
        
        <!-- Header -->
        <div class="p-3 border-b border-gray-200 dark:border-gray-700">
            <div class="flex items-center justify-between">
                <h3 class="font-semibold text-gray-900 dark:text-white text-sm">
                    Notifikasi
                    <?php if($unreadCount > 0): ?>
                        <span class="ml-2 text-xs bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-200 px-2 py-0.5 rounded-full">
                            <?php echo e($unreadCount); ?> baru
                        </span>
                    <?php endif; ?>
                </h3>
                <?php if($unreadCount > 0): ?>
                    <form action="<?php echo e(route('notifications.mark-all-as-read')); ?>" method="POST">
                        <?php echo csrf_field(); ?>
                        <button type="submit" class="text-xs text-blue-600 hover:text-blue-800">
                            Tandai semua dibaca
                        </button>
                    </form>
                <?php endif; ?>
            </div>
        </div>

        <!-- List Notifikasi -->
        <div class="max-h-96 overflow-y-auto">
            <?php $__empty_1 = true; $__currentLoopData = $notifications; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $notification): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                <?php
                    $data = $notification->data;
                    $isUnread = is_null($notification->read_at);
                    $message = $data['message'] ?? 'Notifikasi baru';
                    $time = $notification->created_at->diffForHumans();
                ?>
                
                <div class="p-3 border-b border-gray-100 dark:border-gray-700 hover:bg-gray-50 dark:hover:bg-gray-700 <?php echo e($isUnread ? 'bg-blue-50 dark:bg-blue-900/20' : ''); ?>">
                    <div class="flex items-start gap-3">
                        <!-- Icon -->
                        <div class="flex-shrink-0 mt-0.5">
                           <?php
                            $iconClass = match($data['type'] ?? 'default') {
                                // User notifications
                                'user_created' => 'fas fa-user-plus text-green-500',
                                'user_updated' => 'fas fa-user-edit text-blue-500',
                                
                                // Member notifications
                                'member_created' => 'fas fa-user-plus text-green-500',
                                'member_updated' => 'fas fa-user-pen text-blue-500',
                                
                                // Category notifications
                                'category_created' => 'fas fa-tag text-green-500',
                                'category_updated' => 'fas fa-tag text-blue-500',
                                'category_deleted' => 'fas fa-trash text-red-500',
                                
                                // Product notifications
                                'product_created' => 'fas fa-box text-green-500',
                                'product_updated' => 'fas fa-box text-blue-500',
                                'product_deleted' => 'fas fa-trash text-red-500',
                                'product_stock_low_stock' => 'fas fa-exclamation-triangle text-yellow-500',
                                'product_stock_out_of_stock' => 'fas fa-times-circle text-red-500',
                                'product_stock_restock' => 'fas fa-arrow-up text-green-500',
                                
                                // Delivery notifications
                                'delivery_created' => 'fas fa-truck text-blue-500',
                                'delivery_updated' => 'fas fa-truck text-yellow-500',
                                'delivery_assigned' => 'fas fa-user-check text-purple-500',
                                'delivery_status_changed' => 'fas fa-rotate text-orange-500',
                                'delivery_deleted' => 'fas fa-trash text-red-500',
                                
                                // Vehicle notifications
                                'vehicle_created' => 'fas fa-truck text-green-500',
                                'vehicle_updated' => 'fas fa-truck text-yellow-500',
                                'vehicle_deleted' => 'fas fa-trash text-red-500',
                                'vehicle_status_changed' => 'fas fa-rotate text-blue-500',
                                
                                // Transaction notifications
                                'transaction_created' => 'fas fa-cash-register text-green-500',
                                'transaction_updated' => 'fas fa-cash-register text-yellow-500',
                                'transaction_deleted' => 'fas fa-trash text-red-500',
                                
                                // Receivable notifications
                                'receivable_created' => 'fas fa-hand-holding-dollar text-purple-500',
                                'payment_received' => 'fas fa-money-bill-transfer text-green-500',
                                
                                default => 'fas fa-bell text-gray-400'
                            };
                        ?>
                            <i class="<?php echo e($iconClass); ?> text-sm"></i>
                        </div>

                        <!-- Content -->
                        <div class="flex-1">
                            <p class="text-sm text-gray-900 dark:text-white">
                                <?php echo e($message); ?>

                            </p>
                            <?php if(isset($data['user_name'])): ?>
                                <p class="text-xs text-gray-600 dark:text-gray-400 mt-0.5">
                                    User: <?php echo e($data['user_name']); ?> (<?php echo e($data['user_role'] ?? 'Unknown'); ?>)
                                </p>
                            <?php endif; ?>
                            <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">
                                <?php echo e($time); ?>

                            </p>
                        </div>

                        <?php if($isUnread): ?>
                            <form action="<?php echo e(route('notifications.mark-as-read', $notification->id)); ?>" method="POST">
                                <?php echo csrf_field(); ?>
                                <button type="submit" class="text-blue-600 hover:text-blue-800" title="Tandai dibaca">
                                    <i class="fas fa-check-circle"></i>
                                </button>
                            </form>
                        <?php endif; ?>
                    </div>
                </div>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                <div class="p-8 text-center">
                    <div class="mb-3">
                        <i class="fas fa-bell-slash text-4xl text-gray-300 dark:text-gray-600"></i>
                    </div>
                    <p class="text-sm text-gray-500 dark:text-gray-400">
                        Belum ada notifikasi
                    </p>
                    <!-- DEBUG: Tampilkan info untuk debugging -->
                    <p class="text-xs text-gray-400 mt-2">
                        User ID: <?php echo e(auth()->id()); ?> | 
                        Notif DB: <?php echo e(\App\Models\User::find(auth()->id())->notifications()->count()); ?>

                    </p>
                </div>
            <?php endif; ?>
        </div>

        <?php if($notifications->count() > 0): ?>
            <div class="p-2 border-t border-gray-200 dark:border-gray-700">
                <a href="<?php echo e(route('notifications.index')); ?>" 
                   class="block text-center text-sm text-blue-600 hover:text-blue-800 py-1">
                    Lihat semua notifikasi (<?php echo e($notifications->count()); ?>)
                </a>
            </div>
        <?php endif; ?>
    </div>
</div>
<?php endif; ?><?php /**PATH D:\PROJECT3\Toko-Roni-Mobile-App\tokoroni-app\resources\views/components/notification-dropdown.blade.php ENDPATH**/ ?>