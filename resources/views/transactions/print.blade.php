<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Struk Transaksi - {{ $transaction->invoice_number }}</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Courier New', Courier, monospace;
            background: #f5f5f5;
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
            padding: 20px;
        }

        .receipt {
            width: 80mm;
            max-width: 100%;
            background: white;
            padding: 15px 10px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
            border-radius: 8px;
            margin: 0 auto;
        }

        .header {
            text-align: center;
            margin-bottom: 15px;
            padding-bottom: 10px;
            border-bottom: 2px dashed #333;
        }

        .store-name {
            font-size: 20px;
            font-weight: bold;
            text-transform: uppercase;
            letter-spacing: 2px;
        }

        .store-detail {
            font-size: 12px;
            margin-top: 5px;
            color: #555;
        }

        .title {
            font-size: 16px;
            font-weight: bold;
            margin: 10px 0;
            text-align: center;
        }

        .info {
            font-size: 12px;
            margin-bottom: 10px;
            padding-bottom: 10px;
            border-bottom: 1px dashed #999;
        }

        .info-row {
            display: flex;
            justify-content: space-between;
            margin-bottom: 3px;
        }

        .info-label {
            color: #555;
        }

        .info-value {
            font-weight: bold;
        }

        .items {
            margin-bottom: 15px;
        }

        .item-header {
            display: flex;
            font-weight: bold;
            font-size: 12px;
            border-bottom: 1px solid #333;
            padding-bottom: 5px;
            margin-bottom: 5px;
        }

        .item-name {
            flex: 3;
        }

        .item-qty {
            flex: 1;
            text-align: center;
        }

        .item-price {
            flex: 2;
            text-align: right;
        }

        .item-row {
            display: flex;
            font-size: 11px;
            margin-bottom: 3px;
        }

        .item-row .item-name {
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }

        .item-subtotal {
            text-align: right;
            flex: 2;
        }

        .summary {
            border-top: 2px dashed #333;
            padding-top: 10px;
            margin-top: 10px;
        }

        .summary-row {
            display: flex;
            justify-content: space-between;
            font-size: 12px;
            margin-bottom: 5px;
        }

        .total-row {
            font-size: 14px;
            font-weight: bold;
            border-top: 1px solid #333;
            padding-top: 5px;
            margin-top: 5px;
        }

        .payment-info {
            margin-top: 15px;
            padding-top: 10px;
            border-top: 1px dashed #999;
            font-size: 11px;
        }

        .footer {
            text-align: center;
            margin-top: 20px;
            padding-top: 10px;
            border-top: 2px dashed #333;
            font-size: 11px;
        }

        .member-info {
            background: #f0f0f0;
            padding: 8px;
            border-radius: 4px;
            margin: 10px 0;
            font-size: 11px;
        }

        .status-badge {
            display: inline-block;
            padding: 3px 8px;
            border-radius: 20px;
            font-size: 10px;
            font-weight: bold;
            text-transform: uppercase;
        }

        .status-lunas {
            background: #d4edda;
            color: #155724;
        }

        .status-belum {
            background: #fff3cd;
            color: #856404;
        }

        @media print {
            body {
                background: white;
                padding: 0;
            }
            .receipt {
                box-shadow: none;
                padding: 0;
            }
            .no-print {
                display: none;
            }
        }

        .actions {
            text-align: center;
            margin-top: 20px;
        }

        .btn-print {
            background: #4a90e2;
            color: white;
            border: none;
            padding: 10px 30px;
            border-radius: 5px;
            font-size: 14px;
            cursor: pointer;
            font-family: Arial, sans-serif;
        }

        .btn-print:hover {
            background: #357abd;
        }

        .text-right {
            text-align: right;
        }

        .text-center {
            text-align: center;
        }

        .text-uppercase {
            text-transform: uppercase;
        }

        .mb-1 {
            margin-bottom: 5px;
        }

        .mt-2 {
            margin-top: 10px;
        }
    </style>
</head>
<body>
    <div class="receipt">
        <!-- Header Toko -->
        <div class="header">
            <div class="store-name">TOKO RONI</div>
            <div class="store-detail">Jl. H.Hasan</div>
            <div class="store-detail">Telp: 0812-3456-7890</div>
            <div class="title">STRUK TOKO RONI</div>
        </div>

        <!-- Informasi Transaksi -->
        <div class="info">
            <div class="info-row">
                <span class="info-label">No. Invoice</span>
                <span class="info-value">{{ $transaction->invoice_number }}</span>
            </div>
            <div class="info-row">
                <span class="info-label">Tanggal</span>
                <span class="info-value">{{ $transaction->created_at->format('d/m/Y H:i') }}</span>
            </div>
            <div class="info-row">
                <span class="info-label">Kasir</span>
                <span class="info-value">{{ $transaction->user->name ?? '-' }}</span>
            </div>
            <div class="info-row">
                <span class="info-label">Pelanggan</span>
                <span class="info-value">{{ $transaction->customer_name }}</span>
            </div>
            @if($transaction->customer_phone)
            <div class="info-row">
                <span class="info-label">Telepon</span>
                <span class="info-value">{{ $transaction->customer_phone }}</span>
            </div>
            @endif
        </div>

        <!-- Informasi Member (Jika Ada) -->
        @if($transaction->member)
        <div class="member-info">
            <div class="info-row">
                <span class="info-label">Member</span>
                <span class="info-value">{{ $transaction->member->kode_member }}</span>
            </div>
            <div class="info-row">
                <span class="info-label">Nama Member</span>
                <span class="info-value">{{ $transaction->member->nama }}</span>
            </div>
            <div class="info-row">
                <span class="info-label">Tipe</span>
                <span class="info-value">{{ ucfirst($transaction->member->tipe_member) }}</span>
            </div>
        </div>
        @endif

        <!-- Daftar Item -->
        <div class="items">
            <div class="item-header">
                <div class="item-name">Item</div>
                <div class="item-qty">Qty</div>
                <div class="item-price">Harga</div>
                <div class="item-price">Subtotal</div>
            </div>

            @foreach($transaction->items as $item)
            <div class="item-row">
                <div class="item-name" title="{{ $item->product->name }}">
                    {{ $item->product->name }}
                </div>
                <div class="item-qty">{{ $item->qty }}</div>
                <div class="item-price">Rp {{ number_format($item->price, 0, ',', '.') }}</div>
                <div class="item-price">Rp {{ number_format($item->subtotal, 0, ',', '.') }}</div>
            </div>
            @endforeach
        </div>

        <!-- Ringkasan Pembayaran -->
        <div class="summary">
            <div class="summary-row">
                <span>Subtotal</span>
                <span>Rp {{ number_format($transaction->total_amount, 0, ',', '.') }}</span>
            </div>

            @if($transaction->discount > 0)
            <div class="summary-row">
                <span>Diskon ({{ $transaction->discount }}%)</span>
                <span class="text-red-600">
                    - Rp {{ number_format($transaction->total_amount * $transaction->discount / 100, 0, ',', '.') }}
                </span>
            </div>
            <div class="summary-row total-row">
                <span>Total Setelah Diskon</span>
                <span>Rp {{ number_format($transaction->total_amount, 0, ',', '.') }}</span>
            </div>
            @else
            <div class="summary-row total-row">
                <span>Total</span>
                <span>Rp {{ number_format($transaction->total_amount, 0, ',', '.') }}</span>
            </div>
            @endif

            @if($transaction->payment_method == 'cash')
            <div class="summary-row">
                <span>Tunai</span>
                <span>Rp {{ number_format($transaction->cash_received, 0, ',', '.') }}</span>
            </div>
            <div class="summary-row">
                <span>Kembali</span>
                <span>Rp {{ number_format($transaction->change, 0, ',', '.') }}</span>
            </div>
            @endif
        </div>

        <!-- Informasi Pembayaran -->
        <div class="payment-info">
            <div class="info-row">
                <span>Metode Pembayaran</span>
                <span class="text-uppercase">
                    {{ str_replace('_', ' ', $transaction->payment_method) }}
                </span>
            </div>

            @if($transaction->isCredit())
            <div class="info-row">
                <span>Status</span>
                <span>
                    <span class="status-badge {{ $transaction->payment_status == 'LUNAS' ? 'status-lunas' : 'status-belum' }}">
                        {{ $transaction->payment_status }}
                    </span>
                </span>
            </div>
            @if($transaction->due_date)
            <div class="info-row">
                <span>Jatuh Tempo</span>
                <span>{{ $transaction->due_date->format('d/m/Y') }}</span>
            </div>
            @endif
            @if($transaction->receivable)
            <div class="info-row">
                <span>Sisa Piutang</span>
                <span>Rp {{ number_format($transaction->receivable->sisa_piutang, 0, ',', '.') }}</span>
            </div>
            @endif
            @endif
        </div>

        <!-- Footer -->
        <div class="footer">
            <div>Terima Kasih atas Kunjungan Anda</div>
            <div>Barang yang sudah dibeli tidak dapat dikembalikan</div>
            <div class="mt-2">www.tokoroni.com</div>
        </div>
    </div>

    <!-- Tombol Print (hidden saat print) -->
    <div class="actions no-print">
        <button onclick="window.print()" class="btn-print">
            <i class="fas fa-print"></i> Cetak Struk
        </button>
        <br>
        <small style="display: block; margin-top: 10px; color: #666;">
            Tekan Ctrl+P atau Cmd+P untuk mencetak
        </small>
    </div>

    <script>
        // Auto print saat halaman dimuat (opsional)
        window.onload = function() {
            // Uncomment baris di bawah jika ingin auto print
            // window.print();
        }
    </script>
</body>
</html>
