@extends('layouts.app')

@section('title', 'Detail Member')
@section('page-title', 'Profil Member')
@section('page-subtitle', 'Informasi lengkap member')

@section('content')
    <div class="min-h-screen bg-gradient-to-br from-blue-50/50 to-purple-50/30 p-4 md:p-6">

        {{-- Header --}}
        <div class="glass-effect rounded-3xl p-6 md:p-8 shadow-elegant mb-6 animate-fade-in">
            <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-4">
                <div class="flex items-center gap-4">
                    <div class="relative">
                        <div
                            class="w-20 h-20 rounded-2xl bg-gradient-to-br from-blue-500 to-purple-600 flex items-center justify-center shadow-lg">
                            <span class="text-3xl font-bold text-white">{{ strtoupper(substr($member->nama, 0, 1)) }}</span>
                        </div>
                        <div
                            class="absolute -inset-1 bg-gradient-to-r from-blue-500 to-purple-600 rounded-2xl blur-xl opacity-20">
                        </div>
                    </div>
                    <div>
                        <h1 class="text-3xl font-bold text-gray-800">{{ $member->nama }}</h1>
                        <p class="text-gray-600 mt-1">{{ $member->kode_member }}</p>
                    </div>
                </div>
                <div class="flex gap-3">
                    <a href="{{ route('members.edit', $member) }}"
                        class="px-4 py-2 bg-amber-500 text-white rounded-xl hover:bg-amber-600 transition-all">
                        <i class="fas fa-edit mr-2"></i>
                        Edit
                    </a>
                    <a href="{{ route('members.index') }}"
                        class="px-4 py-2 border border-gray-200 rounded-xl hover:bg-gray-50 transition-all">
                        <i class="fas fa-arrow-left mr-2"></i>
                        Kembali
                    </a>
                </div>
            </div>
        </div>

        {{-- Stats Cards --}}
        <div class="grid grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
            <div class="stat-card group">
                <div class="stat-card-glow bg-gradient-to-r from-blue-500 to-cyan-500"></div>
                <div class="stat-card-content">
                    <p class="text-sm text-gray-500">Total Transaksi</p>
                    <h3 class="text-2xl font-bold text-gray-800">{{ $stats['total_transaksi'] }}</h3>
                </div>
            </div>

            <div class="stat-card group">
                <div class="stat-card-glow bg-gradient-to-r from-green-500 to-emerald-500"></div>
                <div class="stat-card-content">
                    <p class="text-sm text-gray-500">Total Belanja</p>
                    <h3 class="text-2xl font-bold text-green-600">Rp {{ number_format($stats['total_belanja']) }}</h3>
                </div>
            </div>

            <div class="stat-card group">
                <div class="stat-card-glow bg-gradient-to-r from-amber-500 to-orange-500"></div>
                <div class="stat-card-content">
                    <p class="text-sm text-gray-500">Piutang</p>
                    <h3 class="text-2xl font-bold text-amber-600">Rp {{ number_format($member->total_piutang) }}</h3>
                    <p class="text-xs text-gray-400 mt-1">{{ $stats['transaksi_kredit'] }} transaksi kredit</p>
                </div>
            </div>

            <div class="stat-card group">
                <div class="stat-card-glow bg-gradient-to-r from-purple-500 to-pink-500"></div>
                <div class="stat-card-content">
                    <p class="text-sm text-gray-500">Sisa Limit</p>
                    <h3 class="text-2xl font-bold text-purple-600">Rp {{ number_format($stats['sisa_limit']) }}</h3>
                </div>
            </div>
        </div>

        {{-- Detail Information --}}
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mb-6">
            {{-- Info Pribadi --}}
            <div class="glass-effect rounded-2xl p-6">
                <h3 class="text-lg font-semibold text-gray-800 mb-4 flex items-center gap-2">
                    <i class="fas fa-user text-blue-500"></i>
                    Informasi Pribadi
                </h3>
                <div class="space-y-3">
                    <div class="flex justify-between">
                        <span class="text-gray-600">Tipe Member</span>
                        <span
                            class="badge {{ $member->tipe_member == 'platinum'
                                ? 'bg-purple-100 text-purple-800'
                                : ($member->tipe_member == 'gold'
                                    ? 'bg-yellow-100 text-yellow-800'
                                    : 'bg-gray-100 text-gray-800') }}">
                            {{ ucfirst($member->tipe_member) }}
                        </span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-gray-600">Email</span>
                        <span class="font-medium">{{ $member->email ?? '-' }}</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-gray-600">No. Telepon</span>
                        <span class="font-medium">{{ $member->no_telepon ?? '-' }}</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-gray-600">Alamat</span>
                        <span class="font-medium text-right">{{ $member->alamat ?? '-' }}</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-gray-600">Status</span>
                        <span
                            class="badge {{ $member->is_active ? 'bg-green-100 text-green-800' : 'bg-gray-100 text-gray-800' }}">
                            {{ $member->is_active ? 'Aktif' : 'Nonaktif' }}
                        </span>
                    </div>
                </div>
            </div>

            {{-- Info Kredit --}}
            <div class="glass-effect rounded-2xl p-6">
                <h3 class="text-lg font-semibold text-gray-800 mb-4 flex items-center gap-2">
                    <i class="fas fa-credit-card text-amber-500"></i>
                    Informasi Kredit
                </h3>
                <div class="space-y-3">
                    <div class="flex justify-between">
                        <span class="text-gray-600">Limit Kredit</span>
                        <span class="font-medium">Rp {{ number_format($member->limit_kredit) }}</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-gray-600">Piutang Saat Ini</span>
                        <span class="font-medium text-amber-600">Rp {{ number_format($member->total_piutang) }}</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-gray-600">Sisa Limit</span>
                        <span class="font-medium text-green-600">Rp {{ number_format($stats['sisa_limit']) }}</span>
                    </div>
                    <div class="mt-3">
                        <div class="w-full bg-gray-200 rounded-full h-2.5">
                            @php
                                $usagePercentage =
                                    $member->limit_kredit > 0
                                        ? ($member->total_piutang / $member->limit_kredit) * 100
                                        : 0;
                            @endphp
                            <div class="h-2.5 rounded-full {{ $usagePercentage > 80 ? 'bg-red-500' : ($usagePercentage > 50 ? 'bg-yellow-500' : 'bg-green-500') }}"
                                style="width: {{ min($usagePercentage, 100) }}%"></div>
                        </div>
                        <p class="text-xs text-gray-500 mt-1">Penggunaan limit: {{ round($usagePercentage) }}%</p>
                    </div>
                </div>
            </div>

            {{-- bergabung --}}
            <div class="glass-effect rounded-2xl p-6">
                <h3 class="text-lg font-semibold text-gray-800 mb-4 flex items-center gap-2">
                    <i class="fas fa-calendar-alt text-green-500"></i>
                    Informasi Bergabung
                </h3>
                <div class="space-y-3">
                    <div class="flex justify-between">
                        <span class="text-gray-600">Tanggal Registrasi</span>
                        @if ($member->tanggal_registrasi)
                            {{ $member->tanggal_registrasi->format('d/m/Y') }}
                        @else
                            {{ $member->created_at->format('d/m/Y') }}
                        @endif
                    </div>
                    <div class="flex justify-between">
                        <span class="text-gray-600">Terdaftar Sejak</span>
                        <span class="font-medium">{{ $member->created_at->diffForHumans() }}</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-gray-600">Terakhir Update</span>
                        <span class="font-medium">{{ $member->updated_at->diffForHumans() }}</span>
                    </div>
                </div>
            </div>
        </div>

        {{-- Recent Activities --}}
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
            {{-- Recent Receivables --}}
            <div class="glass-effect rounded-2xl p-6">
                <div class="flex items-center justify-between mb-4">
                    <h3 class="text-lg font-semibold text-gray-800 flex items-center gap-2">
                        <i class="fas fa-hand-holding-usd text-amber-500"></i>
                        Piutang Terbaru
                    </h3>
                    <a href="{{ route('members.receivables', $member) }}"
                        class="text-sm text-blue-600 hover:text-blue-800">
                        Lihat Semua <i class="fas fa-arrow-right ml-1"></i>
                    </a>
                </div>
                @if ($member->receivables->count() > 0)
                    <div class="space-y-3">
                        @foreach ($member->receivables->take(5) as $receivable)
                            <div class="flex items-center justify-between p-3 bg-gray-50 rounded-lg">
                                <div>
                                    <p class="font-medium text-sm">{{ $receivable->no_piutang }}</p>
                                    <p class="text-xs text-gray-500">{{ $receivable->tanggal_transaksi->format('d/m/Y') }}
                                    </p>
                                </div>
                                <div class="text-right">
                                    <p class="font-medium">Rp {{ number_format($receivable->sisa_piutang) }}</p>
                                    <span
                                        class="badge {{ $receivable->status == 'LUNAS' ? 'bg-green-100 text-green-800' : 'bg-amber-100 text-amber-800' }} text-xs">
                                        {{ $receivable->status }}
                                    </span>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @else
                    <p class="text-gray-500 text-center py-4">Tidak ada piutang</p>
                @endif
            </div>

            {{-- Recent Transactions --}}
            <div class="glass-effect rounded-2xl p-6">
                <div class="flex items-center justify-between mb-4">
                    <h3 class="text-lg font-semibold text-gray-800 flex items-center gap-2">
                        <i class="fas fa-history text-blue-500"></i>
                        Transaksi Terbaru
                    </h3>
                    <a href="{{ route('members.transactions', $member) }}"
                        class="text-sm text-blue-600 hover:text-blue-800">
                        Lihat Semua <i class="fas fa-arrow-right ml-1"></i>
                    </a>
                </div>
                @if ($member->transactions->count() > 0)
                    <div class="space-y-3">
                        @foreach ($member->transactions->take(5) as $transaction)
                            <div class="flex items-center justify-between p-3 bg-gray-50 rounded-lg">
                                <div>
                                    <p class="font-medium text-sm">{{ $transaction->invoice_number }}</p>
                                    <p class="text-xs text-gray-500">{{ $transaction->created_at->format('d/m/Y H:i') }}
                                    </p>
                                </div>
                                <div class="text-right">
                                    <p class="font-medium">Rp {{ number_format($transaction->total_amount) }}</p>
                                    <span
                                        class="badge {{ $transaction->payment_method == 'kredit' ? 'bg-amber-100 text-amber-800' : 'bg-blue-100 text-blue-800' }} text-xs">
                                        {{ ucfirst($transaction->payment_method) }}
                                    </span>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @else
                    <p class="text-gray-500 text-center py-4">Belum ada transaksi</p>
                @endif
            </div>
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
            box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.1);
        }

        .stat-card-glow {
            position: absolute;
            inset: -0.25rem;
            border-radius: 1.75rem;
            filter: blur(12px);
            opacity: 0;
            transition: opacity 0.5s;
            z-index: -1;
        }

        .stat-card:hover .stat-card-glow {
            opacity: 0.5;
        }

        .glass-effect {
            background: rgba(255, 255, 255, 0.7);
            backdrop-filter: blur(10px);
            border: 1px solid rgba(59, 130, 246, 0.1);
        }
    </style>
@endsection
