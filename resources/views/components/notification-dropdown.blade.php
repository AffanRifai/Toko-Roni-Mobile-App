{{-- resources/views/components/notification-dropdown.blade.php --}}
@props(['align' => 'right'])

@php
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
@endphp

@if($user)
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
        fetch('{{ route('notifications.recent') }}')
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
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
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
        fetch('{{ route('notifications.mark-all-as-read') }}', {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
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
         class="absolute z-50 {{ $alignmentClasses }} mt-2 w-80 rounded-xl shadow-xl bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 overflow-hidden"
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
            <a href="{{ route('notifications.index') }}" 
               class="text-xs font-bold text-gray-600 hover:text-indigo-600 dark:text-gray-400 dark:hover:text-indigo-400 transition-colors uppercase tracking-wider">
                Lihat Semua Notifikasi
            </a>
        </div>
    </div>
</div>
@endif