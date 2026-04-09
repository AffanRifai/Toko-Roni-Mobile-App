

<?php $__env->startSection('title', 'Riwayat Pembayaran - ' . $receivable->no_piutang); ?>
<?php $__env->startSection('page-title', 'Detail Piutang'); ?>
<?php $__env->startSection('page-subtitle', 'Riwayat pembayaran piutang member'); ?>

<?php $__env->startSection('content'); ?>
<div class="min-h-screen bg-gradient-to-br from-blue-50/50 to-purple-50/30 p-4 md:p-6">

    
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
                        No. Piutang: <span class="font-mono font-semibold"><?php echo e($receivable->no_piutang); ?></span>
                    </p>
                    <p class="text-sm text-gray-500">
                        Member: <?php echo e($receivable->member->nama ?? 'N/A'); ?> (<?php echo e($receivable->member->kode_member ?? '-'); ?>)
                    </p>
                </div>
            </div>
            <div class="flex gap-3">
                <a href="<?php echo e(route('transactions.show', $receivable->transaction_id)); ?>"
                   class="px-4 py-2 bg-blue-600 text-white rounded-xl hover:bg-blue-700 transition-all inline-flex items-center">
                    <i class="fas fa-file-invoice mr-2"></i>
                    Lihat Invoice
                </a>
                <a href="<?php echo e(route('members.receivables', $receivable->member_id)); ?>"
                   class="px-4 py-2 border border-gray-200 rounded-xl hover:bg-gray-50 transition-all inline-flex items-center">
                    <i class="fas fa-arrow-left mr-2"></i>
                    Kembali
                </a>
            </div>
        </div>
    </div>

    
    <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-6">
        <div class="bg-white rounded-xl p-4 shadow-sm border-l-4 border-blue-500">
            <p class="text-sm text-gray-500">Total Piutang</p>
            <p class="text-xl font-bold text-gray-800">Rp <?php echo e(number_format($receivable->total_piutang, 0, ',', '.')); ?></p>
        </div>
        <div class="bg-white rounded-xl p-4 shadow-sm border-l-4 border-green-500">
            <p class="text-sm text-gray-500">Total Dibayar</p>
            <p class="text-xl font-bold text-green-600">Rp <?php echo e(number_format($paymentSummary['total_paid'], 0, ',', '.')); ?></p>
        </div>
        <div class="bg-white rounded-xl p-4 shadow-sm border-l-4 border-yellow-500">
            <p class="text-sm text-gray-500">Sisa Piutang</p>
            <p class="text-xl font-bold text-yellow-600">Rp <?php echo e(number_format($paymentSummary['remaining'], 0, ',', '.')); ?></p>
        </div>
        <div class="bg-white rounded-xl p-4 shadow-sm border-l-4 border-purple-500">
            <p class="text-sm text-gray-500">Status</p>
            <p class="text-xl font-bold">
                <span class="badge <?php echo e($receivable->status == 'LUNAS' ? 'bg-green-100 text-green-800' : 'bg-amber-100 text-amber-800'); ?>">
                    <?php echo e($receivable->status); ?>

                </span>
            </p>
        </div>
    </div>

    
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
                    <?php $__empty_1 = true; $__currentLoopData = $receivable->payments; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $payment): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                    <tr class="hover:bg-white/30 transition-colors">
                        <td class="px-6 py-3 text-sm">
                            <?php echo e($payment->tanggal_bayar->format('d/m/Y H:i')); ?>

                        </td>
                        <td class="px-6 py-3 font-medium text-green-600">
                            Rp <?php echo e(number_format($payment->jumlah_bayar, 0, ',', '.')); ?>

                        </td>
                        <td class="px-6 py-3">
                            <span class="badge bg-gray-100 text-gray-700">
                                <?php echo e(ucfirst($payment->metode_bayar)); ?>

                            </span>
                        </td>
                        <td class="px-6 py-3 text-sm">
                            <?php echo e($payment->kasir->name ?? '-'); ?>

                        </td>
                        <td class="px-6 py-3 text-sm text-gray-500">
                            <?php echo e($payment->keterangan ?? '-'); ?>

                        </td>
                    </tr>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
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
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>

    
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
                    <p class="font-mono font-medium"><?php echo e($receivable->invoice_number ?? '-'); ?></p>
                </div>
                <div>
                    <p class="text-sm text-gray-500">Tanggal Transaksi</p>
                    <p><?php echo e(\Carbon\Carbon::parse($receivable->tanggal_transaksi)->format('d/m/Y')); ?></p>
                </div>
                <div>
                    <p class="text-sm text-gray-500">Jatuh Tempo</p>
                    <p class="<?php echo e($receivable->jatuh_tempo && $receivable->jatuh_tempo < now() && $receivable->status != 'LUNAS' ? 'text-red-600 font-medium' : ''); ?>">
                        <?php echo e($receivable->jatuh_tempo ? \Carbon\Carbon::parse($receivable->jatuh_tempo)->format('d/m/Y') : '-'); ?>

                    </p>
                </div>
                <div>
                    <p class="text-sm text-gray-500">Keterangan</p>
                    <p><?php echo e($receivable->keterangan ?? '-'); ?></p>
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
<?php $__env->stopSection(); ?>
<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\laragon\www\Toko-Roni-Mobile-App\resources\views/receivables/show.blade.php ENDPATH**/ ?>