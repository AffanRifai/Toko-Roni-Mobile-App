{{-- resources/views/dashboard/logistik.blade.php --}}
@extends('layouts.app')

@section('title', 'Dashboard Logistik')
@section('page-title', 'Dashboard Logistik')
@section('page-subtitle', 'Manajemen Pengiriman & Distribusi')

@section('content')
<div class="min-h-screen bg-gradient-to-br from-blue-50/50 to-indigo-50/30 p-4 md:p-6">
    <!-- Welcome Header Logistik -->
    <div class="glass-effect rounded-3xl p-6 md:p-8 shadow-elegant mb-6 md:mb-8 animate-fade-in">
        <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-4 md:gap-6">
            <div class="flex-1">
                <div class="flex items-center gap-3 md:gap-4 mb-4">
                    <div class="relative">
                        <div class="w-12 h-12 md:w-16 md:h-16 rounded-2xl bg-gradient-to-br from-indigo-500 to-purple-600 flex items-center justify-center shadow-lg">
                            <i class="fas fa-shipping-fast text-xl md:text-2xl text-white"></i>
                        </div>
                        <div class="absolute -inset-1 md:-inset-2 bg-gradient-to-r from-indigo-500 to-purple-600 rounded-2xl blur-xl opacity-20"></div>
                    </div>
                    <div>
                        <h1 class="text-xl md:text-3xl font-bold text-gray-800">Halo, <span class="gradient-text">{{ Auth::user()->name }}!</span> 🚚</h1>
                        <p class="text-sm md:text-base text-gray-600 mt-1 md:mt-2">Status pengiriman & distribusi hari ini</p>
                    </div>
                </div>
                <div class="flex flex-wrap gap-3">
                    <div class="flex items-center gap-2 px-3 py-2 bg-indigo-50 rounded-lg">
                        <i class="fas fa-truck text-indigo-600"></i>
                        <span class="text-sm font-medium text-gray-700">Armada: {{ $totalFleet ?? 5 }} unit</span>
                    </div>
                    <div class="flex items-center gap-2 px-3 py-2 bg-emerald-50 rounded-lg">
                        <i class="fas fa-map-marked-alt text-emerald-600"></i>
                        <span class="text-sm font-medium text-gray-700">{{ $activeRoutes ?? 3 }} rute aktif</span>
                    </div>
                    <div class="flex items-center gap-2 px-3 py-2 bg-amber-50 rounded-lg">
                        <i class="fas fa-clock text-amber-600"></i>
                        <span class="text-sm font-medium text-gray-700">Ongoing: {{ $ongoingDeliveries ?? 2 }}</span>
                    </div>
                </div>
            </div>
            <div class="mt-4 lg:mt-0">
                <a href="{{ route('delivery.create') }}"
                   class="inline-flex items-center gap-2 px-6 py-3 md:py-4 bg-gradient-to-r from-indigo-500 to-purple-600 text-white font-semibold rounded-xl hover:shadow-lg transition-all duration-300 hover:-translate-y-1">
                    <i class="fas fa-plus-circle"></i>
                    <span>Buat Pengiriman</span>
                </a>
            </div>
        </div>
    </div>

    <!-- Stats Grid Logistik -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 md:gap-6 mb-6 md:mb-8">
        <!-- Total Pengiriman Hari Ini -->
        <div class="stat-card group">
            <div class="stat-card-glow bg-gradient-to-r from-indigo-500 to-purple-500"></div>
            <div class="stat-card-content">
                <div class="flex items-center justify-between mb-4">
                    <div class="flex items-center gap-3">
                        <div class="p-3 rounded-xl bg-gradient-to-br from-indigo-500 to-purple-600 shadow-lg">
                            <i class="fas fa-truck-loading text-lg text-white"></i>
                        </div>
                        <div>
                            <p class="text-xs md:text-sm text-gray-500 font-medium">Pengiriman Hari Ini</p>
                            <h3 class="text-xl md:text-2xl font-bold text-gray-800 mt-1">{{ $todayDeliveries ?? 0 }}</h3>
                        </div>
                    </div>
                </div>
                <div class="flex items-center justify-between">
                    <div class="flex items-center text-xs">
                        <div class="w-6 h-6 rounded-lg bg-emerald-100 flex items-center justify-center mr-2">
                            <i class="fas fa-check text-emerald-600 text-xs"></i>
                        </div>
                        <span class="text-emerald-600 font-semibold">{{ $completedDeliveries ?? 0 }} selesai</span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Pengiriman Dalam Proses -->
        <div class="stat-card group">
            <div class="stat-card-glow bg-gradient-to-r from-amber-500 to-orange-500"></div>
            <div class="stat-card-content">
                <div class="flex items-center justify-between mb-4">
                    <div class="flex items-center gap-3">
                        <div class="p-3 rounded-xl bg-gradient-to-br from-amber-500 to-orange-600 shadow-lg">
                            <i class="fas fa-shipping-fast text-lg text-white"></i>
                        </div>
                        <div>
                            <p class="text-xs md:text-sm text-gray-500 font-medium">Dalam Proses</p>
                            <h3 class="text-xl md:text-2xl font-bold text-gray-800 mt-1">{{ $ongoingDeliveries ?? 0 }}</h3>
                        </div>
                    </div>
                </div>
                <div class="flex items-center justify-between">
                    <div class="flex items-center text-xs">
                        <div class="w-6 h-6 rounded-lg bg-amber-100 flex items-center justify-center mr-2">
                            <i class="fas fa-clock text-amber-600 text-xs"></i>
                        </div>
                        <span class="text-amber-600 font-semibold">{{ $delayedDeliveries ?? 0 }} tertunda</span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Total Barang Dikirim -->
        <div class="stat-card group">
            <div class="stat-card-glow bg-gradient-to-r from-blue-500 to-cyan-500"></div>
            <div class="stat-card-content">
                <div class="flex items-center justify-between mb-4">
                    <div class="flex items-center gap-3">
                        <div class="p-3 rounded-xl bg-gradient-to-br from-blue-500 to-cyan-600 shadow-lg">
                            <i class="fas fa-boxes text-lg text-white"></i>
                        </div>
                        <div>
                            <p class="text-xs md:text-sm text-gray-500 font-medium">Barang Dikirim</p>
                            <h3 class="text-xl md:text-2xl font-bold text-gray-800 mt-1">{{ $totalItemsShipped ?? 0 }}</h3>
                        </div>
                    </div>
                </div>
                <div class="flex items-center justify-between">
                    <div class="flex items-center text-xs">
                        <span class="text-gray-500">{{ $totalWeight ?? 0 }} kg</span>
                        <span class="text-blue-600 font-semibold ml-2">{{ $totalVolume ?? 0 }} m³</span>
                    </div>
                </div>
            </div>
        </div>

        <!-- On-Time Delivery Rate -->
        <div class="stat-card group">
            <div class="stat-card-glow bg-gradient-to-r from-emerald-500 to-green-500"></div>
            <div class="stat-card-content">
                <div class="flex items-center justify-between mb-4">
                    <div class="flex items-center gap-3">
                        <div class="p-3 rounded-xl bg-gradient-to-br from-emerald-500 to-green-600 shadow-lg">
                            <i class="fas fa-chart-line text-lg text-white"></i>
                        </div>
                        <div>
                            <p class="text-xs md:text-sm text-gray-500 font-medium">On-Time Rate</p>
                            <h3 class="text-xl md:text-2xl font-bold text-gray-800 mt-1">{{ $onTimeRate ?? 95 }}%</h3>
                        </div>
                    </div>
                </div>
                <div class="flex items-center justify-between">
                    <div class="flex items-center text-xs">
                        <div class="w-16 h-2 bg-gray-200 rounded-full overflow-hidden">
                            <div class="h-full bg-emerald-500 rounded-full" style="width: {{ $onTimeRate ?? 95 }}%"></div>
                        </div>
                        <span class="text-emerald-600 font-semibold ml-2">Target: 95%</span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Main Content Area -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-4 md:gap-6">
        <!-- Pengiriman Aktif -->
        <div class="lg:col-span-2">
            <div class="glass-effect rounded-3xl overflow-hidden shadow-elegant h-full">
                <div class="p-4 md:p-6 border-b border-gray-100/50">
                    <div class="flex items-center justify-between">
                        <div>
                            <h3 class="text-lg md:text-xl font-bold text-gray-800">Pengiriman Aktif</h3>
                            <p class="text-xs md:text-sm text-gray-600 mt-1">Status real-time pengiriman</p>
                        </div>
                        <a href="{{ route('delivery.index') }}"
                           class="text-indigo-600 hover:text-indigo-700 font-medium text-sm flex items-center gap-1">
                            Lihat Semua <i class="fas fa-arrow-right text-xs"></i>
                        </a>
                    </div>
                </div>
                <div class="divide-y divide-gray-100/50 max-h-96 overflow-y-auto">
                    @forelse($activeDeliveries ?? [] as $delivery)
                    <div class="p-4 md:p-6 hover:bg-white/30 transition-colors duration-200">
                        <div class="flex items-center justify-between mb-3">
                            <div class="flex items-center gap-3">
                                <div class="w-10 h-10 rounded-xl
                                    {{ $delivery->status === 'on_the_way' ? 'bg-blue-100 text-blue-600' :
                                       ($delivery->status === 'loading' ? 'bg-amber-100 text-amber-600' :
                                        'bg-emerald-100 text-emerald-600') }} flex items-center justify-center">
                                    @if($delivery->status === 'on_the_way')
                                        <i class="fas fa-truck-moving"></i>
                                    @elseif($delivery->status === 'loading')
                                        <i class="fas fa-box-open"></i>
                                    @else
                                        <i class="fas fa-check-circle"></i>
                                    @endif
                                </div>
                                <div>
                                    <h4 class="font-semibold text-gray-900">#{{ $delivery->delivery_code }}</h4>
                                    <p class="text-xs text-gray-500 mt-1">
                                        {{ $delivery->driver_name }} • {{ $delivery->vehicle_number }}
                                    </p>
                                </div>
                            </div>
                            <div class="text-right">
                                <div class="text-lg font-bold text-gray-900">{{ $delivery->items_count }} items</div>
                                <span class="inline-block mt-1 px-2 py-1 rounded-full text-xs font-medium
                                    {{ $delivery->status === 'on_the_way' ? 'bg-blue-100 text-blue-800' :
                                       ($delivery->status === 'loading' ? 'bg-amber-100 text-amber-800' :
                                        'bg-emerald-100 text-emerald-800') }}">
                                    {{ ucfirst(str_replace('_', ' ', $delivery->status)) }}
                                </span>
                            </div>
                        </div>
                        <div class="flex items-center justify-between">
                            <div class="flex-1">
                                <div class="flex items-center justify-between text-xs text-gray-500 mb-1">
                                    <span>{{ $delivery->from_location }}</span>
                                    <span>{{ $delivery->to_location }}</span>
                                </div>
                                <div class="h-2 bg-gray-200 rounded-full overflow-hidden">
                                    <div class="h-full bg-indigo-500 rounded-full"
                                         style="width: {{ $delivery->progress ?? 50 }}%"></div>
                                </div>
                                <div class="flex items-center justify-between text-xs text-gray-500 mt-1">
                                    <span>ETA: {{ $delivery->eta }}</span>
                                    <span>{{ $delivery->progress ?? 50 }}%</span>
                                </div>
                            </div>
                        </div>
                    </div>
                    @empty
                    <div class="p-8 text-center">
                        <div class="flex flex-col items-center justify-center text-gray-400">
                            <i class="fas fa-truck text-3xl mb-3"></i>
                            <p class="text-sm">Tidak ada pengiriman aktif</p>
                        </div>
                    </div>
                    @endforelse
                </div>
            </div>
        </div>

        <!-- Quick Actions & Fleet Status -->
        <div class="space-y-4 md:space-y-6">
            <!-- Quick Actions -->
            <div class="glass-effect rounded-3xl p-4 md:p-6 shadow-elegant">
                <h3 class="text-lg md:text-xl font-bold text-gray-800 mb-4">Aksi Cepat</h3>
                <div class="grid grid-cols-2 gap-3">
                    <a href="{{ route('delivery.create') }}"
                       class="group p-4 rounded-xl bg-indigo-50 hover:bg-indigo-100 transition-colors text-center">
                        <div class="w-10 h-10 rounded-lg bg-indigo-500 flex items-center justify-center mx-auto mb-2 group-hover:scale-110 transition-transform">
                            <i class="fas fa-plus text-white"></i>
                        </div>
                        <p class="text-sm font-medium text-gray-900">Pengiriman Baru</p>
                    </a>
                    <a href="{{ route('vehicles.index') }}"
                       class="group p-4 rounded-xl bg-blue-50 hover:bg-blue-100 transition-colors text-center">
                        <div class="w-10 h-10 rounded-lg bg-blue-500 flex items-center justify-center mx-auto mb-2 group-hover:scale-110 transition-transform">
                            <i class="fas fa-user-tie text-white"></i>
                        </div>
                        <p class="text-sm font-medium text-gray-900">Kelola Driver</p>
                    </a>
                    <a href="{{ route('vehicles.index') }}"
                       class="group p-4 rounded-xl bg-emerald-50 hover:bg-emerald-100 transition-colors text-center">
                        <div class="w-10 h-10 rounded-lg bg-emerald-500 flex items-center justify-center mx-auto mb-2 group-hover:scale-110 transition-transform">
                            <i class="fas fa-truck text-white"></i>
                        </div>
                        <p class="text-sm font-medium text-gray-900">Kelola Armada</p>
                    </a>
                    <a href="#"
                       class="group p-4 rounded-xl bg-amber-50 hover:bg-amber-100 transition-colors text-center">
                        <div class="w-10 h-10 rounded-lg bg-amber-500 flex items-center justify-center mx-auto mb-2 group-hover:scale-110 transition-transform">
                            <i class="fas fa-map-marked-alt text-white"></i>
                        </div>
                        <p class="text-sm font-medium text-gray-900">Rute & Area</p>
                    </a>
                </div>
            </div>

            <!-- Fleet Status -->
            <div class="glass-effect rounded-3xl p-4 md:p-6 shadow-elegant">
                <h3 class="text-lg md:text-xl font-bold text-gray-800 mb-4">Status Armada</h3>
                <div class="space-y-3">
                    @forelse($fleetStatus ?? [] as $vehicle)
                    <div class="flex items-center justify-between p-3 rounded-lg
                        {{ $vehicle->status === 'available' ? 'bg-emerald-50' :
                           ($vehicle->status === 'on_delivery' ? 'bg-blue-50' : 'bg-amber-50') }}">
                        <div class="flex items-center gap-2">
                            <div class="w-8 h-8 rounded-lg
                                {{ $vehicle->status === 'available' ? 'bg-emerald-100 text-emerald-600' :
                                   ($vehicle->status === 'on_delivery' ? 'bg-blue-100 text-blue-600' : 'bg-amber-100 text-amber-600') }} flex items-center justify-center">
                                <i class="fas fa-truck text-xs"></i>
                            </div>
                            <div>
                                <p class="text-xs font-medium text-gray-900">{{ $vehicle->name }}</p>
                                <p class="text-xs text-gray-500">{{ $vehicle->type }}</p>
                            </div>
                        </div>
                        <div class="text-right">
                            <span class="text-sm font-bold text-gray-900">{{ $vehicle->license_plate }}</span>
                            <p class="text-xs text-gray-500">{{ ucfirst($vehicle->status) }}</p>
                        </div>
                    </div>
                    @empty
                    <div class="text-center py-4 text-gray-400">
                        <i class="fas fa-truck text-lg mb-2"></i>
                        <p class="text-sm">Belum ada data armada</p>
                    </div>
                    @endforelse
                </div>
            </div>
        </div>
    </div>

    <!-- Delivery Performance -->
    <div class="mt-6 md:mt-8">
        <div class="glass-effect rounded-3xl p-4 md:p-6 shadow-elegant">
            <div class="flex flex-col lg:flex-row lg:items-center justify-between mb-4">
                <div>
                    <h3 class="text-lg md:text-xl font-bold text-gray-800">Kinerja Pengiriman</h3>
                    <p class="text-xs md:text-sm text-gray-600 mt-1">Statistik 7 hari terakhir</p>
                </div>
                <div class="flex items-center gap-3 mt-4 lg:mt-0">
                    <div class="flex items-center gap-2">
                        <div class="w-3 h-3 rounded-full bg-indigo-500"></div>
                        <span class="text-xs text-gray-600">Pengiriman</span>
                    </div>
                    <div class="flex items-center gap-2">
                        <div class="w-3 h-3 rounded-full bg-emerald-500"></div>
                        <span class="text-xs text-gray-600">On-Time</span>
                    </div>
                </div>
            </div>
            <div class="h-64 md:h-80">
                <canvas id="deliveryChart"></canvas>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    // Delivery Performance Chart
    document.addEventListener('DOMContentLoaded', function() {
        const deliveryChartCtx = document.getElementById('deliveryChart');
        if (deliveryChartCtx) {
            const gradient1 = deliveryChartCtx.getContext('2d').createLinearGradient(0, 0, 0, 400);
            gradient1.addColorStop(0, 'rgba(99, 102, 241, 0.25)');
            gradient1.addColorStop(1, 'rgba(99, 102, 241, 0.05)');

            const gradient2 = deliveryChartCtx.getContext('2d').createLinearGradient(0, 0, 0, 400);
            gradient2.addColorStop(0, 'rgba(16, 185, 129, 0.25)');
            gradient2.addColorStop(1, 'rgba(16, 185, 129, 0.05)');

            new Chart(deliveryChartCtx, {
                type: 'bar',
                data: {
                    labels: ['Sen', 'Sel', 'Rab', 'Kam', 'Jum', 'Sab', 'Min'],
                    datasets: [
                        {
                            label: 'Total Pengiriman',
                            data: [12, 15, 18, 14, 20, 8, 10],
                            backgroundColor: gradient1,
                            borderColor: 'rgba(99, 102, 241, 1)',
                            borderWidth: 2,
                            borderRadius: 6,
                        },
                        {
                            label: 'On-Time Delivery',
                            data: [10, 12, 16, 13, 18, 7, 9],
                            backgroundColor: gradient2,
                            borderColor: 'rgba(16, 185, 129, 1)',
                            borderWidth: 2,
                            borderRadius: 6,
                        }
                    ]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            display: false
                        }
                    },
                    scales: {
                        y: {
                            beginAtZero: true,
                            grid: {
                                color: 'rgba(229, 231, 235, 0.5)'
                            },
                            ticks: {
                                callback: function(value) {
                                    return value + ' deliveries';
                                }
                            }
                        },
                        x: {
                            grid: {
                                display: false
                            }
                        }
                    }
                }
            });
        }

        // Auto-refresh delivery status
        setInterval(() => {
            fetch('/api/delivery-status')
                .then(response => response.json())
                .then(data => {
                    updateDeliveryStatus(data);
                });
        }, 60000);
    });

    function updateDeliveryStatus(data) {
        // Update delivery status in real-time
        const deliveryElements = document.querySelectorAll('[data-delivery-id]');
        deliveryElements.forEach(el => {
            const deliveryId = el.dataset.deliveryId;
            const delivery = data.find(d => d.id == deliveryId);
            if (delivery) {
                updateDeliveryElement(el, delivery);
            }
        });
    }
</script>

<style>
    .stat-card {
        @apply relative bg-white/80 backdrop-blur-sm rounded-2xl p-4 shadow-soft border border-white/50;
        transition: all 0.3s ease;
    }

    .stat-card:hover {
        @apply shadow-lg -translate-y-1 border-indigo-200/50;
    }

    .stat-card-glow {
        @apply absolute -inset-1 rounded-2xl blur-xl opacity-0 transition-opacity duration-300;
    }

    .glass-effect {
        background: rgba(255, 255, 255, 0.7);
        backdrop-filter: blur(10px);
        border: 1px solid rgba(99, 102, 241, 0.1);
    }

    .gradient-text {
        background: linear-gradient(135deg, #6366f1 0%, #8b5cf6 100%);
        -webkit-background-clip: text;
        -webkit-text-fill-color: transparent;
    }
</style>
@endsection
