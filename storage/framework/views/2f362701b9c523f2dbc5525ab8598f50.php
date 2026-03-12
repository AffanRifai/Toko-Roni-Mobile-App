<?php $__env->startSection('title', 'Edit Pengguna'); ?>

<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.3/font/bootstrap-icons.css">
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css">
<link rel="stylesheet" href="<?php echo e(asset('css/edituser.css')); ?>">

<?php $__env->startSection('content'); ?>
    <div class="user-page">
        <div class="container-fluid px-3 px-md-4 py-4">

            
            <nav aria-label="breadcrumb" class="mb-4">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item">
                        <a href="<?php echo e(route('users.index')); ?>">
                            <i class="fas fa-users me-1"></i> Manajemen Pengguna
                        </a>
                    </li>
                    <li class="breadcrumb-item active">Edit Pengguna</li>
                </ol>
            </nav>

            
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-body d-flex align-items-center">
                    <div class="bg-primary text-white rounded-3 p-3 me-3">
                        <i class="fas fa-user-edit fs-4"></i>
                    </div>
                    <div>
                        <h5 class="fw-bold mb-1">Edit Pengguna</h5>
                        <p class="text-muted mb-0">Perbarui data akun pengguna</p>
                    </div>
                </div>
            </div>

            <div class="row">
                
                <div class="col-lg-8">
                    <div class="card border-0 shadow-sm">
                        <form method="POST" action="<?php echo e(route('users.update', $user->id)); ?>">
                            <?php echo csrf_field(); ?>
                            <?php echo method_field('PUT'); ?>

                            <div class="card-body">

                                <?php if($errors->any()): ?>
                                    <div class="alert alert-danger">
                                        <strong>Terjadi kesalahan:</strong>
                                        <ul class="mb-0 mt-2">
                                            <?php $__currentLoopData = $errors->all(); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $error): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                                <li><?php echo e($error); ?></li>
                                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                        </ul>
                                    </div>
                                <?php endif; ?>

                                <div class="row g-3">

                                    
                                    <div class="col-md-6">
                                        <label class="form-label fw-semibold">Nama Lengkap</label>
                                        <input type="text" name="name" class="form-control"
                                            value="<?php echo e(old('name', $user->name)); ?>" required>
                                    </div>

                                    
                                    <div class="col-md-6">
                                        <label class="form-label fw-semibold">Email</label>
                                        <input type="email" name="email" class="form-control"
                                            value="<?php echo e(old('email', $user->email)); ?>" required>
                                    </div>

                                    
                                    <div class="col-md-6">
                                        <label class="form-label fw-semibold">
                                            Password Baru <small class="text-muted">(opsional)</small>
                                        </label>
                                        <input type="password" name="password" class="form-control"
                                            placeholder="Kosongkan jika tidak diubah">
                                    </div>

                                    
                                    <div class="col-md-6">
                                        <label class="form-label fw-semibold">Konfirmasi Password</label>
                                        <input type="password" name="password_confirmation" class="form-control">
                                    </div>

                                    
                                     <div class="col-md-6">
                                        <label for="role" class="form-label fw-semibold">
                                            <i class="fas fa-user-tag me-1 text-primary"></i> Peran (Role)
                                            <span class="text-danger">*</span>
                                        </label>
                                        <div class="input-group">
                                            <span class="input-group-text bg-light border-end-0">
                                                <i class="fas fa-users-cog text-muted"></i>
                                            </span>
                                            <select name="role" id="role"
                                                class="form-select border-start-0 <?php $__errorArgs = ['role'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> is-invalid <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>"
                                                required>
                                                <option value="">Pilih Peran</option>
                                                <option value="owner" <?php echo e(old('role') == 'owner' ? 'selected' : ''); ?>>
                                                    Owner (Administrator)
                                                </option>
                                                <option value="kasir" <?php echo e(old('role') == 'kasir' ? 'selected' : ''); ?>>
                                                    Kasir
                                                </option>
                                                <option value="kepala_gudang"
                                                    <?php echo e(old('role') == 'kepala_gudang' ? 'selected' : ''); ?>>
                                                    Kepala Gudang
                                                </option>
                                                <option value="logistik"
                                                    <?php echo e(old('role') == 'logistik' ? 'selected' : ''); ?>>
                                                    Staff Logistik
                                                </option>
                                                <option value="checker_barang"
                                                    <?php echo e(old('role') == 'checker_barang' ? 'selected' : ''); ?>>
                                                    Checker Barang
                                                </option>
                                                <option value="manager_toko_eceran"
                                                    <?php echo e(old('role') == 'manager_toko_eceran' ? 'selected' : ''); ?>>
                                                    Manager Toko Eceran
                                                </option>
                                            </select>
                                            <?php $__errorArgs = ['role'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                                                <div class="invalid-feedback">
                                                    <?php echo e($message); ?>

                                                </div>
                                            <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                                        </div>
                                        <small class="text-muted mt-1 d-block">
                                            Tentukan hak akses pengguna
                                        </small>
                                    </div>
                                    
                                    
                                    <div class="col-md-6">
                                        <label class="form-label fw-semibold">Jenis Toko</label>

                                        <input type="hidden" name="jenis_toko" id="jenis_toko_hidden"
                                            value="<?php echo e($user->jenis_toko); ?>">

                                        <select id="jenis_toko" class="form-select">
                                            <option value="">- Pilih -</option>
                                            <option value="grosir" <?php echo e($user->jenis_toko == 'grosir' ? 'selected' : ''); ?>>
                                                Grosir
                                            </option>
                                            <option value="eceran" <?php echo e($user->jenis_toko == 'eceran' ? 'selected' : ''); ?>>
                                                Eceran
                                            </option>
                                        </select>
                                    </div>

                                    
                                    <div class="col-md-6">
                                        <label class="form-label fw-semibold">Status Akun</label>
                                        <div class="form-check form-switch mt-2">
                                            <input type="hidden" name="is_active" value="0">
                                            <input class="form-check-input" type="checkbox" name="is_active" value="1"
                                                <?php echo e($user->is_active ? 'checked' : ''); ?>>
                                            <label class="form-check-label">Akun aktif</label>
                                        </div>
                                    </div>

                                </div>
                            </div>

                            
                            <div class="card-footer bg-white border-top">
                                <div class="d-flex justify-content-between">
                                    <a href="<?php echo e(route('users.index')); ?>" class="btn btn-outline-secondary">
                                        <i class="fas fa-arrow-left me-1"></i> Kembali
                                    </a>
                                    <button class="btn btn-primary">
                                        <i class="fas fa-save me-1"></i> Simpan
                                    </button>
                                </div>
                            </div>

                        </form>
                    </div>
                </div>

                
                <div class="col-lg-4">
                    <div class="card border-0 shadow-sm sticky-top" style="top:20px">
                        <div class="card-body small text-muted">
                            <ul class="mb-0 ps-3">
                                <li>Password hanya berubah jika diisi</li>
                                <li>Role menentukan hak akses</li>
                                <li>User nonaktif tidak bisa login</li>
                            </ul>
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </div>

    
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const role = document.getElementById('role');
            const jenisToko = document.getElementById('jenis_toko');
            const hiddenJenis = document.getElementById('jenis_toko_hidden');

            function syncJenisToko() {
                if (role.value === 'owner') {
                    jenisToko.disabled = true;
                    hiddenJenis.value = '';
                } else {
                    jenisToko.disabled = false;
                    hiddenJenis.value = jenisToko.value;
                }
            }

            jenisToko.addEventListener('change', () => {
                hiddenJenis.value = jenisToko.value;
            });

            role.addEventListener('change', syncJenisToko);
            syncJenisToko();
        });
    </script>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\laragon\www\tokoroni-app\resources\views/users/edit.blade.php ENDPATH**/ ?>