@extends('layouts.app')

@section('title', 'Tambah Kategori')

@section('content')
<div class="min-h-screen bg-gradient-to-br from-gray-50 to-gray-100 py-8">
    <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">
        <!-- Header -->
        <div class="mb-8">
            <div class="flex items-center">
                <a href="{{ route('categories.index') }}"
                   class="mr-4 p-2 rounded-full hover:bg-white hover:shadow-sm transition-all duration-200">
                    <i class="fas fa-arrow-left text-gray-600"></i>
                </a>
                <div>
                    <h1 class="text-3xl font-bold text-gray-900">
                        Tambah Kategori Baru
                    </h1>
                    <p class="mt-2 text-gray-600">
                        Tambahkan kategori baru untuk mengorganisir produk Anda
                    </p>
                </div>
            </div>
            <div class="mt-4 flex items-center text-sm text-gray-500">
                <i class="fas fa-tag mr-2"></i>
                <span>Manajemen Kategori</span>
                <i class="fas fa-chevron-right mx-2"></i>
                <span class="text-blue-600 font-medium">Tambah Baru</span>
            </div>
        </div>

        <!-- Main Form Card -->
        <div class="bg-white rounded-2xl shadow-xl overflow-hidden">
            <!-- Card Header -->
            <div class="bg-gradient-to-r from-blue-600 to-blue-700 px-6 py-5">
                <div class="flex items-center">
                    <div class="bg-white/20 p-3 rounded-xl mr-4 backdrop-blur-sm">
                        <i class="fas fa-plus-circle text-white text-xl"></i>
                    </div>
                    <div>
                        <h2 class="text-xl font-bold text-white">
                            Form Tambah Kategori
                        </h2>
                        <p class="text-blue-100 mt-1">
                            Isi form di bawah untuk menambahkan kategori baru
                        </p>
                    </div>
                </div>
            </div>

            <!-- Form Content -->
            <form action="{{ route('categories.store') }}" method="POST" class="p-6 sm:p-8">
                @csrf

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
                        <input type="text"
                               name="name"
                               value="{{ old('name') }}"
                               required
                               class="pl-10 pr-4 py-3 w-full border border-gray-300 rounded-xl shadow-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500 focus:shadow-outline-blue transition-all duration-200 @error('name') border-red-500 ring-2 ring-red-200 @enderror"
                               placeholder="Masukkan nama kategori"
                               oninput="updateSlugPreview(this.value)">
                        <div class="absolute inset-y-0 right-0 pr-3 flex items-center pointer-events-none">
                            <span id="charCount" class="text-gray-400 text-sm">0/50</span>
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
                        URL Slug (Otomatis)
                    </label>
                    <div class="flex items-center bg-white rounded-lg border border-gray-200 p-3">
                        <span class="text-gray-500 mr-2">/categories/</span>
                        <span id="slugPreview" class="font-mono text-blue-600 bg-blue-50 px-2 py-1 rounded">
                            {{ old('name') ? Str::slug(old('name')) : 'nama-kategori' }}
                        </span>
                    </div>
                    <div class="mt-2 text-sm text-gray-500">
                        <i class="fas fa-info-circle mr-1"></i>
                        URL slug akan dibuat otomatis dari nama kategori
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
                        <textarea name="description"
                                  rows="4"
                                  class="pl-10 pr-4 py-3 w-full border border-gray-300 rounded-xl shadow-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500 focus:shadow-outline-blue transition-all duration-200 resize-none"
                                  placeholder="Tambahkan deskripsi kategori (opsional)"
                                  oninput="updateDescCharCount(this.value)">{{ old('description') }}</textarea>
                        <div class="absolute bottom-3 right-3 text-gray-400 text-sm">
                            <span id="descCharCount">0/500</span>
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
                            <input type="checkbox"
                                   name="is_active"
                                   id="is_active"
                                   class="sr-only peer"
                                   {{ old('is_active', true) ? 'checked' : '' }}>
                            <div class="relative w-14 h-7 bg-gray-200 peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-blue-300 rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-0.5 after:left-[4px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-6 after:w-6 after:transition-all peer-checked:bg-green-500"></div>
                            <div class="ml-4 flex items-center">
                                <span class="text-lg font-medium text-gray-900 mr-2" id="statusText">
                                    {{ old('is_active', true) ? 'Aktif' : 'Nonaktif' }}
                                </span>
                                <span id="statusBadge" class="px-3 py-1 rounded-full text-sm font-medium {{ old('is_active', true) ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' }}">
                                    <i class="fas {{ old('is_active', true) ? 'fa-check-circle' : 'fa-times-circle' }} mr-1"></i>
                                    {{ old('is_active', true) ? 'Aktif' : 'Nonaktif' }}
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
                            Simpan Kategori
                        </button>
                    </div>
                </div>
            </form>
        </div>

        <!-- Tips Section -->
        <div class="mt-8 bg-gradient-to-r from-blue-50 to-indigo-50 border border-blue-100 rounded-2xl p-6">
            <div class="flex items-start">
                <div class="bg-blue-100 p-3 rounded-xl mr-4">
                    <i class="fas fa-lightbulb text-blue-600 text-xl"></i>
                </div>
                <div>
                    <h3 class="font-bold text-gray-900 mb-2">Tips Menambahkan Kategori</h3>
                    <ul class="space-y-2 text-gray-600">
                        <li class="flex items-start">
                            <i class="fas fa-check text-green-500 mr-2 mt-1"></i>
                            Gunakan nama yang deskriptif dan mudah dipahami
                        </li>
                        <li class="flex items-start">
                            <i class="fas fa-check text-green-500 mr-2 mt-1"></i>
                            Hindari nama yang terlalu panjang atau ambigu
                        </li>
                        <li class="fas fa-check text-green-500 mr-2 mt-1">
                            Pastikan kategori tidak duplikat dengan yang sudah ada
                        </li>
                        <li class="flex items-start">
                            <i class="fas fa-check text-green-500 mr-2 mt-1"></i>
                            Tambahkan deskripsi untuk informasi lebih lengkap
                        </li>
                    </ul>
                </div>
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

    @keyframes pulse-subtle {
        0%, 100% {
            box-shadow: 0 0 0 0 rgba(59, 130, 246, 0);
        }
        50% {
            box-shadow: 0 0 0 10px rgba(59, 130, 246, 0.1);
        }
    }

    .animate-fade-in {
        animation: fade-in 0.3s ease-out;
    }

    .animate-pulse-subtle {
        animation: pulse-subtle 2s infinite;
    }

    /* Custom scrollbar for textarea */
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
    input, textarea, select {
        transition: all 0.2s ease-in-out;
    }

    /* Focus styles */
    input:focus, textarea:focus {
        outline: none;
        box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.1);
    }

    /* Toggle switch animation */
    .peer:checked ~ div .after\\:translate-x-full {
        transform: translateX(100%);
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
                if (this.checked) {
                    statusText.textContent = 'Aktif';
                    statusBadge.className = 'px-3 py-1 rounded-full text-sm font-medium bg-green-100 text-green-800';
                    statusBadge.innerHTML = '<i class="fas fa-check-circle mr-1"></i>Aktif';
                } else {
                    statusText.textContent = 'Nonaktif';
                    statusBadge.className = 'px-3 py-1 rounded-full text-sm font-medium bg-red-100 text-red-800';
                    statusBadge.innerHTML = '<i class="fas fa-times-circle mr-1"></i>Nonaktif';
                }
            });
        }

        // Form submission animation
        const form = document.querySelector('form');
        const submitButton = form.querySelector('button[type="submit"]');

        form.addEventListener('submit', function(e) {
            if (submitButton) {
                const originalText = submitButton.innerHTML;
                submitButton.innerHTML = '<i class="fas fa-spinner fa-spin mr-2"></i>Menyimpan...';
                submitButton.disabled = true;
                submitButton.classList.remove('hover:from-blue-700', 'hover:to-blue-800', 'hover:shadow-lg', 'hover:-translate-y-0.5');

                // Revert after 3 seconds if form submission takes too long
                setTimeout(() => {
                    submitButton.innerHTML = originalText;
                    submitButton.disabled = false;
                    submitButton.classList.add('hover:from-blue-700', 'hover:to-blue-800', 'hover:shadow-lg', 'hover:-translate-y-0.5');
                }, 3000);
            }
        });

        // Add subtle animation to the card
        const formCard = document.querySelector('.bg-white.rounded-2xl');
        if (formCard) {
            formCard.classList.add('animate-fade-in');
        }
    });

    function updateCharCount(text) {
        const charCount = document.getElementById('charCount');
        if (charCount) {
            const count = text.length;
            charCount.textContent = `${count}/50`;

            // Change color based on count
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

            // Change color based on count
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
            // Simple slug conversion (you might want to use a more robust solution)
            const slug = name.toLowerCase()
                .replace(/[^\w\s-]/g, '')
                .replace(/\s+/g, '-')
                .replace(/--+/g, '-')
                .trim();
            slugPreview.textContent = slug || 'nama-kategori';

            // Add subtle animation
            slugPreview.classList.add('animate-pulse-subtle');
            setTimeout(() => {
                slugPreview.classList.remove('animate-pulse-subtle');
            }, 1000);
        }
    }

    // Auto-resize textarea
    function autoResizeTextarea(textarea) {
        textarea.style.height = 'auto';
        textarea.style.height = (textarea.scrollHeight) + 'px';
    }

    // Initialize auto-resize for textarea
    const descTextarea = document.querySelector('textarea[name="description"]');
    if (descTextarea) {
        descTextarea.addEventListener('input', function() {
            autoResizeTextarea(this);
        });
        // Initial resize
        autoResizeTextarea(descTextarea);
    }

    // Add floating labels effect
    const inputs = document.querySelectorAll('input[type="text"], textarea');
    inputs.forEach(input => {
        const parent = input.parentElement;
        const label = parent.previousElementSibling;

        if (label && label.classList.contains('block')) {
            input.addEventListener('focus', () => {
                label.classList.add('text-blue-600');
            });

            input.addEventListener('blur', () => {
                if (!input.value) {
                    label.classList.remove('text-blue-600');
                }
            });

            // Check on load
            if (input.value) {
                label.classList.add('text-blue-600');
            }
        }
    });
</script>
@endsection
