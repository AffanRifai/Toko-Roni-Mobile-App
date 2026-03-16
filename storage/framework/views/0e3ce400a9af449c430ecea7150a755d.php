<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Surat Jalan - <?php echo e($delivery->delivery_code); ?></title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Courier New', Courier, monospace;
            background: #ffffff;
            padding: 20px;
            font-size: 12px;
        }

        .print-container {
            max-width: 800px;
            margin: 0 auto;
            background: white;
            padding: 30px;
            border: 1px solid #ddd;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
        }

        .header {
            text-align: center;
            margin-bottom: 30px;
            padding-bottom: 20px;
            border-bottom: 2px solid #333;
        }

        .store-name {
            font-size: 24px;
            font-weight: bold;
            text-transform: uppercase;
            letter-spacing: 2px;
            margin-bottom: 5px;
        }

        .store-detail {
            font-size: 12px;
            color: #555;
            margin-bottom: 3px;
        }

        .title {
            font-size: 18px;
            font-weight: bold;
            margin: 20px 0;
            text-align: center;
            text-decoration: underline;
        }

        .info-section {
            margin-bottom: 20px;
            padding: 15px;
            background: #f9f9f9;
            border: 1px solid #ddd;
        }

        .info-row {
            display: flex;
            margin-bottom: 8px;
            border-bottom: 1px dotted #ccc;
            padding-bottom: 5px;
        }

        .info-label {
            width: 150px;
            font-weight: bold;
            color: #555;
        }

        .info-value {
            flex: 1;
            font-weight: normal;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin: 20px 0;
        }

        th {
            background: #333;
            color: white;
            padding: 10px;
            text-align: left;
            font-size: 12px;
        }

        td {
            padding: 8px 10px;
            border: 1px solid #ddd;
        }

        .footer {
            margin-top: 40px;
            padding-top: 20px;
            border-top: 2px dashed #333;
            display: flex;
            justify-content: space-between;
        }

        .signature-box {
            width: 200px;
            text-align: center;
        }

        .signature-line {
            margin-top: 50px;
            border-top: 1px solid #333;
            padding-top: 5px;
        }

        .print-button {
            text-align: center;
            margin: 20px 0;
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

        .status-badge {
            display: inline-block;
            padding: 3px 8px;
            border-radius: 3px;
            font-size: 11px;
            font-weight: bold;
        }

        .status-pending {
            background: #fff3cd;
            color: #856404;
        }

        .status-processing {
            background: #cce5ff;
            color: #004085;
        }

        .status-assigned {
            background: #d4edda;
            color: #155724;
        }

        .status-delivered {
            background: #d1e7dd;
            color: #0f5132;
        }

        @media print {
            .print-button {
                display: none;
            }

            body {
                padding: 0;
            }

            .print-container {
                border: none;
                box-shadow: none;
                padding: 15px;
            }
        }

        .watermark {
            position: fixed;
            bottom: 20px;
            right: 20px;
            opacity: 0.1;
            font-size: 50px;
            transform: rotate(-15deg);
            pointer-events: none;
        }
    </style>
</head>

<body>
    <div class="print-container">
        <!-- Watermark -->
        <div class="watermark">SURAT JALAN</div>

        <!-- Header -->
        <div class="header">
            <div class="store-name">TOKO RONI</div>
            <div class="store-detail">Jl.H.Hasan</div>
            <div class="store-detail">Telp: - | Email: info@tokoroni.com</div>
        </div>

        <!-- Title -->
        <div class="title">SURAT JALAN PENGIRIMAN</div>
        <div style="text-align: center; margin-bottom: 20px;">
            No. <?php echo e($delivery->delivery_code); ?>

        </div>

        <!-- Info Pengiriman -->
        <div class="info-section">
            <h3 style="margin-bottom: 15px;">Informasi Pengiriman</h3>

            <div class="info-row">
                <span class="info-label">No. Delivery</span>
                <span class="info-value"><?php echo e($delivery->delivery_code); ?></span>
            </div>

            <div class="info-row">
                <span class="info-label">Tanggal</span>
                <span class="info-value"><?php echo e($delivery->created_at->format('d/m/Y H:i')); ?></span>
            </div>

            <div class="info-row">
                <span class="info-label">Status</span>
                <span class="info-value">
                    <span class="status-badge status-<?php echo e($delivery->status); ?>">
                        <?php echo e(ucfirst(str_replace('_', ' ', $delivery->status))); ?>

                    </span>
                </span>
            </div>

            <div class="info-row">
                <span class="info-label">Asal</span>
                <span class="info-value"><?php echo e($delivery->origin); ?></span>
            </div>

            <div class="info-row">
                <span class="info-label">Tujuan</span>
                <span class="info-value"><?php echo e($delivery->destination); ?></span>
            </div>

            <?php if($delivery->estimated_delivery_time): ?>
                <div class="info-row">
                    <span class="info-label">Estimasi Tiba</span>
                    <span class="info-value"><?php echo e($delivery->estimated_delivery_time->format('d/m/Y H:i')); ?></span>
                </div>
            <?php endif; ?>
        </div>

        <!-- Info Transaksi -->
        <div class="info-section">
            <h3 style="margin-bottom: 15px;">Informasi Transaksi</h3>

            <div class="info-row">
                <span class="info-label">No. Invoice</span>
                <span class="info-value"><?php echo e($delivery->transaction->invoice_number); ?></span>
            </div>

            <div class="info-row">
                <span class="info-label">Customer</span>
                <span class="info-value"><?php echo e($delivery->transaction->customer_name); ?></span>
            </div>

            <?php if($delivery->transaction->customer_phone): ?>
                <div class="info-row">
                    <span class="info-label">No. Telepon</span>
                    <span class="info-value"><?php echo e($delivery->transaction->customer_phone); ?></span>
                </div>
            <?php endif; ?>

            <div class="info-row">
                <span class="info-label">Total Belanja</span>
                <span class="info-value">Rp
                    <?php echo e(number_format($delivery->transaction->total_amount, 0, ',', '.')); ?></span>
            </div>
        </div>

        <!-- Daftar Barang -->
        <h3 style="margin: 20px 0 10px;">Daftar Barang</h3>
        <table>
            <thead>
                <tr>
                    <th style="width: 5%;">No</th>
                    <th style="width: 45%;">Nama Barang</th>
                    <th style="width: 15%;">Qty</th>
                    <th style="width: 35%;">Keterangan</th>
                </tr>
            </thead>
            <tbody>
                <?php
                    $items = [];
                    if ($delivery->transaction && $delivery->transaction->items_to_deliver) {
                        $items = is_string($delivery->transaction->items_to_deliver)
                            ? json_decode($delivery->transaction->items_to_deliver, true)
                            : $delivery->transaction->items_to_deliver;
                    }

                    // Jika tidak ada items_to_deliver, tampilkan semua item transaksi
                    if (empty($items) && $delivery->transaction) {
                        $items = $delivery->transaction->items
                            ->map(function ($item) {
                                return [
                                    'name' => $item->product->name,
                                    'qty' => $item->qty,
                                ];
                            })
                            ->toArray();
                    }
                ?>

                <?php $__empty_1 = true; $__currentLoopData = $items; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $index => $item): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                    <tr>
                        <td style="text-align: center;"><?php echo e($index + 1); ?></td>
                        <td><?php echo e($item['name']); ?></td>
                        <td style="text-align: center;"><?php echo e($item['qty']); ?></td>
                        <td>-</td>
                    </tr>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                    <tr>
                        <td colspan="4" style="text-align: center;">Tidak ada data barang</td>
                    </tr>
                <?php endif; ?>

                <?php if($delivery->total_items > 0): ?>
                    <tr style="font-weight: bold; background: #f2f2f2;">
                        <td colspan="2" style="text-align: right;">Total Item:</td>
                        <td style="text-align: center;"><?php echo e($delivery->total_items); ?></td>
                        <td></td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>

        <!-- Informasi Tambahan -->
        <?php if($delivery->total_weight > 0 || $delivery->total_volume > 0): ?>
            <div style="margin: 10px 0; padding: 10px; background: #f9f9f9;">
                <?php if($delivery->total_weight > 0): ?>
                    <div style="display: inline-block; margin-right: 20px;">
                        <strong>Total Berat:</strong> <?php echo e(number_format($delivery->total_weight, 1)); ?> kg
                    </div>
                <?php endif; ?>
                <?php if($delivery->total_volume > 0): ?>
                    <div style="display: inline-block;">
                        <strong>Total Volume:</strong> <?php echo e(number_format($delivery->total_volume, 1)); ?> m³
                    </div>
                <?php endif; ?>
            </div>
        <?php endif; ?>

        <?php if($delivery->notes): ?>
            <div style="margin: 10px 0; padding: 10px; background: #fff3cd; border-left: 4px solid #ffc107;">
                <strong>Catatan:</strong><br>
                <?php echo e($delivery->notes); ?>

            </div>
        <?php endif; ?>

        <!-- Tanda Tangan -->
        <div class="footer">
            <div class="signature-box">
                <div>Pengirim,</div>
                <div class="signature-line">( _____________ )</div>
                <div style="margin-top: 5px; font-size: 11px;">Kasir/Logistik</div>
            </div>

            <div class="signature-box">
                <div>Kurir,</div>
                <div class="signature-line">( _____________ )</div>
                <div style="margin-top: 5px; font-size: 11px;">Nama Kurir</div>
            </div>

            <div class="signature-box">
                <div>Penerima,</div>
                <div class="signature-line">( _____________ )</div>
                <div style="margin-top: 5px; font-size: 11px;">Nama & Tanda Tangan</div>
            </div>
        </div>

        <!-- Footer Text -->
        <div style="margin-top: 30px; text-align: center; font-size: 10px; color: #777;">
            <p>Dokumen ini adalah bukti pengiriman barang yang sah</p>
            <p>Barang yang sudah diterima tidak dapat dikembalikan kecuali ada perjanjian khusus</p>
        </div>
    </div>

    <!-- Print Button -->
    <div class="print-button">
        <button onclick="window.print()" class="btn-print">
            <i class="fas fa-print"></i> Cetak Surat Jalan
        </button>
        <p style="margin-top: 10px; font-size: 12px; color: #666;">
            Tekan Ctrl+P atau Cmd+P untuk mencetak
        </p>
    </div>

    <script>
        // Auto print when page loads (optional - uncomment if needed)
        // window.onload = function() {
        //     window.print();
        // }
    </script>
</body>

</html>
<?php /**PATH D:\PROJECT3\Toko-Roni-Mobile-App\tokoroni-app\resources\views\Delivery\print-note.blade.php ENDPATH**/ ?>