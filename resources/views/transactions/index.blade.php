@extends('layouts.app')

@section('title', 'Daftar Transaksi')

@section('content')
<div class="min-h-screen bg-gray-50 p-4 md:p-6">
    <!-- Header dengan Stats -->
    <div class="mb-8">
        <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 mb-6">
            <div>
                <h1 class="text-2xl md:text-3xl font-bold text-gray-800 mb-2">Daftar Transaksi</h1>
                <p class="text-gray-600">Kelola dan pantau semua transaksi penjualan</p>
            </div>
            <div class="mt-4 md:mt-0">
                <a href="{{ route('transactions.create') }}" class="inline-flex items-center gap-2 px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors">
                    <i class="fas fa-plus-circle"></i>
                    <span>Tambah Transaksi</span>
                </a>
            </div>
        </div>

        <!-- Stats Cards -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4 mb-8">
            <!-- Total Transaksi -->
            <div class="bg-white rounded-xl shadow-sm p-6">
                <div class="flex justify-between items-center">
                    <div>
                        <p class="text-gray-500 text-sm mb-2">Total Transaksi</p>
                        <p class="text-2xl font-bold text-gray-800">{{ $transactions->total() }}</p>
                        <p class="text-green-600 text-xs mt-2">
                            <i class="fas fa-arrow-up mr-1"></i>+12.5% dari bulan lalu
                        </p>
                    </div>
                    <div class="p-3 bg-blue-100 rounded-lg">
                        <i class="fas fa-shopping-cart text-blue-600 text-xl"></i>
                    </div>
                </div>
            </div>

            <!-- Total Pendapatan -->
            <div class="bg-white rounded-xl shadow-sm p-6">
                <div class="flex justify-between items-center">
                    <div>
                        <p class="text-gray-500 text-sm mb-2">Total Pendapatan</p>
                        <p class="text-2xl font-bold text-gray-800">Rp {{ number_format($totalRevenue, 0, ',', '.') }}</p>
                        <p class="text-green-600 text-xs mt-2">
                            <i class="fas fa-arrow-up mr-1"></i>+8.3% dari bulan lalu
                        </p>
                    </div>
                    <div class="p-3 bg-green-100 rounded-lg">
                        <i class="fas fa-money-bill-wave text-green-600 text-xl"></i>
                    </div>
                </div>
            </div>

            <!-- Rata-rata/Transaksi -->
            <div class="bg-white rounded-xl shadow-sm p-6">
                <div class="flex justify-between items-center">
                    <div>
                        <p class="text-gray-500 text-sm mb-2">Rata-rata/Transaksi</p>
                        <p class="text-2xl font-bold text-gray-800">Rp {{ number_format($averageTransaction, 0, ',', '.') }}</p>
                        <p class="text-green-600 text-xs mt-2">
                            <i class="fas fa-arrow-up mr-1"></i>+5.2% dari bulan lalu
                        </p>
                    </div>
                    <div class="p-3 bg-cyan-100 rounded-lg">
                        <i class="fas fa-chart-line text-cyan-600 text-xl"></i>
                    </div>
                </div>
            </div>

            <!-- Transaksi Hari Ini -->
            <div class="bg-white rounded-xl shadow-sm p-6">
                <div class="flex justify-between items-center">
                    <div>
                        <p class="text-gray-500 text-sm mb-2">Transaksi Hari Ini</p>
                        <p class="text-2xl font-bold text-gray-800">{{ $todayTransactions }}</p>
                        <p class="text-green-600 text-xs mt-2">
                            <i class="fas fa-arrow-up mr-1"></i>+3 transaksi dari kemarin
                        </p>
                    </div>
                    <div class="p-3 bg-yellow-100 rounded-lg">
                        <i class="fas fa-calendar-day text-yellow-600 text-xl"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Filter dan Search - HANYA Filter Metode Pembayaran -->
    <div class="bg-white rounded-xl shadow-sm p-6 mb-6">
        <form method="GET" action="{{ route('transactions.index') }}" id="filterForm">
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <!-- Search Input -->
                <div class="relative">
                    <i class="fas fa-search absolute left-3 top-3 text-gray-400"></i>
                    <input type="text"
                           name="search"
                           value="{{ request('search') }}"
                           placeholder="Cari invoice, customer, atau kasir..."
                           class="w-full pl-10 pr-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-colors"
                           onchange="this.form.submit()">
                </div>

                <!-- ========== HANYA PERTAHANKAN: Payment Method Filter ========== -->
                <div>
                    <select name="payment_method" onchange="this.form.submit()" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500">
                        <option value="all" {{ request('payment_method') == 'all' || !request('payment_method') ? 'selected' : '' }}>Semua Pembayaran</option>
                        <option value="cash" {{ request('payment_method') == 'cash' ? 'selected' : '' }}>💵 Cash (Tunai)</option>
                        <option value="debit_card" {{ request('payment_method') == 'debit_card' ? 'selected' : '' }}>💳 Debit Card</option>
                        <option value="credit_card" {{ request('payment_method') == 'credit_card' ? 'selected' : '' }}>🏦 Credit Card (Hutang)</option>
                        <option value="e_wallet" {{ request('payment_method') == 'e_wallet' ? 'selected' : '' }}>📱 E-Wallet</option>
                    </select>
                </div>

                <!-- Tombol Reset Filter -->
                <div>
                    @if(request('search') || request('payment_method') || request('start_date') || request('end_date'))
                        <a href="{{ route('transactions.index') }}" class="inline-flex items-center justify-center w-full px-4 py-2 bg-red-50 text-red-600 rounded-lg hover:bg-red-100 transition-colors">
                            <i class="fas fa-times-circle mr-2"></i>
                            Reset Filter
                        </a>
                    @endif
                </div>
            </div>

            <!-- Date Range Filter (Opsional, bisa dipertahankan untuk range tanggal) -->
            <div class="flex flex-wrap items-center gap-4 mt-4 pt-2">
                <div class="flex items-center gap-2">
                    <span class="text-sm text-gray-600">Dari:</span>
                    <input type="date" name="start_date" value="{{ request('start_date') }}"
                           class="px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500"
                           onchange="this.form.submit()">
                </div>
                <div class="flex items-center gap-2">
                    <span class="text-sm text-gray-600">Sampai:</span>
                    <input type="date" name="end_date" value="{{ request('end_date') }}"
                           class="px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500"
                           onchange="this.form.submit()">
                </div>
            </div>
        </form>
    </div>

    <!-- Main Table -->
    <div class="bg-white rounded-xl shadow-sm overflow-hidden">
        <!-- Table Header -->
        <div class="px-6 py-4 border-b border-gray-200 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-2">
            <div class="flex items-center gap-2">
                <i class="fas fa-list text-gray-600"></i>
                <h2 class="font-semibold text-gray-800">Riwayat Transaksi</h2>
            </div>
            <div class="text-sm text-gray-600">
                Menampilkan {{ $transactions->count() }} dari {{ $transactions->total() }} transaksi
            </div>
        </div>

        <!-- Table Content -->
        <div class="overflow-x-auto">
            <table class="w-full">
                <thead class="bg-gray-50">
                    <tr class="text-left text-sm font-medium text-gray-500 uppercase tracking-wider">
                        <th class="px-6 py-3">Invoice</th>
                        <th class="px-6 py-3">Tanggal</th>
                        <th class="px-6 py-3">Customer</th>
                        <th class="px-6 py-3 text-center">Pembayaran</th>
                        <th class="px-6 py-3">Kasir</th>
                        <th class="px-6 py-3 text-right">Total</th>
                        <th class="px-6 py-3 text-center">Actions</th>
                     </tr>
                </thead>
                <tbody class="divide-y divide-gray-200">
                    @forelse($transactions as $transaction)
                    <tr class="transaction-row hover:bg-blue-50 transition-colors">
                        <!-- Invoice -->
                        <td class="px-6 py-4">
                            <div class="flex items-center gap-3">
                                <div class="p-2 bg-blue-100 rounded-lg">
                                    <i class="fas fa-receipt text-blue-600"></i>
                                </div>
                                <div>
                                    <div class="font-semibold text-gray-900">{{ $transaction->invoice_number }}</div>
                                    <div class="text-xs text-gray-500">ID: {{ $transaction->id }}</div>
                                </div>
                            </div>
                        </td>

                        <!-- Tanggal -->
                        <td class="px-6 py-4">
                            <div>
                                <div class="font-medium text-gray-900">{{ $transaction->created_at->format('d M Y') }}</div>
                                <div class="text-sm text-gray-500">{{ $transaction->created_at->format('H:i') }}</div>
                            </div>
                        </td>

                        <!-- Customer -->
                        <td class="px-6 py-4">
                            @if($transaction->customer_name)
                                <div class="flex items-center gap-2">
                                    <div class="p-2 bg-gray-100 rounded-lg">
                                        <i class="fas fa-user text-gray-600"></i>
                                    </div>
                                    <span class="text-gray-900">{{ $transaction->customer_name }}</span>
                                </div>
                            @else
                                <span class="text-gray-400">-</span>
                            @endif
                        </td>

                        <!-- Pembayaran -->
                        <td class="px-6 py-4 text-center">
                            @php
                                $paymentColors = [
                                    'cash' => 'bg-green-100 text-green-800 border-green-200',
                                    'debit_card' => 'bg-blue-100 text-blue-800 border-blue-200',
                                    'credit_card' => 'bg-purple-100 text-purple-800 border-purple-200',
                                    'e_wallet' => 'bg-cyan-100 text-cyan-800 border-cyan-200',
                                ];
                                $paymentIcons = [
                                    'cash' => 'money-bill-wave',
                                    'debit_card' => 'credit-card',
                                    'credit_card' => 'hand-holding-usd',
                                    'e_wallet' => 'mobile-alt',
                                ];
                                $paymentLabels = [
                                    'cash' => 'Tunai',
                                    'debit_card' => 'Debit Card',
                                    'credit_card' => 'Kredit',
                                    'e_wallet' => 'E-Wallet',
                                ];
                            @endphp
                            <div class="flex flex-col items-center gap-1">
                                <span class="inline-flex items-center gap-1 px-2 py-1 rounded-full border text-xs font-medium {{ $paymentColors[$transaction->payment_method] ?? 'bg-gray-100 text-gray-800 border-gray-200' }}">
                                    <i class="fas fa-{{ $paymentIcons[$transaction->payment_method] ?? 'credit-card' }}"></i>
                                    {{ $paymentLabels[$transaction->payment_method] ?? ucfirst(str_replace('_', ' ', $transaction->payment_method)) }}
                                </span>
                                @if($transaction->payment_method === 'credit_card')
                                    <span class="text-xs text-red-600 font-medium">
                                        <i class="fas fa-exclamation-triangle mr-1"></i>Hutang
                                    </span>
                                @endif
                            </div>
                        </td>

                        <!-- Kasir -->
                        <td class="px-6 py-4">
                            <div class="flex items-center gap-2">
                                <div class="p-2 bg-blue-100 rounded-lg">
                                    <i class="fas fa-user-tie text-blue-600"></i>
                                </div>
                                <span class="text-gray-900">{{ $transaction->user->name }}</span>
                            </div>
                        </td>

                        <!-- Total -->
                        <td class="px-6 py-4 text-right">
                            <div>
                                <div class="font-bold text-lg text-gray-900">Rp {{ number_format($transaction->total_amount, 0, ',', '.') }}</div>
                                <div class="text-sm text-gray-500">
                                    @if($transaction->items)
                                        {{ $transaction->items->count() }} item
                                    @else
                                        0 item
                                    @endif
                                </div>
                            </div>
                        </td>

                        <!-- Actions -->
                        <td class="px-6 py-4 text-center">
                            <div class="flex items-center justify-center gap-1">
                                <a href="{{ route('transactions.show', $transaction) }}"
                                   class="p-2 text-blue-600 hover:bg-blue-100 rounded-lg transition-colors"
                                   data-tooltip="Detail">
                                    <i class="fas fa-eye"></i>
                                </a>

                                <a href="{{ route('transactions.print', $transaction) }}"
                                   class="p-2 text-green-600 hover:bg-green-100 rounded-lg transition-colors"
                                   target="_blank"
                                   data-tooltip="Print">
                                    <i class="fas fa-print"></i>
                                </a>

                                @if(auth()->user()->role === 'owner')
                                <button type="button"
                                        class="p-2 text-red-600 hover:bg-red-100 rounded-lg transition-colors"
                                        data-tooltip="Hapus"
                                        onclick="openDeleteModal('{{ $transaction->id }}', '{{ $transaction->invoice_number }}', '{{ number_format($transaction->total_amount, 0, ',', '.') }}')">
                                    <i class="fas fa-trash"></i>
                                </button>
                                @endif
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="7" class="px-6 py-12 text-center">
                            <div class="flex flex-col items-center justify-center">
                                <div class="p-4 bg-gray-100 rounded-full mb-4">
                                    <i class="fas fa-shopping-cart text-3xl text-gray-400"></i>
                                </div>
                                <h3 class="text-lg font-medium text-gray-700 mb-2">Belum ada transaksi</h3>
                                <p class="text-gray-500 mb-6">Mulai lakukan transaksi pertama Anda</p>
                                <a href="{{ route('transactions.create') }}"
                                   class="inline-flex items-center gap-2 px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors">
                                    <i class="fas fa-plus-circle"></i>
                                    <span>Tambah Transaksi</span>
                                </a>
                            </div>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <!-- Pagination -->
        @if($transactions->hasPages())
        <div class="px-6 py-4 border-t border-gray-200 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
            <div class="text-sm text-gray-700">
                Menampilkan <span class="font-medium">{{ $transactions->firstItem() }}</span>
                sampai <span class="font-medium">{{ $transactions->lastItem() }}</span>
                dari <span class="font-medium">{{ $transactions->total() }}</span> transaksi
            </div>
            <div class="flex items-center gap-1">
                {{ $transactions->links() }}
            </div>
        </div>
        @endif
    </div>
</div>

<!-- Delete Modal -->
@if(auth()->user()->role === 'owner')
<div id="deleteModal" class="fixed inset-0 bg-black bg-opacity-50 hidden items-center justify-center z-50">
    <div class="bg-white rounded-xl shadow-lg w-full max-w-md mx-4">
        <div class="p-6">
            <h3 class="text-lg font-semibold text-gray-800 mb-4">Konfirmasi Hapus</h3>
            <p class="text-gray-600 mb-4">Apakah Anda yakin ingin menghapus transaksi ini?</p>

            <div class="bg-yellow-50 border border-yellow-200 rounded-lg p-3 mb-4">
                <div class="flex items-center gap-2">
                    <i class="fas fa-exclamation-triangle text-yellow-600"></i>
                    <span class="text-sm text-yellow-800">Data yang dihapus tidak dapat dikembalikan!</span>
                </div>
            </div>

            <div class="bg-gray-50 rounded-lg p-4 mb-6">
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <p class="text-sm text-gray-500">Invoice</p>
                        <p class="font-semibold text-gray-800" id="modalInvoice"></p>
                    </div>
                    <div>
                        <p class="text-sm text-gray-500">Total</p>
                        <p class="font-semibold text-gray-800" id="modalTotal">Rp 0</p>
                    </div>
                </div>
            </div>

            <div class="flex gap-3">
                <button onclick="closeDeleteModal()"
                        class="flex-1 px-4 py-2 border border-gray-300 text-gray-700 rounded-lg hover:bg-gray-50 transition-colors">
                    Batal
                </button>
                <form id="deleteForm" method="POST" class="flex-1">
                    @csrf
                    @method('DELETE')
                    <button type="submit"
                            class="w-full px-4 py-2 bg-red-600 text-white rounded-lg hover:bg-red-700 transition-colors">
                        Hapus Transaksi
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>
@endif

<style>
    [data-tooltip] {
        position: relative;
    }

    [data-tooltip]:before {
        content: attr(data-tooltip);
        position: absolute;
        bottom: 100%;
        left: 50%;
        transform: translateX(-50%);
        padding: 4px 8px;
        background-color: #374151;
        color: white;
        font-size: 12px;
        border-radius: 4px;
        white-space: nowrap;
        opacity: 0;
        visibility: hidden;
        transition: opacity 0.2s, visibility 0.2s;
        z-index: 10;
    }

    [data-tooltip]:hover:before {
        opacity: 1;
        visibility: visible;
    }

    .transaction-row {
        animation: fadeIn 0.3s ease-out;
    }

    @keyframes fadeIn {
        from {
            opacity: 0;
            transform: translateY(10px);
        }
        to {
            opacity: 1;
            transform: translateY(0);
        }
    }

    ::-webkit-scrollbar {
        width: 6px;
        height: 6px;
    }

    ::-webkit-scrollbar-track {
        background: #f1f1f1;
        border-radius: 3px;
    }

    ::-webkit-scrollbar-thumb {
        background: #c1c1c1;
        border-radius: 3px;
    }
</style>

<script>
    function openDeleteModal(id, invoice, total) {
        const modal = document.getElementById('deleteModal');
        const form = document.getElementById('deleteForm');
        const modalInvoice = document.getElementById('modalInvoice');
        const modalTotal = document.getElementById('modalTotal');

        modalInvoice.textContent = invoice;
        modalTotal.textContent = `Rp ${total}`;
        form.action = `/transactions/${id}`;

        modal.classList.remove('hidden');
        modal.classList.add('flex');
    }

    function closeDeleteModal() {
        const modal = document.getElementById('deleteModal');
        modal.classList.add('hidden');
        modal.classList.remove('flex');
    }

    document.addEventListener('click', function(e) {
        const modal = document.getElementById('deleteModal');
        if (modal && !modal.classList.contains('hidden') && e.target === modal) {
            closeDeleteModal();
        }
    });

    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape') {
            closeDeleteModal();
        }
    });
</script>
@endsection