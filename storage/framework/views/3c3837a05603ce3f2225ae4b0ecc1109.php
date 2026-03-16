<?php $__env->startSection('title', 'Tambah Member'); ?>
<?php $__env->startSection('page-title', 'Tambah Member Baru'); ?>
<?php $__env->startSection('page-subtitle', 'Isi data member dengan lengkap'); ?>

<?php $__env->startSection('content'); ?>
    <div class="min-h-screen bg-gradient-to-br from-blue-50/50 to-purple-50/30 p-4 md:p-6">
        <div class="glass-effect rounded-3xl p-6 max-w-3xl mx-auto">
            <form action="<?php echo e(route('members.store')); ?>" method="POST" class="space-y-6">
                <?php echo csrf_field(); ?>

                
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">
                        Nama Lengkap <span class="text-red-500">*</span>
                    </label>
                    <input type="text" name="nama" value="<?php echo e(old('nama')); ?>" required
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
                        <input type="email" name="email" value="<?php echo e(old('email')); ?>"
                            class="w-full px-4 py-3 border border-gray-200 rounded-xl focus:ring-2 focus:ring-blue-500"
                            placeholder="email@example.com">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">
                            No. Telepon
                        </label>
                        <input type="text" name="no_telepon" value="<?php echo e(old('no_telepon')); ?>"
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
                        placeholder="Alamat lengkap"><?php echo e(old('alamat')); ?></textarea>
                </div>

                
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">
                            Tipe Member <span class="text-red-500">*</span>
                        </label>
                        <select name="tipe_member" required
                            class="w-full px-4 py-3 border border-gray-200 rounded-xl focus:ring-2 focus:ring-blue-500">
                            <option value="biasa" <?php echo e(old('tipe_member') == 'biasa' ? 'selected' : ''); ?>>Biasa</option>
                            <option value="gold" <?php echo e(old('tipe_member') == 'gold' ? 'selected' : ''); ?>>Gold</option>
                            <option value="platinum" <?php echo e(old('tipe_member') == 'platinum' ? 'selected' : ''); ?>>Platinum
                            </option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">
                            Limit Kredit (Rp) <span class="text-red-500">*</span>
                        </label>
                        <input type="number" name="limit_kredit" value="<?php echo e(old('limit_kredit', 0)); ?>" min="0"
                            required
                            class="w-full px-4 py-3 border border-gray-200 rounded-xl focus:ring-2 focus:ring-blue-500">
                    </div>
                </div>

                
                <div class="flex items-center gap-2">
                    <input type="checkbox" name="is_active" id="is_active" value="1"
                        <?php echo e(old('is_active', true) ? 'checked' : ''); ?>

                        class="w-4 h-4 text-blue-600 border-gray-300 rounded focus:ring-blue-500">
                    <label for="is_active" class="text-sm font-medium text-gray-700">
                        Aktifkan member
                    </label>
                </div>

                
                <div class="flex gap-3 pt-4 border-t border-gray-200">
                    <button type="submit"
                        class="flex-1 px-6 py-3 bg-gradient-to-r from-blue-500 to-purple-600 text-white rounded-xl hover:shadow-lg transition-all">
                        <i class="fas fa-save mr-2"></i>
                        Simpan Member
                    </button>
                    <a href="<?php echo e(route('members.index')); ?>"
                        class="px-6 py-3 border border-gray-200 rounded-xl hover:bg-gray-50 transition-all">
                        Batal
                    </a>
                </div>
            </form>
        </div>
    </div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH D:\PROJECT3\Toko-Roni-Mobile-App\tokoroni-app\resources\views\members\create.blade.php ENDPATH**/ ?>