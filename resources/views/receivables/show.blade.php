@extends('layouts.app')

@section('title', 'Riwayat Pembayaran - ' . $receivable->no_piutang)
@section('page-title', 'Detail Piutang')
@section('page-subtitle', 'Riwayat pembayaran piutang member')

@section('content')
<div class="min-h-screen bg-gradient-to-br from-blue-50/50 to-purple-50/30 p-4 md:p-6">

    {{-- Header --}}
    <div class="glass-effect rounded-3xl p-6 md:p-8 shadow-elegant mb-6 animate-fade-in">
        <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-4">
            <div class="flex items-center gap-4">
                <div class="relative">
                    <div class="w-16 h-16 rounded-2xl bg-gradient-to-br from-purple-500 to-pink-600 flex items-center justify-center shadow-lg">
                        <i class="fas fa-history text-2xl text-white"></i>
                    </div>
                    <div class="absolute -inset-1 bg-gradient-to-r from-purple-500 to-pink-600 rounded-2xl blur-xl opacity-20"></div>
                </div>
                <div>
                    <h1 class="text-3xl font-bold text-gray-800">Riwayat Pembayaran</h1>
                    <p class="text-gray-600 mt-1">
                        No. Piutang: <span class="font-mono font-semibold">{{ $receivable->no_piutang }}</span>
                    </p>
                    <p class="text-sm text-gray-500">
                        Member: {{ $receivable->member->nama ?? 'N/A' }} ({{ $receivable->member->kode_member ?? '-' }})
                    </p>
                </div>
            </div>
            <div class="flex gap-3">
                <a href="{{ route('transactions.show', $receivable->transaction_id) }}"
                   class="px-4 py-2 bg-blue-600 text-white rounded-xl hover:bg-blue-700 transition-all inline-flex items-center">
                    <i class="fas fa-file-invoice mr-2"></i>
                    Lihat Invoice
                </a>
                <a href="{{ route('members.receivables', $receivable->member_id) }}"
                   class="px-4 py-2 border border-gray-200 rounded-xl hover:bg-gray-50 transition-all inline-flex items-center">
                    <i class="fas fa-arrow-left mr-2"></i>
                    Kembali
                </a>
            </div>
        </div>
    </div>

    {{-- Info Piutang --}}
    <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-6">
        <div class="bg-white rounded-xl p-4 shadow-sm border-l-4 border-blue-500">
            <p class="text-sm text-gray-500">Total Piutang</p>
            <p class="text-xl font-bold text-gray-800">Rp {{ number_format($receivable->total_piutang, 0, ',', '.') }}</p>
        </div>
        <div class="bg-white rounded-xl p-4 shadow-sm border-l-4 border-green-500">
            <p class="text-sm text-gray-500">Total Dibayar</p>
            <p class="text-xl font-bold text-green-600">Rp {{ number_format($paymentSummary['total_paid'], 0, ',', '.') }}</p>
        </div>
        <div class="bg-white rounded-xl p-4 shadow-sm border-l-4 border-yellow-500">
            <p class="text-sm text-gray-500">Sisa Piutang</p>
            <p class="text-xl font-bold text-yellow-600">Rp {{ number_format($paymentSummary['remaining'], 0, ',', '.') }}</p>
        </div>
        <div class="bg-white rounded-xl p-4 shadow-sm border-l-4 border-purple-500">
            <p class="text-sm text-gray-500">Status</p>
            <p class="text-xl font-bold">
                <span class="badge {{ $receivable->status == 'LUNAS' ? 'bg-green-100 text-green-800' : 'bg-amber-100 text-amber-800' }}">
                    {{ $receivable->status }}
                </span>
            </p>
        </div>
    </div>

    {{-- Tabel Riwayat Pembayaran --}}
    <div class="glass-effect rounded-2xl overflow-hidden">
        <div class="px-6 py-4 border-b border-gray-200 bg-gray-50/50">
            <h3 class="font-semibold text-gray-800">
                <i class="fas fa-list-ul mr-2 text-purple-500"></i>
                Riwayat Pembayaran
            </h3>
        </div>
        
        <div class="overflow-x-auto">
            <table class="w-full">
                <thead class="bg-gray-50/50">
                    <tr>
                        <th class="px-6 py-3 text-left text-sm font-semibold text-gray-600">Tanggal Bayar</th>
                        <th class="px-6 py-3 text-left text-sm font-semibold text-gray-600">Jumlah Bayar</th>
                        <th class="px-6 py-3 text-left text-sm font-semibold text-gray-600">Metode Bayar</th>
                        <th class="px-6 py-3 text-left text-sm font-semibold text-gray-600">Kasir</th>
                        <th class="px-6 py-3 text-left text-sm font-semibold text-gray-600">Keterangan</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @forelse($receivable->payments as $payment)
                    <tr class="hover:bg-white/30 transition-colors">
                        <td class="px-6 py-3 text-sm">
                            {{ $payment->tanggal_bayar->format('d/m/Y H:i') }}
                        </td>
                        <td class="px-6 py-3 font-medium text-green-600">
                            Rp {{ number_format($payment->jumlah_bayar, 0, ',', '.') }}
                        </td>
                        <td class="px-6 py-3">
                            <span class="badge bg-gray-100 text-gray-700">
                                {{ ucfirst($payment->metode_bayar) }}
                            </span>
                        </td>
                        <td class="px-6 py-3 text-sm">
                            {{ $payment->kasir->name ?? '-' }}
                        </td>
                        <td class="px-6 py-3 text-sm text-gray-500">
                            {{ $payment->keterangan ?? '-' }}
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="5" class="px-6 py-12 text-center">
                            <div class="flex flex-col items-center">
                                <div class="w-16 h-16 bg-gray-100 rounded-full flex items-center justify-center mb-4">
                                    <i class="fas fa-credit-card text-3xl text-gray-400"></i>
                                </div>
                                <h3 class="text-lg font-medium text-gray-900 mb-2">Belum ada pembayaran</h3>
                                <p class="text-gray-600">Belum ada pembayaran yang dicatat untuk piutang ini</p>
                            </div>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    {{-- Informasi Transaksi --}}
    <div class="mt-6 glass-effect rounded-2xl overflow-hidden">
        <div class="px-6 py-4 border-b border-gray-200 bg-gray-50/50">
            <h3 class="font-semibold text-gray-800">
                <i class="fas fa-shopping-cart mr-2 text-blue-500"></i>
                Informasi Transaksi
            </h3>
        </div>
        <div class="p-6">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <p class="text-sm text-gray-500">Invoice</p>
                    <p class="font-mono font-medium">{{ $receivable->invoice_number ?? '-' }}</p>
                </div>
                <div>
                    <p class="text-sm text-gray-500">Tanggal Transaksi</p>
                    <p>{{ \Carbon\Carbon::parse($receivable->tanggal_transaksi)->format('d/m/Y') }}</p>
                </div>
                <div>
                    <p class="text-sm text-gray-500">Jatuh Tempo</p>
                    <p class="{{ $receivable->jatuh_tempo && $receivable->jatuh_tempo < now() && $receivable->status != 'LUNAS' ? 'text-red-600 font-medium' : '' }}">
                        {{ $receivable->jatuh_tempo ? \Carbon\Carbon::parse($receivable->jatuh_tempo)->format('d/m/Y') : '-' }}
                    </p>
                </div>
                <div>
                    <p class="text-sm text-gray-500">Keterangan</p>
                    <p>{{ $receivable->keterangan ?? '-' }}</p>
                </div>
            </div>
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

.glass-effect {
    background: rgba(255, 255, 255, 0.7);
    backdrop-filter: blur(10px);
    border: 1px solid rgba(59, 130, 246, 0.1);
}

@keyframes fadeIn {
    from {
        opacity: 0;
        transform: translateY(-10px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}

.animate-fade-in {
    animation: fadeIn 0.3s ease-out;
}
</style>
@endsection