{{-- resources/views/members/create.blade.php --}}
@extends('layouts.app')

@section('title', 'Tambah Member')
@section('page-title', 'Tambah Member Baru')
@section('page-subtitle', 'Isi data member dengan lengkap')

@section('content')
    <div class="min-h-screen bg-gradient-to-br from-blue-50/50 to-purple-50/30 p-4 md:p-6">
        <div class="glass-effect rounded-3xl p-6 max-w-3xl mx-auto">
            <form action="{{ route('members.store') }}" method="POST" class="space-y-6">
                @csrf

                {{-- Nama Member --}}
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">
                        Nama Lengkap <span class="text-red-500">*</span>
                    </label>
                    <input type="text" name="nama" value="{{ old('nama') }}" required
                        class="w-full px-4 py-3 border border-gray-200 rounded-xl focus:ring-2 focus:ring-blue-500 @error('nama') border-red-500 @enderror"
                        placeholder="Masukkan nama lengkap">
                    @error('nama')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                {{-- Kontak --}}
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">
                            Email
                        </label>
                        <input type="email" name="email" value="{{ old('email') }}"
                            class="w-full px-4 py-3 border border-gray-200 rounded-xl focus:ring-2 focus:ring-blue-500"
                            placeholder="email@example.com">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">
                            No. Telepon
                        </label>
                        <input type="text" name="no_telepon" value="{{ old('no_telepon') }}"
                            class="w-full px-4 py-3 border border-gray-200 rounded-xl focus:ring-2 focus:ring-blue-500"
                            placeholder="08123456789">
                    </div>
                </div>

                {{-- Alamat --}}
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">
                        Alamat
                    </label>
                    <textarea name="alamat" rows="3"
                        class="w-full px-4 py-3 border border-gray-200 rounded-xl focus:ring-2 focus:ring-blue-500"
                        placeholder="Alamat lengkap">{{ old('alamat') }}</textarea>
                </div>

                {{-- Tipe Member dan Limit --}}
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">
                            Tipe Member <span class="text-red-500">*</span>
                        </label>
                        <select name="tipe_member" required
                            class="w-full px-4 py-3 border border-gray-200 rounded-xl focus:ring-2 focus:ring-blue-500">
                            <option value="biasa" {{ old('tipe_member') == 'biasa' ? 'selected' : '' }}>Biasa</option>
                            <option value="gold" {{ old('tipe_member') == 'gold' ? 'selected' : '' }}>Gold</option>
                            <option value="platinum" {{ old('tipe_member') == 'platinum' ? 'selected' : '' }}>Platinum
                            </option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">
                            Limit Kredit (Rp) <span class="text-red-500">*</span>
                        </label>
                        <input type="number" name="limit_kredit" value="{{ old('limit_kredit', 0) }}" min="0"
                            required
                            class="w-full px-4 py-3 border border-gray-200 rounded-xl focus:ring-2 focus:ring-blue-500">
                    </div>
                </div>

                {{-- Status Aktif --}}
                <div class="flex items-center gap-2">
                    <input type="checkbox" name="is_active" id="is_active" value="1"
                        {{ old('is_active', true) ? 'checked' : '' }}
                        class="w-4 h-4 text-blue-600 border-gray-300 rounded focus:ring-blue-500">
                    <label for="is_active" class="text-sm font-medium text-gray-700">
                        Aktifkan member
                    </label>
                </div>

                {{-- Tombol Submit --}}
                <div class="flex gap-3 pt-4 border-t border-gray-200">
                    <button type="submit"
                        class="flex-1 px-6 py-3 bg-gradient-to-r from-blue-500 to-purple-600 text-white rounded-xl hover:shadow-lg transition-all">
                        <i class="fas fa-save mr-2"></i>
                        Simpan Member
                    </button>
                    <a href="{{ route('members.index') }}"
                        class="px-6 py-3 border border-gray-200 rounded-xl hover:bg-gray-50 transition-all">
                        Batal
                    </a>
                </div>
            </form>
        </div>
    </div>
@endsection
