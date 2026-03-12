<?php $__env->startSection('title', 'Daftar Transaksi'); ?>

<?php $__env->startSection('content'); ?>
<div class="min-h-screen bg-gray-50 p-4 md:p-6">
    <!-- Header dengan Stats -->
    <div class="mb-8">
        <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 mb-6">
            <div>
                <h1 class="text-2xl md:text-3xl font-bold text-gray-800 mb-2">Daftar Transaksi</h1>
                <p class="text-gray-600">Kelola dan pantau semua transaksi penjualan</p>
            </div>
            <div class="mt-4 md:mt-0">
                <a href="<?php echo e(route('transactions.create')); ?>" class="inline-flex items-center gap-2 px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors">
                    <i class="fas fa-plus-circle"></i>
                    <span>Tambah Transaksi</span>
                </a>
            </div>
        </div>

        <!-- Stats Cards -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4 mb-8">
            <!-- Total Transaksi -->
            <div class="bg-white rounded-xl shadow-sm p-6">
                <div class="flex justify-between items-center">
                    <div>
                        <p class="text-gray-500 text-sm mb-2">Total Transaksi</p>
                        <p class="text-2xl font-bold text-gray-800"><?php echo e($transactions->total()); ?></p>
                        <p class="text-green-600 text-xs mt-2">
                            <i class="fas fa-arrow-up mr-1"></i>+12.5% dari bulan lalu
                        </p>
                    </div>
                    <div class="p-3 bg-blue-100 rounded-lg">
                        <i class="fas fa-shopping-cart text-blue-600 text-xl"></i>
                    </div>
                </div>
            </div>

            <!-- Total Pendapatan -->
            <div class="bg-white rounded-xl shadow-sm p-6">
                <div class="flex justify-between items-center">
                    <div>
                        <p class="text-gray-500 text-sm mb-2">Total Pendapatan</p>
                        <p class="text-2xl font-bold text-gray-800">Rp <?php echo e(number_format($totalRevenue, 0, ',', '.')); ?></p>
                        <p class="text-green-600 text-xs mt-2">
                            <i class="fas fa-arrow-up mr-1"></i>+8.3% dari bulan lalu
                        </p>
                    </div>
                    <div class="p-3 bg-green-100 rounded-lg">
                        <i class="fas fa-money-bill-wave text-green-600 text-xl"></i>
                    </div>
                </div>
            </div>

            <!-- Rata-rata/Transaksi -->
            <div class="bg-white rounded-xl shadow-sm p-6">
                <div class="flex justify-between items-center">
                    <div>
                        <p class="text-gray-500 text-sm mb-2">Rata-rata/Transaksi</p>
                        <p class="text-2xl font-bold text-gray-800">Rp <?php echo e(number_format($averageTransaction, 0, ',', '.')); ?></p>
                        <p class="text-green-600 text-xs mt-2">
                            <i class="fas fa-arrow-up mr-1"></i>+5.2% dari bulan lalu
                        </p>
                    </div>
                    <div class="p-3 bg-cyan-100 rounded-lg">
                        <i class="fas fa-chart-line text-cyan-600 text-xl"></i>
                    </div>
                </div>
            </div>

            <!-- Transaksi Hari Ini -->
            <div class="bg-white rounded-xl shadow-sm p-6">
                <div class="flex justify-between items-center">
                    <div>
                        <p class="text-gray-500 text-sm mb-2">Transaksi Hari Ini</p>
                        <p class="text-2xl font-bold text-gray-800"><?php echo e($todayTransactions); ?></p>
                        <p class="text-green-600 text-xs mt-2">
                            <i class="fas fa-arrow-up mr-1"></i>+3 transaksi dari kemarin
                        </p>
                    </div>
                    <div class="p-3 bg-yellow-100 rounded-lg">
                        <i class="fas fa-calendar-day text-yellow-600 text-xl"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Filter dan Search -->
    <div class="bg-white rounded-xl shadow-sm p-6 mb-6">
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div>
                <div class="relative">
                    <i class="fas fa-search absolute left-3 top-3 text-gray-400"></i>
                    <input type="text"
                           id="searchInput"
                           placeholder="Cari invoice, customer, atau kasir..."
                           class="w-full pl-10 pr-10 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-colors"
                           onkeyup="filterTable()">
                    <button onclick="clearSearch()"
                            class="absolute right-3 top-3 text-gray-400 hover:text-gray-600 transition-colors">
                        <i class="fas fa-times"></i>
                    </button>
                </div>
            </div>
            <div>
                <div class="flex gap-2">
                    <div class="relative">
                        <button class="px-4 py-2 border border-blue-200 text-blue-600 rounded-lg hover:bg-blue-50 flex items-center gap-2 transition-colors">
                            <i class="fas fa-filter"></i>
                            <span>Filter</span>
                            <i class="fas fa-chevron-down text-xs"></i>
                        </button>
                        <div class="absolute hidden bg-white shadow-lg rounded-lg mt-1 w-48 z-10">
                            <a href="<?php echo e(request()->fullUrlWithQuery(['filter' => 'today'])); ?>"
                               class="block px-4 py-2 hover:bg-gray-100">Hari Ini</a>
                            <a href="<?php echo e(request()->fullUrlWithQuery(['filter' => 'week'])); ?>"
                               class="block px-4 py-2 hover:bg-gray-100">Minggu Ini</a>
                            <a href="<?php echo e(request()->fullUrlWithQuery(['filter' => 'month'])); ?>"
                               class="block px-4 py-2 hover:bg-gray-100">Bulan Ini</a>
                            <div class="border-t my-1"></div>
                            <a href="<?php echo e(request()->fullUrlWithQuery(['filter' => 'cash'])); ?>"
                               class="block px-4 py-2 hover:bg-gray-100">Cash</a>
                            <a href="<?php echo e(request()->fullUrlWithQuery(['filter' => 'credit_card'])); ?>"
                               class="block px-4 py-2 hover:bg-gray-100">Credit Card</a>
                            <a href="<?php echo e(request()->fullUrlWithQuery(['filter' => 'e_wallet'])); ?>"
                               class="block px-4 py-2 hover:bg-gray-100">E-Wallet</a>
                        </div>
                    </div>
                    <div class="relative">
                        <button class="px-4 py-2 border border-green-200 text-green-600 rounded-lg hover:bg-green-50 flex items-center gap-2 transition-colors">
                            <i class="fas fa-download"></i>
                            <span>Export</span>
                            <i class="fas fa-chevron-down text-xs"></i>
                        </button>
                        <div class="absolute hidden bg-white shadow-lg rounded-lg mt-1 w-48 z-10">
                            <a href="#" class="block px-4 py-2 hover:bg-gray-100 flex items-center gap-2">
                                <i class="fas fa-file-excel text-green-600"></i>
                                Excel
                            </a>
                            <a href="#" class="block px-4 py-2 hover:bg-gray-100 flex items-center gap-2">
                                <i class="fas fa-file-pdf text-red-600"></i>
                                PDF
                            </a>
                            <a href="#" class="block px-4 py-2 hover:bg-gray-100 flex items-center gap-2">
                                <i class="fas fa-print text-gray-600"></i>
                                Print
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Main Table -->
    <div class="bg-white rounded-xl shadow-sm overflow-hidden">
        <!-- Table Header -->
        <div class="px-6 py-4 border-b border-gray-200 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-2">
            <div class="flex items-center gap-2">
                <i class="fas fa-list text-gray-600"></i>
                <h2 class="font-semibold text-gray-800">Riwayat Transaksi</h2>
            </div>
            <div class="text-sm text-gray-600">
                Menampilkan <?php echo e($transactions->count()); ?> dari <?php echo e($transactions->total()); ?> transaksi
            </div>
        </div>

        <!-- Table Content -->
        <div class="overflow-x-auto">
            <table class="w-full">
                <thead class="bg-gray-50">
                    <tr class="text-left text-sm font-medium text-gray-500 uppercase tracking-wider">
                        <th class="px-6 py-3">Invoice</th>
                        <th class="px-6 py-3">Tanggal</th>
                        <th class="px-6 py-3">Customer</th>
                        <th class="px-6 py-3 text-center">Status</th>
                        <th class="px-6 py-3 text-center">Pembayaran</th>
                        <th class="px-6 py-3">Kasir</th>
                        <th class="px-6 py-3 text-right">Total</th>
                        <th class="px-6 py-3 text-center">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200">
                    <?php $__empty_1 = true; $__currentLoopData = $transactions; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $transaction): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                    <tr class="transaction-row hover:bg-blue-50 transition-colors"
                        data-invoice="<?php echo e($transaction->invoice_number); ?>"
                        data-customer="<?php echo e(strtolower($transaction->customer_name)); ?>"
                        data-cashier="<?php echo e(strtolower($transaction->user->name)); ?>">
                        <!-- Invoice -->
                        <td class="px-6 py-4">
                            <div class="flex items-center gap-3">
                                <div class="p-2 bg-blue-100 rounded-lg">
                                    <i class="fas fa-receipt text-blue-600"></i>
                                </div>
                                <div>
                                    <div class="font-semibold text-gray-900"><?php echo e($transaction->invoice_number); ?></div>
                                    <div class="text-xs text-gray-500">ID: <?php echo e($transaction->id); ?></div>
                                </div>
                            </div>
                        </td>

                        <!-- Tanggal -->
                        <td class="px-6 py-4">
                            <div>
                                <div class="font-medium text-gray-900"><?php echo e($transaction->created_at->format('d M Y')); ?></div>
                                <div class="text-sm text-gray-500"><?php echo e($transaction->created_at->format('H:i')); ?></div>
                            </div>
                        </td>

                        <!-- Customer -->
                        <td class="px-6 py-4">
                            <?php if($transaction->customer_name): ?>
                                <div class="flex items-center gap-2">
                                    <div class="p-2 bg-gray-100 rounded-lg">
                                        <i class="fas fa-user text-gray-600"></i>
                                    </div>
                                    <span class="text-gray-900"><?php echo e($transaction->customer_name); ?></span>
                                </div>
                            <?php else: ?>
                                <span class="text-gray-400">-</span>
                            <?php endif; ?>
                        </td>

                        <!-- Status -->
                        <td class="px-6 py-4 text-center">
                            <?php
                                $statusColors = [
                                    'completed' => 'bg-green-100 text-green-800 border-green-200',
                                    'pending' => 'bg-yellow-100 text-yellow-800 border-yellow-200',
                                    'cancelled' => 'bg-red-100 text-red-800 border-red-200',
                                ];
                                $statusIcons = [
                                    'completed' => 'check-circle',
                                    'pending' => 'clock',
                                    'cancelled' => 'times-circle',
                                ];
                            ?>
                            <span class="inline-flex items-center gap-1 px-3 py-1 rounded-full border text-sm font-medium <?php echo e($statusColors[$transaction->status] ?? 'bg-gray-100 text-gray-800 border-gray-200'); ?>">
                                <i class="fas fa-<?php echo e($statusIcons[$transaction->status] ?? 'circle'); ?>"></i>
                                <?php echo e(ucfirst($transaction->status)); ?>

                            </span>
                        </td>

                        <!-- Pembayaran -->
                        <td class="px-6 py-4 text-center">
                            <?php
                                $paymentColors = [
                                    'cash' => 'bg-green-100 text-green-800 border-green-200',
                                    'debit_card' => 'bg-blue-100 text-blue-800 border-blue-200',
                                    'credit_card' => 'bg-purple-100 text-purple-800 border-purple-200',
                                    'e_wallet' => 'bg-cyan-100 text-cyan-800 border-cyan-200',
                                    'transfer' => 'bg-orange-100 text-orange-800 border-orange-200',
                                ];
                                $paymentIcons = [
                                    'cash' => 'money-bill-wave',
                                    'debit_card' => 'credit-card',
                                    'credit_card' => 'credit-card',
                                    'e_wallet' => 'mobile-alt',
                                    'transfer' => 'university',
                                ];
                            ?>
                            <div class="flex flex-col items-center gap-1">
                                <span class="inline-flex items-center gap-1 px-2 py-1 rounded-full border text-xs font-medium <?php echo e($paymentColors[$transaction->payment_method] ?? 'bg-gray-100 text-gray-800 border-gray-200'); ?>">
                                    <i class="fas fa-<?php echo e($paymentIcons[$transaction->payment_method] ?? 'credit-card'); ?>"></i>
                                    <?php echo e(ucfirst(str_replace('_', ' ', $transaction->payment_method))); ?>

                                </span>
                                <?php if($transaction->payment_method === 'credit_card'): ?>
                                    <span class="text-xs text-red-600 font-medium">
                                        <i class="fas fa-exclamation-triangle mr-1"></i>Hutang
                                    </span>
                                <?php endif; ?>
                            </div>
                        </td>

                        <!-- Kasir -->
                        <td class="px-6 py-4">
                            <div class="flex items-center gap-2">
                                <div class="p-2 bg-blue-100 rounded-lg">
                                    <i class="fas fa-user-tie text-blue-600"></i>
                                </div>
                                <span class="text-gray-900"><?php echo e($transaction->user->name); ?></span>
                            </div>
                        </td>

                        <!-- Total -->
                        <td class="px-6 py-4 text-right">
                            <div>
                                <div class="font-bold text-lg text-gray-900">Rp <?php echo e(number_format($transaction->total_amount, 0, ',', '.')); ?></div>
                                <div class="text-sm text-gray-500">
                                    <?php if($transaction->items): ?>
                                        <?php echo e($transaction->items->count()); ?> item
                                    <?php else: ?>
                                        0 item
                                    <?php endif; ?>
                                </div>
                            </div>
                        </td>

                        <!-- Actions -->
                        <td class="px-6 py-4 text-center">
                            <div class="flex items-center justify-center gap-1">
                                <!-- Detail Button -->
                                <a href="<?php echo e(route('transactions.show', $transaction)); ?>"
                                   class="p-2 text-blue-600 hover:bg-blue-100 rounded-lg transition-colors"
                                   data-tooltip="Detail">
                                    <i class="fas fa-eye"></i>
                                </a>

                                <!-- Print Button -->
                                <a href="<?php echo e(route('transactions.print', $transaction)); ?>"
                                   class="p-2 text-green-600 hover:bg-green-100 rounded-lg transition-colors"
                                   target="_blank"
                                   data-tooltip="Print">
                                    <i class="fas fa-print"></i>
                                </a>

                                <!-- Edit Button (Sementara Disabled) -->
                                <button class="p-2 text-yellow-600 hover:bg-yellow-100 rounded-lg transition-colors disabled:opacity-50 disabled:cursor-not-allowed"
                                        data-tooltip="Fitur edit dalam pengembangan"
                                        disabled>
                                    <i class="fas fa-edit"></i>
                                </button>

                                <?php if(auth()->user()->role === 'owner'): ?>
                                <!-- Delete Button -->
                                <button type="button"
                                        class="p-2 text-red-600 hover:bg-red-100 rounded-lg transition-colors"
                                        data-tooltip="Hapus"
                                        onclick="openDeleteModal('<?php echo e($transaction->id); ?>', '<?php echo e($transaction->invoice_number); ?>', '<?php echo e(number_format($transaction->total_amount, 0, ',', '.')); ?>')">
                                    <i class="fas fa-trash"></i>
                                </button>
                                <?php endif; ?>
                            </div>
                        </td>
                    </tr>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                    <tr>
                        <td colspan="8" class="px-6 py-12 text-center">
                            <div class="flex flex-col items-center justify-center">
                                <div class="p-4 bg-gray-100 rounded-full mb-4">
                                    <i class="fas fa-shopping-cart text-3xl text-gray-400"></i>
                                </div>
                                <h3 class="text-lg font-medium text-gray-700 mb-2">Belum ada transaksi</h3>
                                <p class="text-gray-500 mb-6">Mulai lakukan transaksi pertama Anda</p>
                                <a href="<?php echo e(route('transactions.create')); ?>"
                                   class="inline-flex items-center gap-2 px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors">
                                    <i class="fas fa-plus-circle"></i>
                                    <span>Tambah Transaksi</span>
                                </a>
                            </div>
                        </td>
                    </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>

        <!-- Pagination -->
        <?php if($transactions->hasPages()): ?>
        <div class="px-6 py-4 border-t border-gray-200 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
            <div class="text-sm text-gray-700">
                Menampilkan <span class="font-medium"><?php echo e($transactions->firstItem()); ?></span>
                sampai <span class="font-medium"><?php echo e($transactions->lastItem()); ?></span>
                dari <span class="font-medium"><?php echo e($transactions->total()); ?></span> transaksi
            </div>
            <div class="flex items-center gap-1">
                <?php echo e($transactions->links()); ?>

            </div>
        </div>
        <?php endif; ?>
    </div>
</div>

<!-- Delete Modal -->
<?php if(auth()->user()->role === 'owner'): ?>
<div id="deleteModal" class="fixed inset-0 bg-black bg-opacity-50 hidden items-center justify-center z-50">
    <div class="bg-white rounded-xl shadow-lg w-full max-w-md mx-4">
        <div class="p-6">
            <h3 class="text-lg font-semibold text-gray-800 mb-4">Konfirmasi Hapus</h3>
            <p class="text-gray-600 mb-4">Apakah Anda yakin ingin menghapus transaksi ini?</p>

            <div class="bg-yellow-50 border border-yellow-200 rounded-lg p-3 mb-4">
                <div class="flex items-center gap-2">
                    <i class="fas fa-exclamation-triangle text-yellow-600"></i>
                    <span class="text-sm text-yellow-800">Data yang dihapus tidak dapat dikembalikan!</span>
                </div>
            </div>

            <div class="bg-gray-50 rounded-lg p-4 mb-6">
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <p class="text-sm text-gray-500">Invoice</p>
                        <p class="font-semibold text-gray-800" id="modalInvoice"></p>
                    </div>
                    <div>
                        <p class="text-sm text-gray-500">Total</p>
                        <p class="font-semibold text-gray-800" id="modalTotal">Rp 0</p>
                    </div>
                </div>
            </div>

            <div class="flex gap-3">
                <button onclick="closeDeleteModal()"
                        class="flex-1 px-4 py-2 border border-gray-300 text-gray-700 rounded-lg hover:bg-gray-50 transition-colors">
                    Batal
                </button>
                <form id="deleteForm" method="POST" class="flex-1">
                    <?php echo csrf_field(); ?>
                    <?php echo method_field('DELETE'); ?>
                    <button type="submit"
                            class="w-full px-4 py-2 bg-red-600 text-white rounded-lg hover:bg-red-700 transition-colors">
                        Hapus Transaksi
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>
<?php endif; ?>

<style>
    /* Tooltip Styles */
    [data-tooltip] {
        position: relative;
    }

    [data-tooltip]:before {
        content: attr(data-tooltip);
        position: absolute;
        bottom: 100%;
        left: 50%;
        transform: translateX(-50%);
        padding: 4px 8px;
        background-color: #374151;
        color: white;
        font-size: 12px;
        border-radius: 4px;
        white-space: nowrap;
        opacity: 0;
        visibility: hidden;
        transition: opacity 0.2s, visibility 0.2s;
        z-index: 10;
    }

    [data-tooltip]:after {
        content: '';
        position: absolute;
        bottom: 100%;
        left: 50%;
        transform: translateX(-50%);
        border-width: 4px;
        border-style: solid;
        border-color: #374151 transparent transparent transparent;
        opacity: 0;
        visibility: hidden;
        transition: opacity 0.2s, visibility 0.2s;
    }

    [data-tooltip]:hover:before,
    [data-tooltip]:hover:after {
        opacity: 1;
        visibility: visible;
    }

    /* Table row animation */
    .transaction-row {
        animation: fadeIn 0.3s ease-out;
    }

    @keyframes fadeIn {
        from {
            opacity: 0;
            transform: translateY(10px);
        }
        to {
            opacity: 1;
            transform: translateY(0);
        }
    }

    /* Highlight today's transactions */
    .today-transaction {
        border-left: 4px solid #3b82f6 !important;
    }

    /* Pagination styling */
    .pagination {
        display: flex;
        gap: 2px;
    }

    .page-link {
        padding: 6px 12px;
        border-radius: 6px;
        color: #3b82f6;
        font-weight: 500;
        transition: all 0.2s;
    }

    .page-link:hover {
        background-color: rgba(59, 130, 246, 0.1);
    }

    .page-item.active .page-link {
        background-color: #3b82f6;
        color: white;
    }

    /* Custom scrollbar */
    ::-webkit-scrollbar {
        width: 6px;
        height: 6px;
    }

    ::-webkit-scrollbar-track {
        background: #f1f1f1;
        border-radius: 3px;
    }

    ::-webkit-scrollbar-thumb {
        background: #c1c1c1;
        border-radius: 3px;
    }

    ::-webkit-scrollbar-thumb:hover {
        background: #a8a8a8;
    }
</style>

<script>
    // Tooltip functionality
    function initTooltips() {
        const tooltips = document.querySelectorAll('[data-tooltip]');
        tooltips.forEach(element => {
            element.addEventListener('mouseenter', function() {
                const tooltip = this.getAttribute('data-tooltip');
                // Bootstrap tooltips are disabled, using custom tooltips instead
            });
        });
    }

    // Search functionality
    function filterTable() {
        const input = document.getElementById('searchInput');
        const filter = input.value.toLowerCase();
        const rows = document.querySelectorAll('.transaction-row');

        rows.forEach(row => {
            const invoice = row.getAttribute('data-invoice').toLowerCase();
            const customer = row.getAttribute('data-customer');
            const cashier = row.getAttribute('data-cashier');

            if (invoice.includes(filter) || customer.includes(filter) || cashier.includes(filter)) {
                row.style.display = '';
            } else {
                row.style.display = 'none';
            }
        });
    }

    function clearSearch() {
        document.getElementById('searchInput').value = '';
        filterTable();
    }

    // Dropdown functionality
    function initDropdowns() {
        const dropdownButtons = document.querySelectorAll('button:has(.fa-chevron-down)');

        dropdownButtons.forEach(button => {
            const dropdown = button.nextElementSibling;

            if (dropdown && dropdown.classList.contains('hidden')) {
                button.addEventListener('click', (e) => {
                    e.stopPropagation();
                    dropdown.classList.toggle('hidden');
                });

                // Close dropdown when clicking outside
                document.addEventListener('click', () => {
                    dropdown.classList.add('hidden');
                });

                dropdown.addEventListener('click', (e) => {
                    e.stopPropagation();
                });
            }
        });
    }

    // Delete Modal functionality
    function openDeleteModal(id, invoice, total) {
        const modal = document.getElementById('deleteModal');
        const form = document.getElementById('deleteForm');
        const modalInvoice = document.getElementById('modalInvoice');
        const modalTotal = document.getElementById('modalTotal');

        modalInvoice.textContent = invoice;
        modalTotal.textContent = `Rp ${total}`;
        form.action = `/transactions/${id}`;

        modal.classList.remove('hidden');
        modal.classList.add('flex');
    }

    function closeDeleteModal() {
        const modal = document.getElementById('deleteModal');
        modal.classList.add('hidden');
        modal.classList.remove('flex');
    }

    // Highlight today's transactions
    function highlightTodayTransactions() {
        const today = new Date().toISOString().split('T')[0];
        const rows = document.querySelectorAll('.transaction-row');

        rows.forEach(row => {
            const dateCell = row.querySelector('td:nth-child(2)');
            if (dateCell) {
                const dateText = dateCell.querySelector('.font-medium').textContent;
                const todayDate = new Date().getDate();

                // Check if date text contains today's date
                if (dateText.includes(todayDate.toString())) {
                    row.classList.add('today-transaction');
                }
            }
        });
    }

    // Row click functionality for mobile
    function initRowClicks() {
        const rows = document.querySelectorAll('.transaction-row');

        rows.forEach(row => {
            row.addEventListener('click', function(e) {
                // Don't trigger if clicking on a button or link
                if (e.target.closest('button') || e.target.closest('a') ||
                    e.target.tagName === 'BUTTON' || e.target.tagName === 'A' ||
                    e.target.closest('[data-tooltip]')) {
                    return;
                }

                // On mobile, click the detail button
                if (window.innerWidth < 768) {
                    const detailBtn = this.querySelector('a[href*="show"]');
                    if (detailBtn) {
                        detailBtn.click();
                    }
                }
            });
        });
    }

    // Print confirmation
    function initPrintButtons() {
        const printButtons = document.querySelectorAll('a[href*="print"]');

        printButtons.forEach(btn => {
            btn.addEventListener('click', function(e) {
                if (!confirm('Cetak invoice transaksi ini?')) {
                    e.preventDefault();
                }
            });
        });
    }

    // Initialize everything when DOM is loaded
    document.addEventListener('DOMContentLoaded', function() {
        initTooltips();
        initDropdowns();
        initRowClicks();
        initPrintButtons();
        highlightTodayTransactions();

        // Add event listener for search input
        document.getElementById('searchInput').addEventListener('input', filterTable);

        // Add event listener for clear search button
        document.getElementById('clearSearch').addEventListener('click', clearSearch);

        // Close modal when clicking outside
        document.addEventListener('click', function(e) {
            const modal = document.getElementById('deleteModal');
            if (modal && !modal.classList.contains('hidden') &&
                e.target === modal) {
                closeDeleteModal();
            }
        });

        // Close modal with Escape key
        document.addEventListener('keydown', function(e) {
            if (e.key === 'Escape') {
                closeDeleteModal();
            }
        });
    });
</script>

<?php
    // Pastikan variabel ini dikirim dari controller:
    // $totalRevenue = Transaction::sum('total_amount');
    // $averageTransaction = Transaction::avg('total_amount') ?? 0;
    // $todayTransactions = Transaction::whereDate('created_at', today())->count();
?>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\laragon\www\tokoroni-app\resources\views/transactions/index.blade.php ENDPATH**/ ?>