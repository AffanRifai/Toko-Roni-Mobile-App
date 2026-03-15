<?php $__env->startSection('title', 'Daftar Member'); ?>
<?php $__env->startSection('page-title', 'Manajemen Member'); ?>
<?php $__env->startSection('page-subtitle', 'Kelola data member dan piutang'); ?>

<?php $__env->startSection('content'); ?>
<div class="min-h-screen bg-gradient-to-br from-blue-50/50 to-purple-50/30 p-4 md:p-6">

    
    <div class="glass-effect rounded-3xl p-6 md:p-8 shadow-elegant mb-6 animate-fade-in">
        <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-4">
            <div class="flex items-center gap-4">
                <div class="relative">
                    <div class="w-16 h-16 rounded-2xl bg-gradient-to-br from-blue-500 to-purple-600 flex items-center justify-center shadow-lg">
                        <i class="fas fa-users text-2xl text-white"></i>
                    </div>
                    <div class="absolute -inset-1 bg-gradient-to-r from-blue-500 to-purple-600 rounded-2xl blur-xl opacity-20"></div>
                </div>
                <div>
                    <h1 class="text-3xl font-bold text-gray-800">Daftar Member</h1>
                    <p class="text-gray-600 mt-1">Kelola data member dan pantau piutang</p>
                </div>
            </div>
            <a href="<?php echo e(route('members.create')); ?>"
               class="group flex items-center gap-2 px-6 py-3 bg-gradient-to-r from-blue-500 to-purple-600 text-white rounded-xl hover:shadow-lg hover:-translate-y-0.5 transition-all">
                <i class="fas fa-user-plus group-hover:scale-110 transition-transform"></i>
                <span>Tambah Member</span>
            </a>
        </div>
    </div>

    
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
        <div class="stat-card group">
            <div class="stat-card-glow bg-gradient-to-r from-blue-500 to-cyan-500"></div>
            <div class="stat-card-content">
                <p class="text-sm text-gray-500">Total Member</p>
                <h3 class="text-2xl font-bold text-gray-800"><?php echo e(number_format($stats['total'])); ?></h3>
                <p class="text-xs text-gray-400 mt-1"><?php echo e($stats['active']); ?> aktif</p>
            </div>
        </div>

        <div class="stat-card group">
            <div class="stat-card-glow bg-gradient-to-r from-green-500 to-emerald-500"></div>
            <div class="stat-card-content">
                <p class="text-sm text-gray-500">Total Piutang</p>
                <h3 class="text-2xl font-bold text-amber-600">Rp <?php echo e(number_format($stats['total_piutang'])); ?></h3>
                <p class="text-xs text-gray-400 mt-1">Dari <?php echo e($stats['total_limit']); ?> limit</p>
            </div>
        </div>

        <div class="stat-card group">
            <div class="stat-card-glow bg-gradient-to-r from-purple-500 to-pink-500"></div>
            <div class="stat-card-content">
                <p class="text-sm text-gray-500">Member Aktif</p>
                <h3 class="text-2xl font-bold text-green-600"><?php echo e($stats['active']); ?></h3>
                <p class="text-xs text-gray-400 mt-1"><?php echo e(round(($stats['active']/$stats['total'])*100)); ?>% dari total</p>
            </div>
        </div>

        <div class="stat-card group">
            <div class="stat-card-glow bg-gradient-to-r from-orange-500 to-red-500"></div>
            <div class="stat-card-content">
                <p class="text-sm text-gray-500">Rata-rata Piutang</p>
                <h3 class="text-2xl font-bold text-gray-800">Rp <?php echo e(number_format($stats['total_piutang'] / max($stats['total'], 1))); ?></h3>
                <p class="text-xs text-gray-400 mt-1">Per member</p>
            </div>
        </div>
    </div>

    
    <div class="glass-effect rounded-2xl p-5 mb-6">
        <form method="GET" class="grid grid-cols-1 md:grid-cols-4 gap-4">
            <div class="relative">
                <i class="fas fa-search absolute left-3 top-1/2 transform -translate-y-1/2 text-gray-400"></i>
                <input type="text" name="search" value="<?php echo e(request('search')); ?>"
                       placeholder="Cari nama, kode, telepon..."
                       class="w-full pl-10 pr-4 py-3 bg-white/50 border border-gray-200 rounded-xl focus:ring-2 focus:ring-blue-500">
            </div>

            <div class="relative">
                <select name="tipe" class="w-full px-4 py-3 bg-white/50 border border-gray-200 rounded-xl appearance-none">
                    <option value="all">Semua Tipe</option>
                    <option value="biasa" <?php echo e(request('tipe') == 'biasa' ? 'selected' : ''); ?>>Biasa</option>
                    <option value="gold" <?php echo e(request('tipe') == 'gold' ? 'selected' : ''); ?>>Gold</option>
                    <option value="platinum" <?php echo e(request('tipe') == 'platinum' ? 'selected' : ''); ?>>Platinum</option>
                </select>
                <i class="fas fa-chevron-down absolute right-4 top-1/2 transform -translate-y-1/2 text-gray-400"></i>
            </div>

            <div class="relative">
                <select name="status" class="w-full px-4 py-3 bg-white/50 border border-gray-200 rounded-xl appearance-none">
                    <option value="all">Semua Status</option>
                    <option value="active" <?php echo e(request('status') == 'active' ? 'selected' : ''); ?>>Aktif</option>
                    <option value="inactive" <?php echo e(request('status') == 'inactive' ? 'selected' : ''); ?>>Nonaktif</option>
                </select>
                <i class="fas fa-chevron-down absolute right-4 top-1/2 transform -translate-y-1/2 text-gray-400"></i>
            </div>

            <div class="flex gap-2">
                <button type="submit" class="flex-1 px-4 py-3 bg-blue-600 text-white rounded-xl hover:bg-blue-700">
                    <i class="fas fa-filter mr-2"></i>Filter
                </button>
                <a href="<?php echo e(route('members.index')); ?>" class="px-4 py-3 border border-gray-200 rounded-xl hover:bg-gray-50">
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
                        <th class="px-6 py-4 text-left text-sm font-semibold text-gray-600">Kode</th>
                        <th class="px-6 py-4 text-left text-sm font-semibold text-gray-600">Nama</th>
                        <th class="px-6 py-4 text-left text-sm font-semibold text-gray-600">Kontak</th>
                        <th class="px-6 py-4 text-left text-sm font-semibold text-gray-600">Tipe</th>
                        <th class="px-6 py-4 text-left text-sm font-semibold text-gray-600">Limit</th>
                        <th class="px-6 py-4 text-left text-sm font-semibold text-gray-600">Piutang</th>
                        <th class="px-6 py-4 text-left text-sm font-semibold text-gray-600">Sisa Limit</th>
                        <th class="px-6 py-4 text-left text-sm font-semibold text-gray-600">Status</th>
                        <th class="px-6 py-4 text-left text-sm font-semibold text-gray-600">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    <?php $__empty_1 = true; $__currentLoopData = $members; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $member): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                    <tr class="hover:bg-white/30 transition-colors">
                        <td class="px-6 py-4">
                            <span class="font-mono text-sm"><?php echo e($member->kode_member); ?></span>
                        </td>
                        <td class="px-6 py-4">
                            <div class="font-medium text-gray-900"><?php echo e($member->nama); ?></div>
                            <?php if($member->email): ?>
                                <div class="text-xs text-gray-500"><?php echo e($member->email); ?></div>
                            <?php endif; ?>
                        </td>
                        <td class="px-6 py-4">
                            <?php if($member->no_telepon): ?>
                                <div class="text-sm"><?php echo e($member->no_telepon); ?></div>
                            <?php else: ?>
                                <span class="text-gray-400">-</span>
                            <?php endif; ?>
                        </td>
                        <td class="px-6 py-4">
                            <span class="badge <?php echo e($member->tipe_member == 'platinum' ? 'bg-purple-100 text-purple-800' :
                                ($member->tipe_member == 'gold' ? 'bg-yellow-100 text-yellow-800' : 'bg-gray-100 text-gray-800')); ?>">
                                <?php echo e(ucfirst($member->tipe_member)); ?>

                            </span>
                        </td>
                        <td class="px-6 py-4 font-medium">Rp <?php echo e(number_format($member->limit_kredit)); ?></td>
                        <td class="px-6 py-4">
                            <span class="font-medium <?php echo e($member->total_piutang > 0 ? 'text-amber-600' : 'text-green-600'); ?>">
                                Rp <?php echo e(number_format($member->total_piutang)); ?>

                            </span>
                        </td>
                        <td class="px-6 py-4">
                            <?php
                                $sisa = $member->limit_kredit - $member->total_piutang;
                                $percentage = $member->limit_kredit > 0 ? ($sisa / $member->limit_kredit) * 100 : 0;
                            ?>
                            <div class="flex items-center gap-2">
                                <span class="text-sm font-medium <?php echo e($sisa > 0 ? 'text-green-600' : 'text-red-600'); ?>">
                                    Rp <?php echo e(number_format($sisa)); ?>

                                </span>
                                <div class="w-16 h-2 bg-gray-200 rounded-full overflow-hidden">
                                    <div class="h-full <?php echo e($percentage > 50 ? 'bg-green-500' : ($percentage > 20 ? 'bg-yellow-500' : 'bg-red-500')); ?>"
                                         style="width: <?php echo e($percentage); ?>%"></div>
                                </div>
                            </div>
                        </td>
                        <td class="px-6 py-4">
                            <span class="badge <?php echo e($member->is_active ? 'bg-green-100 text-green-800' : 'bg-gray-100 text-gray-800'); ?>">
                                <?php echo e($member->is_active ? 'Aktif' : 'Nonaktif'); ?>

                            </span>
                        </td>
                        <td class="px-6 py-4">
                            <div class="flex items-center gap-2">
                                <a href="<?php echo e(route('members.show', $member)); ?>"
                                   class="w-8 h-8 rounded-lg hover:bg-blue-50 flex items-center justify-center text-blue-600"
                                   title="Detail">
                                    <i class="fas fa-eye"></i>
                                </a>
                                <a href="<?php echo e(route('members.edit', $member)); ?>"
                                   class="w-8 h-8 rounded-lg hover:bg-yellow-50 flex items-center justify-center text-yellow-600"
                                   title="Edit">
                                    <i class="fas fa-edit"></i>
                                </a>
                                <a href="<?php echo e(route('members.receivables', $member)); ?>"
                                   class="w-8 h-8 rounded-lg hover:bg-purple-50 flex items-center justify-center text-purple-600"
                                   title="Piutang">
                                    <i class="fas fa-hand-holding-usd"></i>
                                </a>
                                <form action="<?php echo e(route('members.toggle-status', $member)); ?>" method="POST" class="inline">
                                    <?php echo csrf_field(); ?>
                                    <button type="submit"
                                            class="w-8 h-8 rounded-lg hover:bg-gray-50 flex items-center justify-center text-gray-600"
                                            title="<?php echo e($member->is_active ? 'Nonaktifkan' : 'Aktifkan'); ?>">
                                        <i class="fas <?php echo e($member->is_active ? 'fa-ban' : 'fa-check'); ?>"></i>
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                    <tr>
                        <td colspan="9" class="px-6 py-12 text-center">
                            <div class="flex flex-col items-center">
                                <div class="w-16 h-16 bg-gray-100 rounded-full flex items-center justify-center mb-4">
                                    <i class="fas fa-users text-3xl text-gray-400"></i>
                                </div>
                                <h3 class="text-lg font-medium text-gray-900 mb-2">Belum ada member</h3>
                                <p class="text-gray-600 mb-4">Mulai dengan menambahkan member pertama</p>
                                <a href="<?php echo e(route('members.create')); ?>"
                                   class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700">
                                    <i class="fas fa-plus mr-2"></i>Tambah Member
                                </a>
                            </div>
                        </td>
                    </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>

        
        <?php if($members->hasPages()): ?>
        <div class="px-6 py-4 border-t border-gray-100">
            <?php echo e($members->withQueryString()->links()); ?>

        </div>
        <?php endif; ?>
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

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH D:\PROJECT3\Toko-Roni-Mobile-App\tokoroni-app\resources\views/members/index.blade.php ENDPATH**/ ?>