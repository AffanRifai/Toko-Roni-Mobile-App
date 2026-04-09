

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
<div class="relative" x-data="{ 
    open: false, 
    notifications: [], 
    unreadCount: 0,
    loading: false,
    
    init() {
        this.fetchNotifications();
        // Polling setiap 30 detik
        setInterval(() => this.fetchNotifications(), 30000);
    },
    
    fetchNotifications() {
        fetch('<?php echo e(route('notifications.recent')); ?>')
            .then(res => res.json())
            .then(data => {
                if (data.success) {
                    this.notifications = data.data;
                    this.unreadCount = data.unread_count;
                }
            });
    },
    
    markAsRead(id) {
        fetch(`/notifications/${id}/mark-as-read`, {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': '<?php echo e(csrf_token()); ?>',
                'Accept': 'application/json'
            }
        })
        .then(res => res.json())
        .then(data => {
            if (data.success) {
                this.notifications = this.notifications.map(n => 
                    n.id === id ? { ...n, is_unread: false } : n
                );
                this.unreadCount = data.unread_count;
            }
        });
    },
    
    markAllRead() {
        fetch('<?php echo e(route('notifications.mark-all-as-read')); ?>', {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': '<?php echo e(csrf_token()); ?>',
                'Accept': 'application/json'
            }
        })
        .then(res => res.json())
        .then(data => {
            if (data.success) {
                this.notifications = this.notifications.map(n => ({ ...n, is_unread: false }));
                this.unreadCount = 0;
            }
        });
    }
}">
    <!-- Tombol Notifikasi -->
    <button @click="open = !open" 
            class="relative p-1.5 rounded-lg hover:bg-gray-100 dark:hover:bg-gray-700 transition-colors group focus:outline-none">
        <i class="fas fa-bell text-gray-600 dark:text-gray-300 group-hover:text-gray-900 dark:group-hover:text-white text-sm"></i>
        
        <template x-if="unreadCount > 0">
            <span class="absolute -top-1 -right-1 flex h-4 w-4">
                <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-red-400 opacity-75"></span>
                <span class="relative inline-flex rounded-full h-4 w-4 bg-red-500 text-white text-[10px] items-center justify-center font-bold" x-text="unreadCount > 9 ? '9+' : unreadCount"></span>
            </span>
        </template>
        <template x-if="unreadCount === 0">
            <span class="absolute top-0.5 right-0.5 w-1.5 h-1.5 bg-gray-300 dark:bg-gray-600 rounded-full"></span>
        </template>
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
         class="absolute z-50 <?php echo e($alignmentClasses); ?> mt-2 w-80 rounded-xl shadow-xl bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 overflow-hidden"
         style="display: none;">
        
        <!-- Header -->
        <div class="p-4 border-b border-gray-100 dark:border-gray-700 bg-gray-50/50 dark:bg-gray-900/50">
            <div class="flex items-center justify-between">
                <h3 class="font-bold text-gray-900 dark:text-white text-sm flex items-center">
                    Notifikasi
                    <span x-show="unreadCount > 0" 
                          class="ml-2 px-2 py-0.5 text-[10px] font-bold bg-indigo-100 text-indigo-700 dark:bg-indigo-900/40 dark:text-indigo-300 rounded-full"
                          x-text="unreadCount + ' baru'"></span>
                </h3>
                <button x-show="unreadCount > 0" 
                        @click="markAllRead()"
                        class="text-[11px] font-semibold text-indigo-600 hover:text-indigo-800 dark:text-indigo-400 dark:hover:text-indigo-300 transition-colors">
                    Tandai semua dibaca
                </button>
            </div>
        </div>

        <!-- List Notifikasi -->
<<<<<<< HEAD:storage/framework/views/708cdc29a877f89076f16bec318cde67.php
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
                                    'create' => 'fas fa-user-plus text-green-500',
                                    'update' => 'fas fa-user-edit text-blue-500',
                                    
                                    // Member notifications - lebih spesifik
                                    'member_created' => 'fas fa-user-check text-purple-500',
                                    'member_updated' => 'fas fa-user-cog text-blue-500',
                                    
                                    // Category notifications
                                    'category_created' => 'fas fa-tag text-green-500',
                                    'category_updated' => 'fas fa-tags text-blue-500',
                                    'category_deleted' => 'fas fa-trash-alt text-red-500',
                                    
                                    // Product notifications
                                    'product_created' => 'fas fa-box-open text-green-500',
                                    'product_updated' => 'fas fa-boxes text-blue-500',
                                    'product_deleted' => 'fas fa-trash-alt text-red-500',
                                    'product_stock_low_stock' => 'fas fa-exclamation-triangle text-yellow-500',
                                    'product_stock_out_of_stock' => 'fas fa-ban text-red-500',
                                    'product_stock_restock' => 'fas fa-arrow-up text-green-500',
                                    
                                    // Delivery notifications
                                    'delivery_created' => 'fas fa-truck-moving text-blue-500',
                                    'delivery_updated' => 'fas fa-truck-ramp-box text-yellow-500',
                                    'delivery_assigned' => 'fas fa-user-check text-purple-500',
                                    'delivery_status_changed' => 'fas fa-exchange-alt text-orange-500',
                                    'delivery_deleted' => 'fas fa-trash-alt text-red-500',
                                    
                                    // Vehicle notifications
                                    'vehicle_created' => 'fas fa-truck-pickup text-green-500',
                                    'vehicle_updated' => 'fas fa-truck-moving text-yellow-500',
                                    'vehicle_deleted' => 'fas fa-trash-alt text-red-500',
                                    'vehicle_status_changed' => 'fas fa-sync-alt text-blue-500',
                                    
                                    // Transaction notifications
                                    'transaction_created' => 'fas fa-shopping-cart text-green-500',
                                    'transaction_updated' => 'fas fa-edit text-yellow-500',
                                    'transaction_deleted' => 'fas fa-trash-alt text-red-500',
                                    
                                    // Receivable notifications
                                    'receivable_created' => 'fas fa-file-invoice-dollar text-purple-500',
                                    'payment_received' => 'fas fa-coins text-green-500',
                                    
                                    // Checker Report notifications
                                    'product_reported' => 'fas fa-flag-checkered text-orange-500',
                                    'report_resolved' => 'fas fa-check-double text-green-500',
                                    
                                    // Product notifications
                                    'product_restock' => 'fas fa-cubes text-green-500',
                                    'product_restock_complete' => 'fas fa-check-circle text-green-500',
                                    
                                    default => 'fas fa-bell text-gray-400'
                                };
                            ?>
                            <i class="<?php echo e($iconClass); ?> text-sm"></i>
                        </div>
                        
                        <!-- Content -->
                        <div class="flex-1 min-w-0">
                            <p class="text-sm text-gray-900 dark:text-white break-words">
                                <?php echo e($data['message'] ?? 'Notifikasi baru'); ?>

                            </p>
                            
                            <?php if(isset($data['report_type_label'])): ?>
                                <p class="text-xs text-gray-600 dark:text-gray-400 mt-0.5">
                                    <span class="font-medium">Jenis:</span> <?php echo e($data['report_type_label']); ?>

                                </p>
                            <?php endif; ?>
                            
                            <?php if(isset($data['product_name'])): ?>
                                <p class="text-xs text-gray-600 dark:text-gray-400 mt-0.5">
                                    <span class="font-medium">Produk:</span> <?php echo e($data['product_name']); ?>

                                </p>
                            <?php endif; ?>
                            
                            <?php if(isset($data['notes'])): ?>
                                <p class="text-xs text-gray-500 dark:text-gray-400 mt-1 italic">
                                                    "<?php echo e(Str::limit($data['notes'], 50)); ?>"
                                </p>
                            <?php endif; ?>
                            
                            <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">
                                <?php echo e($time); ?>

                            </p>
                        </div>

                        <!-- Unread Indicator -->
                        <?php if($isUnread): ?>
                            <div class="flex-shrink-0">
                                <span class="inline-block w-2 h-2 bg-blue-500 rounded-full"></span>
                            </div>
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
=======
        <div class="max-h-[400px] overflow-y-auto">
            <template x-if="notifications.length > 0">
                <div class="divide-y divide-gray-100 dark:divide-gray-700">
                    <template x-for="notif in notifications" :key="notif.id">
                        <div class="p-4 hover:bg-gray-50 dark:hover:bg-gray-700/50 transition-colors relative group"
                             :class="notif.is_unread ? 'bg-indigo-50/30 dark:bg-indigo-900/10' : ''">
                            
                            <div class="flex items-start gap-3">
                                <!-- Icon dengan Color -->
                                <div class="flex-shrink-0 w-8 h-8 rounded-full flex items-center justify-center"
                                     :class="`bg-${notif.color}-100 dark:bg-${notif.color}-900/30`">
                                    <i :class="`${notif.icon} text-${notif.color}-600 dark:text-${notif.color}-400 text-xs`"></i>
                                </div>

                                <!-- Content -->
                                <div class="flex-1 min-w-0">
                                    <a :href="notif.url" class="block">
                                        <p class="text-sm text-gray-800 dark:text-gray-200 leading-snug mb-1"
                                           :class="notif.is_unread ? 'font-semibold' : ''"
                                           x-text="notif.message"></p>
                                    </a>
                                    <p class="text-[11px] text-gray-500 dark:text-gray-400 flex items-center">
                                        <i class="far fa-clock mr-1 opacity-70"></i>
                                        <span x-text="notif.time"></span>
                                    </p>
                                </div>

                                <!-- Action Mark as Read (Single) -->
                                <button x-show="notif.is_unread"
                                        @click="markAsRead(notif.id)"
                                        class="opacity-0 group-hover:opacity-100 transition-opacity p-1 text-indigo-500 hover:text-indigo-700"
                                        title="Tandai dibaca">
                                    <i class="fas fa-check-circle text-xs"></i>
                                </button>
                            </div>
                            
                            <!-- Unread Bullet -->
                            <div x-show="notif.is_unread" class="absolute top-4 right-4 w-2 h-2 bg-indigo-500 rounded-full"></div>
                        </div>
                    </template>
                </div>
            </template>
>>>>>>> 1ec65ba3b8f30d623dcd67db18b4a0a05a968987:storage/framework/views/57de9ce022d17d199d53e825f0d900f4.php

            <template x-if="notifications.length === 0">
                <div class="p-10 text-center">
                    <div class="w-16 h-16 bg-gray-100 dark:bg-gray-700 rounded-full flex items-center justify-center mx-auto mb-4">
                        <i class="fas fa-bell-slash text-2xl text-gray-400"></i>
                    </div>
                    <p class="text-sm font-medium text-gray-500 dark:text-gray-400">Belum ada notifikasi</p>
                </div>
            </template>
        </div>

        <!-- Footer -->
        <div class="p-3 border-t border-gray-100 dark:border-gray-700 text-center bg-gray-50/30 dark:bg-gray-900/30">
            <a href="<?php echo e(route('notifications.index')); ?>" 
               class="text-xs font-bold text-gray-600 hover:text-indigo-600 dark:text-gray-400 dark:hover:text-indigo-400 transition-colors uppercase tracking-wider">
                Lihat Semua Notifikasi
            </a>
        </div>
    </div>
</div>
<?php endif; ?><?php /**PATH C:\laragon\www\Toko-Roni-Mobile-App\resources\views/components/notification-dropdown.blade.php ENDPATH**/ ?>