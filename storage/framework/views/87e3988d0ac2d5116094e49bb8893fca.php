<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Laporan Penjualan</title>
    <style>
        body {
            font-family: DejaVu Sans, sans-serif;
            font-size: 11px;
            margin: 20px;
        }
        .header {
            text-align: center;
            margin-bottom: 20px;
            border-bottom: 2px solid #000;
            padding-bottom: 10px;
        }
        .header h2 {
            margin: 0;
            font-size: 24px;
            text-transform: uppercase;
        }
        .header strong {
            font-size: 16px;
            color: #333;
        }
        .info {
            margin-bottom: 20px;
            padding: 10px;
            background-color: #f8f9fa;
            border-radius: 5px;
            font-size: 11px;
            border: 1px solid #dee2e6;
        }
        .info strong {
            width: 100px;
            display: inline-block;
            color: #495057;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }
        th {
            background-color: #2c3e50;
            color: white;
            padding: 10px 5px;
            font-size: 10px;
            text-transform: uppercase;
            border: 1px solid #1a252f;
        }
        td {
            padding: 8px 5px;
            border: 1px solid #dee2e6;
            vertical-align: middle;
        }
        tbody tr:nth-child(even) {
            background-color: #f8f9fa;
        }
        .text-right {
            text-align: right;
        }
        .text-center {
            text-align: center;
        }
        .summary {
            margin-top: 20px;
            width: 100%;
        }
        .summary table {
            width: 350px;
            float: right;
            margin-bottom: 0;
            border: 2px solid #2c3e50;
        }
        .summary table th {
            background-color: #2c3e50;
            color: white;
            padding: 8px;
        }
        .summary table td {
            padding: 8px;
            background-color: white;
        }
        .summary table tr:last-child {
            background-color: #e9ecef;
        }
        .summary table tr:last-child td {
            font-weight: bold;
            font-size: 12px;
        }
        .footer {
            margin-top: 30px;
            font-size: 10px;
            text-align: right;
            border-top: 1px dashed #adb5bd;
            padding-top: 10px;
            color: #6c757d;
        }
        .clearfix::after {
            content: "";
            display: table;
            clear: both;
        }
        .invoice-number {
            font-family: monospace;
            font-weight: bold;
            color: #2c3e50;
        }
    </style>
</head>
<body>

    <div class="header">
        <h2>LAPORAN PENJUALAN</h2>
        <strong><?php echo e(config('app.name')); ?></strong>
    </div>

    <!-- INFO ATAS: PERIODE (TAHUN SAJA) DAN TANGGAL CETAK -->
    <!-- INFO ATAS: PERIODE (TAHUN SAJA) DAN TANGGAL CETAK -->
<div class="info">
    <strong>Periode:</strong> 
    <?php
        // Ambil tahun dari transaksi pertama dan terakhir
        $tahunAwal = $transactions->count() > 0 
            ? \Carbon\Carbon::parse($transactions->sortBy('created_at')->first()->created_at)->format('Y')
            : '-';
        $tahunAkhir = $transactions->count() > 0 
            ? \Carbon\Carbon::parse($transactions->sortByDesc('created_at')->first()->created_at)->format('Y')
            : '-';
        
        // Tentukan format periode
        if ($tahunAwal == $tahunAkhir || $tahunAkhir == '-') {
            $periodeText = $tahunAwal;
        } else {
            $periodeText = $tahunAwal . ' s/d ' . $tahunAkhir;
        }
    ?>
    <?php echo e($periodeText); ?> <br>
    <strong>Tanggal Cetak:</strong> <?php echo e(now()->format('d-m-Y H:i')); ?>

</div>

    <!-- TABEL TRANSAKSI -->
    <table>
        <thead>
            <tr>
                <th width="5%">NO</th>
                <th width="15%">INVOICE</th>
                <th width="15%">TANGGAL</th>
                <th width="10%">KASIR</th>
                <th width="15%">PELANGGAN</th>
                <th width="10%">METODE</th>
                <th width="8%">STATUS</th>
                <th width="10%">JML ITEM</th>
                <th width="12%">TOTAL</th>
            </tr>
        </thead>
        <tbody>
            <?php
                $grandTotal = 0;
                $totalItemsCount = 0;
            ?>

            <?php $__empty_1 = true; $__currentLoopData = $transactions; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $index => $trx): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                <?php
                    $itemCount = $trx->items ? $trx->items->sum('qty') : 0;
                    $grandTotal += $trx->total_amount;
                    $totalItemsCount += $itemCount;
                ?>
                <tr>
                    <td class="text-center"><?php echo e($index + 1); ?></td>
                    <td class="invoice-number"><?php echo e($trx->invoice_number ?? '-'); ?></td>
                    <td><?php echo e(\Carbon\Carbon::parse($trx->created_at)->format('d-m-Y H:i')); ?></td>
                    <td><?php echo e($trx->user->name ?? '-'); ?></td>
                    <td>
                        <?php if($trx->member): ?>
                            <?php echo e($trx->member->name); ?>

                        <?php else: ?>
                            <?php echo e($trx->customer_name ?? 'Pelanggan Umum'); ?>

                        <?php endif; ?>
                    </td>
                    <td class="text-center"><?php echo e(ucfirst($trx->payment_method ?? '-')); ?></td>
                    <td class="text-center"><?php echo e($trx->payment_status ?? '-'); ?></td>
                    <td class="text-center"><?php echo e($itemCount); ?></td>
                    <td class="text-right">Rp <?php echo e(number_format($trx->total_amount, 0, ',', '.')); ?></td>
                </tr>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                <tr>
                    <td colspan="9" class="text-center">Tidak ada data transaksi</td>
                </tr>
            <?php endif; ?>
        </tbody>
    </table>

    <!-- SUMMARY BOX (KANAN) -->
    <div class="summary clearfix">
        <table>
            <tr>
                <th width="60%">TOTAL TRANSAKSI</th>
                <td width="40%" class="text-right"><?php echo e($transactions->count()); ?></td>
            </tr>
            <tr>
                <th>TOTAL ITEM TERJUAL</th>
                <td class="text-right"><?php echo e($totalItemsCount); ?></td>
            </tr>
            <tr>
                <th>TOTAL DISKON</th>
                <td class="text-right">Rp <?php echo e(number_format($transactions->sum('discount'), 0, ',', '.')); ?></td>
            </tr>
            <tr>
                <th>GRAND TOTAL</th>
                <td class="text-right"><strong>Rp <?php echo e(number_format($grandTotal, 0, ',', '.')); ?></strong></td>
            </tr>
        </table>
    </div>

    <!-- FOOTER -->
    <div class="footer">
        Dicetak oleh: <?php echo e(auth()->user()->name ?? 'System'); ?> | <?php echo e($generatedAt ?? now()->format('d-m-Y H:i:s')); ?>

    </div>

</body>
</html><?php /**PATH D:\PROJECT3\Toko-Roni-Mobile-App\tokoroni-app\resources\views\reports\exports\sales-pdf.blade.php ENDPATH**/ ?>