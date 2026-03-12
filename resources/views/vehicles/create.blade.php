@extends('layouts.app')

@section('title', 'Tambah Kendaraan Baru')
@section('page-title', 'Tambah Kendaraan Baru')
@section('page-subtitle', 'Form penambahan kendaraan untuk pengiriman')

@section('content')
<div class="min-h-screen bg-gradient-to-br from-blue-50/50 to-purple-50/30 p-4 md:p-6">

    <!-- Header -->
    <div class="glass-effect rounded-3xl p-6 md:p-8 shadow-elegant mb-6 animate-fade-in">
        <div class="flex items-center gap-4">
            <div class="relative">
                <div class="w-16 h-16 rounded-2xl bg-gradient-to-br from-green-500 to-emerald-600 flex items-center justify-center shadow-lg">
                    <i class="fas fa-plus-circle text-2xl text-white"></i>
                </div>
                <div class="absolute -inset-1 bg-gradient-to-r from-green-500 to-emerald-600 rounded-2xl blur-xl opacity-20"></div>
            </div>
            <div>
                <h1 class="text-3xl font-bold text-gray-800">Tambah Kendaraan Baru</h1>
                <p class="text-gray-600 mt-1">Lengkapi form berikut untuk menambahkan kendaraan baru</p>
            </div>
        </div>
    </div>

    <!-- Form -->
    <div class="glass-effect rounded-2xl p-6 max-w-3xl mx-auto">
        <form action="{{ route('vehicles.store') }}" method="POST" id="vehicleForm" class="space-y-6">
            @csrf

            <!-- Informasi Dasar -->
            <div class="bg-white/50 rounded-xl p-5 border border-gray-200">
                <h3 class="font-semibold text-gray-900 mb-4 flex items-center gap-2">
                    <i class="fas fa-info-circle text-green-600"></i>
                    Informasi Dasar
                </h3>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <!-- Nama Kendaraan -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">
                            Nama Kendaraan <span class="text-red-500">*</span>
                        </label>
                        <div class="relative">
                            <i class="fas fa-truck absolute left-3 top-1/2 transform -translate-y-1/2 text-gray-400"></i>
                            <input type="text" name="name" id="name" value="{{ old('name') }}" required
                                class="w-full pl-10 pr-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-green-500 transition-colors @error('name') border-red-500 @enderror"
                                placeholder="Contoh: Toyota Avanza, Honda Vario">
                        </div>
                        @error('name')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Plat Nomor (license_plate) -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">
                            Plat Nomor <span class="text-red-500">*</span>
                        </label>
                        <div class="relative">
                            <i class="fas fa-id-card absolute left-3 top-1/2 transform -translate-y-1/2 text-gray-400"></i>
                            <input type="text" name="license_plate" id="license_plate" value="{{ old('license_plate') }}" required
                                class="w-full pl-10 pr-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-green-500 transition-colors uppercase @error('license_plate') border-red-500 @enderror"
                                placeholder="Contoh: B 1234 XYZ">
                        </div>
                        @error('license_plate')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Jenis Kendaraan -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">
                            Jenis Kendaraan <span class="text-red-500">*</span>
                        </label>
                        <div class="relative">
                            <i class="fas fa-motorcycle absolute left-3 top-1/2 transform -translate-y-1/2 text-gray-400"></i>
                            <select name="type" id="type" required
                                class="w-full pl-10 pr-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-green-500 appearance-none @error('type') border-red-500 @enderror">
                                <option value="">-- Pilih Jenis Kendaraan --</option>
                                <option value="motor" {{ old('type') == 'motor' ? 'selected' : '' }}>Motor</option>
                                <option value="mobil" {{ old('type') == 'mobil' ? 'selected' : '' }}>Mobil</option>
                                <option value="truck" {{ old('type') == 'truck' ? 'selected' : '' }}>Truck</option>
                            </select>
                            <i class="fas fa-chevron-down absolute right-3 top-1/2 transform -translate-y-1/2 text-gray-400"></i>
                        </div>
                        @error('type')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Status -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">
                            Status <span class="text-red-500">*</span>
                        </label>
                        <div class="relative">
                            <i class="fas fa-circle absolute left-3 top-1/2 transform -translate-y-1/2 text-gray-400"></i>
                            <select name="status" id="status" required
                                class="w-full pl-10 pr-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-green-500 appearance-none @error('status') border-red-500 @enderror">
                                <option value="available" {{ old('status') == 'available' ? 'selected' : '' }}>Tersedia</option>
                                <option value="in_use" {{ old('status') == 'in_use' ? 'selected' : '' }}>Sedang Digunakan</option>
                                <option value="maintenance" {{ old('status') == 'maintenance' ? 'selected' : '' }}>Servis/Maintenance</option>
                            </select>
                            <i class="fas fa-chevron-down absolute right-3 top-1/2 transform -translate-y-1/2 text-gray-400"></i>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Kapasitas -->
            <div class="bg-white/50 rounded-xl p-5 border border-gray-200">
                <h3 class="font-semibold text-gray-900 mb-4 flex items-center gap-2">
                    <i class="fas fa-weight text-orange-600"></i>
                    Kapasitas
                </h3>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <!-- Kapasitas Berat -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">
                            Kapasitas Berat (kg)
                        </label>
                        <div class="relative">
                            <i class="fas fa-weight absolute left-3 top-1/2 transform -translate-y-1/2 text-gray-400"></i>
                            <input type="number" name="capacity_weight" id="capacity_weight" value="{{ old('capacity_weight') }}"
                                min="0" step="0.1"
                                class="w-full pl-10 pr-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-green-500 transition-colors"
                                placeholder="Contoh: 1000">
                        </div>
                    </div>

                    <!-- Kapasitas Volume -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">
                            Kapasitas Volume (m³)
                        </label>
                        <div class="relative">
                            <i class="fas fa-cube absolute left-3 top-1/2 transform -translate-y-1/2 text-gray-400"></i>
                            <input type="number" name="capacity_volume" id="capacity_volume" value="{{ old('capacity_volume') }}"
                                min="0" step="0.1"
                                class="w-full pl-10 pr-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-green-500 transition-colors"
                                placeholder="Contoh: 5.5">
                        </div>
                    </div>
                </div>
            </div>

            <!-- Informasi Tambahan -->
            <div class="bg-white/50 rounded-xl p-5 border border-gray-200">
                <h3 class="font-semibold text-gray-900 mb-4 flex items-center gap-2">
                    <i class="fas fa-calendar-alt text-purple-600"></i>
                    Informasi Tambahan
                </h3>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <!-- Last Maintenance -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">
                            Terakhir Maintenance
                        </label>
                        <div class="relative">
                            <i class="fas fa-calendar-alt absolute left-3 top-1/2 transform -translate-y-1/2 text-gray-400"></i>
                            <input type="date" name="last_maintenance" id="last_maintenance" value="{{ old('last_maintenance') }}"
                                class="w-full pl-10 pr-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-green-500 transition-colors">
                        </div>
                        <p class="text-xs text-gray-500 mt-1">Tanggal terakhir kendaraan diservis</p>
                    </div>

                    <!-- Catatan -->
                    <div class="md:col-span-2">
                        <label class="block text-sm font-medium text-gray-700 mb-2">
                            Catatan
                        </label>
                        <textarea name="notes" id="notes" rows="3"
                            class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-green-500 transition-colors"
                            placeholder="Catatan tambahan tentang kendaraan (kondisi, kelengkapan, dll)">{{ old('notes') }}</textarea>
                    </div>
                </div>
            </div>

            <!-- Tombol Aksi -->
            <div class="flex gap-3 pt-4">
                <button type="submit"
                    class="flex-1 px-6 py-3 bg-gradient-to-r from-green-600 to-emerald-600 text-white font-semibold rounded-xl hover:from-green-700 hover:to-emerald-700 transition-all shadow-md hover:shadow-lg flex items-center justify-center gap-2">
                    <i class="fas fa-save"></i>
                    Simpan Kendaraan
                </button>
                <a href="{{ route('vehicles.index') }}"
                    class="flex-1 px-6 py-3 border border-gray-300 text-gray-700 font-semibold rounded-xl hover:bg-gray-50 transition-all flex items-center justify-center gap-2">
                    <i class="fas fa-arrow-left"></i>
                    Kembali
                </a>
            </div>
        </form>
    </div>
</div>

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
</style>

<script>
// Validasi form sebelum submit
document.getElementById('vehicleForm')?.addEventListener('submit', function(e) {
    const name = document.getElementById('name').value.trim();
    const licensePlate = document.getElementById('license_plate').value.trim();
    const type = document.getElementById('type').value;

    if (!name) {
        e.preventDefault();
        alert('Nama kendaraan harus diisi');
        document.getElementById('name').focus();
        return false;
    }

    if (!licensePlate) {
        e.preventDefault();
        alert('Plat nomor harus diisi');
        document.getElementById('license_plate').focus();
        return false;
    }

    if (!type) {
        e.preventDefault();
        alert('Jenis kendaraan harus dipilih');
        document.getElementById('type').focus();
        return false;
    }

    // Format plat nomor menjadi uppercase
    document.getElementById('license_plate').value = licensePlate.toUpperCase();
});

// Auto uppercase untuk plat nomor
document.getElementById('license_plate')?.addEventListener('input', function(e) {
    this.value = this.value.toUpperCase();
});

// Konfirmasi sebelum meninggalkan halaman jika ada perubahan
let formChanged = false;
document.querySelectorAll('#vehicleForm input, #vehicleForm select, #vehicleForm textarea').forEach(element => {
    element.addEventListener('change', () => formChanged = true);
});

window.addEventListener('beforeunload', function(e) {
    if (formChanged) {
        e.preventDefault();
        e.returnValue = 'Anda memiliki perubahan yang belum disimpan. Yakin ingin meninggalkan halaman ini?';
    }
});
</script>
@endsection
