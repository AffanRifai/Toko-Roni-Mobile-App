@extends('layouts.app')

@section('title', 'Notifikasi')

@section('content')
<div class="max-w-4xl mx-auto" x-data="{
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
                location.reload(); // Sederhananya reload untuk index karena banyak grouping
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
    <!-- Header Page -->
    <div class="flex flex-col md:flex-row md:items-center justify-between mb-6 gap-4">
        <div>
            <h2 class="text-2xl font-bold text-gray-900 dark:text-white flex items-center">
                <i class="fas fa-bell mr-3 text-indigo-500"></i>
                Notifikasi
            </h2>
            <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">
                Kelola semua aktivitas dan pemberitahuan sistem
            </p>
        </div>
        
        <div class="flex items-center gap-2">
            @if($unreadCount > 0)
                <form action="{{ route('notifications.mark-all-as-read') }}" method="POST">
                    @csrf
                    <button type="submit" class="inline-flex items-center px-4 py-2 bg-indigo-50 text-indigo-700 hover:bg-indigo-100 dark:bg-indigo-900/30 dark:text-indigo-400 rounded-xl text-sm font-semibold transition-all">
                        <i class="fas fa-check-double mr-2"></i>
                        Tandai Semua Dibaca
                    </button>
                </form>
            @endif
            
            <form action="{{ route('notifications.clear-all') }}" method="POST" onsubmit="return confirm('Hapus semua notifikasi?')">
                @csrf
                @method('DELETE')
                <button type="submit" class="inline-flex items-center px-4 py-2 bg-red-50 text-red-700 hover:bg-red-100 dark:bg-red-900/30 dark:text-red-400 rounded-xl text-sm font-semibold transition-all">
                    <i class="fas fa-trash-alt mr-2"></i>
                    Bersihkan Semua
                </button>
            </form>
        </div>
    </div>

    <!-- Stats Boxes -->
    <div class="grid grid-cols-2 md:grid-cols-3 gap-4 mb-8">
        <div class="bg-white dark:bg-gray-800 p-4 rounded-2xl shadow-sm border border-gray-100 dark:border-gray-700 flex items-center gap-4">
            <div class="w-12 h-12 rounded-xl bg-indigo-100 dark:bg-indigo-900/30 flex items-center justify-center">
                <i class="fas fa-envelope-open text-indigo-600 dark:text-indigo-400"></i>
            </div>
            <div>
                <p class="text-xs text-gray-500 dark:text-gray-400 font-medium">Belum Dibaca</p>
                <p class="text-xl font-bold text-gray-900 dark:text-white">{{ $unreadCount }}</p>
            </div>
        </div>
        <div class="bg-white dark:bg-gray-800 p-4 rounded-2xl shadow-sm border border-gray-100 dark:border-gray-700 flex items-center gap-4">
            <div class="w-12 h-12 rounded-xl bg-blue-100 dark:bg-blue-900/30 flex items-center justify-center">
                <i class="fas fa-list text-blue-600 dark:text-blue-400"></i>
            </div>
            <div>
                <p class="text-xs text-gray-500 dark:text-gray-400 font-medium">Total Notifikasi</p>
                <p class="text-xl font-bold text-gray-900 dark:text-white">{{ $totalCount }}</p>
            </div>
        </div>
    </div>

    @if($notifications->count() > 0)
        <!-- Grouped Notifications List -->
        <div class="space-y-8">
            @foreach($groupedNotifications as $date => $notifs)
                <div>
                    <h3 class="text-sm font-bold text-gray-400 uppercase tracking-widest flex items-center mb-4">
                        <span class="mr-3">{{ $date }}</span>
                        <div class="flex-1 h-px bg-gray-200 dark:bg-gray-700"></div>
                    </h3>
                    
                    <div class="bg-white dark:bg-gray-800 rounded-3xl shadow-sm border border-gray-100 dark:border-gray-700 overflow-hidden">
                        <div class="divide-y divide-gray-50 dark:divide-gray-700">
                            @foreach($notifs as $notification)
                                @php
                                    $data = $notification->data;
                                    $isUnread = is_null($notification->read_at);
                                    $color = $data['color'] ?? 'blue';
                                    $icon = $data['icon'] ?? 'fas fa-bell';
                                    $message = $data['message'] ?? 'Pemberitahuan sistem';
                                    $url = $data['url'] ?? '#';
                                @endphp
                                
                                <div class="p-5 hover:bg-gray-50 dark:hover:bg-gray-700/30 transition-all group relative {{ $isUnread ? 'bg-indigo-50/20 dark:bg-indigo-900/10' : '' }}">
                                    <div class="flex items-start gap-4">
                                        <!-- Icon -->
                                        <div class="flex-shrink-0 w-12 h-12 rounded-2xl flex items-center justify-center shadow-sm bg-{{ $color }}-100 dark:bg-{{ $color }}-900/30 text-{{ $color }}-600 dark:text-{{ $color }}-400">
                                            <i class="{{ $icon }} text-lg"></i>
                                        </div>

                                        <!-- Content -->
                                        <div class="flex-1 min-w-0">
                                            <div class="flex items-center justify-between mb-1">
                                                <span class="text-[10px] font-bold uppercase tracking-wider text-{{ $color }}-600 bg-{{ $color }}-50 dark:bg-{{ $color }}-900/20 px-2 py-0.5 rounded-lg whitespace-nowrap">
                                                    {{ str_replace('_', ' ', $data['type'] ?? 'UMUM') }}
                                                </span>
                                                <span class="text-[11px] text-gray-400 dark:text-gray-500 font-medium whitespace-nowrap">
                                                    {{ $notification->created_at->format('H:i') }}
                                                </span>
                                            </div>
                                            
                                            <a href="{{ $url }}" class="block {{ $isUnread ? 'font-bold' : '' }} text-gray-900 dark:text-gray-100 hover:text-indigo-600 dark:hover:text-indigo-400 transition-colors leading-relaxed">
                                                {{ $message }}
                                            </a>
                                            
                                            @if(isset($data['user_name']))
                                                <div class="mt-2 flex items-center gap-2">
                                                    <span class="text-[11px] text-gray-500 dark:text-gray-400 flex items-center">
                                                        <i class="fas fa-user-circle mr-1 opacity-70"></i>
                                                        {{ $data['user_name'] }}
                                                    </span>
                                                    <span class="w-1 h-1 bg-gray-300 rounded-full"></span>
                                                    <span class="text-[11px] text-gray-500 dark:text-gray-400">
                                                        {{ $data['user_role'] ?? '' }}
                                                    </span>
                                                </div>
                                            @endif
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
                </div>
            @endforeach
        </div>

        <!-- Pagination -->
        <div class="mt-10 mb-20">
            {{ $notifications->links() }}
        </div>
    @else
        <!-- Empty State -->
        <div class="bg-white dark:bg-gray-800 rounded-[40px] shadow-sm border border-gray-100 dark:border-gray-700 p-20 text-center">
            <div class="w-24 h-24 bg-gray-50 dark:bg-gray-700/50 rounded-full flex items-center justify-center mx-auto mb-6">
                <i class="fas fa-bell-slash text-4xl text-gray-300"></i>
            </div>
            <h3 class="text-xl font-bold text-gray-900 dark:text-white mb-2">Tidak ada notifikasi</h3>
            <p class="text-gray-500 dark:text-gray-400 max-w-sm mx-auto">
                Anda sudah up to date! Saat ini belum ada pemberitahuan baru untuk Anda.
            </p>
        </div>
    @endif
</div>
@endsection