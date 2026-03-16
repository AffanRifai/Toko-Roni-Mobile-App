<?php $__env->startSection('title', 'Tambah Produk Baru'); ?>
<script src="https://cdn.tailwindcss.com"></script>

<?php $__env->startSection('content'); ?>
<div class="min-h-screen bg-gray-50">
    <!-- Header -->
    <div class="bg-gradient-to-r from-blue-600 to-purple-600">
        <div class="container mx-auto px-4 py-4">
            <div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-3">
                <div class="flex items-center gap-3">
                    <div class="w-8 h-8 bg-white/20 rounded-lg flex items-center justify-center">
                        <i class="fas fa-plus-circle text-white"></i>
                    </div>
                    <div>
                        <h1 class="text-xl font-bold text-white">Tambah Produk Baru</h1>
                        <p class="text-white/90 text-xs mt-1">Isi informasi produk di bawah ini</p>
                    </div>
                </div>
                <a href="<?php echo e(route('products.index')); ?>"
                   class="flex items-center gap-2 bg-white/20 hover:bg-white/30 text-white px-3 py-1.5 rounded-lg transition-colors text-sm">
                    <i class="fas fa-arrow-left"></i>
                    Kembali
                </a>
            </div>
        </div>
    </div>

    <!-- Main Content -->
    <div class="container mx-auto px-4 py-4">
        <?php if($errors->any()): ?>
        <div class="mb-4 bg-red-50 border border-red-200 rounded-lg p-3">
            <div class="flex items-start gap-2">
                <i class="fas fa-exclamation-circle text-red-500 mt-0.5 text-sm"></i>
                <div class="flex-1">
                    <h4 class="font-semibold text-red-800 text-sm mb-1">Error Validasi:</h4>
                    <ul class="text-xs text-red-700 space-y-0.5">
                        <?php $__currentLoopData = $errors->all(); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $error): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <li><?php echo e($error); ?></li>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    </ul>
                </div>
            </div>
        </div>
        <?php endif; ?>

        <div class="flex flex-col lg:flex-row gap-4">
            <!-- Form -->
            <div class="lg:w-2/3">
                <div class="bg-white rounded-lg shadow border">
                    <form method="POST" action="<?php echo e(route('products.store')); ?>" enctype="multipart/form-data" class="p-4">
                        <?php echo csrf_field(); ?>

                        <!-- Basic Information -->
                        <div class="mb-6">
                            <h3 class="font-semibold text-gray-900 mb-3 text-sm">Informasi Dasar</h3>

                            <div class="grid grid-cols-1 md:grid-cols-2 gap-3 mb-3">
                                <!-- Product Name -->
                                <div>
                                    <label for="name" class="block text-xs font-medium text-gray-700 mb-1">
                                        Nama Produk *
                                    </label>
                                    <input type="text"
                                           id="name"
                                           name="name"
                                           value="<?php echo e(old('name')); ?>"
                                           required
                                           class="w-full px-3 py-2 text-sm border rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                                           placeholder="Contoh: Aqua 600ml">
                                </div>

                                <!-- Product Code -->
                                <div>
                                    <label for="code" class="block text-xs font-medium text-gray-700 mb-1">
                                        Kode Produk *
                                    </label>
                                    <div class="relative">
                                        <input type="text"
                                               id="code"
                                               name="code"
                                               value="<?php echo e(old('code')); ?>"
                                               required
                                               class="w-full px-3 py-2 pr-8 text-sm border rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                                               placeholder="PRD-001">
                                        <button type="button"
                                                onclick="generateProductCode()"
                                                class="absolute right-2 top-1/2 transform -translate-y-1/2 text-gray-400 hover:text-gray-600">
                                            <i class="fas fa-sync-alt text-xs"></i>
                                        </button>
                                    </div>
                                </div>
                            </div>

                            <!-- Category -->
                            <div class="mb-3">
                                <label for="category_id" class="block text-xs font-medium text-gray-700 mb-1">
                                    Kategori *
                                </label>
                                <select id="category_id"
                                        name="category_id"
                                        required
                                        class="w-full px-3 py-2 text-sm border rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                                    <option value="">-- Pilih Kategori --</option>
                                    <?php $__currentLoopData = $categories; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $category): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                    <option value="<?php echo e($category->id); ?>" <?php echo e(old('category_id') == $category->id ? 'selected' : ''); ?>>
                                        <?php echo e($category->name); ?>

                                    </option>
                                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                </select>
                            </div>

                            <!-- Description -->
                            <div>
                                <label for="description" class="block text-xs font-medium text-gray-700 mb-1">
                                    Deskripsi Produk
                                </label>
                                <textarea id="description"
                                          name="description"
                                          rows="2"
                                          class="w-full px-3 py-2 text-sm border rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                                          placeholder="Deskripsi lengkap tentang produk..."><?php echo e(old('description')); ?></textarea>
                                <div class="flex justify-between items-center mt-1">
                                    <span id="charCount" class="text-xs text-gray-500">0/500</span>
                                </div>
                            </div>
                        </div>

                        <!-- Pricing & Stock -->
                        <div class="mb-6">
                            <h3 class="font-semibold text-gray-900 mb-3 text-sm">Harga & Stok</h3>

                            <div class="grid grid-cols-1 md:grid-cols-3 gap-3 mb-3">
                                <!-- Price -->
                                <div>
                                    <label for="price" class="block text-xs font-medium text-gray-700 mb-1">
                                        Harga Jual *
                                    </label>
                                    <div class="relative">
                                        <span class="absolute left-2 top-1/2 transform -translate-y-1/2 text-gray-500 text-xs">Rp</span>
                                        <input type="number"
                                               id="price"
                                               name="price"
                                               value="<?php echo e(old('price')); ?>"
                                               required
                                               min="0"
                                               step="100"
                                               class="w-full pl-8 pr-3 py-2 text-sm border rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                                               placeholder="0">
                                    </div>
                                </div>

                                <!-- Cost Price -->
                                <div>
                                    <label for="cost_price" class="block text-xs font-medium text-gray-700 mb-1">
                                        Harga Modal
                                    </label>
                                    <div class="relative">
                                        <span class="absolute left-2 top-1/2 transform -translate-y-1/2 text-gray-500 text-xs">Rp</span>
                                        <input type="number"
                                               id="cost_price"
                                               name="cost_price"
                                               value="<?php echo e(old('cost_price')); ?>"
                                               min="0"
                                               step="100"
                                               class="w-full pl-8 pr-3 py-2 text-sm border rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                                               placeholder="0">
                                    </div>
                                </div>

                                <!-- Stock -->
                                <div>
                                    <label for="stock" class="block text-xs font-medium text-gray-700 mb-1">
                                        Stok Awal *
                                    </label>
                                    <input type="number"
                                           id="stock"
                                           name="stock"
                                           value="<?php echo e(old('stock', 0)); ?>"
                                           required
                                           min="0"
                                           class="w-full px-3 py-2 text-sm border rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                                           placeholder="0">
                                </div>
                            </div>

                            <div class="grid grid-cols-1 md:grid-cols-3 gap-3">
                                <!-- Minimum Stock -->
                                <div>
                                    <label for="min_stock" class="block text-xs font-medium text-gray-700 mb-1">
                                        Stok Minimum
                                    </label>
                                    <input type="number"
                                           id="min_stock"
                                           name="min_stock"
                                           value="<?php echo e(old('min_stock', 10)); ?>"
                                           min="0"
                                           class="w-full px-3 py-2 text-sm border rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                                           placeholder="10">
                                </div>

                                <!-- Unit -->
                                <div>
                                    <label for="unit" class="block text-xs font-medium text-gray-700 mb-1">
                                        Satuan
                                    </label>
                                    <select id="unit"
                                            name="unit"
                                            class="w-full px-3 py-2 text-sm border rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                                        <option value="">-- Pilih --</option>
                                        <option value="pcs" <?php echo e(old('unit') == 'pcs' ? 'selected' : ''); ?>>Pcs</option>
                                        <option value="pack" <?php echo e(old('unit') == 'pack' ? 'selected' : ''); ?>>Pack</option>
                                        <option value="dus" <?php echo e(old('unit') == 'dus' ? 'selected' : ''); ?>>Dus</option>
                                        <option value="kg" <?php echo e(old('unit') == 'kg' ? 'selected' : ''); ?>>Kg</option>
                                        <option value="liter" <?php echo e(old('unit') == 'liter' ? 'selected' : ''); ?>>Liter</option>
                                        <option value="meter" <?php echo e(old('unit') == 'meter' ? 'selected' : ''); ?>>Meter</option>
                                    </select>
                                </div>

                                <!-- Barcode -->
                                <div>
                                    <label for="barcode" class="block text-xs font-medium text-gray-700 mb-1">
                                        Barcode
                                    </label>
                                    <div class="relative">
                                        <input type="text"
                                               id="barcode"
                                               name="barcode"
                                               value="<?php echo e(old('barcode')); ?>"
                                               class="w-full px-3 py-2 text-sm border rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 pr-8"
                                               placeholder="Kode barcode">
                                        <button type="button"
                                                onclick="generateBarcode()"
                                                class="absolute right-2 top-1/2 transform -translate-y-1/2 text-gray-400 hover:text-gray-600">
                                            <i class="fas fa-barcode text-xs"></i>
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Product Image -->
                        <div class="mb-6">
                            <h3 class="font-semibold text-gray-900 mb-3 text-sm">Gambar Produk</h3>

                            <div class="border-2 border-dashed border-gray-300 rounded-lg p-4 text-center hover:border-blue-400 transition-colors cursor-pointer"
                                 onclick="document.getElementById('image').click()"
                                 id="uploadArea">
                                <i class="fas fa-cloud-upload-alt text-2xl text-gray-400 mb-2"></i>
                                <h4 class="font-medium text-gray-700 text-sm mb-1">Upload Gambar Produk</h4>
                                <p class="text-xs text-gray-500">Klik untuk memilih file</p>
                                <p class="text-xs text-gray-400 mt-1">Maks. 2MB (JPG, PNG)</p>
                                <input type="file"
                                       id="image"
                                       name="image"
                                       accept="image/*"
                                       class="hidden"
                                       onchange="previewImage(this)">
                            </div>

                            <div id="imagePreview" class="mt-3 hidden">
                                <div class="relative inline-block">
                                    <img id="previewImage"
                                         src=""
                                         alt="Preview"
                                         class="w-32 h-32 object-cover rounded-lg border">
                                    <button type="button"
                                            onclick="removeImage()"
                                            class="absolute top-1 right-1 w-5 h-5 bg-red-500 text-white rounded-full flex items-center justify-center text-xs hover:bg-red-600">
                                        <i class="fas fa-times"></i>
                                    </button>
                                </div>
                            </div>
                        </div>

                        <!-- Additional Information -->
                        <div class="mb-6">
                            <h3 class="font-semibold text-gray-900 mb-3 text-sm">Informasi Tambahan</h3>

                            <div class="grid grid-cols-1 md:grid-cols-3 gap-3 mb-3">
                                <!-- Weight -->
                                <div>
                                    <label for="weight" class="block text-xs font-medium text-gray-700 mb-1">
                                        Berat (gram)
                                    </label>
                                    <input type="number"
                                           id="weight"
                                           name="weight"
                                           value="<?php echo e(old('weight')); ?>"
                                           min="0"
                                           step="0.01"
                                           class="w-full px-3 py-2 text-sm border rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                                           placeholder="0">
                                </div>

                                <!-- Dimensions -->
                                <div>
                                    <label for="dimensions" class="block text-xs font-medium text-gray-700 mb-1">
                                        Dimensi (cm)
                                    </label>
                                    <input type="text"
                                           id="dimensions"
                                           name="dimensions"
                                           value="<?php echo e(old('dimensions')); ?>"
                                           class="w-full px-3 py-2 text-sm border rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                                           placeholder="P x L x T">
                                </div>

                                <!-- Expiry Date -->
                                <div>
                                    <label for="expiry_date" class="block text-xs font-medium text-gray-700 mb-1">
                                        Tanggal Kadaluarsa
                                    </label>
                                    <input type="date"
                                           id="expiry_date"
                                           name="expiry_date"
                                           value="<?php echo e(old('expiry_date')); ?>"
                                           min="<?php echo e(date('Y-m-d')); ?>"
                                           class="w-full px-3 py-2 text-sm border rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                                </div>
                            </div>

                            <!-- Status -->
                            <div class="flex items-center gap-2">
                                <input type="checkbox"
                                       id="is_active"
                                       name="is_active"
                                       value="1"
                                       <?php echo e(old('is_active', true) ? 'checked' : ''); ?>

                                       class="w-4 h-4 text-blue-600 border-gray-300 rounded">
                                <label for="is_active" class="text-xs text-gray-700">
                                    Produk Aktif
                                </label>
                            </div>
                        </div>

                        <!-- Form Actions -->
                        <div class="border-t pt-4">
                            <div class="flex flex-col sm:flex-row gap-2 justify-end">
                                <button type="button"
                                        onclick="resetForm()"
                                        class="px-4 py-2 border border-gray-300 rounded-lg hover:bg-gray-50 flex items-center justify-center gap-2 text-sm">
                                    <i class="fas fa-redo text-xs"></i>
                                    Reset
                                </button>
                                <button type="submit"
                                        class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 flex items-center justify-center gap-2 text-sm">
                                    <i class="fas fa-save text-xs"></i>
                                    Simpan Produk
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Preview Sidebar -->
            <div class="lg:w-1/3">
                <!-- Product Preview -->
                <div class="bg-white rounded-lg shadow border mb-4">
                    <div class="border-b p-3">
                        <h3 class="font-semibold text-gray-900 flex items-center gap-2 text-sm">
                            <i class="fas fa-eye text-gray-400 text-xs"></i>
                            Preview Produk
                        </h3>
                    </div>
                    <div class="p-3">
                        <!-- Image Preview -->
                        <div class="mb-3">
                            <img id="previewImageDisplay"
                                 src="data:image/svg+xml;base64,PHN2ZyB3aWR0aD0iMzAwIiBoZWlnaHQ9IjMwMCIgdmlld0JveD0iMCAwIDMwMCAzMDAiIGZpbGw9Im5vbmUiIHhtbG5zPSJodHRwOi8vd3d3LnczLm9yZy8yMDAwL3N2ZyI+PHJlY3Qgd2lkdGg9IjMwMCIgaGVpZ2h0PSIzMDAiIGZpbGw9IiNGNUY1RjUiLz48dGV4dCB4PSI1MCUiIHk9IjUwJSIgZm9udC1mYW1pbHk9IkFyaWFsIiBmb250LXNpemU9IjE0IiBmaWxsPSIjOTk5IiB0ZXh0LWFuY2hvcj0ibWlkZGxlIiBkeT0iLjNlbSI+Tm8gSW1hZ2U8L3RleHQ+PC9zdmc+"
                                 alt="Preview"
                                 class="w-full h-40 object-cover rounded-lg border">
                        </div>

                        <!-- Product Info -->
                        <div class="space-y-1.5 mb-3">
                            <h4 id="previewName" class="font-semibold text-gray-900 text-base">Nama Produk</h4>
                            <div id="previewCode" class="text-xs text-gray-500">Kode: -</div>
                            <div id="previewCategory" class="text-xs text-gray-500">Kategori: -</div>
                            <div id="previewPrice" class="text-base font-bold text-green-600">Rp 0</div>
                            <div id="previewStock" class="text-xs text-gray-500">Stok: 0</div>
                        </div>

                        <!-- Description -->
                        <div>
                            <h5 class="font-medium text-gray-700 mb-1 text-xs">Deskripsi:</h5>
                            <p id="previewDescription" class="text-xs text-gray-600">Belum ada deskripsi</p>
                        </div>
                    </div>
                </div>

                <!-- Form Status -->
                <div class="bg-white rounded-lg shadow border">
                    <div class="border-b p-3">
                        <h3 class="font-semibold text-gray-900 flex items-center gap-2 text-sm">
                            <i class="fas fa-check-circle text-gray-400 text-xs"></i>
                            Status Form
                        </h3>
                    </div>
                    <div class="p-3">
                        <div class="space-y-2">
                            <?php
                                $fields = ['Nama Produk', 'Kode Produk', 'Kategori', 'Harga Jual', 'Stok Awal'];
                            ?>
                            <?php $__currentLoopData = $fields; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $field): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <div class="flex items-center justify-between">
                                <span class="text-xs text-gray-600"><?php echo e($field); ?></span>
                                <i class="fas fa-circle text-gray-300 text-xs" id="status<?php echo e($loop->index); ?>"></i>
                            </div>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                        </div>
                        <div id="validationStatus" class="mt-3 text-center text-xs font-medium text-gray-600">
                            0 dari <?php echo e(count($fields)); ?> terisi
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Character counter
    const descriptionTextarea = document.getElementById('description');
    const charCount = document.getElementById('charCount');

    if (descriptionTextarea && charCount) {
        descriptionTextarea.addEventListener('input', function() {
            const length = this.value.length;
            charCount.textContent = `${length}/500`;
            if (length > 500) {
                charCount.classList.add('text-red-500');
            } else {
                charCount.classList.remove('text-red-500');
            }
            updatePreview('description', this.value);
        });
        charCount.textContent = `${descriptionTextarea.value.length}/500`;
    }

    // Setup preview
    setupLivePreview();

    // Auto-generate code
    generateProductCode();
});

function generateProductCode() {
    const timestamp = Date.now().toString().slice(-6);
    const random = Math.floor(Math.random() * 100).toString().padStart(2, '0');
    const code = `PRD-${timestamp}${random}`;

    const codeInput = document.getElementById('code');
    if (codeInput && !codeInput.value) {
        codeInput.value = code;
        updatePreview('code', code);
    }
}

function generateBarcode() {
    const barcode = Date.now().toString().slice(-12);
    document.getElementById('barcode').value = barcode;
}

function previewImage(input) {
    const uploadArea = document.getElementById('uploadArea');
    const imagePreview = document.getElementById('imagePreview');
    const previewImage = document.getElementById('previewImage');
    const previewImageDisplay = document.getElementById('previewImageDisplay');

    if (input.files && input.files[0]) {
        const file = input.files[0];
        const maxSize = 2 * 1024 * 1024; // 2MB

        if (file.size > maxSize) {
            alert('Ukuran file terlalu besar. Maksimal 2MB.');
            input.value = '';
            return;
        }

        const reader = new FileReader();
        reader.onload = function(e) {
            // Sembunyikan area upload
            uploadArea.style.display = 'none';

            // Tampilkan preview di form
            imagePreview.classList.remove('hidden');

            // Set sumber gambar untuk kedua preview
            previewImage.src = e.target.result;
            previewImageDisplay.src = e.target.result;
        };
        reader.readAsDataURL(file);
    }
}

function removeImage() {
    const uploadArea = document.getElementById('uploadArea');
    const imagePreview = document.getElementById('imagePreview');
    const previewImage = document.getElementById('previewImage');
    const previewImageDisplay = document.getElementById('previewImageDisplay');
    const fileInput = document.getElementById('image');

    // Tampilkan kembali area upload
    uploadArea.style.display = 'block';

    // Sembunyikan preview di form
    imagePreview.classList.add('hidden');

    // Reset semua preview gambar
    previewImage.src = '';
    previewImageDisplay.src = 'data:image/svg+xml;base64,PHN2ZyB3aWR0aD0iMzAwIiBoZWlnaHQ9IjMwMCIgdmlld0JveD0iMCAwIDMwMCAzMDAiIGZpbGw9Im5vbmUiIHhtbG5zPSJodHRwOi8vd3d3LnczLm9yZy8yMDAwL3N2ZyI+PHJlY3Qgd2lkdGg9IjMwMCIgaGVpZ2h0PSIzMDAiIGZpbGw9IiNGNUY1RjUiLz48dGV4dCB4PSI1MCUiIHk9IjUwJSIgZm9udC1mYW1pbHk9IkFyaWFsIiBmb250LXNpemU9IjE0IiBmaWxsPSIjOTk5IiB0ZXh0LWFuY2hvcj0ibWlkZGxlIiBkeT0iLjNlbSI+Tm8gSW1hZ2U8L3RleHQ+PC9zdmc+';
    fileInput.value = '';
}

function setupLivePreview() {
    const fields = ['name', 'code', 'price', 'stock', 'description'];

    fields.forEach(field => {
        const input = document.getElementById(field);
        if (input) {
            input.addEventListener('input', () => {
                updatePreview(field, input.value);
                updateStatus();
            });
            updatePreview(field, input.value || '');
        }
    });

    const categorySelect = document.getElementById('category_id');
    if (categorySelect) {
        categorySelect.addEventListener('change', function() {
            const text = this.options[this.selectedIndex].text;
            updatePreview('category', text.includes('--') ? '-' : text);
            updateStatus();
        });
        updatePreview('category', categorySelect.options[categorySelect.selectedIndex].text);
    }

    const unitSelect = document.getElementById('unit');
    if (unitSelect) {
        unitSelect.addEventListener('change', () => {
            const stock = document.getElementById('stock').value || 0;
            updatePreview('stock', stock);
        });
    }

    // Tambahkan event listener untuk image input
    const imageInput = document.getElementById('image');
    if (imageInput) {
        imageInput.addEventListener('change', function() {
            previewImage(this);
        });
    }
}

function updatePreview(field, value) {
    const previews = {
        'name': document.getElementById('previewName'),
        'code': document.getElementById('previewCode'),
        'price': document.getElementById('previewPrice'),
        'stock': document.getElementById('previewStock'),
        'description': document.getElementById('previewDescription'),
        'category': document.getElementById('previewCategory')
    };

    if (!previews[field]) return;

    switch(field) {
        case 'price':
            const priceNum = parseFloat(value) || 0;
            previews[field].textContent = `Rp ${priceNum.toLocaleString('id-ID')}`;
            break;
        case 'stock':
            const unit = document.getElementById('unit')?.value || 'pcs';
            const stockNum = parseInt(value) || 0;
            previews[field].textContent = `Stok: ${stockNum.toLocaleString('id-ID')} ${unit}`;
            break;
        case 'code':
            previews[field].textContent = value ? `Kode: ${value}` : 'Kode: -';
            break;
        case 'category':
            previews[field].textContent = value.includes('--') ? 'Kategori: -' : `Kategori: ${value}`;
            break;
        case 'description':
            previews[field].textContent = value || 'Belum ada deskripsi';
            break;
        default:
            previews[field].textContent = value || 'Nama Produk';
    }
}

function updateStatus() {
    const statusIcons = [
        document.getElementById('status0'),
        document.getElementById('status1'),
        document.getElementById('status2'),
        document.getElementById('status3'),
        document.getElementById('status4')
    ];

    const checks = [
        document.getElementById('name')?.value.trim(),
        document.getElementById('code')?.value.trim(),
        document.getElementById('category_id')?.value,
        document.getElementById('price')?.value > 0,
        document.getElementById('stock')?.value !== ''
    ];

    let filledCount = 0;

    checks.forEach((check, index) => {
        if (check && statusIcons[index]) {
            statusIcons[index].className = 'fas fa-check-circle text-green-500 text-xs';
            filledCount++;
        } else if (statusIcons[index]) {
            statusIcons[index].className = 'fas fa-circle text-gray-300 text-xs';
        }
    });

    const statusElement = document.getElementById('validationStatus');
    if (statusElement) {
        statusElement.textContent = `${filledCount} dari ${checks.length} terisi`;
        if (filledCount === checks.length) {
            statusElement.className = 'mt-3 text-center text-xs font-medium text-green-600';
        } else {
            statusElement.className = 'mt-3 text-center text-xs font-medium text-gray-600';
        }
    }
}

function resetForm() {
    if (confirm('Reset form ke keadaan awal?')) {
        document.querySelector('form').reset();

        // Reset image preview
        removeImage();

        // Tampilkan kembali upload area
        document.getElementById('uploadArea').style.display = 'block';
        document.getElementById('imagePreview').classList.add('hidden');

        // Reset preview text
        updatePreview('name', '');
        updatePreview('code', '');
        updatePreview('price', '0');
        updatePreview('stock', '0');
        updatePreview('description', '');
        updatePreview('category', '-');

        // Reset category select
        const categorySelect = document.getElementById('category_id');
        if (categorySelect) categorySelect.selectedIndex = 0;

        // Reset status icons
        document.querySelectorAll('[id^="status"]').forEach(icon => {
            icon.className = 'fas fa-circle text-gray-300 text-xs';
        });

        // Reset validation status
        const statusElement = document.getElementById('validationStatus');
        if (statusElement) {
            statusElement.textContent = '0 dari 5 terisi';
            statusElement.className = 'mt-3 text-center text-xs font-medium text-gray-600';
        }

        // Generate new code
        generateProductCode();
    }
}

// Initial status update
updateStatus();
</script>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH D:\PROJECT3\Toko-Roni-Mobile-App\tokoroni-app\resources\views\products\create.blade.php ENDPATH**/ ?>