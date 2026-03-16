<?php $__env->startSection('title', 'Piutang Member'); ?>
<?php $__env->startSection('page-title', 'Riwayat Piutang Member'); ?>
<?php $__env->startSection('page-subtitle', 'Daftar piutang dan pembayaran'); ?>

<?php $__env->startSection('content'); ?>
    <div class="min-h-screen bg-gradient-to-br from-blue-50/50 to-purple-50/30 p-4 md:p-6">

        
        <?php if(session('success')): ?>
            <div
                class="mb-4 bg-green-100 border-l-4 border-green-500 text-green-700 p-4 rounded-lg flex items-center justify-between animate-fade-in">
                <div class="flex items-center">
                    <i class="fas fa-check-circle text-green-500 mr-3"></i>
                    <span><?php echo e(session('success')); ?></span>
                </div>
                <button onclick="this.parentElement.remove()" class="text-green-700 hover:text-green-900">
                    <i class="fas fa-times"></i>
                </button>
            </div>
        <?php endif; ?>

        <?php if(session('error')): ?>
            <div
                class="mb-4 bg-red-100 border-l-4 border-red-500 text-red-700 p-4 rounded-lg flex items-center justify-between animate-fade-in">
                <div class="flex items-center">
                    <i class="fas fa-exclamation-circle text-red-500 mr-3"></i>
                    <span><?php echo e(session('error')); ?></span>
                </div>
                <button onclick="this.parentElement.remove()" class="text-red-700 hover:text-red-900">
                    <i class="fas fa-times"></i>
                </button>
            </div>
        <?php endif; ?>

        <?php if($errors->any()): ?>
            <div class="mb-4 bg-red-100 border-l-4 border-red-500 text-red-700 p-4 rounded-lg animate-fade-in">
                <div class="flex items-center mb-2">
                    <i class="fas fa-exclamation-triangle text-red-500 mr-3"></i>
                    <span class="font-semibold">Terjadi kesalahan:</span>
                </div>
                <ul class="list-disc list-inside ml-6">
                    <?php $__currentLoopData = $errors->all(); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $error): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <li><?php echo e($error); ?></li>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </ul>
            </div>
        <?php endif; ?>

        
        <div class="glass-effect rounded-3xl p-6 md:p-8 shadow-elegant mb-6 animate-fade-in">
            <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-4">
                <div class="flex items-center gap-4">
                    <div class="relative">
                        <div
                            class="w-16 h-16 rounded-2xl bg-gradient-to-br from-amber-500 to-orange-600 flex items-center justify-center shadow-lg">
                            <i class="fas fa-hand-holding-usd text-2xl text-white"></i>
                        </div>
                        <div
                            class="absolute -inset-1 bg-gradient-to-r from-amber-500 to-orange-600 rounded-2xl blur-xl opacity-20">
                        </div>
                    </div>
                    <div>
                        <h1 class="text-3xl font-bold text-gray-800">Piutang Member</h1>
                        <p class="text-gray-600 mt-1"><?php echo e($member->nama); ?> (<?php echo e($member->kode_member); ?>)</p>
                    </div>
                </div>
                <div class="flex gap-3">
                    <a href="<?php echo e(route('members.show', $member->id)); ?>"
                        class="px-4 py-2 border border-gray-200 rounded-xl hover:bg-gray-50 transition-all inline-flex items-center">
                        <i class="fas fa-arrow-left mr-2"></i>
                        Kembali ke Profil
                    </a>
                </div>
            </div>
        </div>

        
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-6">
            <div class="stat-card group">
                <div class="stat-card-glow bg-gradient-to-r from-blue-500 to-cyan-500"></div>
                <div class="stat-card-content">
                    <p class="text-sm text-gray-500">Total Piutang</p>
                    <h3 class="text-2xl font-bold text-amber-600">Rp
                        <?php echo e(number_format($member->total_piutang, 0, ',', '.')); ?></h3>
                    <p class="text-xs text-gray-400 mt-1">Dari limit Rp
                        <?php echo e(number_format($member->limit_kredit, 0, ',', '.')); ?></p>
                </div>
            </div>

            <div class="stat-card group">
                <div class="stat-card-glow bg-gradient-to-r from-green-500 to-emerald-500"></div>
                <div class="stat-card-content">
                    <p class="text-sm text-gray-500">Sisa Limit</p>
                    <?php $sisaLimit = $member->limit_kredit - $member->total_piutang; ?>
                    <h3 class="text-2xl font-bold <?php echo e($sisaLimit > 0 ? 'text-green-600' : 'text-red-600'); ?>">
                        Rp <?php echo e(number_format($sisaLimit, 0, ',', '.')); ?>

                    </h3>
                    <p class="text-xs text-gray-400 mt-1">Tersedia untuk transaksi kredit</p>
                </div>
            </div>

            <div class="stat-card group">
                <div class="stat-card-glow bg-gradient-to-r from-purple-500 to-pink-500"></div>
                <div class="stat-card-content">
                    <p class="text-sm text-gray-500">Total Transaksi Kredit</p>
                    <h3 class="text-2xl font-bold text-gray-800"><?php echo e($receivables->total()); ?></h3>
                    <p class="text-xs text-gray-400 mt-1">Riwayat transaksi</p>
                </div>
            </div>
        </div>

        
        <div class="glass-effect rounded-2xl overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full">
                    <thead class="bg-gray-50/50">
                        <tr>
                            <th class="px-6 py-4 text-left text-sm font-semibold text-gray-600">No. Piutang</th>
                            <th class="px-6 py-4 text-left text-sm font-semibold text-gray-600">Invoice</th>
                            <th class="px-6 py-4 text-left text-sm font-semibold text-gray-600">Tanggal</th>
                            <th class="px-6 py-4 text-left text-sm font-semibold text-gray-600">Total</th>
                            <th class="px-6 py-4 text-left text-sm font-semibold text-gray-600">Sisa Hutang</th>
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
                                    <span class="font-mono text-sm"><?php echo e($receivable->invoice_number); ?></span>
                                </td>
                                <td class="px-6 py-4 text-sm">
                                    <?php echo e(\Carbon\Carbon::parse($receivable->tanggal_transaksi)->format('d/m/Y')); ?>

                                </td>
                                <td class="px-6 py-4 font-medium">
                                    Rp <?php echo e(number_format($receivable->total_piutang, 0, ',', '.')); ?>

                                </td>
                                <td class="px-6 py-4">
                                    <span
                                        class="font-medium <?php echo e($receivable->sisa_piutang > 0 ? 'text-amber-600' : 'text-green-600'); ?>">
                                        Rp <?php echo e(number_format($receivable->sisa_piutang, 0, ',', '.')); ?>

                                    </span>
                                </td>
                                <td class="px-6 py-4 text-sm">
                                    <?php if($receivable->jatuh_tempo): ?>
                                        <?php
                                            $jatuhTempo = \Carbon\Carbon::parse($receivable->jatuh_tempo);
                                            $now = \Carbon\Carbon::now();
                                        ?>
                                        <span
                                            class="<?php echo e($jatuhTempo < $now && $receivable->status != 'LUNAS' ? 'text-red-600 font-medium' : ''); ?>">
                                            <?php echo e($jatuhTempo->format('d/m/Y')); ?>

                                        </span>
                                        <?php if($jatuhTempo < $now && $receivable->status != 'LUNAS'): ?>
                                            <span class="text-xs text-red-600 block">Terlambat</span>
                                        <?php endif; ?>
                                    <?php else: ?>
                                        <span class="text-gray-400">-</span>
                                    <?php endif; ?>
                                </td>
                                <td class="px-6 py-4">
                                    <?php
                                        $statusClass =
                                            $receivable->status == 'LUNAS'
                                                ? 'bg-green-100 text-green-800'
                                                : 'bg-amber-100 text-amber-800';
                                    ?>
                                    <span class="badge <?php echo e($statusClass); ?>">
                                        <?php echo e($receivable->status); ?>

                                    </span>
                                </td>
                                <td class="px-6 py-4">
                                    <div class="flex items-center gap-2">
                                        <a href="<?php echo e(route('receivables.show', $receivable->id)); ?>"
                                            class="w-8 h-8 rounded-lg hover:bg-blue-50 flex items-center justify-center text-blue-600 transition-colors"
                                            title="Detail">
                                            <i class="fas fa-eye"></i>
                                        </a>
                                        <?php if($receivable->status != 'LUNAS'): ?>
                                            <button
                                                onclick="showPayModal('<?php echo e($receivable->id); ?>', '<?php echo e($receivable->no_piutang); ?>', <?php echo e($receivable->sisa_piutang); ?>)"
                                                class="w-8 h-8 rounded-lg hover:bg-green-50 flex items-center justify-center text-green-600 transition-colors"
                                                title="Bayar">
                                                <i class="fas fa-credit-card"></i>
                                            </button>
                                        <?php endif; ?>
                                        <a href="<?php echo e(route('receivables.payment-history', $receivable->id)); ?>"
                                            class="w-8 h-8 rounded-lg hover:bg-purple-50 flex items-center justify-center text-purple-600 transition-colors"
                                            title="Riwayat Pembayaran">
                                            <i class="fas fa-history"></i>
                                        </a>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                            <tr>
                                <td colspan="8" class="px-6 py-12 text-center">
                                    <div class="flex flex-col items-center">
                                        <div
                                            class="w-16 h-16 bg-gray-100 rounded-full flex items-center justify-center mb-4">
                                            <i class="fas fa-hand-holding-usd text-3xl text-gray-400"></i>
                                        </div>
                                        <h3 class="text-lg font-medium text-gray-900 mb-2">Tidak ada data piutang</h3>
                                        <p class="text-gray-600"><?php echo e($member->nama); ?> belum memiliki transaksi kredit</p>
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

    
    <div id="payModal"
        class="fixed inset-0 bg-black bg-opacity-50 hidden items-center justify-center z-50 transition-all">
        <div class="bg-white rounded-2xl max-w-md w-full p-6 transform transition-all scale-100 opacity-100">
            <div class="flex justify-between items-center mb-4">
                <h3 class="text-xl font-bold text-gray-800">Bayar Piutang</h3>
                <button onclick="closePayModal()" class="text-gray-400 hover:text-gray-600 transition-colors">
                    <i class="fas fa-times text-xl"></i>
                </button>
            </div>

            <form id="payForm" method="POST" action="">
                <?php echo csrf_field(); ?>
                <div class="space-y-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">
                            No. Piutang
                        </label>
                        <input type="text" id="pay_no_piutang" readonly
                            class="w-full px-4 py-2 bg-gray-50 border border-gray-200 rounded-lg text-gray-700">
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">
                            Sisa Piutang
                        </label>
                        <input type="text" id="pay_sisa" readonly
                            class="w-full px-4 py-2 bg-gray-50 border border-gray-200 rounded-lg text-gray-700">
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">
                            Jumlah Bayar <span class="text-red-500">*</span>
                        </label>
                        <div class="relative">
                            <span class="absolute left-3 top-2 text-gray-500">Rp</span>
                            <input type="number" name="jumlah_bayar" id="jumlah_bayar" required min="1000"
                                step="1000"
                                class="w-full pl-10 pr-4 py-2 border border-gray-200 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                                placeholder="Masukkan jumlah bayar">
                        </div>
                        <p class="text-xs text-gray-500 mt-1">Minimal Rp 1000 (kelipatan 1000)</p>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">
                            Metode Bayar <span class="text-red-500">*</span>
                        </label>
                        <select name="metode_bayar" required
                            class="w-full px-4 py-2 border border-gray-200 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                            <option value="tunai">Tunai</option>
                            <option value="transfer">Transfer Bank</option>
                            <option value="Ewallet">E-wallet</option>

                        </select>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">
                            Keterangan
                        </label>
                        <input type="text" name="keterangan" id="keterangan"
                            class="w-full px-4 py-2 border border-gray-200 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                            placeholder="Opsional (contoh: Pembayaran via transfer)">
                    </div>

                    <div class="flex gap-3 pt-4 border-t border-gray-100">
                        <button type="submit"
                            class="flex-1 px-4 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700 transition-colors font-medium">
                            <i class="fas fa-check mr-2"></i>
                            Bayar
                        </button>
                        <button type="button" onclick="closePayModal()"
                            class="flex-1 px-4 py-2 border border-gray-200 rounded-lg hover:bg-gray-50 transition-colors font-medium">
                            <i class="fas fa-times mr-2"></i>
                            Batal
                        </button>
                    </div>
                </div>
            </form>
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
            overflow: hidden;
        }

        .stat-card:hover {
            transform: translateY(-4px);
            box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.1);
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
    <script>
        window.showPayModal = function(id, noPiutang, sisa) {
            console.log('Opening payment modal for:', {
                id,
                noPiutang,
                sisa
            });

            try {
                // Set form action
                const form = document.getElementById('payForm');
                if (!form) {
                    console.error('Form not found');
                    return;
                }
                form.action = '<?php echo e(url('receivables')); ?>/' + id + '/pay';

                // Set values
                const noPiutangInput = document.getElementById('pay_no_piutang');
                const sisaInput = document.getElementById('pay_sisa');
                const jumlahBayar = document.getElementById('jumlah_bayar');
                const keterangan = document.getElementById('keterangan');

                if (noPiutangInput) noPiutangInput.value = noPiutang;
                if (sisaInput) sisaInput.value = 'Rp ' + sisa.toLocaleString('id-ID');

                // Set max value and validation
                if (jumlahBayar) {
                    // Hapus event listener lama
                    if (jumlahBayar._inputHandler) {
                        jumlahBayar.removeEventListener('input', jumlahBayar._inputHandler);
                    }

                    // Reset value dan attributes
                    jumlahBayar.value = '';
                    jumlahBayar.max = sisa;
                    jumlahBayar.min = 1; // Minimal 1 rupiah
                    jumlahBayar.step = 1; // Bisa input angka berapapun
                    jumlahBayar.removeAttribute('readonly');

                    // Buat handler baru - hanya validasi maksimal
                    jumlahBayar._inputHandler = function(e) {
                        let value = parseInt(this.value) || 0;
                        const max = parseInt(this.max) || 0;

                        // Validasi maksimal
                        if (value > max) {
                            this.value = max;
                        }

                        // Validasi minimal
                        if (value < 1) {
                            this.setCustomValidity('Jumlah bayar minimal Rp 1');
                        } else {
                            this.setCustomValidity('');
                        }
                    };

                    // Tambahkan event listener
                    jumlahBayar.addEventListener('input', jumlahBayar._inputHandler);
                }

                if (keterangan) keterangan.value = '';

                // Show modal
                const modal = document.getElementById('payModal');
                if (modal) {
                    modal.classList.remove('hidden');
                    modal.classList.add('flex');

                    // Focus on payment input
                    setTimeout(() => {
                        if (jumlahBayar) jumlahBayar.focus();
                    }, 100);
                }
            } catch (error) {
                console.error('Error showing modal:', error);
                alert('Terjadi kesalahan saat membuka form pembayaran');
            }
        };

        window.closePayModal = function() {
            try {
                const modal = document.getElementById('payModal');
                if (modal) {
                    modal.classList.add('hidden');
                    modal.classList.remove('flex');
                }

                // Reset form
                const form = document.getElementById('payForm');
                if (form) {
                    form.reset();

                    // Remove event listeners
                    const jumlahBayar = document.getElementById('jumlah_bayar');
                    if (jumlahBayar && jumlahBayar._inputHandler) {
                        jumlahBayar.removeEventListener('input', jumlahBayar._inputHandler);
                    }
                }
            } catch (error) {
                console.error('Error Menutup modal:', error);
            }
        };

        // Fungsi tambahan untuk validasi sebelum submit
        document.addEventListener('DOMContentLoaded', function() {
            console.log('DOM loaded, initializing modal handlers');

            const payForm = document.getElementById('payForm');
            if (payForm) {
                payForm.addEventListener('submit', function(e) {
                    const jumlahBayar = document.getElementById('jumlah_bayar');
                    const sisa = parseInt(jumlahBayar.max) || 0;
                    const nilai = parseInt(jumlahBayar.value) || 0;

                    // Validasi final sebelum submit
                    if (nilai < 1) {
                        e.preventDefault();
                        alert('Jumlah bayar minimal Rp 1');
                        return false;
                    }

                    if (nilai > sisa) {
                        e.preventDefault();
                        alert('Jumlah bayar tidak boleh melebihi sisa piutang');
                        return false;
                    }

                    return true;
                });
            }

            // Close modal when clicking outside
            const modal = document.getElementById('payModal');
            if (modal) {
                modal.addEventListener('click', function(e) {
                    if (e.target === modal) {
                        closePayModal();
                    }
                });
            }

            // Add keyboard support (Escape to close)
            document.addEventListener('keydown', function(e) {
                if (e.key === 'Escape') {
                    const modal = document.getElementById('payModal');
                    if (modal && !modal.classList.contains('hidden')) {
                        closePayModal();
                    }
                }
            });

            // Auto-hide alerts after 5 seconds
            const alerts = document.querySelectorAll('.bg-green-100, .bg-red-100');
            alerts.forEach(alert => {
                setTimeout(() => {
                    if (alert && alert.parentElement) {
                        alert.style.transition = 'opacity 0.5s';
                        alert.style.opacity = '0';
                        setTimeout(() => {
                            if (alert.parentElement) alert.remove();
                        }, 500);
                    }
                }, 5000);
            });
        });
    </script>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH D:\PROJECT3\Toko-Roni-Mobile-App\tokoroni-app\resources\views\members\receivables.blade.php ENDPATH**/ ?>