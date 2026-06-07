{{-- resources/views/notifications/index.blade.php --}}
@extends('layouts.app')

@section('title', 'Semua Notifikasi')

@section('content')
<div class="container mx-auto px-4 py-6" x-data="{
    notifications: @js($notifications->items()),
    unreadCount: {{ $unreadCount }},
    
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
                location.reload();
            }
        });
    },
    
    deleteNotif(id) {
        if (!confirm('Hapus notifikasi ini?')) return;
        
        fetch(`/notifications/${id}`, {
            method: 'DELETE',
            headers: {
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                'Accept': 'application/json'
            }
        })
        .then(res => res.json())
        .then(data => {
            if (data.success) {
                location.reload();
            }
        });
    }
}">
    <!-- Header -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 mb-6">
        <div>
            <h1 class="text-2xl font-bold text-gray-800 dark:text-white">Semua Notifikasi</h1>
            <p class="text-sm text-gray-600 dark:text-gray-400 mt-1">
                Riwayat semua notifikasi Anda
            </p>
        </div>
        
        <div class="flex items-center gap-2">
            @if($unreadCount > 0)
                <form action="{{ route('notifications.mark-all-as-read') }}" method="POST">
                    @csrf
                    <button type="submit" 
                            class="px-4 py-2 bg-indigo-50 text-indigo-700 hover:bg-indigo-100 dark:bg-indigo-900/30 dark:text-indigo-400 rounded-xl text-sm font-semibold transition-all inline-flex items-center gap-2">
                        <i class="fas fa-check-double"></i>
                        <span>Tandai Semua Dibaca</span>
                    </button>
                </form>
            @endif
            
            <form action="{{ route('notifications.clear-all') }}" method="POST" onsubmit="return confirm('Hapus semua notifikasi?')">
                @csrf
                @method('DELETE')
                <button type="submit" 
                        class="px-4 py-2 bg-red-50 text-red-700 hover:bg-red-100 dark:bg-red-900/30 dark:text-red-400 rounded-xl text-sm font-semibold transition-all inline-flex items-center gap-2">
                    <i class="fas fa-trash-alt"></i>
                    <span>Bersihkan Semua</span>
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
                    <p class="text-2xl font-bold text-gray-800 dark:text-white">{{ $notifications->total() }}</p>
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
                    <p class="text-2xl font-bold text-gray-800 dark:text-white">{{ auth()->user()->unreadNotifications->count() }}</p>
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
                    <p class="text-2xl font-bold text-gray-800 dark:text-white">{{ $notifications->total() - auth()->user()->unreadNotifications->count() }}</p>
                </div>
                <div class="w-10 h-10 rounded-full bg-green-100 dark:bg-green-900/30 flex items-center justify-center">
                    <i class="fas fa-check-circle text-green-600 dark:text-green-400"></i>
                </div>
            </div>
        </div>
    </div>

    <!-- Notifications List -->
    <div class="bg-white dark:bg-gray-800 rounded-lg shadow border border-gray-200 dark:border-gray-700 overflow-hidden">
        @if($notifications->count() > 0)
            <!-- Grouped Notifications List -->
            <div class="space-y-8 p-4">
                @foreach($groupedNotifications as $date => $notifs)
                    <div>
                        <h3 class="text-sm font-bold text-gray-400 uppercase tracking-widest flex items-center mb-4">
                            <span class="mr-3">{{ $date }}</span>
                            <div class="flex-1 h-px bg-gray-200 dark:bg-gray-700"></div>
                        </h3>
                        
                        <div class="divide-y divide-gray-100 dark:divide-gray-700">
                           @foreach($notifs as $notification)
                            @php
                                $data = $notification->data;
                                $isUnread = is_null($notification->read_at);
                                $color = $data['color'] ?? 'blue';
                                $type = $data['type'] ?? 'default';
                                $message = $data['message'] ?? 'Pemberitahuan sistem';
                                $url = $data['url'] ?? '#';
                                
                                // Gunakan ini - definisi icon class yang sudah lengkap
                                $iconClass = match($type) {
                                    // User notifications
                                    'create' => 'fas fa-user-plus text-green-500',
                                    'update' => 'fas fa-user-edit text-blue-500',
                                    
                                    // Member notifications
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
                                    'product_restock' => 'fas fa-cubes text-green-500',
                                    'product_restock_complete' => 'fas fa-check-circle text-green-500', // TAMBAHKAN INI
                                    
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
                                    
                                    default => 'fas fa-bell text-gray-400'
                                };
                                
                                // Extract icon class components untuk background color
                                preg_match('/text-(\w+)/', $iconClass, $matches);
                                $iconColor = $matches[1] ?? 'blue';
                                
                                $bgClass = $isUnread ? 'bg-blue-50 dark:bg-blue-900/20' : '';
                            @endphp
                            
                            <div class="p-5 hover:bg-gray-50 dark:hover:bg-gray-700/30 transition-all group relative {{ $bgClass }}">
                                <div class="flex items-start gap-4">
                                    <!-- Icon - Gunakan $iconClass, bukan $icon -->
                                    <div class="flex-shrink-0 w-12 h-12 rounded-2xl flex items-center justify-center shadow-sm bg-{{ $iconColor }}-100 dark:bg-{{ $iconColor }}-900/30">
                                        <i class="{{ $iconClass }} text-lg"></i>
                                    </div>

                                    <!-- Content -->
                                    <div class="flex-1 min-w-0">
                                        <div class="flex items-center justify-between mb-1">
                                            <span class="text-[10px] font-bold uppercase tracking-wider text-{{ $iconColor }}-600 bg-{{ $iconColor }}-50 dark:bg-{{ $iconColor }}-900/20 px-2 py-0.5 rounded-lg whitespace-nowrap">
                                                {{ str_replace('_', ' ', $type) }}
                                            </span>
                                            <span class="text-[11px] text-gray-400 dark:text-gray-500 font-medium whitespace-nowrap">
                                                {{ $notification->created_at->format('H:i') }}
                                            </span>
                                        </div>
                                        
                                        <a href="{{ $url }}" class="block {{ $isUnread ? 'font-bold' : '' }} text-gray-900 dark:text-gray-100 hover:text-indigo-600 dark:hover:text-indigo-400 transition-colors leading-relaxed">
                                            {{ $message }}
                                        </a>
                                        
                                        <!-- Rest of your content -->
                                        @if(isset($data['user_name']))
                                            <div class="mt-2 flex items-center gap-2">
                                                <span class="text-[11px] text-gray-500 dark:text-gray-400 flex items-center">
                                                    <i class="fas fa-user-circle mr-1 opacity-70"></i>
                                                    {{ $data['user_name'] }}
                                                </span>
                                                @if(isset($data['user_role']))
                                                    <span class="w-1 h-1 bg-gray-300 rounded-full"></span>
                                                    <span class="text-[11px] text-gray-500 dark:text-gray-400">
                                                        {{ str_replace('_', ' ', $data['user_role']) }}
                                                    </span>
                                                @endif
                                            </div>
                                        @endif

                                        <!-- Additional content... -->
                                        
                                        <div class="flex items-center gap-4 mt-2">
                                            <span class="text-xs text-gray-500 dark:text-gray-400">
                                                <i class="far fa-clock mr-1"></i>
                                                {{ $notification->created_at->format('d M Y H:i') }}
                                            </span>
                                            <span class="text-xs text-gray-500 dark:text-gray-400">
                                                {{ $notification->created_at->diffForHumans() }}
                                            </span>
                                            @if($notification->read_at)
                                                <span class="text-xs text-green-600 dark:text-green-400">
                                                    <i class="fas fa-check-circle mr-1"></i>
                                                    Dibaca {{ $notification->read_at->diffForHumans() }}
                                                </span>
                                            @endif
                                        </div>
                                    </div>

                                    <!-- Actions -->
                                    <div class="flex items-center gap-1 opacity-0 group-hover:opacity-100 transition-opacity">
                                        @if($isUnread)
                                            <button @click="markAsRead('{{ $notification->id }}')" 
                                                    class="p-2 text-indigo-500 hover:bg-indigo-50 dark:hover:bg-indigo-900/30 rounded-lg transition-colors"
                                                    title="Tandai dibaca">
                                                <i class="fas fa-check-circle"></i>
                                            </button>
                                        @endif
                                        <button @click="deleteNotif('{{ $notification->id }}')"
                                                class="p-2 text-red-400 hover:bg-red-50 dark:hover:bg-red-900/30 rounded-lg transition-colors"
                                                title="Hapus">
                                            <i class="fas fa-trash-alt"></i>
                                        </button>
                                    </div>
                                </div>
                                
                                @if($isUnread)
                                    <!-- Unread Indicator -->
                                    <div class="absolute left-0 top-1/2 -translate-y-1/2 w-1 h-8 bg-indigo-500 rounded-r-full"></div>
                                @endif
                            </div>
                        @endforeach
                        </div>
                    </div>
                @endforeach
            </div>

            <!-- Pagination -->
            @if($notifications->hasPages())
                <div class="p-4 border-t border-gray-100 dark:border-gray-700">
                    {{ $notifications->links() }}
                </div>
            @endif
        @else
            <div class="p-12 text-center">
                <div class="mb-4">
                    <i class="fas fa-bell-slash text-5xl text-gray-300 dark:text-gray-600"></i>
                </div>
                <h3 class="text-lg font-medium text-gray-900 dark:text-white mb-2">Belum ada notifikasi</h3>
                <p class="text-sm text-gray-500 dark:text-gray-400">
                    Notifikasi akan muncul di sini ketika ada aktivitas yang memerlukan perhatian Anda
                </p>
            </div>
        @endif
    </div>

    <!-- Back Button -->
    <div class="mt-6">
        <a href="{{ url()->previous() }}" class="text-blue-600 hover:text-blue-800 dark:text-blue-400">
            <i class="fas fa-arrow-left mr-1"></i> Kembali
        </a>
    </div>
</div>
@endsection