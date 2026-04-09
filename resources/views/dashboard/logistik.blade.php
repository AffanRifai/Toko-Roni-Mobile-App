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
                
                <!-- TAMBAHKAN: Informasi untuk staff logistik -->
                @if(isset($isStaffLogistik) && $isStaffLogistik)
                <div class="mt-4 bg-blue-50 border border-blue-200 rounded-xl p-3">
                    <div class="flex items-center gap-2">
                        <i class="fas fa-info-circle text-blue-600"></i>
                        <p class="text-sm text-blue-800">
                            Menampilkan pengiriman yang ditugaskan kepada <strong>{{ Auth::user()->name }}</strong>
                        </p>
                    </div>
                </div>
                @endif
            </div>
        </div>
    </div>

    <!-- Stats Grid Logistik - Baris Pertama (4 Card) dengan style baru -->
    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 md:gap-6 mb-6 md:mb-8">
        <!-- Total Pengiriman Hari Ini -->
        <div class="bg-white/80 backdrop-blur-sm rounded-2xl p-5 md:p-6 shadow-sm border border-white/50 hover:shadow-md transition-all">
            <div class="flex items-center justify-between">
                <div class="flex items-center gap-3 md:gap-4">
                    <div class="p-3 md:p-4 rounded-2xl bg-gradient-to-br from-indigo-500 to-purple-600 shadow-lg">
                        <i class="fas fa-truck-loading text-lg md:text-xl text-white"></i>
                    </div>
                    <div>
                        <p class="text-xs md:text-sm text-gray-500 font-medium">Pengiriman Hari Ini</p>
                        <h3 class="text-xl md:text-3xl font-bold text-gray-800 mt-1">{{ number_format($todayDeliveries ?? 0) }}</h3>
                    </div>
                </div>
                <a href="{{ route('delivery.index') }}?date=today" class="w-8 h-8 rounded-lg bg-gray-100 flex items-center justify-center text-gray-600 hover:bg-indigo-500 hover:text-white transition-all">
                    <i class="fas fa-arrow-right text-xs"></i>
                </a>
            </div>
            <div class="flex items-center justify-between mt-4 pt-3 border-t border-gray-100">
                <div class="flex items-center text-xs md:text-sm">
                    <div class="w-6 h-6 md:w-8 md:h-8 rounded-lg bg-emerald-100 flex items-center justify-center mr-2">
                        <i class="fas fa-check text-emerald-600 text-xs"></i>
                    </div>
                    <span class="text-emerald-600 font-semibold">{{ $completedDeliveries ?? 0 }} selesai</span>
                    <span class="text-gray-500 ml-2 hidden sm:inline">hari ini</span>
                </div>
                <div class="text-xs text-gray-400">Today</div>
            </div>
        </div>

        <!-- Pengiriman Dalam Proses -->
        <div class="bg-white/80 backdrop-blur-sm rounded-2xl p-5 md:p-6 shadow-sm border border-white/50 hover:shadow-md transition-all">
            <div class="flex items-center justify-between">
                <div class="flex items-center gap-3 md:gap-4">
                    <div class="p-3 md:p-4 rounded-2xl bg-gradient-to-br from-amber-500 to-orange-600 shadow-lg">
                        <i class="fas fa-shipping-fast text-lg md:text-xl text-white"></i>
                    </div>
                    <div>
                        <p class="text-xs md:text-sm text-gray-500 font-medium">Dalam Proses</p>
                        <h3 class="text-xl md:text-3xl font-bold text-gray-800 mt-1">{{ number_format($ongoingDeliveries ?? 0) }}</h3>
                    </div>
                </div>
                <a href="{{ route('delivery.index') }}?status=ongoing" class="w-8 h-8 rounded-lg bg-gray-100 flex items-center justify-center text-gray-600 hover:bg-amber-500 hover:text-white transition-all">
                    <i class="fas fa-arrow-right text-xs"></i>
                </a>
            </div>
            <div class="flex items-center justify-between mt-4 pt-3 border-t border-gray-100">
                <div class="flex items-center text-xs md:text-sm">
                    <div class="w-6 h-6 md:w-8 md:h-8 rounded-lg bg-amber-100 flex items-center justify-center mr-2">
                        <i class="fas fa-clock text-amber-600 text-xs"></i>
                    </div>
                    <span class="text-amber-600 font-semibold">{{ $delayedDeliveries ?? 0 }} tertunda</span>
                </div>
                <div class="text-xs text-gray-400">Ongoing</div>
            </div>
        </div>

        <!-- Total Barang Dikirim -->
        <div class="bg-white/80 backdrop-blur-sm rounded-2xl p-5 md:p-6 shadow-sm border border-white/50 hover:shadow-md transition-all">
            <div class="flex items-center justify-between">
                <div class="flex items-center gap-3 md:gap-4">
                    <div class="p-3 md:p-4 rounded-2xl bg-gradient-to-br from-blue-500 to-cyan-600 shadow-lg">
                        <i class="fas fa-boxes text-lg md:text-xl text-white"></i>
                    </div>
                    <div>
                        <p class="text-xs md:text-sm text-gray-500 font-medium">Barang Dikirim</p>
                        <h3 class="text-xl md:text-3xl font-bold text-gray-800 mt-1">{{ number_format($totalItemsShipped ?? 0) }}</h3>
                    </div>
                </div>
                <a href="{{ route('delivery.index') }}?items=shipped" class="w-8 h-8 rounded-lg bg-gray-100 flex items-center justify-center text-gray-600 hover:bg-blue-500 hover:text-white transition-all">
                    <i class="fas fa-arrow-right text-xs"></i>
                </a>
            </div>
            <div class="flex items-center justify-between mt-4 pt-3 border-t border-gray-100">
                <div class="flex items-center text-xs md:text-sm">
                    <span class="text-gray-600">{{ number_format($totalWeight ?? 0) }} kg</span>
                    <span class="text-blue-600 font-semibold ml-2">{{ number_format($totalVolume ?? 0) }} m³</span>
                </div>
                <div class="text-xs text-gray-400">Items</div>
            </div>
        </div>

        <!-- On-Time Delivery Rate -->
        <div class="bg-white/80 backdrop-blur-sm rounded-2xl p-5 md:p-6 shadow-sm border border-white/50 hover:shadow-md transition-all">
            <div class="flex items-center justify-between">
                <div class="flex items-center gap-3 md:gap-4">
                    <div class="p-3 md:p-4 rounded-2xl bg-gradient-to-br from-emerald-500 to-green-600 shadow-lg">
                        <i class="fas fa-chart-line text-lg md:text-xl text-white"></i>
                    </div>
                    <div>
                        <p class="text-xs md:text-sm text-gray-500 font-medium">On-Time Rate</p>
                        <h3 class="text-xl md:text-3xl font-bold text-gray-800 mt-1">{{ number_format($onTimeRate ?? 95) }}%</h3>
                    </div>
                </div>
                <a href="{{ route('delivery.index') }}?performance=on-time" class="w-8 h-8 rounded-lg bg-gray-100 flex items-center justify-center text-gray-600 hover:bg-emerald-500 hover:text-white transition-all">
                    <i class="fas fa-arrow-right text-xs"></i>
                </a>
            </div>
            <div class="flex items-center justify-between mt-4 pt-3 border-t border-gray-100">
                <div class="flex items-center text-xs md:text-sm" style="width: 100%;">
                    <div class="flex-1 h-2 bg-gray-200 rounded-full overflow-hidden mr-2">
                        <div class="h-full bg-emerald-500 rounded-full" style="width: {{ min(100, $onTimeRate ?? 95) }}%"></div>
                    </div>
                    <span class="text-emerald-600 font-semibold text-xs">Target: 95%</span>
                </div>
            </div>
        </div>

    <!-- Di bagian stats grid, tambahkan untuk menampilkan armada tersedia -->
    <div class="bg-white/80 backdrop-blur-sm rounded-2xl p-5 md:p-6 shadow-sm border border-white/50 hover:shadow-md transition-all">
        <div class="flex items-center justify-between">
            <div class="flex items-center gap-3 md:gap-4">
                <div class="p-3 md:p-4 rounded-2xl bg-gradient-to-br from-purple-500 to-pink-600 shadow-lg">
                    <i class="fas fa-truck text-lg md:text-xl text-white"></i>
                </div>
                <div>
                    <p class="text-xs md:text-sm text-gray-500 font-medium">Armada Tersedia</p>
                    <h3 class="text-xl md:text-3xl font-bold text-gray-800 mt-1">{{ number_format($availableFleet ?? 0) }}</h3>
                </div>
            </div>
            <div class="w-8 h-8 rounded-lg bg-gray-100 flex items-center justify-center text-gray-400">
                <i class="fas fa-check-circle text-xs text-green-500"></i>
            </div>
        </div>
        <div class="flex items-center justify-between mt-4 pt-3 border-t border-gray-100">
            <div class="flex items-center text-xs md:text-sm">
                <span class="text-gray-600">Siap digunakan</span>
            </div>
            <div class="text-xs text-gray-400">Available</div>
        </div>
    </div>

<!-- Driver Aktif -->
<div class="bg-white/80 backdrop-blur-sm rounded-2xl p-5 md:p-6 shadow-sm border border-white/50 hover:shadow-md transition-all">
    <div class="flex items-center justify-between">
        <div class="flex items-center gap-3 md:gap-4">
            <div class="p-3 md:p-4 rounded-2xl bg-gradient-to-br from-cyan-500 to-blue-600 shadow-lg">
                <i class="fas fa-user-tie text-lg md:text-xl text-white"></i>
            </div>
            <div>
                <p class="text-xs md:text-sm text-gray-500 font-medium">Driver Aktif</p>
                <h3 class="text-xl md:text-3xl font-bold text-gray-800 mt-1">{{ number_format($activeDrivers ?? 0) }}<span class="text-sm font-normal text-gray-500">/{{ number_format($totalDrivers ?? 0) }}</span></h3>
            </div>
        </div>
        <div class="w-8 h-8 rounded-lg bg-gray-100 flex items-center justify-center text-gray-400">
            <i class="fas fa-users text-xs"></i>
        </div>
    </div>
    <div class="flex items-center justify-between mt-4 pt-3 border-t border-gray-100">
        <div class="flex items-center text-xs md:text-sm">
            <span class="text-gray-600">Sedang bertugas</span>
        </div>
        <div class="text-xs text-gray-400">Drivers</div>
    </div>
</div>

        <!-- Driver Aktif -->
        <div class="bg-white/80 backdrop-blur-sm rounded-2xl p-5 md:p-6 shadow-sm border border-white/50 hover:shadow-md transition-all">
            <div class="flex items-center justify-between">
                <div class="flex items-center gap-3 md:gap-4">
                    <div class="p-3 md:p-4 rounded-2xl bg-gradient-to-br from-cyan-500 to-blue-600 shadow-lg">
                        <i class="fas fa-user-tie text-lg md:text-xl text-white"></i>
                    </div>
                    <div>
                        <p class="text-xs md:text-sm text-gray-500 font-medium">Driver Aktif</p>
                        <h3 class="text-xl md:text-3xl font-bold text-gray-800 mt-1">{{ number_format($totalDrivers ?? 0) }}</h3>
                    </div>
                </div>
                <div class="w-8 h-8 rounded-lg bg-gray-100 flex items-center justify-center text-gray-400">
                    <i class="fas fa-users text-xs"></i>
                </div>
            </div>
            <div class="flex items-center justify-between mt-4 pt-3 border-t border-gray-100">
                <div class="flex items-center text-xs md:text-sm">
                    <span class="text-gray-600">Siap bertugas</span>
                </div>
                <div class="text-xs text-gray-400">Drivers</div>
            </div>
        </div>
    </div>

    <!-- Main Content Area -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-4 md:gap-6">
        <!-- Pengiriman Aktif (Lebar 2 kolom) -->
        <div class="lg:col-span-2">
            <div class="glass-effect rounded-3xl overflow-hidden shadow-elegant h-full">
                <div class="p-4 md:p-6 border-b border-gray-100/50">
                    <div class="flex items-center justify-between">
                        <div>
                            <h3 class="text-lg md:text-xl font-bold text-gray-800">
                                @if(isset($isStaffLogistik) && $isStaffLogistik)
                                    Pengiriman Saya
                                @else
                                    Pengiriman Aktif
                                @endif
                            </h3>
                            <p class="text-xs md:text-sm text-gray-600 mt-1">
                                @if(isset($isStaffLogistik) && $isStaffLogistik)
                                    Pengiriman yang ditugaskan kepada Anda
                                @else
                                    Status real-time pengiriman
                                @endif
                            </p>
                        </div>
                        <a href="{{ route('delivery.index') }}"
                           class="text-indigo-600 hover:text-indigo-700 font-medium text-sm flex items-center gap-1">
                            Lihat Semua <i class="fas fa-arrow-right text-xs"></i>
                        </a>
                    </div>
                </div>
                <div class="divide-y divide-gray-100/50 max-h-96 overflow-y-auto elegant-scrollbar">
                    @forelse($activeDeliveries ?? [] as $delivery)
                    <div class="p-4 md:p-6 hover:bg-white/30 transition-colors duration-200">
                        <div class="flex items-center justify-between mb-3">
                            <div class="flex items-center gap-3">
                                <div class="w-10 h-10 rounded-xl
                                    {{ $delivery->status === 'on_delivery' ? 'bg-blue-100 text-blue-600' :
                                       ($delivery->status === 'assigned' ? 'bg-amber-100 text-amber-600' :
                                        'bg-emerald-100 text-emerald-600') }} flex items-center justify-center">
                                    @if($delivery->status === 'on_delivery')
                                        <i class="fas fa-truck-moving"></i>
                                    @elseif($delivery->status === 'picked_up')
                                        <i class="fas fa-box-open"></i>
                                    @elseif($delivery->status === 'assigned')
                                        <i class="fas fa-user-check"></i>
                                    @else
                                        <i class="fas fa-check-circle"></i>
                                    @endif
                                </div>
                                <div>
                                    <h4 class="font-semibold text-gray-900">{{ $delivery->delivery_code }}</h4>
                                    <p class="text-xs text-gray-500 mt-1">
                                        {{ $delivery->driver_name ?? 'Driver' }} • {{ $delivery->vehicle_number ?? 'N/A' }}
                                    </p>
                                </div>
                            </div>
                            <div class="text-right">
                                <div class="text-lg font-bold text-gray-900">{{ $delivery->items_count ?? 0 }} items</div>
                                <span class="inline-block mt-1 px-2 py-1 rounded-full text-xs font-medium
                                    {{ $delivery->status === 'on_delivery' ? 'bg-blue-100 text-blue-800' :
                                       ($delivery->status === 'picked_up' ? 'bg-indigo-100 text-indigo-800' :
                                        ($delivery->status === 'assigned' ? 'bg-amber-100 text-amber-800' :
                                        'bg-emerald-100 text-emerald-800')) }}">
                                    {{ $delivery->status_label ?? ucfirst(str_replace('_', ' ', $delivery->status)) }}
                                </span>
                            </div>
                        </div>
                        <div class="flex items-center justify-between">
                            <div class="flex-1">
                                <div class="flex items-center justify-between text-xs text-gray-500 mb-1">
                                    <span>{{ $delivery->from_location ?? 'Gudang' }}</span>
                                    <span>{{ $delivery->to_location ?? 'Tujuan' }}</span>
                                </div>
                                <div class="h-2 bg-gray-200 rounded-full overflow-hidden">
                                    <div class="h-full bg-indigo-500 rounded-full"
                                         style="width: {{ $delivery->progress ?? 50 }}%"></div>
                                </div>
                                <div class="flex items-center justify-between text-xs text-gray-500 mt-1">
                                    <span>ETA: {{ $delivery->eta ?? '15:00' }}</span>
                                    <span>{{ $delivery->progress ?? 50 }}%</span>
                                </div>
                            </div>
                        </div>
                    </div>
                    @empty
                    <div class="p-8 text-center">
                        <div class="flex flex-col items-center justify-center text-gray-400">
                            <i class="fas fa-truck text-3xl mb-3"></i>
                            <p class="text-sm">
                                @if(isset($isStaffLogistik) && $isStaffLogistik)
                                    Anda belum memiliki pengiriman aktif
                                @else
                                    Tidak ada pengiriman aktif
                                @endif
                            </p>
                        </div>
                    </div>
                    @endforelse
                </div>
            </div>
        </div>

        <!-- Fleet Status (1 kolom) -->
        <div>
            <div class="glass-effect rounded-3xl p-4 md:p-6 shadow-elegant h-full">
                <h3 class="text-lg md:text-xl font-bold text-gray-800 mb-4">Status Armada</h3>
                <div class="space-y-3 max-h-96 overflow-y-auto elegant-scrollbar pr-2">
                    @forelse($fleetStatus ?? [] as $vehicle)
                    <div class="flex items-center justify-between p-3 rounded-lg
                        {{ $vehicle->status === 'available' ? 'bg-emerald-50' :
                           ($vehicle->status === 'in_use' ? 'bg-blue-50' : 'bg-amber-50') }}">
                        <div class="flex items-center gap-2">
                            <div class="w-8 h-8 rounded-lg
                                {{ $vehicle->status === 'available' ? 'bg-emerald-100 text-emerald-600' :
                                   ($vehicle->status === 'in_use' ? 'bg-blue-100 text-blue-600' : 'bg-amber-100 text-amber-600') }} flex items-center justify-center">
                                <i class="fas fa-truck text-xs"></i>
                            </div>
                            <div>
                                <p class="text-xs font-medium text-gray-900">{{ $vehicle->name ?? 'Kendaraan' }}</p>
                                <p class="text-xs text-gray-500">{{ $vehicle->type ?? 'Unknown' }}</p>
                            </div>
                        </div>
                        <div class="text-right">
                            <span class="text-sm font-bold text-gray-900">{{ $vehicle->license_plate ?? 'N/A' }}</span>
                            <p class="text-xs {{ $vehicle->status === 'available' ? 'text-green-600' : ($vehicle->status === 'in_use' ? 'text-blue-600' : 'text-amber-600') }}">
                                {{ $vehicle->status_display ?? ucfirst($vehicle->status ?? 'unknown') }}
                            </p>
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
                    <p class="text-xs md:text-sm text-gray-600 mt-1">
                        @if(isset($isStaffLogistik) && $isStaffLogistik)
                            Statistik pengiriman Anda
                        @else
                            Statistik pengiriman
                        @endif
                        <span id="chartRangeLabel">
                            @if($chartRange == 7) 7 hari terakhir
                            @elseif($chartRange == 30) 30 hari terakhir
                            @else 90 hari terakhir
                            @endif
                        </span>
                    </p>
                </div>
                
                <!-- FILTER BUTTONS -->
                <div class="flex items-center gap-2 mt-4 lg:mt-0">
                    <div class="bg-gray-100 rounded-xl p-1 flex gap-1">
                        <button onclick="changeChartRange(7)" 
                            class="range-btn px-4 py-2 rounded-lg text-sm font-medium transition-all {{ $chartRange == 7 ? 'bg-white shadow-md text-indigo-600' : 'text-gray-600 hover:bg-gray-200' }}"
                            data-range="7">
                            7 Hari
                        </button>
                        <button onclick="changeChartRange(30)" 
                            class="range-btn px-4 py-2 rounded-lg text-sm font-medium transition-all {{ $chartRange == 30 ? 'bg-white shadow-md text-indigo-600' : 'text-gray-600 hover:bg-gray-200' }}"
                            data-range="30">
                            30 Hari
                        </button>
                        <button onclick="changeChartRange(90)" 
                            class="range-btn px-4 py-2 rounded-lg text-sm font-medium transition-all {{ $chartRange == 90 ? 'bg-white shadow-md text-indigo-600' : 'text-gray-600 hover:bg-gray-200' }}"
                            data-range="90">
                            90 Hari
                        </button>
                    </div>
                </div>
            </div>
            
            <div class="flex items-center gap-4 mb-4">
                <div class="flex items-center gap-2">
                    <div class="w-3 h-3 rounded-full bg-indigo-500"></div>
                    <span class="text-xs text-gray-600">Total Pengiriman (Semua Status)</span>
                </div>
                <div class="flex items-center gap-2">
                    <div class="w-3 h-3 rounded-full bg-emerald-500"></div>
                    <span class="text-xs text-gray-600">Terkirim (Delivered)</span>
                </div>
            </div>
            
            <div class="h-64 md:h-80">
                <canvas id="deliveryChart"></canvas>
            </div>
        </div>
    </div>

    <!-- Loading Overlay untuk Chart -->
    <div id="chartLoading" class="fixed inset-0 bg-black/20 hidden items-center justify-center z-50">
        <div class="bg-white rounded-xl p-4 flex items-center gap-3 shadow-lg">
            <div class="spinner-small"></div>
            <span class="text-sm text-gray-700">Memuat data...</span>
        </div>
    </div>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    let deliveryChart = null;
    let currentRange = {{ $chartRange ?? 7 }};
    
    // Inisialisasi chart pertama kali
    document.addEventListener('DOMContentLoaded', function() {
        loadChartData(currentRange);
    });
    
    // Fungsi untuk mengubah range chart
    function changeChartRange(range) {
        if (currentRange === range) return;
        
        currentRange = range;
        
        // Update active state pada buttons
        document.querySelectorAll('.range-btn').forEach(btn => {
            if (parseInt(btn.dataset.range) === range) {
                btn.classList.add('bg-white', 'shadow-md', 'text-indigo-600');
                btn.classList.remove('text-gray-600', 'hover:bg-gray-200');
            } else {
                btn.classList.remove('bg-white', 'shadow-md', 'text-indigo-600');
                btn.classList.add('text-gray-600', 'hover:bg-gray-200');
            }
        });
        
        // Update label
        const labelMap = {
            7: '7 hari terakhir',
            30: '30 hari terakhir',
            90: '90 hari terakhir'
        };
        document.getElementById('chartRangeLabel').innerText = labelMap[range];
        
        // Load data baru
        loadChartData(range);
    }
    
    // Fungsi untuk load data chart via AJAX
    function loadChartData(range) {
        const loadingDiv = document.getElementById('chartLoading');
        if (loadingDiv) {
            loadingDiv.classList.remove('hidden');
            loadingDiv.classList.add('flex');
        }
        
        // Tentukan URL berdasarkan role
        let url = `/dashboard/logistik/chart-data/${range}`;
        
        fetch(url, {
            method: 'GET',
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'Accept': 'application/json'
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                updateChart(data.data);
                
                // Update juga statistik ringkasan jika perlu
                updateSummaryStats(data.summary);
            } else {
                console.error('Error loading chart data:', data.message);
                showError('Gagal memuat data chart');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            showError('Terjadi kesalahan saat memuat data');
        })
        .finally(() => {
            if (loadingDiv) {
                loadingDiv.classList.add('hidden');
                loadingDiv.classList.remove('flex');
            }
        });
    }
    
    // Fungsi untuk update chart
    function updateChart(chartData) {
        const ctx = document.getElementById('deliveryChart');
        if (!ctx) return;
        
        const labels = chartData.labels || [];
        const totalData = chartData.total_deliveries || [];
        const onTimeData = chartData.on_time_deliveries || [];
        
        // Hancurkan chart lama jika ada
        if (deliveryChart) {
            deliveryChart.destroy();
        }
        
        const canvasCtx = ctx.getContext('2d');
        
        const gradient1 = canvasCtx.createLinearGradient(0, 0, 0, 400);
        gradient1.addColorStop(0, 'rgba(99, 102, 241, 0.25)');
        gradient1.addColorStop(1, 'rgba(99, 102, 241, 0.05)');
        
        const gradient2 = canvasCtx.createLinearGradient(0, 0, 0, 400);
        gradient2.addColorStop(0, 'rgba(16, 185, 129, 0.25)');
        gradient2.addColorStop(1, 'rgba(16, 185, 129, 0.05)');
        
        deliveryChart = new Chart(ctx, {
            type: 'bar',
            data: {
                labels: labels,
                datasets: [
                    {
                        label: 'Total Pengiriman',
                        data: totalData,
                        backgroundColor: gradient1,
                        borderColor: 'rgba(99, 102, 241, 1)',
                        borderWidth: 2,
                        borderRadius: 6,
                        barPercentage: 0.7,
                        categoryPercentage: 0.8,
                    },
                    {
                        label: 'Terkirim (Delivered)',
                        data: onTimeData,
                        backgroundColor: gradient2,
                        borderColor: 'rgba(16, 185, 129, 1)',
                        borderWidth: 2,
                        borderRadius: 6,
                        barPercentage: 0.7,
                        categoryPercentage: 0.8,
                    }
                ]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    tooltip: {
                        callbacks: {
                            label: function(context) {
                                let label = context.dataset.label || '';
                                let value = context.raw;
                                return `${label}: ${value} pengiriman`;
                            }
                        }
                    },
                    legend: {
                        position: 'top',
                        labels: {
                            usePointStyle: true,
                            boxWidth: 10,
                            font: { size: 11 }
                        }
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        grid: {
                            color: 'rgba(229, 231, 235, 0.5)'
                        },
                        ticks: {
                            stepSize: 1,
                            callback: function(value) {
                                return value + ' kirim';
                            }
                        },
                        title: {
                            display: true,
                            text: 'Jumlah Pengiriman',
                            font: { size: 11 }
                        }
                    },
                    x: {
                        grid: { display: false },
                        ticks: {
                            font: { size: 11 },
                            maxRotation: 45,
                            minRotation: 45
                        }
                    }
                }
            }
        });
    }
    
    // Fungsi untuk update statistik ringkasan (opsional)
    function updateSummaryStats(summary) {
        if (!summary) return;
        
        // Update elemen statistik jika ada
        if (document.getElementById('totalItemsShipped')) {
            document.getElementById('totalItemsShipped').innerText = formatNumber(summary.total_items_shipped || 0);
        }
        if (document.getElementById('onTimeRate')) {
            document.getElementById('onTimeRate').innerText = (summary.on_time_rate || 100) + '%';
        }
    }
    
    // Fungsi format number
    function formatNumber(num) {
        return num.toString().replace(/\B(?=(\d{3})+(?!\d))/g, ".");
    }
    
    // Fungsi show error
    function showError(message) {
        const toast = document.createElement('div');
        toast.className = 'fixed bottom-4 right-4 bg-red-500 text-white px-6 py-3 rounded-lg shadow-lg z-50 animate-fade-in';
        toast.innerHTML = `
            <div class="flex items-center gap-3">
                <i class="fas fa-exclamation-circle"></i>
                <span>${message}</span>
            </div>
        `;
        document.body.appendChild(toast);
        setTimeout(() => toast.remove(), 3000);
    }
</script>

<style>
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

    .animate-fade-in {
        animation: fadeIn 0.6s ease-out;
    }

    @keyframes fadeIn {
        from { opacity: 0; transform: translateY(10px); }
        to { opacity: 1; transform: translateY(0); }
    }

    .elegant-scrollbar::-webkit-scrollbar {
        width: 6px;
    }

    .elegant-scrollbar::-webkit-scrollbar-track {
        background: rgba(99, 102, 241, 0.1);
        border-radius: 10px;
    }

    .elegant-scrollbar::-webkit-scrollbar-thumb {
        background: linear-gradient(135deg, #6366f1 0%, #8b5cf6 100%);
        border-radius: 10px;
    }

    .elegant-scrollbar::-webkit-scrollbar-thumb:hover {
        background: linear-gradient(135deg, #4f46e5 0%, #7c3aed 100%);
    }
    .spinner-small {
        width: 20px;
        height: 20px;
        border: 2px solid #f3f3f3;
        border-top: 2px solid #6366f1;
        border-radius: 50%;
        animation: spin 0.8s linear infinite;
    }
    
    @keyframes spin {
        0% { transform: rotate(0deg); }
        100% { transform: rotate(360deg); }
    }
    
    .animate-fade-in {
        animation: fadeIn 0.3s ease-out;
    }
    
    @keyframes fadeIn {
        from { opacity: 0; transform: translateY(10px); }
        to { opacity: 1; transform: translateY(0); }
    }
</style>
@endsection