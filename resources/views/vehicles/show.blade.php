{{-- resources/views/vehicles/show.blade.php --}}
@extends('layouts.app')

@section('title', 'Detail Kendaraan')
@section('page-title', 'Detail Kendaraan')
@section('page-subtitle', 'Informasi lengkap kendaraan')

@section('content')
    <div class="min-h-screen bg-gradient-to-br from-blue-50/50 to-purple-50/30 p-4 md:p-6">

        <!-- Header -->
        <div class="glass-effect rounded-3xl p-6 md:p-8 shadow-elegant mb-6 animate-fade-in">
            <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-4">
                <div class="flex items-center gap-4">
                    <div class="relative">
                        <div
                            class="w-16 h-16 rounded-2xl bg-gradient-to-br from-blue-500 to-indigo-600 flex items-center justify-center shadow-lg">
                            <i class="fas fa-truck text-2xl text-white"></i>
                        </div>
                        <div
                            class="absolute -inset-1 bg-gradient-to-r from-blue-500 to-indigo-600 rounded-2xl blur-xl opacity-20">
                        </div>
                    </div>
                    <div>
                        <h1 class="text-3xl font-bold text-gray-800">{{ $vehicle->name }}</h1>
                        <p class="text-gray-600 mt-1">{{ $vehicle->license_plate }}</p>
                    </div>
                </div>
                <div class="flex gap-3">
                    <a href="{{ route('vehicles.edit', $vehicle) }}"
                        class="flex items-center gap-2 px-6 py-3 bg-yellow-600 text-white rounded-xl hover:bg-yellow-700 transition-all">
                        <i class="fas fa-edit"></i>
                        <span>Edit</span>
                    </a>
                    <a href="{{ route('vehicles.index') }}"
                        class="flex items-center gap-2 px-6 py-3 border border-gray-300 text-gray-700 rounded-xl hover:bg-gray-50 transition-all">
                        <i class="fas fa-arrow-left"></i>
                        <span>Kembali</span>
                    </a>
                </div>
            </div>
        </div>

        <!-- Status Card -->
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-5 mb-6">
            <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
                <div class="flex items-center gap-3">
                    <span class="text-gray-600">Status:</span>
                    <span class="badge {{ $vehicle->getStatusBadgeClass() }} text-sm px-3 py-1.5">
                        <i class="fas fa-circle mr-2 text-[10px]"></i>
                        @if ($vehicle->status == 'available')
                            Tersedia
                        @elseif($vehicle->status == 'in_use')
                            Sedang Digunakan
                        @else
                            Servis/Maintenance
                        @endif
                    </span>
                </div>
                <div class="flex gap-3">
                    @if ($vehicle->status == 'available')
                        <span class="text-sm text-green-600 bg-green-50 px-3 py-1.5 rounded-lg">
                            <i class="fas fa-check-circle mr-1"></i> Siap digunakan
                        </span>
                    @elseif($vehicle->status == 'in_use')
                        <span class="text-sm text-blue-600 bg-blue-50 px-3 py-1.5 rounded-lg">
                            <i class="fas fa-truck mr-1"></i> Sedang dalam pengiriman
                        </span>
                    @else
                        <span class="text-sm text-red-600 bg-red-50 px-3 py-1.5 rounded-lg">
                            <i class="fas fa-tools mr-1"></i> Dalam perbaikan
                        </span>
                    @endif
                </div>
            </div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            <!-- Main Content -->
            <div class="lg:col-span-2 space-y-6">
                <!-- Informasi Detail -->
                <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
                    <div class="border-b border-gray-200 px-5 py-4 bg-gradient-to-r from-blue-50 to-indigo-50">
                        <h3 class="font-semibold text-gray-900 flex items-center gap-2">
                            <i class="fas fa-info-circle text-blue-600"></i>
                            Informasi Detail
                        </h3>
                    </div>
                    <div class="p-5">
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <!-- Spesifikasi -->
                            <div>
                                <h4 class="font-medium text-gray-700 mb-3 flex items-center gap-2">
                                    <i class="fas fa-cog text-gray-500"></i>
                                    Spesifikasi
                                </h4>
                                <div class="space-y-3">
                                    <div class="flex justify-between py-2 border-b border-gray-100">
                                        <span class="text-gray-600">Jenis Kendaraan</span>
                                        <span class="font-medium text-gray-900">
                                            @if ($vehicle->type == 'motor')
                                                🏍️ Motor
                                            @elseif($vehicle->type == 'mobil')
                                                🚗 Mobil
                                            @else
                                                🚛 Truck
                                            @endif
                                        </span>
                                    </div>
                                    <div class="flex justify-between py-2 border-b border-gray-100">
                                        <span class="text-gray-600">Kapasitas Berat</span>
                                        <span
                                            class="font-medium text-gray-900">{{ $vehicle->capacity_weight_formatted }}</span>
                                    </div>
                                    <div class="flex justify-between py-2 border-b border-gray-100">
                                        <span class="text-gray-600">Kapasitas Volume</span>
                                        <span
                                            class="font-medium text-gray-900">{{ $vehicle->capacity_volume_formatted }}</span>
                                    </div>
                                </div>
                            </div>

                            <!-- Maintenance -->
                            <div>
                                <h4 class="font-medium text-gray-700 mb-3 flex items-center gap-2">
                                    <i class="fas fa-tools text-gray-500"></i>
                                    Maintenance
                                </h4>
                                <div class="space-y-3">
                                    <div class="flex justify-between py-2 border-b border-gray-100">
                                        <span class="text-gray-600">Terakhir Servis</span>
                                        <span
                                            class="font-medium text-gray-900">{{ $vehicle->last_maintenance_formatted }}</span>
                                    </div>
                                    @php
                                        $daysSince = $vehicle->days_since_maintenance;
                                    @endphp
                                    @if ($daysSince)
                                        <div class="flex justify-between py-2 border-b border-gray-100">
                                            <span class="text-gray-600">Hari Sejak Servis</span>
                                            <span
                                                class="font-medium {{ $daysSince > 30 ? 'text-red-600' : 'text-gray-900' }}">
                                                {{ $daysSince }} hari
                                                @if ($daysSince > 30)
                                                    <i class="fas fa-exclamation-triangle text-red-500 ml-1"
                                                        title="Perlu maintenance"></i>
                                                @endif
                                            </span>
                                        </div>
                                    @endif
                                    <div class="flex justify-between py-2">
                                        <span class="text-gray-600">Kebutuhan Servis</span>
                                        <span class="font-medium">
                                            @if ($vehicle->needs_maintenance)
                                                <span class="text-red-600">Perlu servis</span>
                                            @else
                                                <span class="text-green-600">Baik</span>
                                            @endif
                                        </span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Riwayat Pengiriman -->
                <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
                    <div class="border-b border-gray-200 px-5 py-4 bg-gradient-to-r from-blue-50 to-indigo-50">
                        <h3 class="font-semibold text-gray-900 flex items-center gap-2">
                            <i class="fas fa-history text-blue-600"></i>
                            Riwayat Pengiriman
                        </h3>
                    </div>
                    <div class="p-5">
                        @if ($vehicle->deliveries && $vehicle->deliveries->count() > 0)
                            <div class="space-y-3">
                                @foreach ($vehicle->deliveries->take(5) as $delivery)
                                    <div
                                        class="flex items-center justify-between p-3 bg-gray-50 rounded-lg hover:bg-gray-100 transition-colors">
                                        <div class="flex items-center gap-3">
                                            <div class="w-8 h-8 rounded-full bg-blue-100 flex items-center justify-center">
                                                <i class="fas fa-truck text-blue-600 text-xs"></i>
                                            </div>
                                            <div>
                                                <p class="font-medium text-gray-900">{{ $delivery->delivery_code }}</p>
                                                <p class="text-xs text-gray-500">
                                                    {{ $delivery->created_at->format('d/m/Y H:i') }}</p>
                                            </div>
                                        </div>
                                        <div class="text-right">
                                            <span class="badge {{ $delivery->getStatusBadgeClass() }} text-xs">
                                                {{ ucfirst(str_replace('_', ' ', $delivery->status)) }}
                                            </span>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                            @if ($vehicle->deliveries->count() > 5)
                                <div class="mt-4 text-center">
                                    <a href="#" class="text-sm text-blue-600 hover:text-blue-800">
                                        Lihat semua ({{ $vehicle->deliveries->count() }} pengiriman)
                                    </a>
                                </div>
                            @endif
                        @else
                            <div class="text-center py-8">
                                <div class="text-gray-300 text-4xl mb-3">
                                    <i class="fas fa-box-open"></i>
                                </div>
                                <p class="text-gray-600">Belum ada riwayat pengiriman</p>
                            </div>
                        @endif
                    </div>
                </div>
            </div>

            <!-- Sidebar -->
            <div class="space-y-6">
                <!-- Statistik Cepat -->
                <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
                    <div class="border-b border-gray-200 px-5 py-4 bg-gradient-to-r from-blue-50 to-indigo-50">
                        <h3 class="font-semibold text-gray-900 flex items-center gap-2">
                            <i class="fas fa-chart-pie text-blue-600"></i>
                            Statistik Cepat
                        </h3>
                    </div>
                    <div class="p-5">
                        <div class="space-y-4">
                            <div class="flex justify-between items-center">
                                <span class="text-gray-600">Total Pengiriman</span>
                                <span class="text-2xl font-bold text-blue-600">{{ $stats['total_deliveries'] ?? 0 }}</span>
                            </div>
                            <div class="flex justify-between items-center">
                                <span class="text-gray-600">Pengiriman Selesai</span>
                                <span
                                    class="text-xl font-semibold text-green-600">{{ $stats['completed_deliveries'] ?? 0 }}</span>
                            </div>
                            <div class="flex justify-between items-center">
                                <span class="text-gray-600">Sedang Berjalan</span>
                                <span
                                    class="text-xl font-semibold text-orange-600">{{ $stats['active_delivery'] ? 1 : 0 }}</span>
                            </div>
                            <div class="pt-3 border-t border-gray-100">
                                <div class="flex justify-between items-center">
                                    <span class="text-gray-600">Terakhir Digunakan</span>
                                    <span class="font-medium text-gray-900">
                                        @if ($stats['active_delivery'])
                                            {{ $stats['active_delivery']->created_at->diffForHumans() }}
                                        @else
                                            -
                                        @endif
                                    </span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- resources/views/vehicles/show.blade.php --}}

                <!-- Quick Actions -->
                <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
                    <div class="border-b border-gray-200 px-5 py-4 bg-gradient-to-r from-blue-50 to-indigo-50">
                        <h3 class="font-semibold text-gray-900 flex items-center gap-2">
                            <i class="fas fa-bolt text-yellow-500"></i>
                            Aksi Cepat
                        </h3>
                    </div>
                    <div class="p-5">
                        <div class="space-y-3">
                            @if ($vehicle->status != 'in_use')
                                <form action="{{ route('vehicles.status', $vehicle) }}" method="POST"
                                    onsubmit="return confirm('Yakin ingin mengubah status kendaraan menjadi Sedang Digunakan?')">
                                    @csrf
                                    <input type="hidden" name="status" value="in_use">
                                    <button type="submit"
                                        class="w-full py-3 px-4 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors flex items-center justify-center gap-2">
                                        <i class="fas fa-play"></i>
                                        <span>Tandai Sedang Digunakan</span>
                                    </button>
                                </form>
                            @endif

                            @if ($vehicle->status != 'maintenance')
                                <form action="{{ route('vehicles.status', $vehicle) }}" method="POST"
                                    onsubmit="return confirm('Yakin ingin mengubah status kendaraan menjadi Servis?')">
                                    @csrf
                                    <input type="hidden" name="status" value="maintenance">
                                    <button type="submit"
                                        class="w-full py-3 px-4 bg-orange-600 text-white rounded-lg hover:bg-orange-700 transition-colors flex items-center justify-center gap-2">
                                        <i class="fas fa-tools"></i>
                                        <span>Tandai Servis</span>
                                    </button>
                                </form>
                            @endif

                            @if ($vehicle->status != 'available')
                                <form action="{{ route('vehicles.status', $vehicle) }}" method="POST"
                                    onsubmit="return confirm('Yakin ingin mengubah status kendaraan menjadi Tersedia?')">
                                    @csrf
                                    <input type="hidden" name="status" value="available">
                                    <button type="submit"
                                        class="w-full py-3 px-4 bg-green-600 text-white rounded-lg hover:bg-green-700 transition-colors flex items-center justify-center gap-2">
                                        <i class="fas fa-check-circle"></i>
                                        <span>Tandai Tersedia</span>
                                    </button>
                                </form>
                            @endif

                            @if ($vehicle->status != 'in_use')
                                <form action="{{ route('vehicles.destroy', $vehicle) }}" method="POST"
                                    onsubmit="return confirm('Yakin ingin menghapus kendaraan ini?')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit"
                                        class="w-full py-3 px-4 bg-red-600 text-white rounded-lg hover:bg-red-700 transition-colors flex items-center justify-center gap-2">
                                        <i class="fas fa-trash"></i>
                                        <span>Hapus Kendaraan</span>
                                    </button>
                                </form>
                            @endif
                        </div>
                    </div>
                </div>

                <!-- Informasi Sistem -->
                <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-5">
                    <h4 class="font-medium text-gray-700 mb-3">Informasi Sistem</h4>
                    <div class="space-y-2 text-sm">
                        <div class="flex justify-between">
                            <span class="text-gray-500">ID Kendaraan</span>
                            <span class="font-mono text-gray-900">#{{ $vehicle->id }}</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-gray-500">Ditambahkan</span>
                            <span class="text-gray-900">{{ $vehicle->created_at->format('d/m/Y H:i') }}</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-gray-500">Terakhir Update</span>
                            <span class="text-gray-900">{{ $vehicle->updated_at->format('d/m/Y H:i') }}</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Form untuk update status (hidden) -->
    <form id="statusForm" method="POST" style="display: none;">
        @csrf
        @method('PUT')
    </form>

    <style>
        .glass-effect {
            background: rgba(255, 255, 255, 0.7);
            backdrop-filter: blur(10px);
            border: 1px solid rgba(59, 130, 246, 0.1);
        }

        .animate-fade-in {
            animation: fadeIn 0.6s ease-out;
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

        .badge {
            display: inline-flex;
            align-items: center;
            padding: 0.25rem 0.75rem;
            border-radius: 9999px;
            font-size: 0.75rem;
            font-weight: 500;
            border-width: 1px;
        }
    </style>

    <script>
        function updateStatus(newStatus) {
            if (confirm(`Yakin ingin mengubah status kendaraan menjadi ${newStatus}?`)) {
                const form = document.getElementById('statusForm');
                form.action = "{{ route('vehicles.status', $vehicle) }}";

                // Tambah input status
                const statusInput = document.createElement('input');
                statusInput.type = 'hidden';
                statusInput.name = 'status';
                statusInput.value = newStatus;
                form.appendChild(statusInput);

                form.submit();
            }
        }
    </script>
@endsection
