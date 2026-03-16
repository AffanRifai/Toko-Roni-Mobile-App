<?php $__env->startSection('title', 'Dashboard Logistik Kurir'); ?>

<?php $__env->startSection('content'); ?>
    <div class="min-h-screen bg-gray-50">
        <!-- Header -->
        <div class="bg-gradient-to-r from-blue-600 to-indigo-700 shadow-lg">
            <div class="container mx-auto px-4 py-8">
                <div class="flex flex-col md:flex-row justify-between items-center">
                    <div class="flex items-center space-x-4 mb-6 md:mb-0">
                        <div class="bg-white/20 backdrop-blur-sm p-3 rounded-2xl">
                            <i class="fas fa-shipping-fast text-white text-3xl"></i>
                        </div>
                        <div>
                            <h1 class="text-3xl font-bold text-white">Dashboard Logistik Kurir</h1>
                            <p class="text-blue-100 mt-1">Kelola pengiriman internal toko Anda</p>
                        </div>
                    </div>
                    <a href="<?php echo e(route('delivery.create')); ?>"
                        class="inline-flex items-center space-x-2 bg-white text-blue-600 hover:bg-blue-50 font-semibold py-3 px-6 rounded-xl transition-all duration-300 transform hover:-translate-y-1 shadow-lg hover:shadow-xl">
                        <i class="fas fa-plus"></i>
                        <span>Buat Pengiriman</span>
                    </a>
                </div>
            </div>
        </div>

        <!-- Stats Cards -->
        <div class="container mx-auto px-4 py-8">
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6">
                <!-- Total Pengiriman -->
                <div
                    class="bg-white rounded-2xl shadow-lg p-6 border-l-4 border-blue-500 hover:shadow-xl transition-shadow duration-300">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-gray-500 text-sm font-medium">Total Pengiriman</p>
                            <h3 class="text-3xl font-bold text-gray-800 mt-2"><?php echo e($stats['total_deliveries'] ?? 0); ?></h3>
                        </div>
                        <div class="bg-blue-100 p-3 rounded-xl">
                            <i class="fas fa-box text-blue-600 text-2xl"></i>
                        </div>
                    </div>
                    <div class="mt-4">
                        <span class="inline-flex items-center text-sm text-gray-600">
                            <i class="fas fa-arrow-up text-green-500 mr-1"></i>
                            <span>+5% dari kemarin</span>
                        </span>
                    </div>
                </div>

                <!-- Menunggu Pengiriman -->
                <div
                    class="bg-white rounded-2xl shadow-lg p-6 border-l-4 border-amber-500 hover:shadow-xl transition-shadow duration-300">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-gray-500 text-sm font-medium">Menunggu Pengiriman</p>
                            <h3 class="text-3xl font-bold text-gray-800 mt-2"><?php echo e($stats['pending_deliveries'] ?? 0); ?></h3>
                        </div>
                        <div class="bg-amber-100 p-3 rounded-xl">
                            <i class="fas fa-clock text-amber-600 text-2xl"></i>
                        </div>
                    </div>
                    <div class="mt-4">
                        <a href="<?php echo e(route('delivery.index', ['status' => 'pending'])); ?>"
                            class="text-sm text-amber-600 hover:text-amber-700 font-medium inline-flex items-center">
                            Lihat antrian
                            <i class="fas fa-arrow-right ml-1 text-xs"></i>
                        </a>
                    </div>
                </div>

                <!-- Terkirim Hari Ini -->
                <div
                    class="bg-white rounded-2xl shadow-lg p-6 border-l-4 border-emerald-500 hover:shadow-xl transition-shadow duration-300">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-gray-500 text-sm font-medium">Terkirim Hari Ini</p>
                            <h3 class="text-3xl font-bold text-gray-800 mt-2"><?php echo e($stats['delivered_today'] ?? 0); ?></h3>
                        </div>
                        <div class="bg-emerald-100 p-3 rounded-xl">
                            <i class="fas fa-check-circle text-emerald-600 text-2xl"></i>
                        </div>
                    </div>
                    <div class="mt-4">
                        <span class="inline-flex items-center text-sm text-gray-600">
                            <i class="fas fa-bolt text-blue-500 mr-1"></i>
                            <span>Rata-rata 2.4 jam/pengiriman</span>
                        </span>
                    </div>
                </div>

                <!-- Kurir Aktif -->
                <div
                    class="bg-white rounded-2xl shadow-lg p-6 border-l-4 border-cyan-500 hover:shadow-xl transition-shadow duration-300">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-gray-500 text-sm font-medium">Kurir Aktif</p>
                            <h3 class="text-3xl font-bold text-gray-800 mt-2"><?php echo e($stats['active_staff'] ?? 0); ?></h3>
                        </div>
                        <div class="bg-cyan-100 p-3 rounded-xl">
                            <i class="fas fa-motorcycle text-cyan-600 text-2xl"></i>
                        </div>
                    </div>
                    <div class="mt-4">
                        <a href="<?php echo e(route('delivery.staff.index')); ?>"
                            class="text-sm text-cyan-600 hover:text-cyan-700 font-medium inline-flex items-center">
                            Kelola kurir
                            <i class="fas fa-arrow-right ml-1 text-xs"></i>
                        </a>
                    </div>
                </div>
            </div>
        </div>

        <!-- Main Content -->
        <div class="container mx-auto px-4 pb-12">
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
                <!-- Left Column - 2/3 width -->
                <div class="lg:col-span-2 space-y-8">
                    <!-- Quick Actions -->
                    <div class="bg-white rounded-2xl shadow-lg overflow-hidden">
                        <div class="px-6 py-4 border-b border-gray-100">
                            <div class="flex items-center space-x-2">
                                <div class="bg-blue-100 p-2 rounded-lg">
                                    <i class="fas fa-bolt text-blue-600"></i>
                                </div>
                                <h2 class="text-xl font-bold text-gray-800">Aksi Cepat</h2>
                            </div>
                        </div>
                        <div class="p-6">
                            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                                <!-- Lihat Antrian -->
                                <a href="<?php echo e(route('delivery.index', ['status' => 'pending'])); ?>"
                                    class="group bg-gradient-to-br from-blue-50 to-white border border-blue-100 rounded-xl p-5 hover:border-blue-300 hover:shadow-md transition-all duration-300">
                                    <div class="flex items-center space-x-3">
                                        <div
                                            class="bg-blue-600 text-white p-3 rounded-lg group-hover:scale-110 transition-transform duration-300">
                                            <i class="fas fa-list text-lg"></i>
                                        </div>
                                        <div>
                                            <h3 class="font-semibold text-gray-800">Lihat Antrian</h3>
                                            <p class="text-sm text-gray-600 mt-1"><?php echo e($stats['pending_deliveries'] ?? 0); ?>

                                                pengiriman menunggu</p>
                                        </div>
                                    </div>
                                    <div class="mt-3 text-blue-600 text-sm font-medium flex items-center">
                                        <span>Akses sekarang</span>
                                        <i class="fas fa-arrow-right ml-2 text-xs"></i>
                                    </div>
                                </a>

                                <!-- Kelola Kurir -->
                                <a href="<?php echo e(route('delivery.staff.index')); ?>"
                                    class="group bg-gradient-to-br from-emerald-50 to-white border border-emerald-100 rounded-xl p-5 hover:border-emerald-300 hover:shadow-md transition-all duration-300">
                                    <div class="flex items-center space-x-3">
                                        <div
                                            class="bg-emerald-600 text-white p-3 rounded-lg group-hover:scale-110 transition-transform duration-300">
                                            <i class="fas fa-users text-lg"></i>
                                        </div>
                                        <div>
                                            <h3 class="font-semibold text-gray-800">Kelola Kurir</h3>
                                            <p class="text-sm text-gray-600 mt-1"><?php echo e($stats['active_staff'] ?? 0); ?> kurir aktif
                                            </p>
                                        </div>
                                    </div>
                                    <div class="mt-3 text-emerald-600 text-sm font-medium flex items-center">
                                        <span>Kelola tim</span>
                                        <i class="fas fa-arrow-right ml-2 text-xs"></i>
                                    </div>
                                </a>

                                <!-- Laporan -->
                                <a href="<?php echo e(route('delivery.reports')); ?>"
                                    class="group bg-gradient-to-br from-purple-50 to-white border border-purple-100 rounded-xl p-5 hover:border-purple-300 hover:shadow-md transition-all duration-300">
                                    <div class="flex items-center space-x-3">
                                        <div
                                            class="bg-purple-600 text-white p-3 rounded-lg group-hover:scale-110 transition-transform duration-300">
                                            <i class="fas fa-chart-bar text-lg"></i>
                                        </div>
                                        <div>
                                            <h3 class="font-semibold text-gray-800">Laporan</h3>
                                            <p class="text-sm text-gray-600 mt-1">Analisis performa pengiriman</p>
                                        </div>
                                    </div>
                                    <div class="mt-3 text-purple-600 text-sm font-medium flex items-center">
                                        <span>Lihat laporan</span>
                                        <i class="fas fa-arrow-right ml-2 text-xs"></i>
                                    </div>
                                </a>
                            </div>
                        </div>
                    </div>

                    <!-- Recent Deliveries -->
                    <div class="bg-white rounded-2xl shadow-lg overflow-hidden">
                        <div class="px-6 py-4 border-b border-gray-100 flex justify-between items-center">
                            <div class="flex items-center space-x-2">
                                <div class="bg-gray-100 p-2 rounded-lg">
                                    <i class="fas fa-history text-gray-600"></i>
                                </div>
                                <h2 class="text-xl font-bold text-gray-800">Pengiriman Terbaru</h2>
                            </div>
                            <a href="<?php echo e(route('delivery.index')); ?>"
                                class="text-blue-600 hover:text-blue-700 font-medium text-sm flex items-center">
                                Lihat Semua
                                <i class="fas fa-chevron-right ml-1 text-xs"></i>
                            </a>
                        </div>
                        <div class="overflow-x-auto">
                            <?php if(isset($recentDeliveries) && $recentDeliveries->count() > 0): ?>
                                <table class="w-full">
                                    <thead class="bg-gray-50">
                                        <tr>
                                            <th
                                                class="py-3 px-6 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">
                                                Kode</th>
                                            <th
                                                class="py-3 px-6 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">
                                                Penerima</th>
                                            <th
                                                class="py-3 px-6 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">
                                                Lokasi</th>
                                            <th
                                                class="py-3 px-6 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">
                                                Status</th>
                                            <th
                                                class="py-3 px-6 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">
                                                Aksi</th>
                                        </tr>
                                    </thead>
                                    <tbody class="divide-y divide-gray-100">
                                        <?php $__currentLoopData = $recentDeliveries; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $delivery): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                            <tr class="hover:bg-gray-50 transition-colors duration-150">
                                                <td class="py-4 px-6">
                                                    <div>
                                                        <span
                                                            class="font-semibold text-gray-800"><?php echo e($delivery->delivery_code ?? 'N/A'); ?></span>
                                                        <div class="text-xs text-gray-500 mt-1">
                                                            <?php echo e($delivery->created_at->format('H:i') ?? 'N/A'); ?>

                                                        </div>
                                                    </div>
                                                </td>
                                                <td class="py-4 px-6">
                                                    <div class="flex items-center space-x-3">
                                                        <div class="flex-shrink-0">
                                                            <div
                                                                class="w-10 h-10 bg-blue-100 rounded-lg flex items-center justify-center">
                                                                <i class="fas fa-user text-blue-600"></i>
                                                            </div>
                                                        </div>
                                                        <div>
                                                            <div class="font-medium text-gray-900">
                                                                <?php echo e($delivery->recipient_name ?? 'N/A'); ?></div>
                                                            <div class="text-sm text-gray-500">
                                                                <?php echo e($delivery->recipient_phone ?? 'N/A'); ?></div>
                                                        </div>
                                                    </div>
                                                </td>
                                                <td class="py-4 px-6">
                                                    <div class="flex items-center text-gray-600">
                                                        <i class="fas fa-map-marker-alt text-red-500 mr-2"></i>
                                                        <span
                                                            class="truncate max-w-[150px]"><?php echo e($delivery->delivery_district ?? 'N/A'); ?></span>
                                                    </div>
                                                </td>
                                                <td class="py-4 px-6">
                                                    <?php
                                                        $statusColors = [
                                                            'pending' => 'bg-amber-100 text-amber-800',
                                                            'assigned' => 'bg-blue-100 text-blue-800',
                                                            'preparing' => 'bg-purple-100 text-purple-800',
                                                            'picked_up' => 'bg-indigo-100 text-indigo-800',
                                                            'on_the_way' => 'bg-teal-100 text-teal-800',
                                                            'delivered' => 'bg-emerald-100 text-emerald-800',
                                                            'cancelled' => 'bg-red-100 text-red-800',
                                                        ];
                                                        $status = $delivery->status ?? 'pending';
                                                    ?>
                                                    <span
                                                        class="px-3 py-1 rounded-full text-xs font-medium <?php echo e($statusColors[$status] ?? 'bg-gray-100 text-gray-800'); ?>">
                                                        <?php echo e(ucfirst(str_replace('_', ' ', $status))); ?>

                                                    </span>
                                                </td>
                                                <td class="py-4 px-6">
                                                    <a href="<?php echo e(route('delivery.show', $delivery)); ?>"
                                                        class="inline-flex items-center justify-center w-8 h-8 text-blue-600 hover:text-blue-800 hover:bg-blue-50 rounded-lg transition-colors duration-200">
                                                        <i class="fas fa-eye"></i>
                                                    </a>
                                                </td>
                                            </tr>
                                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                    </tbody>
                                </table>
                            <?php else: ?>
                                <div class="text-center py-8">
                                    <div class="text-gray-400 mb-4">
                                        <i class="fas fa-box-open text-4xl"></i>
                                    </div>
                                    <p class="text-gray-500">Belum ada data pengiriman</p>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>

                <!-- Right Column - 1/3 width -->
                <div class="space-y-8">
                    <!-- Active Staff -->
                    <div class="bg-white rounded-2xl shadow-lg overflow-hidden">
                        <div class="px-6 py-4 border-b border-gray-100">
                            <div class="flex items-center justify-between">
                                <div class="flex items-center space-x-2">
                                    <div class="bg-cyan-100 p-2 rounded-lg">
                                        <i class="fas fa-motorcycle text-cyan-600"></i>
                                    </div>
                                    <h2 class="text-xl font-bold text-gray-800">Kurir Aktif</h2>
                                </div>
                                <span class="bg-cyan-100 text-cyan-800 text-xs font-semibold px-2.5 py-0.5 rounded-full">
                                    <?php echo e($stats['active_staff'] ?? 0); ?> Online
                                </span>
                            </div>
                        </div>
                        <div class="p-6 space-y-4">
                            <?php if(isset($activeStaff) && $activeStaff->count() > 0): ?>
                                <?php $__currentLoopData = $activeStaff; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $staff): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                    <div
                                        class="group p-4 bg-gradient-to-r from-gray-50 to-white border border-gray-100 rounded-xl hover:border-cyan-200 hover:shadow-sm transition-all duration-300">
                                        <div class="flex items-center space-x-3">
                                            <!-- Avatar -->
                                            <div class="relative">
                                                <div
                                                    class="w-12 h-12 bg-gradient-to-br from-cyan-500 to-blue-600 rounded-xl flex items-center justify-center text-white font-bold text-lg">
                                                    <?php echo e(substr($staff->name ?? 'K', 0, 1)); ?>

                                                </div>
                                                <?php if($staff->current_lat && $staff->current_lng): ?>
                                                    <div
                                                        class="absolute -bottom-1 -right-1 w-4 h-4 bg-emerald-500 border-2 border-white rounded-full">
                                                    </div>
                                                <?php endif; ?>
                                            </div>

                                            <!-- Info -->
                                            <div class="flex-1 min-w-0">
                                                <div class="flex justify-between items-start">
                                                    <div>
                                                        <h4 class="font-semibold text-gray-900 truncate"><?php echo e($staff->name ?? 'Kurir'); ?>

                                                        </h4>
                                                        <div class="flex items-center space-x-3 mt-1">
                                                            <span class="inline-flex items-center text-sm text-gray-600">
                                                                <i class="fas fa-phone text-gray-400 mr-1 text-xs"></i>
                                                                <?php echo e($staff->phone ?? 'Tidak ada telepon'); ?>

                                                            </span>
                                                            <span class="inline-flex items-center text-sm text-gray-600">
                                                                <i class="fas fa-star text-amber-400 mr-1 text-xs"></i>
                                                                <?php echo e($staff->rating ?? 0); ?>

                                                            </span>
                                                        </div>
                                                    </div>
                                                    <span
                                                        class="bg-blue-100 text-blue-800 text-xs font-semibold px-2 py-1 rounded">
                                                        <?php echo e($staff->deliveries_count ?? 0); ?>

                                                    </span>
                                                </div>

                                                <!-- Status -->
                                                <?php if($staff->current_lat && $staff->current_lng): ?>
                                                    <div class="mt-3 flex items-center justify-between">
                                                        <span class="inline-flex items-center text-xs text-emerald-600">
                                                            <i class="fas fa-circle text-emerald-500 mr-1.5 text-xs"></i>
                                                            Online
                                                        </span>
                                                        <span class="text-xs text-gray-500">
                                                            <?php echo e($staff->last_location_update ? $staff->last_location_update->diffForHumans() : 'Belum pernah update'); ?>

                                                        </span>
                                                    </div>
                                                <?php else: ?>
                                                    <div class="mt-3">
                                                        <span class="inline-flex items-center text-xs text-gray-500">
                                                            <i class="fas fa-circle text-gray-400 mr-1.5 text-xs"></i>
                                                            Offline
                                                        </span>
                                                    </div>
                                                <?php endif; ?>
                                            </div>
                                        </div>
                                    </div>
                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                            <?php else: ?>
                                <div class="text-center py-4">
                                    <div class="text-gray-400 mb-2">
                                        <i class="fas fa-users text-2xl"></i>
                                    </div>
                                    <p class="text-gray-500 text-sm">Belum ada kurir aktif</p>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>

                    <!-- Delivery Chart -->
                    <div class="bg-white rounded-2xl shadow-lg overflow-hidden">
                        <div class="px-6 py-4 border-b border-gray-100">
                            <div class="flex items-center space-x-2">
                                <div class="bg-purple-100 p-2 rounded-lg">
                                    <i class="fas fa-chart-line text-purple-600"></i>
                                </div>
                                <h2 class="text-xl font-bold text-gray-800">Statistik 7 Hari</h2>
                            </div>
                        </div>
                        <div class="p-6">
                            <canvas id="deliveryChart" height="200"></canvas>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Delivery Chart
            const ctx = document.getElementById('deliveryChart');
            if (!ctx) return;

            const chartData = <?php echo json_encode($deliveryChart ?? [], 15, 512) ?>;

            if (chartData.length === 0) {
                ctx.parentElement.innerHTML = `
                    <div class="text-center py-8">
                        <div class="text-gray-400 mb-4">
                            <i class="fas fa-chart-line text-4xl"></i>
                        </div>
                        <p class="text-gray-500">Belum ada data statistik</p>
                    </div>
                `;
                return;
            }

            const labels = chartData.map(item => {
                const date = new Date(item.date);
                return date.toLocaleDateString('id-ID', {
                    weekday: 'short'
                });
            });

            const data = chartData.map(item => item.count);

            try {
                new Chart(ctx, {
                    type: 'line',
                    data: {
                        labels: labels,
                        datasets: [{
                            label: 'Pengiriman',
                            data: data,
                            borderColor: '#3b82f6',
                            backgroundColor: 'rgba(59, 130, 246, 0.05)',
                            borderWidth: 3,
                            fill: true,
                            tension: 0.4,
                            pointBackgroundColor: '#3b82f6',
                            pointBorderColor: '#ffffff',
                            pointBorderWidth: 2,
                            pointRadius: 5,
                            pointHoverRadius: 7
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        plugins: {
                            legend: {
                                display: false
                            },
                            tooltip: {
                                backgroundColor: 'rgba(255, 255, 255, 0.95)',
                                titleColor: '#1f2937',
                                bodyColor: '#4b5563',
                                borderColor: '#e5e7eb',
                                borderWidth: 1,
                                boxPadding: 10,
                                callbacks: {
                                    label: function(context) {
                                        return `Pengiriman: ${context.parsed.y}`;
                                    }
                                }
                            }
                        },
                        scales: {
                            y: {
                                beginAtZero: true,
                                grid: {
                                    color: 'rgba(0, 0, 0, 0.05)'
                                },
                                ticks: {
                                    stepSize: 1,
                                    color: '#6b7280'
                                }
                            },
                            x: {
                                grid: {
                                    display: false
                                },
                                ticks: {
                                    color: '#6b7280'
                                }
                            }
                        },
                        interaction: {
                            intersect: false,
                            mode: 'index'
                        }
                    }
                });
            } catch (error) {
                console.error('Chart error:', error);
                ctx.parentElement.innerHTML = `
                    <div class="text-center py-8">
                        <div class="text-red-400 mb-4">
                            <i class="fas fa-exclamation-triangle text-4xl"></i>
                        </div>
                        <p class="text-red-500">Gagal memuat grafik</p>
                    </div>
                `;
            }

            // Smooth scroll and hover effects
            const cards = document.querySelectorAll('.bg-white');
            cards.forEach(card => {
                card.addEventListener('mouseenter', () => {
                    card.style.transform = 'translateY(-2px)';
                });
                card.addEventListener('mouseleave', () => {
                    card.style.transform = 'translateY(0)';aaa
                });
            });

            // Auto-refresh every 30 seconds
            setTimeout(() => {
                window.location.reload();
            }, 30000);
        });
    </script>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH D:\PROJECT3\Toko-Roni-Mobile-App\tokoroni-app\resources\views\Delivery\dashboard.blade.php ENDPATH**/ ?>