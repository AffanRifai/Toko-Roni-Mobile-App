<?php $__env->startSection('content'); ?>
<h1>Laporan Penjualan</h1>

<form method="GET">
    <label>Tanggal:</label>
    <input type="date" name="date">

    <label>Bulan:</label>
    <input type="month" name="month">

    <button>Tampilkan</button>
</form>

<hr>

<h3>Total Omzet: Rp <?php echo e(number_format($total)); ?></h3>

<table>
<tr>
    <th>Tanggal</th>
    <th>Kasir</th>
    <th>Total</th>
</tr>
<?php $__currentLoopData = $transactions; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $t): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
<tr>
    <td><?php echo e($t->created_at->format('d-m-Y')); ?></td>
    <td><?php echo e($t->user->name ?? '-'); ?></td>
    <td>Rp <?php echo e(number_format($t->total)); ?></td>
</tr>
<?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
</table>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH D:\PROJECT3\Toko-Roni-Mobile-App\tokoroni-app\resources\views\reports\reports.blade.php ENDPATH**/ ?>