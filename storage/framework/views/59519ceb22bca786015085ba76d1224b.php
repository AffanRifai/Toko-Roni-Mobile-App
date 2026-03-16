<?php $__env->startSection('title', 'Daftar Piutang'); ?>
<?php $__env->startSection('page-title', 'Manajemen Piutang'); ?>
<?php $__env->startSection('page-subtitle', 'Kelola piutang member'); ?>

<?php $__env->startSection('content'); ?>
<div class="min-h-screen bg-gradient-to-br from-blue-50/50 to-purple-50/30 p-4 md:p-6">

    
    <div class="glass-effect rounded-3xl p-6 md:p-8 shadow-elegant mb-6">
        <div class="flex items-center gap-4">
            <div class="relative">
                <div class="w-16 h-16 rounded-2xl bg-gradient-to-br from-amber-500 to-orange-600 flex items-center justify-center shadow-lg">
                    <i class="fas fa-hand-holding-usd text-2xl text-white"></i>
                </div>
                <div class="absolute -inset-1 bg-gradient-to-r from-amber-500 to-orange-600 rounded-2xl blur-xl opacity-20"></div>
            </div>
            <div>
                <h1 class="text-3xl font-bold text-gray-800">Daftar Piutang</h1>
                <p class="text-gray-600 mt-1">Total piutang: Rp <?php echo e(number_format($stats['total_piutang'])); ?></p>
            </div>
        </div>
    </div>

    
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
        <div class="stat-card group">
            <div class="stat-card-glow bg-gradient-to-r from-amber-500 to-orange-500"></div>
            <div class="stat-card-content">
                <p class="text-sm text-gray-500">Total Piutang</p>
                <h3 class="text-2xl font-bold text-amber-600">Rp <?php echo e(number_format($stats['total_piutang'])); ?></h3>
                <p class="text-xs text-gray-400 mt-1"><?php echo e($stats['jumlah_piutang']); ?> transaksi</p>
            </div>
        </div>

        <div class="stat-card group">
            <div class="stat-card-glow bg-gradient-to-r from-green-500 to-emerald-500"></div>
            <div class="stat-card-content">
                <p class="text-sm text-gray-500">Sudah Lunas</p>
                <h3 class="text-2xl font-bold text-green-600">Rp <?php echo e(number_format($stats['total_lunas'])); ?></h3>
                <p class="text-xs text-gray-400 mt-1">Total yang sudah dibayar</p>
            </div>
        </div>

        <div class="stat-card group">
            <div class="stat-card-glow bg-gradient-to-r from-red-500 to-pink-500"></div>
            <div class="stat-card-content">
                <p class="text-sm text-gray-500">Jatuh Tempo</p>
                <h3 class="text-2xl font-bold text-red-600"><?php echo e($stats['overdue_count']); ?></h3>
                <p class="text-xs text-gray-400 mt-1">Piutang melewati jatuh tempo</p>
            </div>
        </div>

        <div class="stat-card group">
            <div class="stat-card-glow bg-gradient-to-r from-blue-500 to-cyan-500"></div>
            <div class="stat-card-content">
                <p class="text-sm text-gray-500">Rata-rata Piutang</p>
                <h3 class="text-2xl font-bold text-gray-800">
                    Rp <?php echo e(number_format($stats['total_piutang'] / max($stats['jumlah_piutang'], 1))); ?>

                </h3>
            </div>
        </div>
    </div>

    
    <div class="glass-effect rounded-2xl p-5 mb-6">
        <form method="GET" class="grid grid-cols-1 md:grid-cols-5 gap-4">
            <div class="md:col-span-2 relative">
                <i class="fas fa-search absolute left-3 top-1/2 transform -translate-y-1/2 text-gray-400"></i>
                <input type="text" name="search" value="<?php echo e(request('search')); ?>"
                       placeholder="Cari no.piutang, invoice, nama member..."
                       class="w-full pl-10 pr-4 py-3 bg-white/50 border border-gray-200 rounded-xl">
            </div>

            <div class="relative">
                <select name="status" class="w-full px-4 py-3 bg-white/50 border border-gray-200 rounded-xl appearance-none">
                    <option value="all">Semua Status</option>
                    <option value="BELUM LUNAS" <?php echo e(request('status') == 'BELUM LUNAS' ? 'selected' : ''); ?>>Belum Lunas</option>
                    <option value="LUNAS" <?php echo e(request('status') == 'LUNAS' ? 'selected' : ''); ?>>Lunas</option>
                </select>
                <i class="fas fa-chevron-down absolute right-4 top-1/2 transform -translate-y-1/2 text-gray-400"></i>
            </div>

            <div class="relative">
                <select name="member_id" class="w-full px-4 py-3 bg-white/50 border border-gray-200 rounded-xl appearance-none">
                    <option value="all">Semua Member</option>
                    <?php $__currentLoopData = $members; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $member): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <option value="<?php echo e($member->id); ?>" <?php echo e(request('member_id') == $member->id ? 'selected' : ''); ?>>
                            <?php echo e($member->kode_member); ?> - <?php echo e($member->nama); ?>

                        </option>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </select>
                <i class="fas fa-chevron-down absolute right-4 top-1/2 transform -translate-y-1/2 text-gray-400"></i>
            </div>

            <div class="flex gap-2">
                <button type="submit" class="flex-1 px-4 py-3 bg-blue-600 text-white rounded-xl hover:bg-blue-700">
                    <i class="fas fa-filter mr-2"></i>Filter
                </button>
                <a href="<?php echo e(route('receivables.index')); ?>" class="px-4 py-3 border border-gray-200 rounded-xl hover:bg-gray-50">
                    <i class="fas fa-redo"></i>
                </a>
            </div>
        </form>
    </div>

    
    <div class="glass-effect rounded-2xl overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full">
                <thead class="bg-gray-50/50">
                    <tr>
                        <th class="px-6 py-4 text-left text-sm font-semibold text-gray-600">No. Piutang</th>
                        <th class="px-6 py-4 text-left text-sm font-semibold text-gray-600">Member</th>
                        <th class="px-6 py-4 text-left text-sm font-semibold text-gray-600">Invoice</th>
                        <th class="px-6 py-4 text-left text-sm font-semibold text-gray-600">Tgl Transaksi</th>
                        <th class="px-6 py-4 text-left text-sm font-semibold text-gray-600">Total</th>
                        <th class="px-6 py-4 text-left text-sm font-semibold text-gray-600">Sisa</th>
                        <th class="px-6 py-4 text-left text-sm font-semibold text-gray-600">Jatuh Tempo</th>
                        <th class="px-6 py-4 text-left text-sm font-semibold text-gray-600">Status</th>
                        <th class="px-6 py-4 text-left text-sm font-semibold text-gray-600">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    <?php $__empty_1 = true; $__currentLoopData = $receivables; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $receivable): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                    <tr class="hover:bg-white/30 transition-colors">
                        <td class="px-6 py-4">
                            <span class="font-mono text-sm"><?php echo e($receivable->no_piutang); ?></span>
                        </td>
                        <td class="px-6 py-4">
                            <div class="font-medium"><?php echo e($receivable->member->nama); ?></div>
                            <div class="text-xs text-gray-500"><?php echo e($receivable->member->kode_member); ?></div>
                        </td>
                        <td class="px-6 py-4">
                            <span class="font-mono text-sm"><?php echo e($receivable->invoice_number); ?></span>
                        </td>
                        <td class="px-6 py-4 text-sm">
                            <?php echo e($receivable->tanggal_transaksi->format('d/m/Y')); ?>

                        </td>
                        <td class="px-6 py-4 font-medium">
                            Rp <?php echo e(number_format($receivable->total_piutang)); ?>

                        </td>
                        <td class="px-6 py-4">
                            <span class="font-medium <?php echo e($receivable->sisa_piutang > 0 ? 'text-amber-600' : 'text-green-600'); ?>">
                                Rp <?php echo e(number_format($receivable->sisa_piutang)); ?>

                            </span>
                        </td>
                        <td class="px-6 py-4 text-sm">
                            <?php if($receivable->jatuh_tempo): ?>
                                <span class="<?php echo e($receivable->jatuh_tempo < now() && $receivable->status != 'LUNAS' ? 'text-red-600 font-medium' : ''); ?>">
                                    <?php echo e($receivable->jatuh_tempo->format('d/m/Y')); ?>

                                </span>
                                <?php if($receivable->jatuh_tempo < now() && $receivable->status != 'LUNAS'): ?>
                                    <span class="text-xs text-red-600 block">Terlambat</span>
                                <?php endif; ?>
                            <?php else: ?>
                                <span class="text-gray-400">-</span>
                            <?php endif; ?>
                        </td>
                        <td class="px-6 py-4">
                            <span class="badge <?php echo e($receivable->status == 'LUNAS' ? 'bg-green-100 text-green-800' : 'bg-amber-100 text-amber-800'); ?>">
                                <?php echo e($receivable->status); ?>

                            </span>
                        </td>
                        <td class="px-6 py-4">
                            <div class="flex items-center gap-2">
                                <a href="<?php echo e(route('receivables.show', $receivable)); ?>"
                                   class="w-8 h-8 rounded-lg hover:bg-blue-50 flex items-center justify-center text-blue-600"
                                   title="Detail">
                                    <i class="fas fa-eye"></i>
                                </a>
                                <?php if($receivable->status != 'LUNAS'): ?>
                                <button onclick="showPayModal('<?php echo e($receivable->id); ?>', '<?php echo e($receivable->no_piutang); ?>', <?php echo e($receivable->sisa_piutang); ?>)"
                                        class="w-8 h-8 rounded-lg hover:bg-green-50 flex items-center justify-center text-green-600"
                                        title="Bayar">
                                    <i class="fas fa-credit-card"></i>
                                </button>
                                <?php endif; ?>
                            </div>
                        </td>
                    </tr>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                    <tr>
                        <td colspan="9" class="px-6 py-12 text-center">
                            <div class="flex flex-col items-center">
                                <div class="w-16 h-16 bg-gray-100 rounded-full flex items-center justify-center mb-4">
                                    <i class="fas fa-hand-holding-usd text-3xl text-gray-400"></i>
                                </div>
                                <h3 class="text-lg font-medium text-gray-900 mb-2">Tidak ada data piutang</h3>
                                <p class="text-gray-600">Belum ada transaksi kredit</p>
                            </div>
                        </td>
                    </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>

        
        <?php if($receivables->hasPages()): ?>
        <div class="px-6 py-4 border-t border-gray-100">
            <?php echo e($receivables->withQueryString()->links()); ?>

        </div>
        <?php endif; ?>
    </div>
</div>


<div id="payModal" class="fixed inset-0 bg-black bg-opacity-50 hidden items-center justify-center z-50">
    <div class="bg-white rounded-2xl max-w-md w-full p-6">
        <h3 class="text-xl font-bold mb-4">Bayar Piutang</h3>
        <form id="payForm" method="POST">
            <?php echo csrf_field(); ?>
            <div class="space-y-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">
                        No. Piutang
                    </label>
                    <input type="text" id="pay_no_piutang" readonly
                           class="w-full px-4 py-2 bg-gray-50 border border-gray-200 rounded-lg">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">
                        Sisa Piutang
                    </label>
                    <input type="text" id="pay_sisa" readonly
                           class="w-full px-4 py-2 bg-gray-50 border border-gray-200 rounded-lg">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">
                        Jumlah Bayar <span class="text-red-500">*</span>
                    </label>
                    <input type="number" name="jumlah_bayar" id="jumlah_bayar" required min="1"
                           class="w-full px-4 py-2 border border-gray-200 rounded-lg focus:ring-2 focus:ring-blue-500">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">
                        Metode Bayar
                    </label>
                    <select name="metode_bayar" class="w-full px-4 py-2 border border-gray-200 rounded-lg">
                        <option value="tunai">Tunai</option>
                        <option value="transfer">Transfer</option>
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">
                        Keterangan
                    </label>
                    <input type="text" name="keterangan"
                           class="w-full px-4 py-2 border border-gray-200 rounded-lg"
                           placeholder="Opsional">
                </div>
                <div class="flex gap-3 pt-4">
                    <button type="submit"
                            class="flex-1 px-4 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700">
                        Bayar
                    </button>
                    <button type="button" onclick="closePayModal()"
                            class="flex-1 px-4 py-2 border border-gray-200 rounded-lg hover:bg-gray-50">
                        Batal
                    </button>
                </div>
            </div>
        </form>
    </div>
</div>

<script>
function showPayModal(id, noPiutang, sisa) {
    document.getElementById('payForm').action = '/receivables/' + id + '/pay';
    document.getElementById('pay_no_piutang').value = noPiutang;
    document.getElementById('pay_sisa').value = 'Rp ' + new Intl.NumberFormat('id-ID').format(sisa);
    document.getElementById('jumlah_bayar').max = sisa;
    document.getElementById('payModal').classList.remove('hidden');
    document.getElementById('payModal').classList.add('flex');
}

function closePayModal() {
    document.getElementById('payModal').classList.add('hidden');
    document.getElementById('payModal').classList.remove('flex');
}
</script>

<style>
.badge {
    display: inline-flex;
    align-items: center;
    padding: 0.25rem 0.75rem;
    border-radius: 9999px;
    font-size: 0.75rem;
    font-weight: 500;
}
.stat-card {
    position: relative;
    background: rgba(255, 255, 255, 0.8);
    backdrop-filter: blur(8px);
    border-radius: 1.5rem;
    padding: 1.5rem;
    border: 1px solid rgba(255, 255, 255, 0.5);
    transition: all 0.3s;
}
.stat-card:hover {
    transform: translateY(-4px);
    box-shadow: 0 20px 25px -5px rgba(0,0,0,0.1);
}
.stat-card-glow {
    position: absolute;
    inset: -0.25rem;
    border-radius: 1.75rem;
    filter: blur(12px);
    opacity: 0;
    transition: opacity 0.5s;
    z-index: -1;
}
.stat-card:hover .stat-card-glow {
    opacity: 0.5;
}
.glass-effect {
    background: rgba(255, 255, 255, 0.7);
    backdrop-filter: blur(10px);
    border: 1px solid rgba(59, 130, 246, 0.1);
}
</style>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH D:\PROJECT3\Toko-Roni-Mobile-App\tokoroni-app\resources\views\receivables\index.blade.php ENDPATH**/ ?>