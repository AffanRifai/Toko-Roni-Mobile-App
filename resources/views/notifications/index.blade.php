{{-- resources/views/notifications/index.blade.php --}}
@extends('layouts.app')

@section('title', 'Semua Notifikasi')

@section('content')
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
            <form action="{{ route('notifications.mark-all-as-read') }}" method="POST">
                @csrf
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
        @forelse($notifications as $notification)
            @php
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
            @endphp
            
            <div class="p-4 border-b border-gray-100 dark:border-gray-700 {{ $bgClass }} hover:bg-gray-50 dark:hover:bg-gray-700/50 transition-colors">
                <div class="flex items-start gap-4">
                    <!-- Icon -->
                    <div class="flex-shrink-0 mt-1">
                        <i class="{{ $iconClass }} text-lg"></i>
                    </div>

                    <!-- Content -->
                    <div class="flex-1">
                        <p class="text-gray-900 dark:text-white font-medium">
                            {{ $message }}
                        </p>
                        
                        @if(isset($data['user_name']))
                            <p class="text-sm text-gray-600 dark:text-gray-400 mt-1">
                                User: {{ $data['user_name'] }} 
                                @if(isset($data['user_role']))
                                    ({{ str_replace('_', ' ', $data['user_role']) }})
                                @endif
                            </p>
                        @endif

                        @if(isset($data['updated_by']))
                            <p class="text-sm text-gray-600 dark:text-gray-400">
                                Diperbarui oleh: {{ $data['updated_by'] }}
                            </p>
                        @endif

                        @if(isset($data['created_by']))
                            <p class="text-sm text-gray-600 dark:text-gray-400">
                                Dibuat oleh: {{ $data['created_by'] }}
                            </p>
                        @endif

                        @if(isset($data['changed_fields']) && !empty($data['changed_fields']))
                            <div class="mt-2 flex flex-wrap gap-2">
                                @foreach($data['changed_fields'] as $field)
                                    <span class="inline-flex items-center px-2 py-1 rounded bg-gray-100 dark:bg-gray-700 text-xs text-gray-700 dark:text-gray-300">
                                        <i class="fas fa-edit mr-1 text-blue-500"></i>
                                        {{ $field }}
                                    </span>
                                @endforeach
                            </div>
                        @endif

                        <div class="flex items-center gap-4 mt-2">
                            <span class="text-xs text-gray-500 dark:text-gray-400">
                                <i class="far fa-clock mr-1"></i>
                                {{ $notification->created_at->format('d M Y H:i') }}
                            </span>
                            <span class="text-xs text-gray-500 dark:text-gray-400">
                                {{ $time }}
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
                    <div class="flex items-center gap-2">
                        @if($isUnread)
                            <form action="{{ route('notifications.mark-as-read', $notification->id) }}" method="POST">
                                @csrf
                                <button type="submit" 
                                        class="p-2 text-blue-600 hover:text-blue-800 dark:text-blue-400 hover:bg-blue-50 dark:hover:bg-blue-900/30 rounded-lg transition-colors"
                                        title="Tandai dibaca">
                                    <i class="fas fa-check-circle"></i>
                                </button>
                            </form>
                        @endif
                        
                        <form action="{{ route('notifications.destroy', $notification->id) }}" method="POST">
                            @csrf
                            @method('DELETE')
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
        @empty
            <div class="p-12 text-center">
                <div class="mb-4">
                    <i class="fas fa-bell-slash text-5xl text-gray-300 dark:text-gray-600"></i>
                </div>
                <h3 class="text-lg font-medium text-gray-900 dark:text-white mb-2">Belum ada notifikasi</h3>
                <p class="text-sm text-gray-500 dark:text-gray-400">
                    Notifikasi akan muncul di sini ketika ada aktivitas yang memerlukan perhatian Anda
                </p>
            </div>
        @endforelse

        <!-- Pagination -->
        @if($notifications->hasPages())
            <div class="p-4 border-t border-gray-100 dark:border-gray-700">
                {{ $notifications->links() }}
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