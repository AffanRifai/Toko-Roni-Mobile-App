@extends('layouts.app')

@section('title', 'Edit Produk')
<script src="https://cdn.tailwindcss.com"></script>

@section('content')
<div class="min-h-screen bg-gray-50 py-6">
    <div class="container mx-auto px-4">
        <!-- Header -->
        <div class="mb-8">
            <div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-4">
                <div>
                    <h1 class="text-2xl md:text-3xl font-bold text-gray-900">Edit Produk</h1>
                    <p class="text-gray-600 mt-2">Perbarui informasi produk</p>
                </div>
                <a href="{{ route('products.index') }}"
                   class="flex items-center gap-2 text-blue-600 hover:text-blue-800">
                    <i class="fas fa-arrow-left"></i>
                    Kembali ke Daftar Produk
                </a>
            </div>
        </div>

        <!-- Form Card -->
        <div class="bg-white rounded-xl shadow-lg border">
            <form method="POST" action="{{ route('products.update', $product) }}" enctype="multipart/form-data" id="productForm">
                @csrf
                @method('PUT')

                <div class="p-6">
                    <!-- Basic Information -->
                    <div class="mb-8">
                        <h2 class="text-lg font-semibold text-gray-900 mb-4 border-b pb-2">
                            Informasi Dasar
                        </h2>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <!-- Product Name -->
                            <div>
                                <label for="name" class="block text-sm font-medium text-gray-700 mb-2">
                                    Nama Produk <span class="text-red-500">*</span>
                                </label>
                                <input type="text"
                                       id="name"
                                       name="name"
                                       value="{{ old('name', $product->name) }}"
                                       required
                                       class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 @error('name') border-red-500 @enderror"
                                       placeholder="Masukkan nama produk">
                                @error('name')
                                <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>

                            <!-- Product Code -->
                            <div>
                                <label for="code" class="block text-sm font-medium text-gray-700 mb-2">
                                    Kode Produk <span class="text-red-500">*</span>
                                </label>
                                <input type="text"
                                       id="code"
                                       name="code"
                                       value="{{ old('code', $product->code) }}"
                                       required
                                       class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 @error('code') border-red-500 @enderror"
                                       placeholder="Masukkan kode produk">
                                @error('code')
                                <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>
                        </div>

                        <!-- Category -->
                        <div class="mt-6">
                            <label for="category_id" class="block text-sm font-medium text-gray-700 mb-2">
                                Kategori <span class="text-red-500">*</span>
                            </label>
                            <select id="category_id"
                                    name="category_id"
                                    required
                                    class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 @error('category_id') border-red-500 @enderror">
                                <option value="">-- Pilih Kategori --</option>
                                @foreach($categories as $category)
                                <option value="{{ $category->id }}"
                                    {{ old('category_id', $product->category_id) == $category->id ? 'selected' : '' }}>
                                    {{ $category->name }}
                                </option>
                                @endforeach
                            </select>
                            @error('category_id')
                            <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Description -->
                        <div class="mt-6">
                            <label for="description" class="block text-sm font-medium text-gray-700 mb-2">
                                Deskripsi
                            </label>
                            <textarea id="description"
                                      name="description"
                                      rows="3"
                                      class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 @error('description') border-red-500 @enderror"
                                      placeholder="Deskripsi produk...">{{ old('description', $product->description) }}</textarea>
                            @error('description')
                            <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>

                    <!-- Pricing & Stock -->
                    <div class="mb-8">
                        <h2 class="text-lg font-semibold text-gray-900 mb-4 border-b pb-2">
                            Harga & Stok
                        </h2>

                        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                            <!-- Price -->
                            <div>
                                <label for="price" class="block text-sm font-medium text-gray-700 mb-2">
                                    Harga Jual <span class="text-red-500">*</span>
                                </label>
                                <div class="relative">
                                    <span class="absolute left-3 top-1/2 transform -translate-y-1/2 text-gray-500">Rp</span>
                                    <input type="text"
                                           id="price"
                                           name="price"
                                           value="{{ old('price', number_format($product->price, 0, ',', '.')) }}"
                                           required
                                           class="w-full pl-10 pr-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 @error('price') border-red-500 @enderror format-number"
                                           placeholder="0"
                                           onkeyup="formatNumber(this)">
                                </div>
                                @error('price')
                                <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>

                            <!-- Cost Price -->
                            <div>
                                <label for="cost_price" class="block text-sm font-medium text-gray-700 mb-2">
                                    Harga Modal
                                </label>
                                <div class="relative">
                                    <span class="absolute left-3 top-1/2 transform -translate-y-1/2 text-gray-500">Rp</span>
                                    <input type="text"
                                           id="cost_price"
                                           name="cost_price"
                                           value="{{ old('cost_price', number_format($product->cost_price, 0, ',', '.')) }}"
                                           min="0"
                                           class="w-full pl-10 pr-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 @error('cost_price') border-red-500 @enderror format-number"
                                           placeholder="0"
                                           onkeyup="formatNumber(this)">
                                </div>
                                @error('cost_price')
                                <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>

                            <!-- Stock -->
                            <div>
                                <label for="stock" class="block text-sm font-medium text-gray-700 mb-2">
                                    Stok <span class="text-red-500">*</span>
                                </label>
                                <input type="number"
                                       id="stock"
                                       name="stock"
                                       value="{{ old('stock', $product->stock) }}"
                                       required
                                       min="0"
                                       class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 @error('stock') border-red-500 @enderror"
                                       placeholder="0">
                                @error('stock')
                                <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mt-6">
                            <!-- Minimum Stock -->
                            <div>
                                <label for="min_stock" class="block text-sm font-medium text-gray-700 mb-2">
                                    Stok Minimum
                                </label>
                                <input type="number"
                                       id="min_stock"
                                       name="min_stock"
                                       value="{{ old('min_stock', $product->min_stock) }}"
                                       min="0"
                                       class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 @error('min_stock') border-red-500 @enderror"
                                       placeholder="10">
                                @error('min_stock')
                                <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>

                            <!-- Unit -->
                            <div>
                                <label for="unit" class="block text-sm font-medium text-gray-700 mb-2">
                                    Satuan <span class="text-red-500">*</span>
                                </label>
                                <select id="unit"
                                        name="unit"
                                        required
                                        class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 @error('unit') border-red-500 @enderror">
                                    <option value="">-- Pilih Satuan --</option>
                                    <option value="pcs" {{ old('unit', $product->unit) == 'pcs' ? 'selected' : '' }}>Pcs</option>
                                    <option value="pack" {{ old('unit', $product->unit) == 'pack' ? 'selected' : '' }}>Pack</option>
                                    <option value="dus" {{ old('unit', $product->unit) == 'dus' ? 'selected' : '' }}>Dus</option>
                                    <option value="kg" {{ old('unit', $product->unit) == 'kg' ? 'selected' : '' }}>Kg</option>
                                    <option value="liter" {{ old('unit', $product->unit) == 'liter' ? 'selected' : '' }}>Liter</option>
                                    <option value="meter" {{ old('unit', $product->unit) == 'meter' ? 'selected' : '' }}>Meter</option>
                                </select>
                                @error('unit')
                                <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>

                            <!-- Barcode -->
                            <div>
                                <label for="barcode" class="block text-sm font-medium text-gray-700 mb-2">
                                    Barcode
                                </label>
                                <input type="text"
                                       id="barcode"
                                       name="barcode"
                                       value="{{ old('barcode', $product->barcode) }}"
                                       class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 @error('barcode') border-red-500 @enderror"
                                       placeholder="Kode barcode">
                                @error('barcode')
                                <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>
                        </div>
                    </div>

                    <!-- Product Image -->
                    <div class="mb-8">
                        <h2 class="text-lg font-semibold text-gray-900 mb-4 border-b pb-2">
                            Gambar Produk
                        </h2>

                        <!-- Current Image -->
                        @if($product->image)
                        <div class="mb-4">
                            <p class="text-sm text-gray-600 mb-2">Gambar Saat Ini:</p>
                            <div class="relative inline-block">
                                <img src="{{ asset('storage/' . $product->image) }}"
                                     alt="{{ $product->name }}"
                                     class="w-40 h-40 object-cover rounded-lg border">
                            </div>
                        </div>
                        @endif

                        <!-- New Image Upload -->
                        <div>
                            <label for="image" class="block text-sm font-medium text-gray-700 mb-2">
                                Unggah Gambar Baru (Opsional)
                            </label>
                            <div class="border-2 border-dashed border-gray-300 rounded-lg p-8 text-center hover:border-blue-400 transition-colors cursor-pointer"
                                 onclick="document.getElementById('image').click()">
                                <i class="fas fa-cloud-upload-alt text-3xl text-gray-400 mb-3"></i>
                                <h4 class="font-medium text-gray-700 mb-1">Klik untuk memilih file</h4>
                                <p class="text-sm text-gray-500 mb-2">Drag & drop juga didukung</p>
                                <p class="text-xs text-gray-400">Format: JPG, PNG, GIF, WEBP (Maks. 5MB)</p>
                                <input type="file"
                                       id="image"
                                       name="image"
                                       accept="image/*"
                                       class="hidden">
                            </div>

                            <!-- Image Preview -->
                            <div id="imagePreview" class="mt-4 hidden">
                                <div class="relative inline-block">
                                    <img id="previewImage"
                                         src=""
                                         alt="Preview"
                                         class="w-40 h-40 object-cover rounded-lg border">
                                    <button type="button"
                                            onclick="removeImage()"
                                            class="absolute top-2 right-2 w-6 h-6 bg-red-500 text-white rounded-full flex items-center justify-center text-xs hover:bg-red-600">
                                        <i class="fas fa-times"></i>
                                    </button>
                                </div>
                            </div>

                            @error('image')
                            <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>

                    <!-- Additional Information -->
                    <div class="mb-8">
                        <h2 class="text-lg font-semibold text-gray-900 mb-4 border-b pb-2">
                            Informasi Tambahan
                        </h2>

                        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                            <!-- Weight -->
                            <div>
                                <label for="weight" class="block text-sm font-medium text-gray-700 mb-2">
                                    Berat (gram)
                                </label>
                                <input type="number"
                                       id="weight"
                                       name="weight"
                                       value="{{ old('weight', $product->weight) }}"
                                       min="0"
                                       step="0.01"
                                       class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 @error('weight') border-red-500 @enderror"
                                       placeholder="0">
                                @error('weight')
                                <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>

                            <!-- Dimensions -->
                            <div>
                                <label for="dimensions" class="block text-sm font-medium text-gray-700 mb-2">
                                    Dimensi (cm)
                                </label>
                                <input type="text"
                                       id="dimensions"
                                       name="dimensions"
                                       value="{{ old('dimensions', $product->dimensions) }}"
                                       class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 @error('dimensions') border-red-500 @enderror"
                                       placeholder="P x L x T">
                                @error('dimensions')
                                <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>

                            <!-- Expiry Date -->
                            <div>
                                <label for="expiry_date" class="block text-sm font-medium text-gray-700 mb-2">
                                    Tanggal Kadaluarsa
                                </label>
                                <input type="date"
                                       id="expiry_date"
                                       name="expiry_date"
                                       value="{{ old('expiry_date', $product->expiry_date ? $product->expiry_date->format('Y-m-d') : '') }}"
                                       min="{{ date('Y-m-d') }}"
                                       class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 @error('expiry_date') border-red-500 @enderror">
                                @error('expiry_date')
                                <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>
                        </div>

                        <!-- Status -->
                        <div class="mt-6">
                            <div class="flex items-center gap-3">
                                <input type="checkbox"
                                       id="is_active"
                                       name="is_active"
                                       value="1"
                                       {{ old('is_active', $product->is_active) ? 'checked' : '' }}
                                       class="w-4 h-4 text-blue-600 border-gray-300 rounded focus:ring-blue-500">
                                <label for="is_active" class="text-sm text-gray-700">
                                    Produk Aktif (ditampilkan di katalog)
                                </label>
                            </div>
                            <p class="text-xs text-gray-500 mt-1">
                                Nonaktifkan jika produk sedang tidak tersedia
                            </p>
                        </div>
                    </div>

                    <!-- Form Actions -->
                    <div class="border-t pt-6">
                        <div class="flex flex-col sm:flex-row gap-3 justify-end">
                            <a href="{{ route('products.index') }}"
                               class="px-6 py-3 border border-gray-300 rounded-lg hover:bg-gray-50 flex items-center justify-center gap-2">
                                <i class="fas fa-times"></i>
                                Batal
                            </a>
                            <button type="submit"
                                    class="px-6 py-3 bg-blue-600 text-white rounded-lg hover:bg-blue-700 flex items-center justify-center gap-2">
                                <i class="fas fa-save"></i>
                                Simpan Perubahan
                            </button>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Toast Notification -->
<div id="toast" class="fixed top-4 right-4 z-50 hidden">
    <div class="px-6 py-3 rounded-lg shadow-lg flex items-center gap-3">
        <i id="toastIcon" class="fas"></i>
        <span id="toastMessage"></span>
    </div>
</div>

<style>
/* Sembunyikan spinner di input number untuk Chrome, Safari, Edge, Opera */
input::-webkit-outer-spin-button,
input::-webkit-inner-spin-button {
    -webkit-appearance: none;
    margin: 0;
}

/* Sembunyikan spinner di input number untuk Firefox */
input[type=number] {
    -moz-appearance: textfield;
    appearance: textfield;
}

/* Style untuk input yang diformat */
input.format-number {
    text-align: right;
}

/* Animasi fade in */
@keyframes fadeIn {
    from { opacity: 0; transform: translateY(-10px); }
    to { opacity: 1; transform: translateY(0); }
}

.fade-in {
    animation: fadeIn 0.3s ease-in-out;
}
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Image preview functionality
    const imageInput = document.getElementById('image');
    if (imageInput) {
        imageInput.addEventListener('change', previewImage);
    }

    // Calculate profit on price/cost changes
    const priceInput = document.getElementById('price');
    const costInput = document.getElementById('cost_price');

    if (priceInput) {
        priceInput.addEventListener('keyup', function() {
            formatNumber(this);
            calculateProfit();
        });
    }
    
    if (costInput) {
        costInput.addEventListener('keyup', function() {
            formatNumber(this);
            calculateProfit();
        });
    }

    // Initialize profit calculation
    setTimeout(calculateProfit, 100);
    
    // Format initial values
    formatNumber(priceInput);
    formatNumber(costInput);
});

// Format angka dengan pemisah ribuan dan desimal
function formatNumber(input) {
    if (!input) return;
    
    // Simpan posisi kursor
    let cursorPos = input.selectionStart;
    let originalLength = input.value.length;
    
    // Hapus semua karakter non-digit kecuali koma
    let value = input.value.replace(/[^\d,]/g, '');
    
    // Jika ada koma, pisahkan bagian desimal
    let parts = value.split(',');
    let wholePart = parts[0];
    let decimalPart = parts[1] || '';
    
    // Batasi desimal maksimal 2 digit
    if (decimalPart.length > 2) {
        decimalPart = decimalPart.substring(0, 2);
    }
    
    // Format bagian bulat dengan pemisah ribuan
    if (wholePart) {
        wholePart = wholePart.replace(/\B(?=(\d{3})+(?!\d))/g, '.');
    }
    
    // Gabungkan kembali
    let formattedValue = decimalPart ? wholePart + ',' + decimalPart : wholePart;
    
    // Update nilai input
    input.value = formattedValue;
    
    // Sesuaikan posisi kursor
    let newLength = input.value.length;
    cursorPos = cursorPos + (newLength - originalLength);
    input.setSelectionRange(cursorPos, cursorPos);
}

// Ubah dari format tampilan ke format numerik (titik untuk desimal)
function unformatNumber(value) {
    if (!value) return '0';
    // Hapus semua titik (pemisah ribuan)
    let unformatted = value.replace(/\./g, '');
    // Ganti koma desimal dengan titik
    unformatted = unformatted.replace(',', '.');
    return unformatted;
}

// Handle form submission
document.getElementById('productForm').addEventListener('submit', function(e) {
    const priceInput = document.getElementById('price');
    const costInput = document.getElementById('cost_price');
    
    // Simpan nilai yang sudah diformat untuk ditampilkan kembali jika error
    if (priceInput) {
        priceInput.dataset.displayValue = priceInput.value;
        // Ubah ke format numerik sebelum dikirim
        priceInput.value = unformatNumber(priceInput.value);
    }
    
    if (costInput) {
        costInput.dataset.displayValue = costInput.value;
        costInput.value = unformatNumber(costInput.value);
    }
    
    // Disable tombol submit untuk mencegah double submit
    const submitBtn = this.querySelector('button[type="submit"]');
    if (submitBtn) {
        submitBtn.disabled = true;
        submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Menyimpan...';
    }
});

// Kembalikan ke format tampilan jika ada error validasi
@if($errors->any())
document.addEventListener('DOMContentLoaded', function() {
    const priceInput = document.getElementById('price');
    const costInput = document.getElementById('cost_price');
    
    if (priceInput && priceInput.dataset.displayValue) {
        priceInput.value = priceInput.dataset.displayValue;
    } else if (priceInput) {
        // Format ulang nilai old
        let oldPrice = '{{ old('price', $product->price) }}';
        priceInput.value = formatNumberDisplay(oldPrice);
    }
    
    if (costInput && costInput.dataset.displayValue) {
        costInput.value = costInput.dataset.displayValue;
    } else if (costInput) {
        let oldCost = '{{ old('cost_price', $product->cost_price) }}';
        costInput.value = formatNumberDisplay(oldCost);
    }
});
@endif

// Format angka untuk ditampilkan
function formatNumberDisplay(value) {
    if (!value) return '';
    // Jika sudah dalam format numerik (pakai titik)
    if (value.includes('.')) {
        let parts = value.split('.');
        let wholePart = parts[0].replace(/\B(?=(\d{3})+(?!\d))/g, '.');
        return parts[1] ? wholePart + ',' + parts[1] : wholePart;
    }
    return value;
}

// Image preview function
function previewImage(event) {
    const input = event.target;
    const imagePreview = document.getElementById('imagePreview');
    const previewImage = document.getElementById('previewImage');

    if (input.files && input.files[0]) {
        const file = input.files[0];

        // Validate file size (max 5MB)
        const maxSize = 5 * 1024 * 1024;
        if (file.size > maxSize) {
            showToast('Ukuran file terlalu besar. Maksimal 5MB.', 'error');
            input.value = '';
            return;
        }

        // Validate file type
        const validTypes = ['image/jpeg', 'image/jpg', 'image/png', 'image/gif', 'image/webp'];
        if (!validTypes.includes(file.type)) {
            showToast('Format file tidak didukung.', 'error');
            input.value = '';
            return;
        }

        const reader = new FileReader();

        reader.onload = function(e) {
            imagePreview.classList.remove('hidden');
            previewImage.src = e.target.result;
            previewImage.classList.add('fade-in');
        };

        reader.readAsDataURL(file);
    }
}

// Remove image
function removeImage() {
    const imageInput = document.getElementById('image');
    const imagePreview = document.getElementById('imagePreview');

    imageInput.value = '';
    imagePreview.classList.add('hidden');
}

// Calculate profit
function calculateProfit() {
    const priceInput = document.getElementById('price');
    const costInput = document.getElementById('cost_price');
    
    if (!priceInput || !costInput) return;
    
    const price = parseFloat(unformatNumber(priceInput.value)) || 0;
    const cost = parseFloat(unformatNumber(costInput.value)) || 0;

    if (price > 0 && cost > 0) {
        const profit = price - cost;
        const percentage = ((profit / cost) * 100).toFixed(1);

        // Only show toast if both values are valid numbers
        if (!isNaN(profit) && !isNaN(percentage)) {
            showToast(`Keuntungan: Rp ${profit.toLocaleString('id-ID')} (${percentage}%)`, 'info');
        }
    }
}

// Show toast notification
function showToast(message, type = 'info') {
    const toast = document.getElementById('toast');
    const toastIcon = document.getElementById('toastIcon');
    const toastMessage = document.getElementById('toastMessage');

    if (!toast || !toastIcon || !toastMessage) return;

    // Hide any existing timeout
    if (window.toastTimeout) {
        clearTimeout(window.toastTimeout);
    }

    // Set icon and color based on type
    const types = {
        success: { icon: 'fa-check-circle', color: 'bg-green-100 text-green-800 border-green-200' },
        error: { icon: 'fa-exclamation-circle', color: 'bg-red-100 text-red-800 border-red-200' },
        info: { icon: 'fa-info-circle', color: 'bg-blue-100 text-blue-800 border-blue-200' },
        warning: { icon: 'fa-exclamation-triangle', color: 'bg-yellow-100 text-yellow-800 border-yellow-200' }
    };

    const config = types[type] || types.info;

    toastIcon.className = `fas ${config.icon}`;
    toastMessage.textContent = message;
    toast.className = `fixed top-4 right-4 z-50 px-6 py-3 rounded-lg shadow-lg flex items-center gap-3 border ${config.color} fade-in`;
    toast.classList.remove('hidden');

    // Auto hide after 3 seconds
    window.toastTimeout = setTimeout(() => {
        toast.classList.add('hidden');
    }, 3000);
}

// Prevent enter key from submitting form unexpectedly
document.addEventListener('keypress', function(e) {
    if (e.key === 'Enter' && e.target.tagName !== 'TEXTAREA') {
        e.preventDefault();
    }
});
</script>
@endsection