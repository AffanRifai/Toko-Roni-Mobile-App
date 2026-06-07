<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Laporan Inventory</title>
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
            width: 120px;
            display: inline-block;
            color: #495057;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
            font-size: 10px;
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
            padding: 6px 5px;
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
        .text-left {
            text-align: left;
        }
        .summary {
            margin-top: 20px;
            width: 100%;
        }
        .summary table {
            width: 400px;
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
        .badge {
            display: inline-block;
            padding: 2px 6px;
            border-radius: 3px;
            font-size: 9px;
            font-weight: bold;
        }
        .badge-success {
            background-color: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }
        .badge-warning {
            background-color: #fff3cd;
            color: #856404;
            border: 1px solid #ffeeba;
        }
        .badge-danger {
            background-color: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
        }
        .badge-secondary {
            background-color: #e2e3e5;
            color: #383d41;
            border: 1px solid #d6d8db;
        }
    </style>
</head>
<body>

    <div class="header">
        <h2>LAPORAN INVENTORY</h2>
        <strong><?php echo e(config('app.name')); ?></strong>
        <div style="margin-top: 5px; font-size: 12px;">Data Seluruh Produk & Stok</div>
    </div>

    <!-- INFO PERIODE DAN TANGGAL CETAK -->
    <div class="info">
        <strong>Periode:</strong> Seluruh Waktu (All Time)<br>
        <strong>Tanggal Cetak:</strong> <?php echo e(now()->format('d-m-Y H:i')); ?>

    </div>

    <!-- TABEL PRODUK DAN STOK -->
    <table>
        <thead>
            <tr>
                <th width="4%">NO</th>
                <th width="12%">KODE</th>
                <th width="20%">NAMA PRODUK</th>
                <th width="10%">KATEGORI</th>
                <th width="8%">STOK</th>
                <th width="8%">MIN STOK</th>
                <th width="10%">STATUS STOK</th>
                <th width="10%">HARGA</th>
                <th width="10%">TOTAL NILAI</th>
                <th width="8%">STATUS</th>
            </tr>
        </thead>
        <tbody>
            <?php
                $totalProducts = 0;
                $totalStock = 0;
                $totalValue = 0;
                $lowStockCount = 0;
                $outOfStockCount = 0;
            ?>

            <?php $__empty_1 = true; $__currentLoopData = $products ?? []; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $index => $product): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                <?php
                    $totalProducts++;
                    $totalStock += $product->stock ?? 0;
                    $nilaiProduk = ($product->price ?? 0) * ($product->stock ?? 0);
                    $totalValue += $nilaiProduk;
                    
                    // Status Stok
                    if (($product->stock ?? 0) <= 0) {
                        $statusStok = '<span class="badge badge-danger">Habis</span>';
                        $outOfStockCount++;
                    } elseif (($product->stock ?? 0) < ($product->min_stock ?? 10)) {
                        $statusStok = '<span class="badge badge-warning">Stok Rendah</span>';
                        $lowStockCount++;
                    } else {
                        $statusStok = '<span class="badge badge-success">Normal</span>';
                    }
                ?>
                <tr>
                    <td class="text-center"><?php echo e($index + 1); ?></td>
                    <td class="text-center"><?php echo e($product->code ?? '-'); ?></td>
                    <td><?php echo e($product->name ?? '-'); ?></td>
                    <td><?php echo e($product->category->name ?? '-'); ?></td>
                    <td class="text-center"><?php echo e(number_format($product->stock ?? 0)); ?></td>
                    <td class="text-center"><?php echo e(number_format($product->min_stock ?? 10)); ?></td>
                    <td class="text-center"><?php echo $statusStok; ?></td>
                    <td class="text-right">Rp <?php echo e(number_format($product->price ?? 0, 0, ',', '.')); ?></td>
                    <td class="text-right">Rp <?php echo e(number_format($nilaiProduk, 0, ',', '.')); ?></td>
                    <td class="text-center">
                        <?php if($product->is_active ?? true): ?>
                            <span class="badge badge-success">Aktif</span>
                        <?php else: ?>
                            <span class="badge badge-secondary">Nonaktif</span>
                        <?php endif; ?>
                    </td>
                </tr>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                <tr>
                    <td colspan="10" class="text-center">Tidak ada data produk</td>
                </tr>
            <?php endif; ?>
        </tbody>
    </table>

    <!-- TABEL DATA TRANSAKSI ITEM (PENJUALAN) -->
    <div style="margin-top: 30px;">
        <h3 style="margin-bottom: 10px; border-bottom: 1px solid #333; padding-bottom: 5px;">DATA PENJUALAN PRODUK (Transaction Items)</h3>
        
        <table>
            <thead>
                <tr>
                    <th width="4%">NO</th>
                    <th width="12%">INVOICE</th>
                    <th width="15%">TANGGAL</th>
                    <th width="18%">PRODUK</th>
                    <th width="8%">QTY</th>
                    <th width="10%">HARGA</th>
                    <th width="10%">SUBTOTAL</th>
                    <th width="8%">KASIR</th>
                    <th width="8%">PELANGGAN</th>
                </tr>
            </thead>
            <tbody>
                <?php
                    $totalQty = 0;
                    $totalSubtotal = 0;
                ?>

                <?php $__empty_1 = true; $__currentLoopData = $transactionItems ?? []; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $index => $item): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                    <?php
                        $totalQty += $item->qty ?? 0;
                        $totalSubtotal += $item->subtotal ?? 0;
                    ?>
                    <tr>
                        <td class="text-center"><?php echo e($index + 1); ?></td>
                        <td class="text-center"><?php echo e($item->transaction->invoice_number ?? '-'); ?></td>
                        <td><?php echo e($item->created_at ? \Carbon\Carbon::parse($item->created_at)->format('d-m-Y H:i') : '-'); ?></td>
                        <td><?php echo e($item->product->name ?? '-'); ?></td>
                        <td class="text-center"><?php echo e(number_format($item->qty ?? 0)); ?></td>
                        <td class="text-right">Rp <?php echo e(number_format($item->price ?? 0, 0, ',', '.')); ?></td>
                        <td class="text-right">Rp <?php echo e(number_format($item->subtotal ?? 0, 0, ',', '.')); ?></td>
                        <td><?php echo e($item->transaction->user->name ?? '-'); ?></td>
                        <td>
                            <?php if($item->transaction->member ?? null): ?>
                                <?php echo e($item->transaction->member->name); ?>

                            <?php else: ?>
                                <?php echo e($item->transaction->customer_name ?? 'Umum'); ?>

                            <?php endif; ?>
                        </td>
                    </tr>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                    <tr>
                        <td colspan="9" class="text-center">Tidak ada data transaksi item</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>

    <!-- SUMMARY BOX (KANAN) - INVENTORY SUMMARY -->
    <div class="summary clearfix" style="margin-top: 60px;">
        <table>
            <tr>
                <th width="60%">TOTAL PRODUK</th>
                <td width="40%" class="text-right"><?php echo e(number_format($totalProducts)); ?></td>
            </tr>
            <tr>
                <th>TOTAL UNIT STOK</th>
                <td class="text-right"><?php echo e(number_format($totalStock)); ?></td>
            </tr>
            <tr>
                <th>PRODUK STOK RENDAH</th>
                <td class="text-right"><?php echo e(number_format($lowStockCount)); ?></td>
            </tr>
            <tr>
                <th>PRODUK HABIS</th>
                <td class="text-right"><?php echo e(number_format($outOfStockCount)); ?></td>
            </tr>
            <tr>
                <th>PRODUK AKTIF</th>
                <td class="text-right"><?php echo e(number_format($products->where('is_active', true)->count() ?? 0)); ?></td>
            </tr>
            <tr>
                <th>TOTAL NILAI INVENTORI</th>
                <td class="text-right"><strong>Rp <?php echo e(number_format($totalValue, 0, ',', '.')); ?></strong></td>
            </tr>
        </table>
    </div>

    <!-- SUMMARY BOX (KANAN) - SALES SUMMARY DARI TRANSACTION ITEMS -->
    <div style="margin-top: 20px; width: 100%;">
        <table style="width: 400px; float: right; border: 2px solid #2c3e50;">
            <tr>
                <th colspan="2" style="text-align: center;">RINGKASAN PENJUALAN</th>
            </tr>
            <tr>
                <th width="60%">TOTAL ITEM TERJUAL</th>
                <td width="40%" class="text-right"><?php echo e(number_format($totalQty)); ?></td>
            </tr>
            <tr>
                <th>TOTAL TRANSAKSI ITEM</th>
                <td class="text-right"><?php echo e(number_format($transactionItems->count() ?? 0)); ?></td>
            </tr>
            <tr>
                <th>TOTAL PENDAPATAN</th>
                <td class="text-right"><strong>Rp <?php echo e(number_format($totalSubtotal, 0, ',', '.')); ?></strong></td>
            </tr>
            <tr>
                <th>RATA-RATA PER ITEM</th>
                <td class="text-right">Rp <?php echo e(number_format($totalQty > 0 ? $totalSubtotal / $totalQty : 0, 0, ',', '.')); ?></td>
            </tr>
        </table>
    </div>

    <!-- FOOTER -->
    <div class="footer" style="clear: both; margin-top: 50px;">
        Dicetak oleh: <?php echo e(auth()->user()->name ?? 'System'); ?> | <?php echo e(now()->format('d-m-Y H:i:s')); ?>

    </div>

</body>
</html><?php /**PATH D:\laravel project 3\Toko-Roni-Mobile-App\resources\views/reports/exports/inventory-pdf.blade.php ENDPATH**/ ?>