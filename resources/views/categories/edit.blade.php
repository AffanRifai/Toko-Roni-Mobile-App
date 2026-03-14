@extends('layouts.app')

@section('title', 'Edit Kategori')

@section('content')
    <div class="min-h-screen bg-gradient-to-br from-gray-50 to-gray-100 py-8">
        <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">
            <!-- Header -->
            <div class="mb-8">
                <div class="flex items-center">
                    <a href="{{ route('categories.index') }}"
                        class="mr-4 p-2 rounded-full hover:bg-white hover:shadow-sm transition-all duration-200 group">
                        <i class="fas fa-arrow-left text-gray-600 group-hover:text-blue-600 transition-colors"></i>
                    </a>
                    <div>
                        <h1 class="text-3xl font-bold text-gray-900 flex items-center">
                            <i class="fas fa-edit text-blue-600 mr-3"></i>
                            Edit Kategori
                        </h1>
                        <p class="mt-2 text-gray-600">
                            Perbarui informasi kategori "{{ $category->name }}"
                        </p>
                    </div>
                </div>

                <!-- Breadcrumb -->
                <div class="mt-4 flex items-center text-sm text-gray-500">
                    <a href="{{ route('categories.index') }}" class="hover:text-blue-600 transition-colors">
                        <i class="fas fa-tag mr-2"></i>
                        Manajemen Kategori
                    </a>
                    <i class="fas fa-chevron-right mx-2"></i>
                    <span class="text-blue-600 font-medium">{{ $category->name }}</span>
                    <i class="fas fa-chevron-right mx-2"></i>
                    <span class="text-gray-900 font-medium">Edit</span>
                </div>

                <!-- Category Info Badge -->
                <div class="mt-4 flex flex-wrap gap-2">
                    <span
                        class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-blue-100 text-blue-800">
                        <i class="fas fa-hashtag mr-1.5"></i>
                        ID: {{ $category->id }}
                    </span>
                    <span
                        class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium {{ $category->is_active ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' }}">
                        <i class="fas {{ $category->is_active ? 'fa-check-circle' : 'fa-times-circle' }} mr-1.5"></i>
                        {{ $category->is_active ? 'Aktif' : 'Nonaktif' }}
                    </span>
                    <span
                        class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-gray-100 text-gray-800">
                        <i class="fas fa-calendar mr-1.5"></i>
                        {{ $category->created_at->format('d M Y') }}
                    </span>
                </div>
            </div>

            <!-- Main Form Card -->
            <div class="bg-white rounded-2xl shadow-xl overflow-hidden">
                <!-- Card Header -->
                <div class="bg-gradient-to-r from-blue-600 to-blue-700 px-6 py-5">
                    <div class="flex items-center justify-between">
                        <div class="flex items-center">
                            <div class="bg-white/20 p-3 rounded-xl mr-4 backdrop-blur-sm">
                                <i class="fas fa-pencil-alt text-white text-xl"></i>
                            </div>
                            <div>
                                <h2 class="text-xl font-bold text-white">
                                    Form Edit Kategori
                                </h2>
                                <p class="text-blue-100 mt-1">
                                    Perbarui data kategori sesuai kebutuhan
                                </p>
                            </div>
                        </div>
                        <div class="text-white/80">
                            <i class="fas fa-database text-2xl"></i>
                        </div>
                    </div>
                </div>

                <!-- Form Content -->
                <form action="{{ route('categories.update', $category) }}" method="POST" class="p-6 sm:p-8">
                    @csrf
                    @method('PUT')

                    <!-- Success Message -->
                    @if (session('success'))
                        <div class="mb-6 rounded-lg bg-green-50 p-4 animate-fade-in">
                            <div class="flex">
                                <div class="flex-shrink-0">
                                    <i class="h-5 w-5 text-green-400 fas fa-check-circle"></i>
                                </div>
                                <div class="ml-3">
                                    <h3 class="text-sm font-medium text-green-800">
                                        Berhasil Diperbarui!
                                    </h3>
                                    <div class="mt-1 text-sm text-green-700">
                                        <p>{{ session('success') }}</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    @endif

                    <!-- Nama Kategori -->
                    <div class="mb-8">
                        <label class="block text-sm font-semibold text-gray-900 mb-3 flex items-center">
                            <i class="fas fa-font text-blue-600 mr-2"></i>
                            Nama Kategori
                            <span class="text-red-500 ml-1">*</span>
                        </label>
                        <div class="relative">
                            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                <i class="fas fa-tag text-gray-400"></i>
                            </div>
                            <input type="text" name="name" value="{{ old('name', $category->name) }}" required
                                class="pl-10 pr-4 py-3 w-full border border-gray-300 rounded-xl shadow-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500 focus:shadow-outline-blue transition-all duration-200 @error('name') border-red-500 ring-2 ring-red-200 @enderror"
                                placeholder="Masukkan nama kategori" oninput="updateSlugPreview(this.value)">
                            <div class="absolute inset-y-0 right-0 pr-3 flex items-center pointer-events-none">
                                <span id="charCount"
                                    class="text-gray-400 text-sm">{{ strlen(old('name', $category->name)) }}/50</span>
                            </div>
                        </div>
                        @error('name')
                            <div class="mt-2 flex items-center text-sm text-red-600 animate-fade-in">
                                <i class="fas fa-exclamation-circle mr-2"></i>
                                {{ $message }}
                            </div>
                        @enderror
                        <div class="mt-2 text-sm text-gray-500">
                            Nama kategori akan ditampilkan di halaman produk dan katalog
                        </div>
                    </div>

                    <!-- Slug Preview -->
                    <div class="mb-8 bg-blue-50 border border-blue-100 rounded-xl p-4">
                        <label class="block text-sm font-semibold text-gray-900 mb-2 flex items-center">
                            <i class="fas fa-link text-blue-600 mr-2"></i>
                            URL Slug
                        </label>
                        <div class="flex items-center">
                            <div class="bg-white rounded-lg border border-gray-200 p-3 flex-1">
                                <span class="text-gray-500 mr-2">/categories/</span>
                                <span id="slugPreview" class="font-mono text-blue-600 bg-blue-50 px-2 py-1 rounded">
                                    {{ $category->slug }}
                                </span>
                            </div>
                            <button type="button" onclick="regenerateSlug()"
                                class="ml-3 inline-flex items-center px-3 py-2 border border-gray-300 shadow-sm text-sm leading-4 font-medium rounded-lg text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                                <i class="fas fa-redo-alt mr-1.5"></i>
                                Regenerate
                            </button>
                        </div>
                        <div class="mt-2 text-sm text-gray-500">
                            <i class="fas fa-info-circle mr-1"></i>
                            URL slug dibuat otomatis dari nama kategori. Klik "Regenerate" untuk memperbarui.
                        </div>
                    </div>

                    <!-- Deskripsi -->
                    <div class="mb-8">
                        <label class="block text-sm font-semibold text-gray-900 mb-3 flex items-center">
                            <i class="fas fa-align-left text-blue-600 mr-2"></i>
                            Deskripsi Kategori
                        </label>
                        <div class="relative">
                            <div class="absolute top-3 left-3 text-gray-400">
                                <i class="fas fa-pen"></i>
                            </div>
                            <textarea name="description" rows="4"
                                class="pl-10 pr-4 py-3 w-full border border-gray-300 rounded-xl shadow-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500 focus:shadow-outline-blue transition-all duration-200 resize-none"
                                placeholder="Tambahkan deskripsi kategori (opsional)" oninput="updateDescCharCount(this.value)">{{ old('description', $category->description) }}</textarea>
                            <div class="absolute bottom-3 right-3 text-gray-400 text-sm">
                                <span
                                    id="descCharCount">{{ strlen(old('description', $category->description)) }}/500</span>
                            </div>
                        </div>
                        <div class="mt-2 text-sm text-gray-500">
                            Deskripsi membantu pengguna memahami kategori produk ini
                        </div>
                    </div>

                    <!-- Status -->
                    <div class="mb-10">
                        <label class="block text-sm font-semibold text-gray-900 mb-3 flex items-center">
                            <i class="fas fa-toggle-on text-blue-600 mr-2"></i>
                            Status Kategori
                        </label>
                        <div class="bg-gradient-to-r from-gray-50 to-white border border-gray-200 rounded-xl p-4">
                            <label class="inline-flex items-center cursor-pointer">
                                <input type="checkbox" name="is_active" id="is_active" class="sr-only peer"
                                    {{ old('is_active', $category->is_active) ? 'checked' : '' }}>
                                <div
                                    class="relative w-14 h-7 bg-gray-200 peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-blue-300 rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-0.5 after:left-[4px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-6 after:w-6 after:transition-all peer-checked:bg-green-500">
                                </div>
                                <div class="ml-4 flex items-center">
                                    <span class="text-lg font-medium text-gray-900 mr-2" id="statusText">
                                        {{ old('is_active', $category->is_active) ? 'Aktif' : 'Nonaktif' }}
                                    </span>
                                    <span id="statusBadge"
                                        class="px-3 py-1 rounded-full text-sm font-medium {{ old('is_active', $category->is_active) ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' }}">
                                        <i
                                            class="fas {{ old('is_active', $category->is_active) ? 'fa-check-circle' : 'fa-times-circle' }} mr-1"></i>
                                        {{ old('is_active', $category->is_active) ? 'Aktif' : 'Nonaktif' }}
                                    </span>
                                </div>
                            </label>
                            <div class="mt-3 text-sm text-gray-500">
                                <i class="fas fa-info-circle mr-1"></i>
                                Kategori yang nonaktif tidak akan ditampilkan di halaman publik
                            </div>
                        </div>
                    </div>

                    <!-- Form Actions -->
                    <div class="pt-6 border-t border-gray-200">
                        <div class="flex flex-col sm:flex-row gap-4 justify-end">
                            <a href="{{ route('categories.index') }}"
                                class="inline-flex items-center justify-center px-6 py-3 border-2 border-gray-300 text-gray-700 font-semibold rounded-xl hover:bg-gray-50 hover:border-gray-400 hover:shadow-sm transition-all duration-200">
                                <i class="fas fa-times mr-2"></i>
                                Batal
                            </a>
                            <button type="submit"
                                class="inline-flex items-center justify-center px-6 py-3 bg-gradient-to-r from-blue-600 to-blue-700 text-white font-semibold rounded-xl shadow-md hover:from-blue-700 hover:to-blue-800 hover:shadow-lg transform hover:-translate-y-0.5 transition-all duration-200">
                                <i class="fas fa-save mr-2"></i>
                                Simpan Perubahan
                            </button>
                        </div>
                    </div>
                </form>
            </div>

            <!-- Advanced Options Card -->
            <div class="mt-8 bg-white rounded-2xl shadow-sm overflow-hidden">
                <div class="border-b border-gray-200 px-6 py-4">
                    <h3 class="text-lg font-semibold text-gray-900 flex items-center">
                        <i class="fas fa-cogs text-gray-600 mr-2"></i>
                        Opsi Lanjutan
                    </h3>
                </div>
                <div class="p-6">
                    <div class="space-y-4">
                        <!-- Delete Button -->
                        <div class="flex items-center justify-between">
                            <div>
                                <h4 class="text-sm font-medium text-gray-900">Hapus Kategori</h4>
                                <p class="text-sm text-gray-500 mt-1">
                                    Hapus permanen kategori ini. Tindakan ini tidak dapat dibatalkan.
                                </p>
                            </div>
                            <button type="button" onclick="showDeleteConfirmation()"
                                class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-lg shadow-sm text-white bg-gradient-to-r from-red-600 to-red-700 hover:from-red-700 hover:to-red-800 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500">
                                <i class="fas fa-trash-alt mr-2"></i>
                                Hapus Kategori
                            </button>
                        </div>

                        <!-- Stats -->
                        <div class="grid grid-cols-2 gap-4 pt-4 border-t border-gray-200">
                            <div class="text-center p-4 bg-blue-50 rounded-lg">
                                <div class="text-2xl font-bold text-blue-600">
                                    <i class="fas fa-box"></i>
                                </div>
                                <div class="mt-2 text-sm text-gray-600">Total Produk</div>
                                <div class="text-lg font-semibold text-gray-900">{{ $category->products_count ?? 0 }}
                                </div>
                            </div>
                            <div class="text-center p-4 bg-green-50 rounded-lg">
                                <div class="text-2xl font-bold text-green-600">
                                    <i class="fas fa-eye"></i>
                                </div>
                                <div class="mt-2 text-sm text-gray-600">Terakhir Diperbarui</div>
                                <div class="text-sm font-semibold text-gray-900">
                                    {{ $category->updated_at->diffForHumans() }}</div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Delete Confirmation Modal -->
    <div id="deleteModal" class="fixed inset-0 z-50 overflow-y-auto hidden" aria-labelledby="modal-title" role="dialog"
        aria-modal="true">
        <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
            <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" aria-hidden="true"></div>
            <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>
            <div
                class="inline-block align-bottom bg-white rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full">
                <div class="bg-white px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
                    <div class="sm:flex sm:items-start">
                        <div
                            class="mx-auto flex-shrink-0 flex items-center justify-center h-12 w-12 rounded-full bg-red-100 sm:mx-0 sm:h-10 sm:w-10">
                            <i class="fas fa-exclamation-triangle text-red-600"></i>
                        </div>
                        <div class="mt-3 text-center sm:mt-0 sm:ml-4 sm:text-left">
                            <h3 class="text-lg leading-6 font-medium text-gray-900" id="modal-title">
                                Hapus Kategori
                            </h3>
                            <div class="mt-2">
                                <p class="text-sm text-gray-500">
                                    Apakah Anda yakin ingin menghapus kategori <span
                                        class="font-semibold text-gray-900">"{{ $category->name }}"</span>?
                                    @if ($category->products_count > 0)
                                        <br><br>
                                        <span class="text-red-600 font-medium">
                                            <i class="fas fa-exclamation-circle mr-1"></i>
                                            PERHATIAN: Kategori ini memiliki {{ $category->products_count }} produk.
                                            Semua produk dalam kategori ini akan terpengaruh.
                                        </span>
                                    @endif
                                </p>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="bg-gray-50 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse">
                    <form action="{{ route('categories.destroy', $category) }}" method="POST" class="inline">
                        @csrf
                        @method('DELETE')
                        <button type="submit"
                            class="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-red-600 text-base font-medium text-white hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500 sm:ml-3 sm:w-auto sm:text-sm">
                            <i class="fas fa-trash-alt mr-2"></i>
                            Ya, Hapus
                        </button>
                    </form>
                    <button type="button" onclick="hideDeleteConfirmation()"
                        class="mt-3 w-full inline-flex justify-center rounded-md border border-gray-300 shadow-sm px-4 py-2 bg-white text-base font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 sm:mt-0 sm:ml-3 sm:w-auto sm:text-sm">
                        <i class="fas fa-times mr-2"></i>
                        Batal
                    </button>
                </div>
            </div>
        </div>
    </div>

    <style>
        @keyframes fade-in {
            from {
                opacity: 0;
                transform: translateY(10px);
            }

            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        @keyframes slide-in {
            from {
                opacity: 0;
                transform: translateY(-20px);
            }

            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .animate-fade-in {
            animation: fade-in 0.3s ease-out;
        }

        .animate-slide-in {
            animation: slide-in 0.3s ease-out;
        }

        /* Custom scrollbar */
        textarea::-webkit-scrollbar {
            width: 6px;
        }

        textarea::-webkit-scrollbar-track {
            background: #f1f1f1;
            border-radius: 3px;
        }

        textarea::-webkit-scrollbar-thumb {
            background: #c1c1c1;
            border-radius: 3px;
        }

        textarea::-webkit-scrollbar-thumb:hover {
            background: #a8a8a8;
        }

        /* Smooth transitions */
        input,
        textarea,
        select,
        button {
            transition: all 0.2s ease-in-out;
        }

        /* Custom focus styles */
        .focus\:shadow-outline-blue:focus {
            box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.1);
        }

        /* Stat card hover effects */
        .bg-blue-50:hover,
        .bg-green-50:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
        }
    </style>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Initialize character counters
            const nameInput = document.querySelector('input[name="name"]');
            const descTextarea = document.querySelector('textarea[name="description"]');
            const charCount = document.getElementById('charCount');
            const descCharCount = document.getElementById('descCharCount');

            if (nameInput && charCount) {
                updateCharCount(nameInput.value);
                nameInput.addEventListener('input', function() {
                    updateCharCount(this.value);
                });
            }

            if (descTextarea && descCharCount) {
                updateDescCharCount(descTextarea.value);
            }

            // Toggle switch functionality
            const toggleSwitch = document.getElementById('is_active');
            const statusText = document.getElementById('statusText');
            const statusBadge = document.getElementById('statusBadge');

            if (toggleSwitch) {
                toggleSwitch.addEventListener('change', function() {
                    updateStatusDisplay(this.checked);
                });

                // Initial display
                updateStatusDisplay(toggleSwitch.checked);
            }

            // Auto-resize textarea
            if (descTextarea) {
                descTextarea.addEventListener('input', function() {
                    autoResizeTextarea(this);
                });
                autoResizeTextarea(descTextarea);
            }

            // Form submission animation
            const form = document.querySelector('form');
            const submitButton = form.querySelector('button[type="submit"]');

            form.addEventListener('submit', function(e) {
                if (submitButton) {
                    const originalText = submitButton.innerHTML;
                    submitButton.innerHTML = '<i class="fas fa-spinner fa-spin mr-2"></i>Menyimpan...';
                    submitButton.disabled = true;
                    submitButton.classList.remove('hover:from-blue-700', 'hover:to-blue-800',
                        'hover:shadow-lg', 'hover:-translate-y-0.5');

                    setTimeout(() => {
                        submitButton.innerHTML = originalText;
                        submitButton.disabled = false;
                        submitButton.classList.add('hover:from-blue-700', 'hover:to-blue-800',
                            'hover:shadow-lg', 'hover:-translate-y-0.5');
                    }, 3000);
                }
            });
        });

        function updateCharCount(text) {
            const charCount = document.getElementById('charCount');
            if (charCount) {
                const count = text.length;
                charCount.textContent = `${count}/50`;

                if (count > 45) {
                    charCount.className = 'text-red-500 text-sm font-medium';
                } else if (count > 35) {
                    charCount.className = 'text-yellow-500 text-sm font-medium';
                } else {
                    charCount.className = 'text-gray-400 text-sm';
                }
            }
        }

        function updateDescCharCount(text) {
            const descCharCount = document.getElementById('descCharCount');
            if (descCharCount) {
                const count = text.length;
                descCharCount.textContent = `${count}/500`;

                if (count > 450) {
                    descCharCount.className = 'text-red-500 text-sm font-medium';
                } else if (count > 400) {
                    descCharCount.className = 'text-yellow-500 text-sm font-medium';
                } else {
                    descCharCount.className = 'text-gray-400 text-sm';
                }
            }
        }

        function updateSlugPreview(name) {
            const slugPreview = document.getElementById('slugPreview');
            if (slugPreview && name) {
                const slug = name.toLowerCase()
                    .replace(/[^\w\s-]/g, '')
                    .replace(/\s+/g, '-')
                    .replace(/--+/g, '-')
                    .trim();
                slugPreview.textContent = slug || 'nama-kategori';
                slugPreview.classList.add('animate-fade-in');
                setTimeout(() => slugPreview.classList.remove('animate-fade-in'), 300);
            }
        }

        function regenerateSlug() {
            const nameInput = document.querySelector('input[name="name"]');
            if (nameInput && nameInput.value) {
                updateSlugPreview(nameInput.value);

                // Show success feedback
                const slugPreview = document.getElementById('slugPreview');
                slugPreview.classList.add('bg-green-100', 'text-green-800');
                setTimeout(() => {
                    slugPreview.classList.remove('bg-green-100', 'text-green-800');
                    slugPreview.classList.add('bg-blue-50', 'text-blue-600');
                }, 1500);
            }
        }

        function updateStatusDisplay(isActive) {
            const statusText = document.getElementById('statusText');
            const statusBadge = document.getElementById('statusBadge');

            if (isActive) {
                statusText.textContent = 'Aktif';
                statusBadge.className = 'px-3 py-1 rounded-full text-sm font-medium bg-green-100 text-green-800';
                statusBadge.innerHTML = '<i class="fas fa-check-circle mr-1"></i>Aktif';
            } else {
                statusText.textContent = 'Nonaktif';
                statusBadge.className = 'px-3 py-1 rounded-full text-sm font-medium bg-red-100 text-red-800';
                statusBadge.innerHTML = '<i class="fas fa-times-circle mr-1"></i>Nonaktif';
            }
        }

        function autoResizeTextarea(textarea) {
            textarea.style.height = 'auto';
            textarea.style.height = (textarea.scrollHeight) + 'px';
        }

        function showDeleteConfirmation() {
            const modal = document.getElementById('deleteModal');
            modal.classList.remove('hidden');
            modal.classList.add('block', 'animate-slide-in');
            document.body.classList.add('overflow-hidden');
        }

        function hideDeleteConfirmation() {
            const modal = document.getElementById('deleteModal');
            modal.classList.remove('block', 'animate-slide-in');
            modal.classList.add('hidden');
            document.body.classList.remove('overflow-hidden');
        }

        // Close modal on outside click
        document.addEventListener('click', function(e) {
            const modal = document.getElementById('deleteModal');
            if (modal && modal.classList.contains('block') && e.target === modal) {
                hideDeleteConfirmation();
            }
        });

        // Close modal with Escape key
        document.addEventListener('keydown', function(e) {
            if (e.key === 'Escape') {
                hideDeleteConfirmation();
            }
        });
    </script>
@endsection
