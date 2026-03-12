@extends('layouts.app')

@section('title', 'Detail Pengiriman #' . $delivery->delivery_code)
@section('page-title', 'Detail Pengiriman')
@section('page-subtitle', 'Informasi lengkap pengiriman barang')

@section('content')
<div class="min-h-screen bg-gradient-to-br from-blue-50/50 to-purple-50/30 p-4 md:p-6">

    <!-- Header -->
    <div class="glass-effect rounded-3xl p-6 md:p-8 shadow-elegant mb-6 animate-fade-in">
        <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-4">
            <div class="flex items-center gap-4">
                <div class="relative">
                    <div class="w-16 h-16 rounded-2xl bg-gradient-to-br from-blue-500 to-indigo-600 flex items-center justify-center shadow-lg">
                        <i class="fas fa-truck text-2xl text-white"></i>
                    </div>
                    <div class="absolute -inset-1 bg-gradient-to-r from-blue-500 to-indigo-600 rounded-2xl blur-xl opacity-20"></div>
                </div>
                <div>
                    <h1 class="text-3xl font-bold text-gray-800">Detail Pengiriman</h1>
                    <p class="text-gray-600 mt-1">{{ $delivery->delivery_code }}</p>
                </div>
            </div>
            <div class="flex gap-3">
                <a href="{{ route('delivery.index') }}"
                   class="px-4 py-2 border border-gray-200 rounded-xl hover:bg-gray-50 transition-all">
                    <i class="fas fa-arrow-left mr-2"></i>Kembali
                </a>
                <a href="{{ route('delivery.print.note', $delivery) }}" target="_blank"
                   class="px-4 py-2 bg-green-600 text-white rounded-xl hover:bg-green-700 transition-all">
                    <i class="fas fa-print mr-2"></i>Cetak Surat Jalan
                </a>
            </div>
        </div>
    </div>

    <!-- Status Card -->
    <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-5 mb-6">
        <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
            <div class="flex items-center gap-3">
                <span class="text-gray-600">Status:</span>
                <span class="badge {{ $delivery->getStatusBadgeClass() }} text-sm px-3 py-1.5">
                    <i class="fas {{ $delivery->getStatusIcon() }} mr-2"></i>
                    {{ ucfirst(str_replace('_', ' ', $delivery->status)) }}
                </span>
            </div>
            <div class="flex gap-3">
                @if(in_array($delivery->status, ['pending', 'processing']))
                <button onclick="openAssignModal()"
                        class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors">
                    <i class="fas fa-user-plus mr-2"></i>Assign Kurir
                </button>
                @endif
                @if($delivery->status === 'assigned')
                <form action="{{ route('delivery.pickup', $delivery) }}" method="POST" class="inline">
                    @csrf
                    @method('POST')
                    <button type="submit" class="px-4 py-2 bg-purple-600 text-white rounded-lg hover:bg-purple-700 transition-colors">
                        <i class="fas fa-box-open mr-2"></i>Paket Diambil
                    </button>
                </form>
                @endif
                @if($delivery->status === 'picked_up')
                <form action="{{ route('delivery.start', $delivery) }}" method="POST" class="inline">
                    @csrf
                    @method('POST')
                    <button type="submit" class="px-4 py-2 bg-orange-600 text-white rounded-lg hover:bg-orange-700 transition-colors">
                        <i class="fas fa-truck mr-2"></i>Mulai Pengiriman
                    </button>
                </form>
                @endif
                @if(in_array($delivery->status, ['on_delivery', 'picked_up', 'assigned']))
                <button onclick="openCompleteModal()"
                        class="px-4 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700 transition-colors">
                    <i class="fas fa-check-circle mr-2"></i>Selesaikan
                </button>
                @endif
                @if(in_array($delivery->status, ['pending', 'processing', 'assigned']))
                <button onclick="openCancelModal()"
                        class="px-4 py-2 bg-red-600 text-white rounded-lg hover:bg-red-700 transition-colors">
                    <i class="fas fa-times-circle mr-2"></i>Batalkan
                </button>
                @endif
            </div>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Main Content -->
        <div class="lg:col-span-2 space-y-6">
            <!-- Informasi Transaksi -->
            <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
                <div class="border-b border-gray-200 px-5 py-4 bg-gradient-to-r from-blue-50 to-indigo-50">
                    <h3 class="font-semibold text-gray-900 flex items-center gap-2">
                        <i class="fas fa-receipt text-blue-600"></i>
                        Informasi Transaksi
                    </h3>
                </div>
                <div class="p-5">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <p class="text-sm text-gray-600">No. Invoice</p>
                            <p class="font-semibold text-gray-900">{{ $delivery->transaction->invoice_number }}</p>
                        </div>
                        <div>
                            <p class="text-sm text-gray-600">Tanggal Transaksi</p>
                            <p class="font-semibold text-gray-900">{{ $delivery->transaction->created_at->format('d/m/Y H:i') }}</p>
                        </div>
                        <div>
                            <p class="text-sm text-gray-600">Customer</p>
                            <p class="font-semibold text-gray-900">{{ $delivery->transaction->customer_name }}</p>
                        </div>
                        <div>
                            <p class="text-sm text-gray-600">Total Belanja</p>
                            <p class="font-semibold text-green-600">Rp {{ number_format($delivery->transaction->total_amount, 0, ',', '.') }}</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Timeline Pengiriman -->
            <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
                <div class="border-b border-gray-200 px-5 py-4 bg-gradient-to-r from-blue-50 to-indigo-50">
                    <h3 class="font-semibold text-gray-900 flex items-center gap-2">
                        <i class="fas fa-clock text-blue-600"></i>
                        Timeline Pengiriman
                    </h3>
                </div>
                <div class="p-5">
                    <div class="relative">
                        <div class="absolute left-4 top-0 bottom-0 w-0.5 bg-gray-200"></div>

                        <!-- Dibuat -->
                        <div class="flex items-start mb-6 relative">
                            <div class="w-8 h-8 rounded-full bg-green-500 flex items-center justify-center text-white z-10">
                                <i class="fas fa-check text-sm"></i>
                            </div>
                            <div class="ml-4 flex-1">
                                <h4 class="font-semibold text-gray-900">Pengiriman Dibuat</h4>
                                <p class="text-sm text-gray-600">{{ $delivery->created_at->format('d/m/Y H:i') }}</p>
                            </div>
                        </div>

                        <!-- Diproses Logistik -->
                        <div class="flex items-start mb-6 relative">
                            <div class="w-8 h-8 rounded-full {{ !in_array($delivery->status, ['pending']) ? 'bg-green-500' : 'bg-gray-300' }} flex items-center justify-center text-white z-10">
                                <i class="fas {{ !in_array($delivery->status, ['pending']) ? 'fa-check' : 'fa-clock' }} text-sm"></i>
                            </div>
                            <div class="ml-4 flex-1">
                                <h4 class="font-semibold text-gray-900">Diproses Logistik</h4>
                                @if(!in_array($delivery->status, ['pending']))
                                    <p class="text-sm text-gray-600">{{ $delivery->updated_at->format('d/m/Y H:i') }}</p>
                                @endif
                            </div>
                        </div>

                        <!-- Ditugaskan ke Kurir -->
                        <div class="flex items-start mb-6 relative">
                            <div class="w-8 h-8 rounded-full {{ $delivery->driver_id ? 'bg-green-500' : 'bg-gray-300' }} flex items-center justify-center text-white z-10">
                                <i class="fas {{ $delivery->driver_id ? 'fa-check' : 'fa-user' }} text-sm"></i>
                            </div>
                            <div class="ml-4 flex-1">
                                <h4 class="font-semibold text-gray-900">Ditugaskan ke Kurir</h4>
                                @if($delivery->driver)
                                    <p class="text-sm font-medium text-gray-900">{{ $delivery->driver->name }}</p>
                                    <p class="text-xs text-gray-500">{{ $delivery->driver->phone ?? '-' }}</p>
                                @endif
                            </div>
                        </div>

                        <!-- Paket Diambil -->
                        <div class="flex items-start mb-6 relative">
                            <div class="w-8 h-8 rounded-full {{ in_array($delivery->status, ['picked_up', 'on_delivery', 'delivered']) ? 'bg-green-500' : 'bg-gray-300' }} flex items-center justify-center text-white z-10">
                                <i class="fas {{ in_array($delivery->status, ['picked_up', 'on_delivery', 'delivered']) ? 'fa-check' : 'fa-box' }} text-sm"></i>
                            </div>
                            <div class="ml-4 flex-1">
                                <h4 class="font-semibold text-gray-900">Paket Diambil Kurir</h4>
                                @if($delivery->pickup_time)
                                    <p class="text-sm text-gray-600">{{ $delivery->pickup_time->format('d/m/Y H:i') }}</p>
                                @endif
                            </div>
                        </div>

                        <!-- Dalam Perjalanan -->
                        <div class="flex items-start mb-6 relative">
                            <div class="w-8 h-8 rounded-full {{ in_array($delivery->status, ['on_delivery', 'delivered']) ? 'bg-green-500' : 'bg-gray-300' }} flex items-center justify-center text-white z-10">
                                <i class="fas {{ in_array($delivery->status, ['on_delivery', 'delivered']) ? 'fa-check' : 'fa-truck' }} text-sm"></i>
                            </div>
                            <div class="ml-4 flex-1">
                                <h4 class="font-semibold text-gray-900">Dalam Perjalanan</h4>
                                @if($delivery->start_delivery_time)
                                    <p class="text-sm text-gray-600">{{ $delivery->start_delivery_time->format('d/m/Y H:i') }}</p>
                                @endif
                            </div>
                        </div>

                        <!-- Terkirim -->
                        <div class="flex items-start relative">
                            <div class="w-8 h-8 rounded-full {{ $delivery->status === 'delivered' ? 'bg-green-500' : 'bg-gray-300' }} flex items-center justify-center text-white z-10">
                                <i class="fas {{ $delivery->status === 'delivered' ? 'fa-check' : 'fa-flag' }} text-sm"></i>
                            </div>
                            <div class="ml-4 flex-1">
                                <h4 class="font-semibold text-gray-900">Terkirim</h4>
                                @if($delivery->delivered_at)
                                    <p class="text-sm text-gray-600">{{ $delivery->delivered_at->format('d/m/Y H:i') }}</p>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Sidebar -->
        <div class="space-y-6">
            <!-- Informasi Rute -->
            <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
                <div class="border-b border-gray-200 px-5 py-4 bg-gradient-to-r from-blue-50 to-indigo-50">
                    <h3 class="font-semibold text-gray-900 flex items-center gap-2">
                        <i class="fas fa-route text-blue-600"></i>
                        Informasi Rute
                    </h3>
                </div>
                <div class="p-5">
                    <div class="space-y-3">
                        <div>
                            <p class="text-sm text-gray-600 mb-1">Asal</p>
                            <p class="font-medium text-gray-900 bg-gray-50 p-2 rounded">{{ $delivery->origin }}</p>
                        </div>
                        <div>
                            <p class="text-sm text-gray-600 mb-1">Tujuan</p>
                            <p class="font-medium text-gray-900 bg-gray-50 p-2 rounded">{{ $delivery->destination }}</p>
                        </div>
                        @if($delivery->estimated_delivery_time)
                        <div>
                            <p class="text-sm text-gray-600 mb-1">Estimasi Tiba</p>
                            <p class="font-medium text-gray-900">{{ $delivery->estimated_delivery_time->format('d/m/Y H:i') }}</p>
                        </div>
                        @endif
                    </div>
                </div>
            </div>

            <!-- Informasi Barang -->
            <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
                <div class="border-b border-gray-200 px-5 py-4 bg-gradient-to-r from-blue-50 to-indigo-50">
                    <h3 class="font-semibold text-gray-900 flex items-center gap-2">
                        <i class="fas fa-boxes text-blue-600"></i>
                        Informasi Barang
                    </h3>
                </div>
                <div class="p-5">
                    <div class="space-y-3">
                        <div class="flex justify-between">
                            <span class="text-gray-600">Total Item</span>
                            <span class="font-semibold text-gray-900">{{ $delivery->total_items }} item</span>
                        </div>
                        @if($delivery->total_weight > 0)
                        <div class="flex justify-between">
                            <span class="text-gray-600">Total Berat</span>
                            <span class="font-medium text-gray-900">{{ $delivery->total_weight }} kg</span>
                        </div>
                        @endif
                        @if($delivery->total_volume > 0)
                        <div class="flex justify-between">
                            <span class="text-gray-600">Total Volume</span>
                            <span class="font-medium text-gray-900">{{ $delivery->total_volume }} m³</span>
                        </div>
                        @endif
                        @if($delivery->delivery_fee > 0)
                        <div class="flex justify-between pt-2 border-t">
                            <span class="text-gray-600">Biaya Kirim</span>
                            <span class="font-semibold text-green-600">Rp {{ number_format($delivery->delivery_fee, 0, ',', '.') }}</span>
                        </div>
                        @endif
                    </div>
                </div>
            </div>

            <!-- Catatan -->
            @if($delivery->notes)
            <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
                <div class="border-b border-gray-200 px-5 py-4 bg-gradient-to-r from-blue-50 to-indigo-50">
                    <h3 class="font-semibold text-gray-900 flex items-center gap-2">
                        <i class="fas fa-sticky-note text-blue-600"></i>
                        Catatan
                    </h3>
                </div>
                <div class="p-5">
                    <p class="text-gray-700 whitespace-pre-line">{{ $delivery->notes }}</p>
                </div>
            </div>
            @endif
        </div>
    </div>
</div>

<!-- Modal Assign Kurir -->
<div id="assignModal" class="fixed inset-0 bg-black bg-opacity-50 hidden items-center justify-center z-50">
    <div class="bg-white rounded-2xl max-w-md w-full p-6">
        <h3 class="text-xl font-bold text-gray-900 mb-4">Assign Kurir & Kendaraan</h3>
        <form action="{{ route('delivery.assign', $delivery) }}" method="POST">
            @csrf
            @method('POST')
            <div class="space-y-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">
                        Pilih Kurir <span class="text-red-500">*</span>
                    </label>
                    <select name="driver_id" required class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500">
                        <option value="">-- Pilih Kurir --</option>
                        @foreach($availableDrivers ?? [] as $driver)
                            <option value="{{ $driver->id }}">{{ $driver->name }} ({{ $driver->phone ?? '-' }})</option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">
                        Pilih Kendaraan <span class="text-red-500">*</span>
                    </label>
                    <select name="vehicle_id" required class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500">
                        <option value="">-- Pilih Kendaraan --</option>
                        @foreach($availableVehicles ?? [] as $vehicle)
                            <option value="{{ $vehicle->id }}">{{ $vehicle->name }} - {{ $vehicle->plate_number }} ({{ $vehicle->type }})</option>
                        @endforeach
                    </select>
                </div>

                <div class="flex gap-3 pt-4">
                    <button type="submit" class="flex-1 px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700">
                        Assign
                    </button>
                    <button type="button" onclick="closeAssignModal()" class="flex-1 px-4 py-2 border border-gray-300 rounded-lg hover:bg-gray-50">
                        Batal
                    </button>
                </div>
            </div>
        </form>
    </div>
</div>

<!-- Modal Selesaikan Pengiriman -->
<div id="completeModal" class="fixed inset-0 bg-black bg-opacity-50 hidden items-center justify-center z-50">
    <div class="bg-white rounded-2xl max-w-md w-full p-6">
        <h3 class="text-xl font-bold text-gray-900 mb-4">Selesaikan Pengiriman</h3>
        <form action="{{ route('delivery.complete', $delivery) }}" method="POST">
            @csrf
            @method('POST')
            <div class="space-y-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">
                        Tanggal Diterima <span class="text-red-500">*</span>
                    </label>
                    <input type="datetime-local" name="delivered_at" required
                           value="{{ now()->format('Y-m-d\TH:i') }}"
                           class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500">
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">
                        Catatan
                    </label>
                    <textarea name="notes" rows="3"
                              class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500"
                              placeholder="Catatan tambahan..."></textarea>
                </div>

                <div class="flex gap-3 pt-4">
                    <button type="submit" class="flex-1 px-4 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700">
                        Selesaikan
                    </button>
                    <button type="button" onclick="closeCompleteModal()" class="flex-1 px-4 py-2 border border-gray-300 rounded-lg hover:bg-gray-50">
                        Batal
                    </button>
                </div>
            </div>
        </form>
    </div>
</div>

<!-- Modal Batalkan Pengiriman -->
<div id="cancelModal" class="fixed inset-0 bg-black bg-opacity-50 hidden items-center justify-center z-50">
    <div class="bg-white rounded-2xl max-w-md w-full p-6">
        <h3 class="text-xl font-bold text-gray-900 mb-4">Batalkan Pengiriman</h3>
        <form action="{{ route('delivery.cancel', $delivery) }}" method="POST">
            @csrf
            @method('POST')
            <div class="space-y-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">
                        Alasan Pembatalan <span class="text-red-500">*</span>
                    </label>
                    <textarea name="cancellation_reason" rows="3" required
                              class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-red-500"
                              placeholder="Masukkan alasan pembatalan..."></textarea>
                </div>

                <div class="flex gap-3 pt-4">
                    <button type="submit" class="flex-1 px-4 py-2 bg-red-600 text-white rounded-lg hover:bg-red-700">
                        Batalkan
                    </button>
                    <button type="button" onclick="closeCancelModal()" class="flex-1 px-4 py-2 border border-gray-300 rounded-lg hover:bg-gray-50">
                        Tutup
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
    border-width: 1px;
}

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
</style>

<script>
function openAssignModal() {
    document.getElementById('assignModal').classList.remove('hidden');
    document.getElementById('assignModal').classList.add('flex');
}

function closeAssignModal() {
    document.getElementById('assignModal').classList.add('hidden');
    document.getElementById('assignModal').classList.remove('flex');
}

function openCompleteModal() {
    document.getElementById('completeModal').classList.remove('hidden');
    document.getElementById('completeModal').classList.add('flex');
}

function closeCompleteModal() {
    document.getElementById('completeModal').classList.add('hidden');
    document.getElementById('completeModal').classList.remove('flex');
}

function openCancelModal() {
    document.getElementById('cancelModal').classList.remove('hidden');
    document.getElementById('cancelModal').classList.add('flex');
}

function closeCancelModal() {
    document.getElementById('cancelModal').classList.add('hidden');
    document.getElementById('cancelModal').classList.remove('flex');
}

// Close modals when clicking outside
window.onclick = function(event) {
    const assignModal = document.getElementById('assignModal');
    const completeModal = document.getElementById('completeModal');
    const cancelModal = document.getElementById('cancelModal');

    if (event.target === assignModal) {
        closeAssignModal();
    }
    if (event.target === completeModal) {
        closeCompleteModal();
    }
    if (event.target === cancelModal) {
        closeCancelModal();
    }
}
</script>
@endsection
