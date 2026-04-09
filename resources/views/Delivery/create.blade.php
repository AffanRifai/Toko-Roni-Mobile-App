@extends('layouts.app')

@section('title', 'Buat Pengiriman Baru')
@section('page-title', 'Buat Pengiriman Baru')
@section('page-subtitle', 'Form pembuatan pengiriman barang')

@section('content')
<div class="min-h-screen bg-gradient-to-br from-blue-50/50 to-purple-50/30 p-4 md:p-6">

    <!-- Header -->
    <div class="glass-effect rounded-3xl p-6 md:p-8 shadow-elegant mb-6 animate-fade-in">
        <div class="flex items-center gap-4">
            <div class="relative">
                <div class="w-16 h-16 rounded-2xl bg-gradient-to-br from-blue-500 to-indigo-600 flex items-center justify-center shadow-lg">
                    <i class="fas fa-plus-circle text-2xl text-white"></i>
                </div>
                <div class="absolute -inset-1 bg-gradient-to-r from-blue-500 to-indigo-600 rounded-2xl blur-xl opacity-20"></div>
            </div>
            <div>
                <h1 class="text-3xl font-bold text-gray-800">Buat Pengiriman Baru</h1>
                <p class="text-gray-600 mt-1">Lengkapi form berikut untuk membuat pengiriman baru</p>
            </div>
        </div>
    </div>

    <!-- Form -->
    <div class="glass-effect rounded-2xl p-6 max-w-4xl mx-auto">
        <form action="{{ route('delivery.store') }}" method="POST" id="deliveryForm" class="space-y-6">
            @csrf

            <!-- Pilih Transaksi -->
            <div class="bg-white/50 rounded-xl p-5 border border-gray-200">
                <h3 class="font-semibold text-gray-900 mb-4 flex items-center gap-2">
                    <i class="fas fa-shopping-cart text-blue-600"></i>
                    Pilih Transaksi
                </h3>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">
                            Cari Transaksi <span class="text-red-500">*</span>
                        </label>
                        <div class="relative">
                            <i class="fas fa-search absolute left-3 top-1/2 transform -translate-y-1/2 text-gray-400"></i>
                            <input type="text" id="searchTransaction"
                                   class="w-full pl-10 pr-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                                   placeholder="Masukkan nomor invoice atau nama customer..."
                                   autocomplete="off">
                            <div id="searchResults" class="absolute z-50 w-full mt-1 bg-white border border-gray-200 rounded-lg shadow-lg hidden max-h-60 overflow-y-auto"></div>
                        </div>
                        <input type="hidden" name="transaction_id" id="transaction_id" required>
                        <p class="text-xs text-gray-500 mt-1">Ketik untuk mencari transaksi</p>
                    </div>

                    <div id="selectedTransactionInfo" class="hidden">
                        <label class="block text-sm font-medium text-gray-700 mb-2">
                            Transaksi Terpilih
                        </label>
                        <div class="bg-blue-50 border border-blue-200 rounded-lg p-3">
                            <div class="flex items-start gap-3">
                                <div class="w-8 h-8 rounded-full bg-blue-100 flex items-center justify-center flex-shrink-0">
                                    <i class="fas fa-check-circle text-blue-600"></i>
                                </div>
                                <div class="flex-1">
                                    <div id="selectedInvoice" class="font-medium text-gray-900"></div>
                                    <div id="selectedCustomer" class="text-sm text-gray-600"></div>
                                    <div id="selectedTotal" class="text-sm font-semibold text-blue-600 mt-1"></div>
                                    <div id="selectedDate" class="text-xs text-gray-500 mt-1"></div>
                                </div>
                                <button type="button" onclick="clearSelectedTransaction()"
                                        class="text-gray-400 hover:text-red-600 transition-colors">
                                    <i class="fas fa-times"></i>
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Informasi Rute -->
            <div class="bg-white/50 rounded-xl p-5 border border-gray-200">
                <h3 class="font-semibold text-gray-900 mb-4 flex items-center gap-2">
                    <i class="fas fa-route text-green-600"></i>
                    Informasi Rute
                </h3>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">
                            Asal Pengiriman <span class="text-red-500">*</span>
                        </label>
                        <div class="relative">
                            <i class="fas fa-map-marker-alt absolute left-3 top-1/2 transform -translate-y-1/2 text-gray-400"></i>
                            <input type="text" name="origin" id="origin" value="{{ old('origin', 'Toko Roni Juntinyuat') }}" required
                                   class="w-full pl-10 pr-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                                   placeholder="Masukkan lokasi asal">
                        </div>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">
                            Tujuan Pengiriman <span class="text-red-500">*</span>
                        </label>
                        <div class="relative">
                            <i class="fas fa-map-marker-alt absolute left-3 top-1/2 transform -translate-y-1/2 text-gray-400"></i>
                            <input type="text" name="destination" id="destination" value="{{ old('destination') }}" required
                                   class="w-full pl-10 pr-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                                   placeholder="Masukkan alamat tujuan">
                        </div>
                    </div>
                </div>
            </div>

            <!-- Informasi Barang -->
            <div class="bg-white/50 rounded-xl p-5 border border-gray-200">
                <h3 class="font-semibold text-gray-900 mb-4 flex items-center gap-2">
                    <i class="fas fa-box text-orange-600"></i>
                    Informasi Barang
                </h3>

                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">
                            Jumlah Item <span class="text-red-500">*</span>
                        </label>
                        <div class="relative">
                            <i class="fas fa-cubes absolute left-3 top-1/2 transform -translate-y-1/2 text-gray-400"></i>
                            <input type="number" name="total_items" id="total_items" value="{{ old('total_items') }}" required min="1"
                                   class="w-full pl-10 pr-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                                   placeholder="Contoh: 10">
                        </div>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">
                            Total Berat (kg)
                        </label>
                        <div class="relative">
                            <i class="fas fa-weight absolute left-3 top-1/2 transform -translate-y-1/2 text-gray-400"></i>
                            <input type="number" name="total_weight" id="total_weight" value="{{ old('total_weight') }}" min="0" step="0.1"
                                   class="w-full pl-10 pr-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                                   placeholder="Contoh: 5.5">
                        </div>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">
                            Total Volume (m³)
                        </label>
                        <div class="relative">
                            <i class="fas fa-cube absolute left-3 top-1/2 transform -translate-y-1/2 text-gray-400"></i>
                            <input type="number" name="total_volume" id="total_volume" value="{{ old('total_volume') }}" min="0" step="0.01"
                                   class="w-full pl-10 pr-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                                   placeholder="Contoh: 0.5">
                        </div>
                    </div>
                </div>
            </div>

            <!-- Informasi Pengiriman -->
            <div class="bg-white/50 rounded-xl p-5 border border-gray-200">
                <h3 class="font-semibold text-gray-900 mb-4 flex items-center gap-2">
                    <i class="fas fa-clock text-purple-600"></i>
                    Informasi Pengiriman
                </h3>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">
                            Estimasi Waktu Pengiriman
                        </label>
                        <div class="relative">
                            <i class="fas fa-calendar-alt absolute left-3 top-1/2 transform -translate-y-1/2 text-gray-400"></i>
                            <input type="datetime-local" name="estimated_delivery_time" id="estimated_delivery_time"
                                   value="{{ old('estimated_delivery_time', now()->addDays(3)->format('Y-m-d\TH:i')) }}"
                                   min="{{ now()->format('Y-m-d\TH:i') }}"
                                   class="w-full pl-10 pr-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                        </div>
                        <p class="text-xs text-gray-500 mt-1">Minimal {{ now()->addDay()->format('d/m/Y H:i') }}</p>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">
                            Status Awal
                        </label>
                        <div class="relative">
                            <i class="fas fa-tag absolute left-3 top-1/2 transform -translate-y-1/2 text-gray-400"></i>
                            <select name="status" class="w-full pl-10 pr-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                                <option value="pending" selected>Pending</option>
                                <option value="processing">Processing</option>
                            </select>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Catatan -->
            <div class="bg-white/50 rounded-xl p-5 border border-gray-200">
                <h3 class="font-semibold text-gray-900 mb-4 flex items-center gap-2">
                    <i class="fas fa-sticky-note text-yellow-600"></i>
                    Catatan
                </h3>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">
                        Catatan Pengiriman
                    </label>
                    <textarea name="notes" rows="3" class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                              placeholder="Masukkan catatan tambahan untuk kurir...">{{ old('notes') }}</textarea>
                </div>
            </div>

            <!-- Tombol Aksi -->
            <div class="flex gap-3 pt-4">
                <button type="submit"
                        class="flex-1 px-6 py-3 bg-gradient-to-r from-blue-600 to-indigo-600 text-white font-semibold rounded-xl hover:from-blue-700 hover:to-indigo-700 transition-all shadow-md hover:shadow-lg flex items-center justify-center gap-2">
                    <i class="fas fa-save"></i>
                    Simpan Pengiriman
                </button>
                <a href="{{ route('delivery.index') }}"
                   class="flex-1 px-6 py-3 border border-gray-300 text-gray-700 font-semibold rounded-xl hover:bg-gray-50 transition-all flex items-center justify-center gap-2">
                    <i class="fas fa-arrow-left"></i>
                    Kembali
                </a>
            </div>
        </form>
    </div>
</div>

<!-- Template untuk hasil pencarian -->
<template id="searchResultTemplate">
    <div class="p-3 hover:bg-blue-50 cursor-pointer border-b border-gray-100 last:border-0 transaction-item" data-id="">
        <div class="flex items-start gap-3">
            <div class="w-8 h-8 rounded-full bg-gray-100 flex items-center justify-center flex-shrink-0">
                <i class="fas fa-receipt text-gray-500"></i>
            </div>
            <div class="flex-1">
                <div class="font-medium text-gray-900 invoice-number"></div>
                <div class="text-sm text-gray-600 customer-name"></div>
                <div class="flex justify-between items-center mt-1">
                    <span class="text-xs text-gray-500 transaction-date"></span>
                    <span class="text-xs font-semibold text-blue-600 transaction-total"></span>
                </div>
            </div>
        </div>
    </div>
</template>

<style>
.transaction-item {
    transition: all 0.2s;
}
.transaction-item:hover {
    background: #f0f7ff;
}
.transaction-item.selected {
    background: #e6f0ff;
    border-left: 3px solid #3b82f6;
}
.spinner {
    border: 2px solid #f3f3f3;
    border-top: 2px solid #3b82f6;
    border-radius: 50%;
    width: 20px;
    height: 20px;
    animation: spin 1s linear infinite;
    display: inline-block;
}

@keyframes spin {
    0% { transform: rotate(0deg); }
    100% { transform: rotate(360deg); }
}
</style>

<script>
let selectedTransaction = null;
let searchTimeout = null;

document.addEventListener('DOMContentLoaded', function() {
    const searchInput = document.getElementById('searchTransaction');
    const searchResults = document.getElementById('searchResults');
    const template = document.getElementById('searchResultTemplate');

    if (!searchInput || !searchResults || !template) {
        console.error('Elements not found');
        return;
    }

    // Search functionality
    searchInput.addEventListener('input', function() {
        clearTimeout(searchTimeout);
        const query = this.value.trim();

        if (query.length < 2) {
            searchResults.classList.add('hidden');
            return;
        }

        // Tampilkan loading
        searchResults.innerHTML = `
            <div class="p-4 text-center text-gray-500">
                <div class="spinner mx-auto mb-2"></div>
                <p>Mencari...</p>
            </div>
        `;
        searchResults.classList.remove('hidden');

        searchTimeout = setTimeout(() => {
            // PERBAIKAN: Gunakan route yang sudah kita buat
            fetch(`/search-transactions?q=${encodeURIComponent(query)}`, {
                method: 'GET',
                headers: {
                    'Accept': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest'
                }
            })
            .then(response => {
                if (!response.ok) {
                    return response.text().then(text => {
                        throw new Error(`HTTP ${response.status}: ${text.substring(0, 100)}`);
                    });
                }
                return response.json();
            })
            .then(data => {
                console.log('Search results:', data);
                
                searchResults.innerHTML = '';

                if (!data || data.length === 0) {
                    searchResults.innerHTML = `
                        <div class="p-4 text-center text-gray-500">
                            <i class="fas fa-search mb-2 text-2xl"></i>
                            <p>Tidak ada transaksi ditemukan</p>
                        </div>
                    `;
                } else {
                    data.forEach(transaction => {
                        const item = document.importNode(template.content, true);

                        // Set data
                        const itemDiv = item.querySelector('.transaction-item');
                        if (itemDiv) itemDiv.dataset.id = transaction.id;

                        const invoiceNumber = item.querySelector('.invoice-number');
                        if (invoiceNumber) invoiceNumber.textContent = transaction.invoice_number;

                        const customerName = item.querySelector('.customer-name');
                        if (customerName) customerName.textContent = transaction.customer_name;

                        const transactionDate = item.querySelector('.transaction-date');
                        if (transactionDate) transactionDate.textContent = transaction.date;

                        const transactionTotal = item.querySelector('.transaction-total');
                        if (transactionTotal) transactionTotal.textContent = 'Rp ' + transaction.total_formatted;

                        // Add click event
                        const clickableItem = item.querySelector('.transaction-item');
                        if (clickableItem) {
                            clickableItem.addEventListener('click', function() {
                                selectTransaction(transaction);
                                searchResults.classList.add('hidden');
                                searchInput.value = transaction.invoice_number + ' - ' + transaction.customer_name;
                            });
                        }

                        searchResults.appendChild(item);
                    });
                }
            })
            .catch(error => {
                console.error('Search error:', error);
                searchResults.innerHTML = `
                    <div class="p-4 text-center text-red-500">
                        <i class="fas fa-exclamation-circle mb-2 text-2xl"></i>
                        <p>Gagal memuat data: ${error.message}</p>
                    </div>
                `;
            });
        }, 500);
    });

    // Close search results when clicking outside
    document.addEventListener('click', function(e) {
        if (searchInput && searchResults && 
            !searchInput.contains(e.target) && 
            !searchResults.contains(e.target)) {
            searchResults.classList.add('hidden');
        }
    });

    // Form validation
    document.getElementById('deliveryForm').addEventListener('submit', function(e) {
        if (!selectedTransaction) {
            e.preventDefault();
            alert('Silakan pilih transaksi terlebih dahulu');
            searchInput.focus();
        }
    });

    // Set default origin
    const originInput = document.getElementById('origin');
    if (originInput && !originInput.value) {
        originInput.value = 'Toko Roni Juntinyuat';
    }
});

function selectTransaction(transaction) {
    console.log('Selected transaction:', transaction);
    
    selectedTransaction = transaction;

    document.getElementById('transaction_id').value = transaction.id;
    document.getElementById('selectedInvoice').textContent = transaction.invoice_number;
    document.getElementById('selectedCustomer').textContent = 'Customer: ' + transaction.customer_name;
    document.getElementById('selectedTotal').textContent = 'Total: Rp ' + transaction.total_formatted;
    document.getElementById('selectedDate').textContent = 'Tanggal: ' + transaction.date;

    document.getElementById('selectedTransactionInfo').classList.remove('hidden');

    // Auto-fill total items (DARI KODE 2)
    const totalItems = document.getElementById('total_items');
    if (totalItems && transaction.total_items) {
        totalItems.value = transaction.total_items;
    }

    // Auto-fill destination if empty (bisa diambil dari alamat customer) - DARI KODE 2
    const destination = document.getElementById('destination');
    if (destination && !destination.value && transaction.customer_address) {
        destination.value = transaction.customer_address;
    }
}

function clearSelectedTransaction() {
    selectedTransaction = null;
    document.getElementById('transaction_id').value = '';
    document.getElementById('selectedTransactionInfo').classList.add('hidden');
    
    const searchInput = document.getElementById('searchTransaction');
    if (searchInput) {
        searchInput.value = '';
        searchInput.focus();
    }
    
    // Reset destination jika diperlukan - DARI KODE 2
    // Biarkan destination tetap jika sudah diisi manual
}
</script>
@endsection