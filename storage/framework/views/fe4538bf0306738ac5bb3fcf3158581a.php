<?php $__env->startSection('title', 'Edit Member'); ?>
<?php $__env->startSection('page-title', 'Edit Data Member'); ?>
<?php $__env->startSection('page-subtitle', 'Perbarui informasi member'); ?>

<?php $__env->startSection('content'); ?>
<div class="min-h-screen bg-gradient-to-br from-blue-50/50 to-purple-50/30 p-4 md:p-6">
    <div class="glass-effect rounded-3xl p-6 max-w-3xl mx-auto">
        <form action="<?php echo e(route('members.update', $member)); ?>" method="POST" class="space-y-6">
            <?php echo csrf_field(); ?>
            <?php echo method_field('PUT'); ?>

            
            <div class="bg-blue-50/50 rounded-xl p-4 mb-4">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-full bg-blue-500 flex items-center justify-center text-white font-bold">
                        <?php echo e(strtoupper(substr($member->nama, 0, 1))); ?>

                    </div>
                    <div>
                        <h3 class="font-semibold text-gray-900"><?php echo e($member->nama); ?></h3>
                        <p class="text-sm text-gray-600">Kode Member: <span class="font-mono"><?php echo e($member->kode_member); ?></span></p>
                    </div>
                </div>
            </div>

            
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">
                    Nama Lengkap <span class="text-red-500">*</span>
                </label>
                <input type="text" name="nama" value="<?php echo e(old('nama', $member->nama)); ?>" required
                       class="w-full px-4 py-3 border border-gray-200 rounded-xl focus:ring-2 focus:ring-blue-500 <?php $__errorArgs = ['nama'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> border-red-500 <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>"
                       placeholder="Masukkan nama lengkap">
                <?php $__errorArgs = ['nama'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                    <p class="mt-1 text-sm text-red-600"><?php echo e($message); ?></p>
                <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
            </div>

            
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">
                        Email
                    </label>
                    <input type="email" name="email" value="<?php echo e(old('email', $member->email)); ?>"
                           class="w-full px-4 py-3 border border-gray-200 rounded-xl focus:ring-2 focus:ring-blue-500"
                           placeholder="email@example.com">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">
                        No. Telepon
                    </label>
                    <input type="text" name="no_telepon" value="<?php echo e(old('no_telepon', $member->no_telepon)); ?>"
                           class="w-full px-4 py-3 border border-gray-200 rounded-xl focus:ring-2 focus:ring-blue-500"
                           placeholder="08123456789">
                </div>
            </div>

            
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">
                    Alamat
                </label>
                <textarea name="alamat" rows="3"
                          class="w-full px-4 py-3 border border-gray-200 rounded-xl focus:ring-2 focus:ring-blue-500"
                          placeholder="Alamat lengkap"><?php echo e(old('alamat', $member->alamat)); ?></textarea>
            </div>

            
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">
                        Tipe Member <span class="text-red-500">*</span>
                    </label>
                    <select name="tipe_member" required
                            class="w-full px-4 py-3 border border-gray-200 rounded-xl focus:ring-2 focus:ring-blue-500">
                        <option value="biasa" <?php echo e(old('tipe_member', $member->tipe_member) == 'biasa' ? 'selected' : ''); ?>>Biasa</option>
                        <option value="gold" <?php echo e(old('tipe_member', $member->tipe_member) == 'gold' ? 'selected' : ''); ?>>Gold</option>
                        <option value="platinum" <?php echo e(old('tipe_member', $member->tipe_member) == 'platinum' ? 'selected' : ''); ?>>Platinum</option>
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">
                        Limit Kredit (Rp) <span class="text-red-500">*</span>
                    </label>
                    <input type="number" name="limit_kredit" value="<?php echo e(old('limit_kredit', $member->limit_kredit)); ?>" min="0" required
                           class="w-full px-4 py-3 border border-gray-200 rounded-xl focus:ring-2 focus:ring-blue-500">
                </div>
            </div>

            
            <?php if($member->total_piutang > 0): ?>
            <div class="bg-yellow-50 border border-yellow-200 rounded-xl p-4">
                <div class="flex items-start gap-3">
                    <div class="text-yellow-600">
                        <i class="fas fa-exclamation-triangle"></i>
                    </div>
                    <div>
                        <h4 class="font-medium text-yellow-800">Perhatian!</h4>
                        <p class="text-sm text-yellow-700">
                            Member ini memiliki piutang sebesar <strong>Rp <?php echo e(number_format($member->total_piutang)); ?></strong>.
                            Perubahan limit kredit akan mempengaruhi sisa limit member.
                        </p>
                    </div>
                </div>
            </div>
            <?php endif; ?>

            
            <div class="flex items-center gap-2">
                <input type="checkbox" name="is_active" id="is_active" value="1" <?php echo e(old('is_active', $member->is_active) ? 'checked' : ''); ?>

                       class="w-4 h-4 text-blue-600 border-gray-300 rounded focus:ring-blue-500">
                <label for="is_active" class="text-sm font-medium text-gray-700">
                    Aktifkan member
                </label>
            </div>

            
            <div class="flex gap-3 pt-4 border-t border-gray-200">
                <button type="submit"
                        class="flex-1 px-6 py-3 bg-gradient-to-r from-blue-500 to-purple-600 text-white rounded-xl hover:shadow-lg transition-all">
                    <i class="fas fa-save mr-2"></i>
                    Update Member
                </button>
                <a href="<?php echo e(route('members.show', $member)); ?>"
                   class="px-6 py-3 border border-gray-200 rounded-xl hover:bg-gray-50 transition-all">
                    Batal
                </a>
            </div>
        </form>
    </div>
</div>

<style>
.glass-effect {
    background: rgba(255, 255, 255, 0.7);
    backdrop-filter: blur(10px);
    border: 1px solid rgba(59, 130, 246, 0.1);
}
</style>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH D:\PROJECT3\Toko-Roni-Mobile-App\tokoroni-app\resources\views\members\edit.blade.php ENDPATH**/ ?>