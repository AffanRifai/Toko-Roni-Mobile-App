<?php $__env->startSection('title', 'Manajemen Pengguna'); ?>
<script src="https://cdn.tailwindcss.com"></script>


<?php $__env->startSection('content'); ?>
<div class="min-h-screen bg-gray-50 p-4 md:p-6">
    <!-- Header Section -->
    <div class="mb-8">
        <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 md:gap-6 mb-6 pb-4 border-b border-gray-200">
            <div class="flex items-center gap-4">
                <div class="w-12 h-12 md:w-14 md:h-14 bg-blue-100 rounded-xl flex items-center justify-center">
                    <i class="fas fa-users text-xl md:text-2xl text-blue-600"></i>
                </div>
                <div>
                    <h1 class="text-2xl md:text-3xl font-bold text-gray-900">Manajemen Pengguna</h1>
                    <p class="text-gray-600 mt-1">Kelola pengguna dan akses sistem</p>
                </div>
            </div>

            <a href="<?php echo e(route('users.create')); ?>"
               class="inline-flex items-center gap-2 bg-gradient-to-r from-blue-600 to-blue-700 hover:from-blue-700 hover:to-blue-800 text-white font-semibold px-6 py-3 rounded-xl shadow-md hover:shadow-lg transition-all duration-200 transform hover:-translate-y-0.5">
                <i class="fas fa-plus"></i>
                Tambah Pengguna
            </a>
        </div>
    </div>

    <!-- Filter Section -->
    <div class="bg-white rounded-2xl shadow-sm border border-gray-200 p-6 mb-6">
        <div class="flex items-center gap-2 mb-6">
            <i class="fas fa-filter text-blue-600"></i>
            <h2 class="text-lg font-semibold text-gray-900">Filter Data</h2>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
            <!-- Search Input -->
            <div>
                <label for="searchInput" class="block text-sm font-medium text-gray-900 mb-2">
                    <i class="fas fa-search mr-2"></i>Cari Pengguna
                </label>
                <input type="text"
                       id="searchInput"
                       class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-all duration-200"
                       placeholder="Nama, email, atau role...">
            </div>

            <!-- Role Filter -->
            <div>
                <label for="roleFilter" class="block text-sm font-medium text-gray-900 mb-2">
                    <i class="fas fa-user-tag mr-2"></i>Role
                </label>
                <select id="roleFilter"
                        class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-all duration-200 bg-white">
                    <option value="">Semua Role</option>
                    <option value="owner">Owner</option>
                    <option value="kasir">Kasir</option>
                    <option value="gudang">Gudang</option>
                    <option value="admin">Admin</option>
                    <option value="logistik">Logistik</option>
                </select>
            </div>

            <!-- Status Filter -->
            <div>
                <label for="statusFilter" class="block text-sm font-medium text-gray-900 mb-2">
                    <i class="fas fa-circle mr-2"></i>Status
                </label>
                <select id="statusFilter"
                        class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-all duration-200 bg-white">
                    <option value="">Semua Status</option>
                    <option value="1">Aktif</option>
                    <option value="0">Nonaktif</option>
                </select>
            </div>
        </div>
    </div>

    <!-- Table Section -->
    <div class="bg-white rounded-2xl shadow-sm border border-gray-200 overflow-hidden">
        <!-- Table Header -->
        <div class="px-6 py-5 border-b border-gray-200">
            <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
                <div>
                    <h2 class="text-lg font-semibold text-gray-900">Daftar Pengguna</h2>
                    <p id="userCount" class="text-gray-600 text-sm mt-1">
                        <?php echo e($users->count()); ?> pengguna ditemukan
                    </p>
                </div>
            </div>
        </div>

        <!-- Table Content -->
        <div class="overflow-x-auto">
            <table class="w-full">
                <thead>
                    <tr class="bg-gray-50">
                        <th class="px-6 py-4 text-left text-xs font-semibold text-gray-900 uppercase tracking-wider border-b border-gray-200">
                            Pengguna
                        </th>
                        <th class="px-6 py-4 text-left text-xs font-semibold text-gray-900 uppercase tracking-wider border-b border-gray-200">
                            Role
                        </th>
                        <th class="px-6 py-4 text-left text-xs font-semibold text-gray-900 uppercase tracking-wider border-b border-gray-200">
                            Jenis Toko
                        </th>
                        <th class="px-6 py-4 text-left text-xs font-semibold text-gray-900 uppercase tracking-wider border-b border-gray-200">
                            Status
                        </th>
                        <th class="px-6 py-4 text-left text-xs font-semibold text-gray-900 uppercase tracking-wider border-b border-gray-200">
                            Bergabung
                        </th>
                        <th class="px-6 py-4 text-left text-xs font-semibold text-gray-900 uppercase tracking-wider border-b border-gray-200">
                            Aksi
                        </th>
                    </tr>
                </thead>
                <tbody id="userTable" class="divide-y divide-gray-200">
                    <?php $__empty_1 = true; $__currentLoopData = $users; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $user): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                    <tr class="user-row hover:bg-gray-50 transition-colors duration-150"
                        data-name="<?php echo e(strtolower($user->name)); ?>"
                        data-email="<?php echo e(strtolower($user->email)); ?>"
                        data-role="<?php echo e($user->role); ?>"
                        data-active="<?php echo e($user->is_active); ?>">

                        <!-- User Column -->
                        <td class="px-6 py-4">
                            <div class="flex items-center gap-3">
                                <div class="w-10 h-10 rounded-full bg-gradient-to-r from-blue-500 to-purple-600 flex items-center justify-center text-white font-semibold">
                                    <?php echo e(substr($user->name, 0, 1)); ?>

                                </div>
                                <div>
                                    <div class="font-medium text-gray-900"><?php echo e($user->name); ?></div>
                                    <div class="text-sm text-gray-600"><?php echo e($user->email); ?></div>
                                </div>
                            </div>
                        </td>

                        <!-- Role Column -->
                        <td class="px-6 py-4">
                            <?php
                                $roleColors = [
                                    'owner' => 'bg-yellow-100 text-yellow-800',
                                    'kasir' => 'bg-blue-100 text-blue-800',
                                    'gudang' => 'bg-green-100 text-green-800',
                                    'admin' => 'bg-purple-100 text-purple-800'
                                ];
                                $roleIcons = [
                                    'owner' => 'crown',
                                    'kasir' => 'cash-register',
                                    'gudang' => 'box',
                                    'admin' => 'user-shield'
                                ];
                            ?>
                            <span class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-full text-xs font-semibold <?php echo e($roleColors[$user->role] ?? 'bg-gray-100 text-gray-800'); ?>">
                                <i class="fas fa-<?php echo e($roleIcons[$user->role] ?? 'user'); ?> text-xs"></i>
                                <?php echo e(ucfirst($user->role)); ?>

                            </span>
                        </td>

                        <!-- Store Type Column -->
                        <td class="px-6 py-4">
                            <?php if($user->jenis_toko): ?>
                                <span class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-full text-xs font-semibold bg-gray-100 text-gray-800">
                                    <i class="fas fa-store text-xs"></i>
                                    <?php echo e($user->jenis_toko); ?>

                                </span>
                            <?php else: ?>
                                <span class="text-gray-400">-</span>
                            <?php endif; ?>
                        </td>

                        <!-- Status Column -->
                        <td class="px-6 py-4">
                            <?php if($user->is_active): ?>
                                <span class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-full text-xs font-semibold bg-green-100 text-green-800">
                                    <i class="fas fa-check-circle text-xs"></i>
                                    Aktif
                                </span>
                            <?php else: ?>
                                <span class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-full text-xs font-semibold bg-red-100 text-red-800">
                                    <i class="fas fa-times-circle text-xs"></i>
                                    Nonaktif
                                </span>
                            <?php endif; ?>
                        </td>

                        <!-- Date Column -->
                        <td class="px-6 py-4">
                            <div class="text-sm text-gray-900">
                                <?php echo e($user->created_at->format('d M Y')); ?>

                            </div>
                            <div class="text-xs text-gray-500">
                                <?php echo e($user->created_at->diffForHumans()); ?>

                            </div>
                        </td>

                        <!-- Action Column -->
                        <td class="px-6 py-4">
                            <div class="flex items-center gap-2">
                                <!-- Edit Button -->
                                <a href="<?php echo e(route('users.edit', $user->id)); ?>"
                                   class="w-9 h-9 flex items-center justify-center rounded-lg border border-gray-300 text-gray-600 hover:bg-blue-50 hover:text-blue-600 hover:border-blue-300 transition-all duration-200"
                                   title="Edit Pengguna">
                                    <i class="fas fa-edit"></i>
                                </a>

                                <!-- Face Registration Button -->
                                <?php if(auth()->guard()->check()): ?>
                                    <?php if(in_array(auth()->user()->role, ['owner', 'manager'])): ?>
                                        <a href="<?php echo e(route('users.face.registration', $user->id)); ?>"
                                           class="w-9 h-9 flex items-center justify-center rounded-lg border border-gray-300 text-gray-600 hover:bg-purple-50 hover:text-purple-600 hover:border-purple-300 transition-all duration-200"
                                           title="Daftar Wajah">
                                            <i class="fas fa-camera"></i>
                                        </a>
                                    <?php endif; ?>
                                <?php endif; ?>

                                <!-- Delete Button -->
                                <form action="<?php echo e(route('users.destroy', $user->id)); ?>" method="POST" class="inline">
                                    <?php echo csrf_field(); ?>
                                    <?php echo method_field('DELETE'); ?>
                                    <button type="submit"
                                            onclick="return confirm('Apakah Anda yakin ingin menghapus pengguna ini?')"
                                            class="w-9 h-9 flex items-center justify-center rounded-lg border border-gray-300 text-gray-600 hover:bg-red-50 hover:text-red-600 hover:border-red-300 transition-all duration-200"
                                            title="Hapus Pengguna">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                    <tr>
                        <td colspan="6" class="px-6 py-12">
                            <div class="text-center">
                                <div class="mx-auto w-16 h-16 flex items-center justify-center rounded-full bg-gray-100 mb-4">
                                    <i class="fas fa-users-slash text-3xl text-gray-400"></i>
                                </div>
                                <h3 class="text-lg font-semibold text-gray-900 mb-2">Belum ada pengguna</h3>
                                <p class="text-gray-600 mb-6">
                                    Tambahkan pengguna baru untuk mulai mengelola akses sistem
                                </p>
                                <a href="<?php echo e(route('users.create')); ?>"
                                   class="inline-flex items-center gap-2 bg-blue-600 hover:bg-blue-700 text-white font-semibold px-6 py-3 rounded-xl transition-all duration-200">
                                    <i class="fas fa-plus"></i>
                                    Tambah Pengguna Pertama
                                </a>
                            </div>
                        </td>
                    </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>

        <!-- Empty State Template (Hidden) -->
        <template id="emptyStateTemplate">
            <tr>
                <td colspan="6" class="px-6 py-12">
                    <div class="text-center">
                        <div class="mx-auto w-16 h-16 flex items-center justify-center rounded-full bg-gray-100 mb-4">
                            <i class="fas fa-search text-3xl text-gray-400"></i>
                        </div>
                        <h3 class="text-lg font-semibold text-gray-900 mb-2">Tidak ada pengguna ditemukan</h3>
                        <p class="text-gray-600">
                            Coba ubah filter pencarian Anda
                        </p>
                    </div>
                </td>
            </tr>
        </template>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const searchInput = document.getElementById('searchInput');
    const roleFilter = document.getElementById('roleFilter');
    const statusFilter = document.getElementById('statusFilter');
    const userRows = document.querySelectorAll('.user-row');
    const userCount = document.getElementById('userCount');
    const tbody = document.querySelector('#userTable');
    const emptyStateTemplate = document.getElementById('emptyStateTemplate');

    function filterUsers() {
        const searchTerm = searchInput.value.toLowerCase();
        const selectedRole = roleFilter.value;
        const selectedStatus = statusFilter.value;
        let visibleCount = 0;

        // Hapus empty state sebelumnya jika ada
        const existingEmptyState = document.querySelector('.empty-state-row');
        if (existingEmptyState) {
            existingEmptyState.remove();
        }

        userRows.forEach(row => {
            const name = row.dataset.name.toLowerCase();
            const email = row.dataset.email.toLowerCase();
            const role = row.dataset.role;
            const isActive = row.dataset.active;

            const matchesSearch = !searchTerm ||
                name.includes(searchTerm) ||
                email.includes(searchTerm);
            const matchesRole = !selectedRole || role === selectedRole;
            const matchesStatus = !selectedStatus || isActive === selectedStatus;

            const isVisible = matchesSearch && matchesRole && matchesStatus;
            row.style.display = isVisible ? '' : 'none';

            if (isVisible) visibleCount++;
        });

        // Update counter dengan animasi
        userCount.textContent = `${visibleCount} pengguna ditemukan`;

        // Tambahkan efek pada counter
        userCount.classList.add('scale-105');
        setTimeout(() => {
            userCount.classList.remove('scale-105');
        }, 300);

        //  empty state jika tidak ada user yang sesuai
        if (visibleCount === 0 && userRows.length > 0) {
            const emptyState = emptyStateTemplate.content.cloneNode(true);
            const emptyStateRow = emptyState.querySelector('tr');
            emptyStateRow.classList.add('empty-state-row');
            tbody.appendChild(emptyStateRow);
        }
    }

    // Event listeners dengan debounce untuk search
    let searchTimeout;
    searchInput.addEventListener('input', function() {
        clearTimeout(searchTimeout);
        searchTimeout = setTimeout(filterUsers, 300);
    });

    roleFilter.addEventListener('change', filterUsers);
    statusFilter.addEventListener('change', filterUsers);

    // Initialize filtering
    filterUsers();
});
</script>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH D:\laravel project 3\Toko-Roni-Mobile-App\resources\views/users/index.blade.php ENDPATH**/ ?>