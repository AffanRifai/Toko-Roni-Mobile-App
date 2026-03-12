@extends('layouts.app')

@section('title', 'Transaksi Member')
@section('page-title', 'Riwayat Transaksi Member')
@section('page-subtitle', 'Daftar semua transaksi member')

@section('content')
    <div class="min-h-screen bg-gradient-to-br from-blue-50/50 to-purple-50/30 p-4 md:p-6">

        {{-- Header with Member Info --}}
        <div class="glass-effect rounded-3xl p-6 md:p-8 shadow-elegant mb-6 animate-fade-in">
            <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-4">
                <div class="flex items-center gap-4">
                    <div class="relative">
                        <div
                            class="w-16 h-16 rounded-2xl bg-gradient-to-br from-blue-500 to-purple-600 flex items-center justify-center shadow-lg">
                            <i class="fas fa-history text-2xl text-white"></i>
                        </div>
                        <div
                            class="absolute -inset-1 bg-gradient-to-r from-blue-500 to-purple-600 rounded-2xl blur-xl opacity-20">
                        </div>
                    </div>
                    <div>
                        <h1 class="text-3xl font-bold text-gray-800">Riwayat Transaksi</h1>
                        <p class="text-gray-600 mt-1">{{ $member->nama }} ({{ $member->kode_member }})</p>
                    </div>
                </div>
                <div class="flex gap-3">
                    <a href="{{ route('members.show', $member) }}"
                        class="px-4 py-2 border border-gray-200 rounded-xl hover:bg-gray-50 transition-all">
                        <i class="fas fa-arrow-left mr-2"></i>
                        Kembali ke Profil
                    </a>
                </div>
            </div>
        </div>

        {{-- Summary Cards --}}
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-6">
            <div class="stat-card group">
                <div class="stat-card-glow bg-gradient-to-r from-blue-500 to-cyan-500"></div>
                <div class="stat-card-content">
                    <p class="text-sm text-gray-500">Total Transaksi</p>
                    <h3 class="text-2xl font-bold text-gray-800">{{ $transactions->total() }}</h3>
                </div>
            </div>

            <div class="stat-card group">
                <div class="stat-card-glow bg-gradient-to-r from-green-500 to-emerald-500"></div>
                <div class="stat-card-content">
                    <p class="text-sm text-gray-500">Total Belanja</p>
                    <h3 class="text-2xl font-bold text-green-600">
                        Rp {{ number_format($member->transactions()->sum('total_amount')) }}
                    </h3>
                </div>
            </div>

            <div class="stat-card group">
                <div class="stat-card-glow bg-gradient-to-r from-purple-500 to-pink-500"></div>
                <div class="stat-card-content">
                    <p class="text-sm text-gray-500">Rata-rata Transaksi</p>
                    @php
                        $avgTransaction = $member->transactions()->avg('total_amount') ?? 0;
                    @endphp
                    <h3 class="text-2xl font-bold text-purple-600">
                        Rp {{ number_format($avgTransaction) }}
                    </h3>
                </div>
            </div>
        </div>

        {{-- Transactions Table --}}
        <div class="glass-effect rounded-2xl overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full">
                    <thead class="bg-gray-50/50">
                        <tr>
                            <th class="px-6 py-4 text-left text-sm font-semibold text-gray-600">Invoice</th>
                            <th class="px-6 py-4 text-left text-sm font-semibold text-gray-600">Tanggal</th>
                            <th class="px-6 py-4 text-left text-sm font-semibold text-gray-600">Total</th>
                            <th class="px-6 py-4 text-left text-sm font-semibold text-gray-600">Metode</th>
                            <th class="px-6 py-4 text-left text-sm font-semibold text-gray-600">Status</th>
                            <th class="px-6 py-4 text-left text-sm font-semibold text-gray-600">Kasir</th>
                            <th class="px-6 py-4 text-left text-sm font-semibold text-gray-600">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        @forelse($transactions as $transaction)
                            <tr class="hover:bg-white/30 transition-colors">
                                <td class="px-6 py-4">
                                    <span class="font-mono text-sm">{{ $transaction->invoice_number }}</span>
                                </td>
                                <td class="px-6 py-4 text-sm">
                                    {{ $transaction->created_at->format('d/m/Y H:i') }}
                                </td>
                                <td class="px-6 py-4 font-medium">
                                    Rp {{ number_format($transaction->total_amount) }}
                                </td>
                                <td class="px-6 py-4">
                                    <span
                                        class="badge {{ $transaction->payment_method == 'kredit' ? 'bg-amber-100 text-amber-800' : 'bg-blue-100 text-blue-800' }}">
                                        {{ ucfirst($transaction->payment_method) }}
                                    </span>
                                </td>
                                <td class="px-6 py-4">
                                    <span
                                        class="badge {{ $transaction->payment_status == 'LUNAS' ? 'bg-green-100 text-green-800' : 'bg-amber-100 text-amber-800' }}">
                                        {{ $transaction->payment_status }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 text-sm">
                                    {{ $transaction->user->name ?? '-' }}
                                </td>
                                <td class="px-6 py-4">
                                    <a href="{{ route('transactions.show', $transaction) }}"
                                        class="w-8 h-8 rounded-lg hover:bg-blue-50 flex items-center justify-center text-blue-600"
                                        title="Detail">
                                        <i class="fas fa-eye"></i>
                                    </a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="px-6 py-12 text-center">
                                    <div class="flex flex-col items-center">
                                        <div
                                            class="w-16 h-16 bg-gray-100 rounded-full flex items-center justify-center mb-4">
                                            <i class="fas fa-receipt text-3xl text-gray-400"></i>
                                        </div>
                                        <h3 class="text-lg font-medium text-gray-900 mb-2">Belum ada transaksi</h3>
                                        <p class="text-gray-600">{{ $member->nama }} belum melakukan transaksi</p>
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            {{-- Pagination --}}
            @if ($transactions->hasPages())
                <div class="px-6 py-4 border-t border-gray-100">
                    {{ $transactions->withQueryString()->links() }}
                </div>
            @endif
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
