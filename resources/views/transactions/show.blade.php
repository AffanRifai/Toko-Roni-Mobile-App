@extends('layouts.app')

@section('title', 'Detail Transaksi #' . $transaction->invoice_number)

@section('content')
    <div class="min-h-screen bg-gray-50">
        <div class="container mx-auto px-4 py-6 sm:px-6 lg:px-8">
            <!-- Header -->
            <div class="mb-6 bg-white rounded-xl shadow-sm p-4 md:p-6">
                <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
                    <div class="flex items-center">
                        <div class="bg-blue-100 p-3 rounded-full mr-4">
                            <i class="fas fa-receipt text-blue-600 text-xl"></i>
                        </div>
                        <div>
                            <h1 class="text-2xl font-bold text-gray-900 mb-1">Detail Transaksi</h1>
                            <p class="text-gray-600">
                                <i class="fas fa-calendar-alt mr-2"></i>
                                {{ $transaction->created_at->format('d F Y, H:i') }}
                            </p>
                        </div>
                    </div>
                    <div class="bg-blue-50 border border-blue-200 rounded-full px-4 py-2 flex items-center gap-3">
                        <span class="font-bold text-blue-700">
                            <i class="fas fa-hashtag mr-2"></i>
                            {{ $transaction->invoice_number }}
                        </span>
                        @if ($transaction->delivery)
                            <span class="px-2 py-1 bg-green-100 text-green-700 rounded-full text-xs font-medium">
                                <i class="fas fa-truck mr-1"></i>
                                {{ str_replace('_', ' ', ucfirst($transaction->delivery->status)) }}
                            </span>
                        @endif
                        @if ($transaction->need_delivery && !$transaction->delivery)
                            <span class="px-2 py-1 bg-yellow-100 text-yellow-700 rounded-full text-xs font-medium">
                                <i class="fas fa-clock mr-1"></i>
                                Menunggu Pengiriman
                            </span>
                        @endif
                    </div>
                </div>
            </div>

            <div class="flex flex-col lg:flex-row gap-6">
                <!-- Main Content -->
                <div class="lg:w-2/3 space-y-6">
                    <!-- Informasi Toko dan Pelanggan -->
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <!-- Informasi Toko -->
                        <div class="bg-white rounded-xl shadow-sm p-5">
                            <div class="flex items-center mb-4">
                                <div class="bg-cyan-100 p-2 rounded-full mr-3">
                                    <i class="fas fa-store text-cyan-600"></i>
                                </div>
                                <h2 class="font-bold text-gray-900">Informasi Toko</h2>
                            </div>
                            <div class="space-y-3">
                                <div class="flex justify-between items-center">
                                    <span class="text-gray-600">Kasir</span>
                                    <span class="font-medium text-gray-900">{{ $transaction->user->name ?? 'N/A' }}</span>
                                </div>
                                <div class="flex justify-between items-center">
                                    <span class="text-gray-600">Status</span>
                                    <span class="bg-green-100 text-green-800 text-sm font-medium px-3 py-1 rounded-full">
                                        <i class="fas fa-check-circle mr-1"></i>
                                        Selesai
                                    </span>
                                </div>
                            </div>
                        </div>

                        <!-- Informasi Pelanggan -->
                        <div class="bg-white rounded-xl shadow-sm p-5">
                            <div class="flex items-center mb-4">
                                <div class="bg-yellow-100 p-2 rounded-full mr-3">
                                    <i class="fas fa-user text-yellow-600"></i>
                                </div>
                                <h2 class="font-bold text-gray-900">Informasi Pelanggan</h2>
                            </div>
                            <div class="space-y-3">
                                <div class="flex justify-between items-center">
                                    <span class="text-gray-600">Nama</span>
                                    <span class="font-medium text-gray-900">{{ $transaction->customer_name }}</span>
                                </div>
                                <div class="flex justify-between items-center">
                                    <span class="text-gray-600">Telepon</span>
                                    <span class="font-medium text-gray-900">{{ $transaction->customer_phone ?? '-' }}</span>
                                </div>
                                <div class="flex justify-between items-center">
                                    <span class="text-gray-600">Pembayaran</span>
                                    @php
                                        $paymentColors = [
                                            'cash' => [
                                                'bg' => 'bg-green-100',
                                                'text' => 'text-green-800',
                                                'border' => 'border-green-200',
                                            ],
                                            'debit_card' => [
                                                'bg' => 'bg-blue-100',
                                                'text' => 'text-blue-800',
                                                'border' => 'border-blue-200',
                                            ],
                                            'credit_card' => [
                                                'bg' => 'bg-cyan-100',
                                                'text' => 'text-cyan-800',
                                                'border' => 'border-cyan-200',
                                            ],
                                            'e_wallet' => [
                                                'bg' => 'bg-purple-100',
                                                'text' => 'text-purple-800',
                                                'border' => 'border-purple-200',
                                            ],
                                        ];
                                        $paymentIcons = [
                                            'cash' => 'money-bill-wave',
                                            'debit_card' => 'credit-card',
                                            'credit_card' => 'credit-card',
                                            'e_wallet' => 'mobile-alt',
                                        ];
                                        $paymentConfig = $paymentColors[$transaction->payment_method] ?? [
                                            'bg' => 'bg-gray-100',
                                            'text' => 'text-gray-800',
                                            'border' => 'border-gray-200',
                                        ];
                                        $paymentIcon = $paymentIcons[$transaction->payment_method] ?? 'credit-card';
                                    @endphp
                                    <span
                                        class="px-3 py-1 rounded-full text-sm font-medium {{ $paymentConfig['bg'] }} {{ $paymentConfig['text'] }} border {{ $paymentConfig['border'] }}">
                                        <i class="fas fa-{{ $paymentIcon }} mr-1"></i>
                                        {{ ucfirst(str_replace('_', ' ', $transaction->payment_method)) }}
                                    </span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Daftar Produk dengan Status Pengiriman -->
                    <div class="bg-white rounded-xl shadow-sm overflow-hidden">
                        <div class="border-b border-gray-200 px-5 py-4">
                            <div class="flex justify-between items-center">
                                <h2 class="font-bold text-gray-900 text-lg">
                                    <i class="fas fa-shopping-cart text-blue-600 mr-2"></i>
                                    Daftar Produk
                                </h2>
                                <span class="bg-blue-100 text-blue-700 px-3 py-1 rounded-full text-sm font-medium">
                                    {{ $transaction->items->count() }} Item
                                </span>
                            </div>
                        </div>
                        <div class="overflow-x-auto">
                            <table class="w-full">
                                <thead class="bg-gray-50">
                                    <tr>
                                        <th
                                            class="text-left py-3 px-4 text-sm font-semibold text-gray-700 uppercase tracking-wider">
                                            #</th>
                                        <th
                                            class="text-left py-3 px-4 text-sm font-semibold text-gray-700 uppercase tracking-wider">
                                            Produk</th>
                                        <th
                                            class="text-center py-3 px-4 text-sm font-semibold text-gray-700 uppercase tracking-wider">
                                            Harga</th>
                                        <th
                                            class="text-center py-3 px-4 text-sm font-semibold text-gray-700 uppercase tracking-wider">
                                            Qty</th>
                                        <th
                                            class="text-right py-3 px-4 text-sm font-semibold text-gray-700 uppercase tracking-wider">
                                            Subtotal</th>
                                        @if ($transaction->need_delivery)
                                            <th
                                                class="text-center py-3 px-4 text-sm font-semibold text-gray-700 uppercase tracking-wider">
                                                Status</th>
                                        @endif
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-gray-200">
                                    @foreach ($transaction->items as $item)
                                        @php
                                            $isDelivered =
                                                $transaction->items_to_deliver_list &&
                                                $transaction->items_to_deliver_list->contains('id', $item->product_id);
                                            $isTaken =
                                                $transaction->items_taken_list &&
                                                $transaction->items_taken_list->contains('id', $item->product_id);
                                        @endphp
                                        <tr class="hover:bg-gray-50 transition-colors duration-150">
                                            <td class="py-4 px-4 text-gray-500 font-medium">{{ $loop->iteration }}</td>
                                            <td class="py-4 px-4">
                                                <div class="flex items-center">
                                                    <div class="bg-gray-100 p-2 rounded-full mr-3">
                                                        <i class="fas fa-box text-gray-500"></i>
                                                    </div>
                                                    <div>
                                                        <div class="font-semibold text-gray-900">
                                                            {{ $item->product->name ?? 'Produk tidak ditemukan' }}</div>
                                                        <div class="text-sm text-gray-500">
                                                            <i class="fas fa-barcode mr-1"></i>
                                                            {{ $item->product->code ?? 'N/A' }}
                                                        </div>
                                                    </div>
                                                </div>
                                            </td>
                                            <td class="py-4 px-4 text-center">
                                                <span class="font-semibold text-gray-900">
                                                    Rp {{ number_format($item->price, 0, ',', '.') }}
                                                </span>
                                            </td>
                                            <td class="py-4 px-4 text-center">
                                                <span
                                                    class="bg-blue-100 text-blue-700 px-2 py-1 rounded text-sm font-semibold">
                                                    {{ $item->qty }}
                                                </span>
                                            </td>
                                            <td class="py-4 px-4 text-right">
                                                <span class="font-bold text-gray-900">
                                                    Rp {{ number_format($item->subtotal, 0, ',', '.') }}
                                                </span>
                                            </td>
                                            @if ($transaction->need_delivery)
                                                <td class="py-4 px-4 text-center">
                                                    @if ($isDelivered)
                                                        <span
                                                            class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
                                                            <i class="fas fa-truck mr-1"></i> Dikirim
                                                        </span>
                                                    @elseif($isTaken)
                                                        <span
                                                            class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                                            <i class="fas fa-shopping-bag mr-1"></i> Dibawa
                                                        </span>
                                                    @else
                                                        <span
                                                            class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-gray-100 text-gray-800">
                                                            <i class="fas fa-store mr-1"></i> Di Toko
                                                        </span>
                                                    @endif
                                                </td>
                                            @endif
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>

                    <!-- Informasi Pengiriman (Jika perlu dikirim) -->
                    @if ($transaction->need_delivery)
                        <div class="bg-white rounded-xl shadow-sm overflow-hidden">
                            <div class="border-b border-gray-200 px-5 py-4 bg-gradient-to-r from-blue-50 to-indigo-50">
                                <h2 class="font-bold text-gray-900 text-lg flex items-center gap-2">
                                    <i class="fas fa-truck text-blue-600"></i>
                                    Informasi Pengiriman
                                </h2>
                            </div>
                            <div class="p-5">
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                    <!-- Data Penerima -->
                                    <div class="space-y-3">
                                        <h3 class="font-semibold text-gray-800 flex items-center gap-2">
                                            <i class="fas fa-user-circle text-blue-500"></i>
                                            Data Penerima
                                        </h3>
                                        <div class="bg-gray-50 rounded-lg p-4 space-y-2">
                                            <div class="flex justify-between">
                                                <span class="text-gray-600">Nama:</span>
                                                <span
                                                    class="font-medium text-gray-900">{{ $transaction->recipient_name ?? $transaction->customer_name }}</span>
                                            </div>
                                            <div class="flex justify-between">
                                                <span class="text-gray-600">Telepon:</span>
                                                <span
                                                    class="font-medium text-gray-900">{{ $transaction->recipient_phone ?? ($transaction->customer_phone ?? '-') }}</span>
                                            </div>
                                            <div class="flex justify-between">
                                                <span class="text-gray-600">Alamat:</span>
                                                <span
                                                    class="font-medium text-gray-900 text-right">{{ $transaction->delivery_address ?? '-' }}</span>
                                            </div>
                                            @if ($transaction->desired_delivery_date)
                                                <div class="flex justify-between">
                                                    <span class="text-gray-600">Tanggal:</span>
                                                    <span
                                                        class="font-medium text-gray-900">{{ \Carbon\Carbon::parse($transaction->desired_delivery_date)->format('d/m/Y') }}</span>
                                                </div>
                                            @endif
                                        </div>
                                    </div>

                                    <!-- Ringkasan Barang -->
                                    <div class="space-y-3">
                                        <h3 class="font-semibold text-gray-800 flex items-center gap-2">
                                            <i class="fas fa-boxes text-green-500"></i>
                                            Ringkasan Barang
                                        </h3>
                                        <div class="bg-gray-50 rounded-lg p-4">
                                            <div
                                                class="flex justify-between items-center mb-3 pb-2 border-b border-gray-200">
                                                <span class="text-gray-600">Barang Dikirim:</span>
                                                <span
                                                    class="font-semibold text-blue-600">{{ $transaction->total_items_to_deliver ?? 0 }}
                                                    item</span>
                                            </div>
                                            @if ($transaction->items_to_deliver_list && $transaction->items_to_deliver_list->isNotEmpty())
                                                <div class="space-y-1 mb-3">
                                                    @foreach ($transaction->items_to_deliver_list as $item)
                                                        <div class="flex justify-between text-sm">
                                                            <span
                                                                class="text-gray-700">{{ $item['name'] ?? 'Produk' }}</span>
                                                            <span
                                                                class="font-medium text-gray-900">{{ $item['qty'] ?? 0 }}
                                                                pcs</span>
                                                        </div>
                                                    @endforeach
                                                </div>
                                            @endif

                                            <div
                                                class="flex justify-between items-center my-3 py-2 border-t border-gray-200">
                                                <span class="text-gray-600">Barang Dibawa:</span>
                                                <span
                                                    class="font-semibold text-green-600">{{ $transaction->total_items_taken ?? 0 }}
                                                    item</span>
                                            </div>
                                            @if ($transaction->items_taken_list && $transaction->items_taken_list->isNotEmpty())
                                                <div class="space-y-1">
                                                    @foreach ($transaction->items_taken_list as $item)
                                                        <div class="flex justify-between text-sm">
                                                            <span
                                                                class="text-gray-700">{{ $item['name'] ?? 'Produk' }}</span>
                                                            <span
                                                                class="font-medium text-gray-900">{{ $item['qty'] ?? 0 }}
                                                                pcs</span>
                                                        </div>
                                                    @endforeach
                                                </div>
                                            @endif
                                        </div>
                                    </div>

                                    <!-- Biaya & Catatan -->
                                    @if (($transaction->delivery_fee ?? 0) > 0 || $transaction->delivery_notes)
                                        <div class="md:col-span-2 space-y-3">
                                            @if (($transaction->delivery_fee ?? 0) > 0)
                                                <div class="flex justify-between items-center bg-blue-50 rounded-lg p-3">
                                                    <span class="text-gray-700 font-medium">Biaya Pengiriman:</span>
                                                    <span class="font-bold text-blue-600 text-lg">Rp
                                                        {{ number_format($transaction->delivery_fee, 0, ',', '.') }}</span>
                                                </div>
                                            @endif

                                            @if ($transaction->delivery_notes)
                                                <div class="bg-yellow-50 rounded-lg p-3">
                                                    <span class="text-gray-700 font-medium block mb-1">Catatan:</span>
                                                    <p class="text-gray-800">{{ $transaction->delivery_notes }}</p>
                                                </div>
                                            @endif
                                        </div>
                                    @endif
                                </div>
                            </div>
                        </div>
                    @endif

                    <!-- Ringkasan Pembayaran -->
                    <div class="bg-white rounded-xl shadow-sm">
                        <div class="border-b border-gray-200 px-5 py-4">
                            <h2 class="font-bold text-gray-900 text-lg">
                                <i class="fas fa-file-invoice-dollar text-blue-600 mr-2"></i>
                                Ringkasan Pembayaran
                            </h2>
                        </div>
                        <div class="p-5">
                            <div class="max-w-2xl mx-auto">
                                <div class="bg-gradient-to-br from-white to-gray-50 rounded-lg border border-gray-200 p-6">
                                    <!-- Subtotal -->
                                    <div class="flex justify-between items-center py-2">
                                        <span class="text-gray-600">Subtotal</span>
                                        <span class="font-medium text-gray-900">
                                            Rp {{ number_format($transaction->items->sum('subtotal'), 0, ',', '.') }}
                                        </span>
                                    </div>

                                    <!-- Discount -->
                                    @if ($transaction->discount > 0)
                                        <div class="flex justify-between items-center py-2 text-red-600">
                                            <span>Diskon ({{ $transaction->discount }}%)</span>
                                            <span class="font-medium">
                                                - Rp
                                                {{ number_format(($transaction->items->sum('subtotal') * $transaction->discount) / 100, 0, ',', '.') }}
                                            </span>
                                        </div>
                                    @endif

                                    <!-- Delivery Fee -->
                                    @if (($transaction->delivery_fee ?? 0) > 0)
                                        <div class="flex justify-between items-center py-2">
                                            <span class="text-gray-600">Biaya Pengiriman</span>
                                            <span class="font-medium text-blue-600">
                                                + Rp {{ number_format($transaction->delivery_fee, 0, ',', '.') }}
                                            </span>
                                        </div>
                                    @endif

                                    <!-- Total -->
                                    <div
                                        class="flex justify-between items-center py-4 border-t border-b border-gray-200 my-2">
                                        <span class="font-bold text-lg text-gray-900">Total Bayar</span>
                                        <span class="font-bold text-2xl text-blue-600">
                                            Rp
                                            {{ number_format($transaction->total_amount + ($transaction->delivery_fee ?? 0), 0, ',', '.') }}
                                        </span>
                                    </div>

                                    <!-- Cash Payment Details -->
                                    @if ($transaction->payment_method === 'cash')
                                        <div class="mt-6">
                                            <div class="flex justify-between items-center py-2">
                                                <span class="text-gray-600">Cash Diterima</span>
                                                <span class="font-medium text-gray-900">
                                                    Rp {{ number_format($transaction->cash_received, 0, ',', '.') }}
                                                </span>
                                            </div>
                                            <div
                                                class="flex justify-between items-center py-4 bg-green-50 rounded-lg px-4">
                                                <span class="font-bold text-green-700">Kembalian</span>
                                                <span class="font-bold text-xl text-green-700">
                                                    <i class="fas fa-arrow-right mr-2"></i>
                                                    Rp {{ number_format($transaction->change, 0, ',', '.') }}
                                                </span>
                                            </div>
                                        </div>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Sidebar -->
                <div class="lg:w-1/3 space-y-6">
                    <!-- Success Alert -->
                    @if (session('success'))
                        <div class="bg-green-50 border-l-4 border-green-500 p-4 rounded-lg shadow-sm">
                            <div class="flex items-center">
                                <div class="flex-shrink-0 mr-3">
                                    <i class="fas fa-check-circle text-green-600 text-xl"></i>
                                </div>
                                <div class="flex-1">
                                    <h3 class="font-bold text-green-800 mb-1">Sukses!</h3>
                                    <p class="text-green-700">{{ session('success') }}</p>
                                </div>
                            </div>
                        </div>
                    @endif

                    <!-- Error Alert -->
                    @if (session('error'))
                        <div class="bg-red-50 border-l-4 border-red-500 p-4 rounded-lg shadow-sm">
                            <div class="flex items-center">
                                <div class="flex-shrink-0 mr-3">
                                    <i class="fas fa-exclamation-circle text-red-600 text-xl"></i>
                                </div>
                                <div class="flex-1">
                                    <h3 class="font-bold text-red-800 mb-1">Error!</h3>
                                    <p class="text-red-700">{{ session('error') }}</p>
                                </div>
                            </div>
                        </div>
                    @endif

                    <!-- ========== FITUR PENGIRIMAN ========== -->
                    @if (auth()->user()->role === 'kasir' || auth()->user()->role === 'owner')
                        @if ($transaction->need_delivery && !$transaction->delivery)
                            <!-- Form Konfirmasi Pengiriman (Jika sudah dicatat saat transaksi) -->
                            <div class="bg-white rounded-xl shadow-sm p-5 border-l-4 border-green-500">
                                <h3 class="font-bold text-gray-900 mb-4">
                                    <i class="fas fa-check-circle text-green-500 mr-2"></i>
                                    Pengiriman Tercatat
                                </h3>
                                <div class="space-y-3">
                                    <p class="text-sm text-gray-600">
                                        Pengiriman sudah dicatat saat transaksi. Menunggu diproses oleh logistik.
                                    </p>
                                    <div class="bg-blue-50 rounded-lg p-3 text-sm">
                                        <i class="fas fa-info-circle text-blue-600 mr-2"></i>
                                        Status: <span class="font-semibold text-blue-700">Menunggu Konfirmasi
                                            Logistik</span>
                                    </div>
                                    <button onclick="createDeliveryRequest()"
                                        class="w-full bg-blue-600 hover:bg-blue-700 text-white font-semibold py-3 px-4 rounded-lg transition-colors">
                                        <i class="fas fa-paper-plane mr-2"></i>
                                        Kirim ke Logistik
                                    </button>
                                </div>
                            </div>
                        @elseif(!$transaction->need_delivery && !$transaction->delivery)
                            <!-- Form Pengiriman (Hanya untuk Kasir/Owner) -->
                            <div class="bg-white rounded-xl shadow-sm p-5 border-l-4 border-blue-500">
                                <h3 class="font-bold text-gray-900 mb-4">
                                    <i class="fas fa-truck text-blue-500 mr-2"></i>
                                    Opsi Pengiriman
                                </h3>

                                <form id="deliveryForm" action="{{ route('delivery.request', $transaction) }}"
                                    method="POST" class="space-y-4">
                                    @csrf

                                    <!-- Pilih Jenis Pengiriman -->
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 mb-3">
                                            Apakah perlu dikirim?
                                        </label>
                                        <div class="space-y-3">
                                            <label
                                                class="flex items-center p-3 border rounded-lg cursor-pointer hover:bg-gray-50 transition-colors">
                                                <input type="radio" name="need_delivery" value="yes" class="mr-3"
                                                    onchange="toggleDeliveryOptions(true)">
                                                <div>
                                                    <span class="font-medium text-gray-900">Ya, perlu dikirim</span>
                                                    <p class="text-xs text-gray-500">Pesanan akan diantar ke alamat
                                                        pelanggan</p>
                                                </div>
                                            </label>

                                            <label
                                                class="flex items-center p-3 border rounded-lg cursor-pointer hover:bg-gray-50 transition-colors">
                                                <input type="radio" name="need_delivery" value="no" class="mr-3"
                                                    onchange="toggleDeliveryOptions(false)" checked>
                                                <div>
                                                    <span class="font-medium text-gray-900">Tidak perlu dikirim</span>
                                                    <p class="text-xs text-gray-500">Pelanggan ambil sendiri di toko</p>
                                                </div>
                                            </label>
                                        </div>
                                    </div>

                                    <!-- Delivery Options (Hidden by default) -->
                                    <div id="deliveryOptions" class="space-y-4 hidden">
                                        <!-- Data Penerima -->
                                        <div class="grid grid-cols-2 gap-3">
                                            <div>
                                                <label class="block text-sm font-medium text-gray-700 mb-2">
                                                    Nama Penerima
                                                </label>
                                                <input type="text" name="recipient_name" id="recipient_name"
                                                    value="{{ $transaction->customer_name }}"
                                                    class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500">
                                            </div>
                                            <div>
                                                <label class="block text-sm font-medium text-gray-700 mb-2">
                                                    No. Telepon
                                                </label>
                                                <input type="text" name="recipient_phone" id="recipient_phone"
                                                    value="{{ $transaction->customer_phone }}"
                                                    class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500">
                                            </div>
                                        </div>

                                        <div>
                                            <label class="block text-sm font-medium text-gray-700 mb-2">
                                                Alamat Pengiriman
                                            </label>
                                            <textarea name="delivery_address" id="delivery_address" rows="2"
                                                class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500"
                                                placeholder="Masukkan alamat lengkap">{{ $transaction->customer_address ?? '' }}</textarea>
                                        </div>

                                        <div>
                                            <label class="block text-sm font-medium text-gray-700 mb-2">
                                                Tanggal Pengiriman
                                            </label>
                                            <input type="date" name="desired_delivery_date" id="desired_delivery_date"
                                                value="{{ now()->addDays(2)->format('Y-m-d') }}"
                                                min="{{ now()->addDay()->format('Y-m-d') }}"
                                                class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500">
                                        </div>

                                        <!-- Pilih Barang yang Akan Dikirim -->
                                        <div>
                                            <label class="block text-sm font-medium text-gray-700 mb-2">
                                                Barang yang Akan Dikirim
                                            </label>
                                            <div class="bg-gray-50 rounded-lg p-3 max-h-48 overflow-y-auto">
                                                @foreach ($transaction->items as $item)
                                                    <label
                                                        class="flex items-center p-2 hover:bg-white rounded cursor-pointer">
                                                        <input type="checkbox" name="items_to_deliver[]"
                                                            value="{{ $item->product_id }}"
                                                            class="mr-3 text-blue-600 rounded item-checkbox">
                                                        <span
                                                            class="flex-1 text-sm">{{ $item->product->name ?? 'Produk' }}</span>
                                                        <span
                                                            class="text-sm font-medium text-gray-700">{{ $item->qty }}
                                                            pcs</span>
                                                    </label>
                                                @endforeach
                                            </div>
                                            <p class="text-xs text-gray-500 mt-1">*Kosongkan jika semua barang akan dikirim
                                            </p>
                                        </div>

                                        <div>
                                            <label class="block text-sm font-medium text-gray-700 mb-2">
                                                Pilih Kendaraan
                                            </label>
                                            <select name="vehicle_type" id="vehicle_type"
                                                class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500">
                                                <option value="motor">Motor</option>
                                                <option value="mobil">Mobil</option>
                                                <option value="truck">Truck</option>
                                            </select>
                                        </div>

                                        <div>
                                            <label class="block text-sm font-medium text-gray-700 mb-2">
                                                Biaya Pengiriman (Rp)
                                            </label>
                                            <input type="number" name="delivery_fee" id="delivery_fee" min="0"
                                                step="1000" value="0"
                                                class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500">
                                        </div>

                                        <div>
                                            <label class="block text-sm font-medium text-gray-700 mb-2">
                                                Catatan Pengiriman
                                            </label>
                                            <textarea name="delivery_notes" id="delivery_notes" rows="2"
                                                class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500"
                                                placeholder="Catatan tambahan untuk kurir"></textarea>
                                        </div>

                                        <div
                                            class="bg-yellow-50 border border-yellow-200 rounded-lg p-3 text-sm text-yellow-800">
                                            <i class="fas fa-info-circle mr-2"></i>
                                            Pesanan akan diteruskan ke bagian logistik untuk ditugaskan ke kurir.
                                        </div>
                                    </div>

                                    <button type="submit"
                                        class="w-full bg-blue-600 hover:bg-blue-700 text-white font-semibold py-3 px-4 rounded-lg transition-colors">
                                        <i class="fas fa-paper-plane mr-2"></i>
                                        Kirim ke Logistik
                                    </button>
                                </form>
                            </div>
                        @elseif($transaction->delivery)
                            <!-- Status Pengiriman (Jika sudah ada delivery) -->
                            <div class="bg-white rounded-xl shadow-sm p-5 border-l-4 border-green-500">
                                <h3 class="font-bold text-gray-900 mb-4 flex items-center justify-between">
                                    <span>
                                        <i class="fas fa-truck text-green-500 mr-2"></i>
                                        Status Pengiriman
                                    </span>
                                    <span
                                        class="px-2 py-1 rounded-full text-xs font-medium
                                        @if ($transaction->delivery->status == 'delivered') bg-green-100 text-green-800
                                        @elseif($transaction->delivery->status == 'on_delivery') bg-orange-100 text-orange-800
                                        @elseif($transaction->delivery->status == 'pending') bg-yellow-100 text-yellow-800
                                        @else bg-gray-100 text-gray-800 @endif">
                                        <i
                                            class="fas
                                        @if ($transaction->delivery->status == 'delivered') fa-check-circle
                                        @elseif($transaction->delivery->status == 'on_delivery') fa-truck
                                        @elseif($transaction->delivery->status == 'pending') fa-clock
                                        @else fa-info-circle @endif mr-1"></i>
                                        {{ ucfirst(str_replace('_', ' ', $transaction->delivery->status)) }}
                                    </span>
                                </h3>

                                <div class="space-y-4">
                                    <!-- Timeline Pengiriman -->
                                    <div class="relative">
                                        <div class="absolute left-4 top-0 bottom-0 w-0.5 bg-gray-200"></div>

                                        <!-- Pesanan Diterima -->
                                        <div class="flex items-start mb-6 relative">
                                            <div
                                                class="w-8 h-8 rounded-full bg-green-500 flex items-center justify-center text-white z-10">
                                                <i class="fas fa-check text-sm"></i>
                                            </div>
                                            <div class="ml-4 flex-1">
                                                <h4 class="font-semibold text-gray-900">Pesanan Diterima</h4>
                                                <p class="text-xs text-gray-500">
                                                    {{ $transaction->delivery->created_at ? $transaction->delivery->created_at->format('d/m/Y H:i') : '-' }}
                                                </p>
                                            </div>
                                        </div>

                                        <!-- Diproses Logistik -->
                                        <div class="flex items-start mb-6 relative">
                                            <div
                                                class="w-8 h-8 rounded-full {{ !in_array($transaction->delivery->status, ['pending']) ? 'bg-green-500' : 'bg-gray-300' }} flex items-center justify-center text-white z-10">
                                                <i
                                                    class="fas {{ !in_array($transaction->delivery->status, ['pending']) ? 'fa-check' : 'fa-clock' }} text-sm"></i>
                                            </div>
                                            <div class="ml-4 flex-1">
                                                <h4 class="font-semibold text-gray-900">Diproses Logistik</h4>
                                                @if ($transaction->delivery->updated_at && $transaction->delivery->status != 'pending')
                                                    <p class="text-xs text-gray-500">
                                                        {{ $transaction->delivery->updated_at->format('d/m/Y H:i') }}</p>
                                                @endif
                                            </div>
                                        </div>

                                        <!-- Ditugaskan ke Kurir -->
                                        <div class="flex items-start mb-6 relative">
                                            <div
                                                class="w-8 h-8 rounded-full {{ $transaction->delivery->driver_id ? 'bg-green-500' : 'bg-gray-300' }} flex items-center justify-center text-white z-10">
                                                <i
                                                    class="fas {{ $transaction->delivery->driver_id ? 'fa-check' : 'fa-user' }} text-sm"></i>
                                            </div>
                                            <div class="ml-4 flex-1">
                                                <h4 class="font-semibold text-gray-900">Ditugaskan ke Kurir</h4>
                                                @if ($transaction->delivery->driver)
                                                    <p class="text-sm text-gray-800">
                                                        {{ $transaction->delivery->driver->name ?? 'N/A' }}</p>
                                                    <p class="text-xs text-gray-500">
                                                        {{ $transaction->delivery->driver->phone ?? '-' }}</p>
                                                @endif
                                            </div>
                                        </div>

                                        <!-- Dalam Perjalanan -->
                                        @if (in_array($transaction->delivery->status, ['on_delivery', 'delivered']))
                                            <div class="flex items-start mb-6 relative">
                                                <div
                                                    class="w-8 h-8 rounded-full {{ $transaction->delivery->status == 'delivered' ? 'bg-green-500' : 'bg-orange-500' }} flex items-center justify-center text-white z-10">
                                                    <i class="fas fa-truck text-sm"></i>
                                                </div>
                                                <div class="ml-4 flex-1">
                                                    <h4 class="font-semibold text-gray-900">Dalam Perjalanan</h4>
                                                    @if ($transaction->delivery->start_delivery_time)
                                                        <p class="text-xs text-gray-500">
                                                            {{ \Carbon\Carbon::parse($transaction->delivery->start_delivery_time)->format('d/m/Y H:i') }}
                                                        </p>
                                                    @endif
                                                </div>
                                            </div>
                                        @endif

                                        <!-- Terkirim -->
                                        <div class="flex items-start relative">
                                            <div
                                                class="w-8 h-8 rounded-full {{ $transaction->delivery->status == 'delivered' ? 'bg-green-500' : 'bg-gray-300' }} flex items-center justify-center text-white z-10">
                                                <i
                                                    class="fas {{ $transaction->delivery->status == 'delivered' ? 'fa-check' : 'fa-truck' }} text-sm"></i>
                                            </div>
                                            <div class="ml-4 flex-1">
                                                <h4 class="font-semibold text-gray-900">Terkirim</h4>
                                                @if ($transaction->delivery->delivered_at)
                                                    <p class="text-xs text-gray-500">
                                                        {{ \Carbon\Carbon::parse($transaction->delivery->delivered_at)->format('d/m/Y H:i') }}
                                                    </p>
                                                @endif
                                                @if ($transaction->delivery->recipient_name)
                                                    <p class="text-xs text-gray-600 mt-1">Penerima:
                                                        {{ $transaction->delivery->recipient_name }}</p>
                                                @endif
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Info Pengiriman -->
                                    <div class="bg-gray-50 rounded-lg p-4 space-y-3">
                                        <div class="flex justify-between text-sm">
                                            <span class="text-gray-600">Kendaraan:</span>
                                            <span
                                                class="font-medium text-gray-900">{{ ucfirst($transaction->delivery->vehicle_type ?? 'motor') }}</span>
                                        </div>
                                        <div class="flex justify-between text-sm">
                                            <span class="text-gray-600">Alamat:</span>
                                            <span
                                                class="font-medium text-gray-900 text-right">{{ $transaction->delivery->address ?? ($transaction->delivery->destination ?? '-') }}</span>
                                        </div>
                                        @if ($transaction->delivery->notes)
                                            <div class="text-sm">
                                                <span class="text-gray-600">Catatan:</span>
                                                <p class="text-gray-800 mt-1 bg-white p-2 rounded">
                                                    {{ $transaction->delivery->notes }}</p>
                                            </div>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        @endif
                    @endif

                    <!-- Action Buttons -->
                    <div class="bg-white rounded-xl shadow-sm p-5">
                        <h3 class="font-bold text-gray-900 mb-4">
                            <i class="fas fa-bolt text-yellow-500 mr-2"></i>
                            Aksi Cepat
                        </h3>
                        <div class="space-y-3">
                            <a href="{{ route('transactions.create') }}"
                                class="w-full bg-gradient-to-r from-blue-600 to-blue-700 text-white font-semibold py-3 px-4 rounded-lg hover:from-blue-700 hover:to-blue-800 transition-all duration-300 shadow-md hover:shadow-lg flex items-center justify-center">
                                <i class="fas fa-plus-circle mr-2"></i>
                                Transaksi Baru
                            </a>
                            <a href="{{ route('transactions.print', $transaction) }}" target="_blank"
                                class="w-full border-2 border-blue-600 text-blue-600 font-semibold py-3 px-4 rounded-lg hover:bg-blue-50 transition-colors duration-200 flex items-center justify-center">
                                <i class="fas fa-print mr-2"></i>
                                Print Struk
                            </a>
                            <a href="{{ route('transactions.history') }}"
                                class="w-full border-2 border-gray-300 text-gray-700 font-semibold py-3 px-4 rounded-lg hover:bg-gray-50 transition-colors duration-200 flex items-center justify-center">
                                <i class="fas fa-history mr-2"></i>
                                Lihat Riwayat
                            </a>
                        </div>
                    </div>

                    <!-- Transaction Stats -->
                    <div class="bg-white rounded-xl shadow-sm p-5">
                        <h3 class="font-bold text-gray-900 mb-4">
                            <i class="fas fa-chart-bar text-cyan-500 mr-2"></i>
                            Statistik Transaksi
                        </h3>
                        <div class="grid grid-cols-2 gap-3">
                            <!-- ID Transaksi -->
                            <div
                                class="bg-gradient-to-br from-white to-blue-50 border border-blue-100 rounded-lg p-4 text-center transition-transform duration-300 hover:-translate-y-1">
                                <div class="text-blue-500 mb-2">
                                    <i class="fas fa-hashtag text-2xl"></i>
                                </div>
                                <div class="font-bold text-2xl text-gray-900">#{{ $transaction->id }}</div>
                                <div class="text-sm text-gray-500 mt-1">ID Transaksi</div>
                            </div>

                            <!-- Total Item -->
                            <div
                                class="bg-gradient-to-br from-white to-green-50 border border-green-100 rounded-lg p-4 text-center transition-transform duration-300 hover:-translate-y-1">
                                <div class="text-green-500 mb-2">
                                    <i class="fas fa-box text-2xl"></i>
                                </div>
                                <div class="font-bold text-2xl text-gray-900">{{ $transaction->items->sum('qty') }}</div>
                                <div class="text-sm text-gray-500 mt-1">Total Item</div>
                            </div>

                            <!-- Jenis Produk -->
                            <div
                                class="bg-gradient-to-br from-white to-yellow-50 border border-yellow-100 rounded-lg p-4 text-center transition-transform duration-300 hover:-translate-y-1">
                                <div class="text-yellow-500 mb-2">
                                    <i class="fas fa-tags text-2xl"></i>
                                </div>
                                <div class="font-bold text-2xl text-gray-900">{{ $transaction->items->count() }}</div>
                                <div class="text-sm text-gray-500 mt-1">Jenis Produk</div>
                            </div>

                            <!-- Waktu -->
                            <div
                                class="bg-gradient-to-br from-white to-cyan-50 border border-cyan-100 rounded-lg p-4 text-center transition-transform duration-300 hover:-translate-y-1">
                                <div class="text-cyan-500 mb-2">
                                    <i class="fas fa-clock text-2xl"></i>
                                </div>
                                <div class="font-bold text-lg text-gray-900">
                                    {{ $transaction->created_at->diffForHumans() }}</div>
                                <div class="text-sm text-gray-500 mt-1">Waktu</div>
                            </div>
                        </div>
                    </div>

                    <!-- Quick Info -->
                    <div class="bg-white rounded-xl shadow-sm p-5">
                        <h3 class="font-bold text-gray-900 mb-4">
                            <i class="fas fa-info-circle text-blue-500 mr-2"></i>
                            Info Cepat
                        </h3>
                        <div class="bg-gradient-to-br from-white to-gray-50 rounded-lg border border-gray-200 p-4">
                            <div class="flex justify-between items-center py-2 border-b border-gray-100">
                                <span class="text-gray-600">Tanggal</span>
                                <span
                                    class="font-medium text-gray-900">{{ $transaction->created_at->format('d/m/Y') }}</span>
                            </div>
                            <div class="flex justify-between items-center py-2 border-b border-gray-100">
                                <span class="text-gray-600">Jam</span>
                                <span
                                    class="font-medium text-gray-900">{{ $transaction->created_at->format('H:i') }}</span>
                            </div>
                            <div class="flex justify-between items-center py-2">
                                <span class="text-gray-600">Lokasi</span>
                                <span class="font-medium text-gray-900">Kasir
                                    {{ $transaction->user->name ?? 'N/A' }}</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Hidden Form untuk Create Delivery -->
    <form id="createDeliveryForm" action="{{ route('delivery.request', $transaction) }}" method="POST" class="hidden">
        @csrf
        <input type="hidden" name="need_delivery" value="yes">
        <input type="hidden" name="recipient_name"
            value="{{ $transaction->recipient_name ?? $transaction->customer_name }}">
        <input type="hidden" name="recipient_phone"
            value="{{ $transaction->recipient_phone ?? $transaction->customer_phone }}">
        <input type="hidden" name="delivery_address" value="{{ $transaction->delivery_address }}">
        <input type="hidden" name="desired_delivery_date" value="{{ $transaction->desired_delivery_date }}">
        <input type="hidden" name="delivery_notes" value="{{ $transaction->delivery_notes }}">
        <input type="hidden" name="delivery_fee" value="{{ $transaction->delivery_fee ?? 0 }}">
        @if ($transaction->items_to_deliver_list)
            @foreach ($transaction->items_to_deliver_list as $item)
                <input type="hidden" name="items_to_deliver[]" value="{{ $item['id'] ?? '' }}">
            @endforeach
        @endif
    </form>

    @push('scripts')
        <script>
            // ================= FIXED JAVASCRIPT =================
            (function() {
                'use strict';

                /**
                 * Toggle delivery options visibility
                 * @param {boolean} show - Whether to show delivery options
                 */
                window.toggleDeliveryOptions = function(show) {
                    console.log('Toggling delivery options:', show);
                    const options = document.getElementById('deliveryOptions');
                    if (options) {
                        options.style.display = show ? 'block' : 'none';

                        // Toggle required attributes for validation
                        const requiredFields = ['recipient_name', 'recipient_phone', 'delivery_address',
                            'desired_delivery_date', 'delivery_fee'
                        ];
                        requiredFields.forEach(fieldId => {
                            const field = document.getElementById(fieldId);
                            if (field) {
                                if (show) {
                                    field.setAttribute('required', 'required');
                                } else {
                                    field.removeAttribute('required');
                                }
                            }
                        });
                    }
                };

                /**
                 * Create delivery request for transactions that already have delivery info
                 */
                window.createDeliveryRequest = function() {
                    if (confirm('Kirim permintaan pengiriman ke logistik?')) {
                        const form = document.getElementById('createDeliveryForm');
                        if (form) {
                            // Show loading state
                            const button = event.target;
                            const originalText = button.innerHTML;
                            button.innerHTML = '<i class="fas fa-spinner fa-spin mr-2"></i>Mengirim...';
                            button.disabled = true;

                            form.submit();
                        }
                    }
                };

                /**
                 * Validate delivery form before submission
                 */
                function validateDeliveryForm(form) {
                    const needDelivery = form.querySelector('input[name="need_delivery"]:checked');
                    if (!needDelivery) {
                        alert('Pilih apakah perlu dikirim atau tidak');
                        return false;
                    }

                    if (needDelivery.value === 'yes') {
                        const recipientName = form.querySelector('input[name="recipient_name"]');
                        const recipientPhone = form.querySelector('input[name="recipient_phone"]');
                        const deliveryAddress = form.querySelector('textarea[name="delivery_address"]');
                        const deliveryDate = form.querySelector('input[name="desired_delivery_date"]');

                        if (!recipientName.value.trim()) {
                            alert('Nama penerima harus diisi');
                            recipientName.focus();
                            return false;
                        }

                        if (!recipientPhone.value.trim()) {
                            alert('Nomor telepon penerima harus diisi');
                            recipientPhone.focus();
                            return false;
                        }

                        if (!deliveryAddress.value.trim()) {
                            alert('Alamat pengiriman harus diisi');
                            deliveryAddress.focus();
                            return false;
                        }

                        if (!deliveryDate.value) {
                            alert('Tanggal pengiriman harus diisi');
                            deliveryDate.focus();
                            return false;
                        }

                        // Validate at least one item is selected for delivery
                        const checkboxes = form.querySelectorAll('.item-checkbox:checked');
                        if (checkboxes.length === 0) {
                            if (!confirm(
                                    'Tidak ada item yang dipilih untuk dikirim. Semua item akan dianggap dikirim. Lanjutkan?'
                                    )) {
                                return false;
                            }
                        }
                    }

                    return true;
                }

                /**
                 * Handle form submission with AJAX
                 */
                function setupFormSubmission() {
                    const form = document.getElementById('deliveryForm');
                    if (!form) return;

                    form.addEventListener('submit', async function(e) {
                        e.preventDefault();

                        // Validate form
                        if (!validateDeliveryForm(this)) {
                            return;
                        }

                        // Show loading state
                        const submitBtn = this.querySelector('button[type="submit"]');
                        const originalText = submitBtn.innerHTML;
                        submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin mr-2"></i>Mengirim...';
                        submitBtn.disabled = true;

                        try {
                            const formData = new FormData(this);

                            // If no items selected for delivery, send all items
                            const checkboxes = this.querySelectorAll('.item-checkbox:checked');
                            if (checkboxes.length === 0) {
                                // Clear existing items_to_deliver
                                formData.delete('items_to_deliver[]');
                                // Add all items
                                document.querySelectorAll('.item-checkbox').forEach(cb => {
                                    formData.append('items_to_deliver[]', cb.value);
                                });
                            }

                            const response = await fetch(this.action, {
                                method: 'POST',
                                body: formData,
                                headers: {
                                    'X-Requested-With': 'XMLHttpRequest',
                                    'X-CSRF-TOKEN': document.querySelector('input[name="_token"]').value
                                }
                            });

                            const data = await response.json();

                            if (response.ok && data.success) {
                                // Show success message
                                showNotification('success', data.message ||
                                    'Permintaan pengiriman berhasil dikirim');

                                // Reload page after short delay
                                setTimeout(() => {
                                    window.location.reload();
                                }, 1500);
                            } else {
                                // Show error message
                                showNotification('error', data.message ||
                                    'Terjadi kesalahan. Silakan coba lagi.');

                                // Restore button
                                submitBtn.innerHTML = originalText;
                                submitBtn.disabled = false;
                            }
                        } catch (error) {
                            console.error('Error:', error);
                            showNotification('error', 'Terjadi kesalahan jaringan. Silakan coba lagi.');

                            // Restore button
                            submitBtn.innerHTML = originalText;
                            submitBtn.disabled = false;
                        }
                    });
                }

                /**
                 * Show notification
                 */
                function showNotification(type, message) {
                    // Check if notification container exists, if not create it
                    let container = document.getElementById('notification-container');
                    if (!container) {
                        container = document.createElement('div');
                        container.id = 'notification-container';
                        container.className = 'fixed top-4 right-4 z-50 space-y-2';
                        document.body.appendChild(container);
                    }

                    // Create notification element
                    const notification = document.createElement('div');
                    notification.className = `p-4 rounded-lg shadow-lg transform transition-all duration-300 translate-x-0 ${
                    type === 'success' ? 'bg-green-500' : 'bg-red-500'
                } text-white`;

                    notification.innerHTML = `
                    <div class="flex items-center">
                        <i class="fas fa-${type === 'success' ? 'check-circle' : 'exclamation-circle'} mr-2"></i>
                        <span>${message}</span>
                    </div>
                `;

                    // Add to container
                    container.appendChild(notification);

                    // Remove after 3 seconds
                    setTimeout(() => {
                        notification.style.transform = 'translateX(400px)';
                        setTimeout(() => {
                            notification.remove();
                            if (container.children.length === 0) {
                                container.remove();
                            }
                        }, 300);
                    }, 3000);
                }

                /**
                 * Initialize page
                 */
                function init() {
                    console.log('Initializing transaction detail page...');

                    // Initialize delivery options visibility
                    const needDeliveryYes = document.querySelector('input[name="need_delivery"][value="yes"]');
                    const needDeliveryNo = document.querySelector('input[name="need_delivery"][value="no"]');

                    if (needDeliveryNo && needDeliveryNo.checked) {
                        toggleDeliveryOptions(false);
                    }

                    // Set min date for delivery date input
                    const deliveryDate = document.getElementById('desired_delivery_date');
                    if (deliveryDate) {
                        const tomorrow = new Date();
                        tomorrow.setDate(tomorrow.getDate() + 1);
                        deliveryDate.min = tomorrow.toISOString().split('T')[0];
                    }

                    // Setup form submission
                    setupFormSubmission();

                    // Auto-hide alerts after 5 seconds
                    setTimeout(() => {
                        document.querySelectorAll('.bg-green-50, .bg-red-50').forEach(alert => {
                            alert.style.transition = 'opacity 0.5s';
                            alert.style.opacity = '0';
                            setTimeout(() => alert.remove(), 500);
                        });
                    }, 5000);

                    console.log('Initialization complete');
                }

                // Run initialization when DOM is ready
                if (document.readyState === 'loading') {
                    document.addEventListener('DOMContentLoaded', init);
                } else {
                    init();
                }
            })();
        </script>
    @endpush

    <!-- Consolidated Styles -->
    <style>
        /* Custom animations */
        @keyframes fadeInUp {
            from {
                opacity: 0;
                transform: translateY(20px);
            }

            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        /* Apply animations to cards */
        .bg-white {
            animation: fadeInUp 0.5s ease-out forwards;
        }

        /* Custom scrollbar for table */
        .overflow-x-auto::-webkit-scrollbar {
            height: 6px;
        }

        .overflow-x-auto::-webkit-scrollbar-track {
            background: #f1f1f1;
            border-radius: 3px;
        }

        .overflow-x-auto::-webkit-scrollbar-thumb {
            background: #c1c1c1;
            border-radius: 3px;
        }

        .overflow-x-auto::-webkit-scrollbar-thumb:hover {
            background: #a8a8a8;
        }

        /* Notification animations */
        #notification-container>div {
            animation: slideIn 0.3s ease-out;
        }

        @keyframes slideIn {
            from {
                transform: translateX(400px);
                opacity: 0;
            }

            to {
                transform: translateX(0);
                opacity: 1;
            }
        }

        /* Print styles */
        @media print {

            .lg\:w-1\/3,
            .lg\:w-2\/3 .bg-white:nth-child(2),
            .lg\:w-2\/3 .bg-white:last-child .border-b,
            a[href] {
                display: none !important;
            }

            body {
                background: white !important;
            }

            .bg-white {
                box-shadow: none !important;
                border: 1px solid #e5e7eb !important;
                animation: none !important;
            }
        }

        /* Mobile sidebar styles (if needed) */
        @media (max-width: 1024px) {
            #sidebar {
                position: fixed;
                top: 0;
                left: 0;
                bottom: 0;
                width: 280px !important;
                z-index: 50;
                transition: transform 0.3s ease, left 0.3s ease;
            }

            #sidebar:not(.sidebar-open) {
                transform: translateX(-100%);
                left: -280px;
            }

            #sidebar.sidebar-open {
                transform: translateX(0);
                left: 0;
            }

            #sidebar-overlay {
                transition: opacity 0.3s ease;
            }

            #sidebar-overlay.hidden {
                display: none;
            }

            #sidebar-overlay:not(.hidden) {
                display: block;
            }
        }

        /* Loading spinner */
        .fa-spinner {
            animation: spin 1s linear infinite;
        }

        @keyframes spin {
            from {
                transform: rotate(0deg);
            }

            to {
                transform: rotate(360deg);
            }
        }
    </style>
@endsection
