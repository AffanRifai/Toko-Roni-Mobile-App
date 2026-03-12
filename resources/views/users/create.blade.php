@extends('layouts.app')

@section('title', 'Tambah Pengguna Baru')

@vite(['resources/css/tambahuser.css'])
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.3/font/bootstrap-icons.css">
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css">
<link rel="stylesheet" href="{{ asset('css/tambahuser.css') }}">

@section('content')
    <div class="user-page">
        <div class="container-fluid px-3 px-md-4 py-3 py-md-4">
            <!-- Breadcrumb -->
            <nav aria-label="breadcrumb" class="mb-4">
                <ol class="breadcrumb mb-0">
                    <li class="breadcrumb-item">
                        <a href="{{ route('dashboard') }}" class="text-decoration-none">
                            <i class="fas fa-home me-1"></i> Dashboard
                        </a>
                    </li>
                    <li class="breadcrumb-item">
                        <a href="{{ route('users.index') }}" class="text-decoration-none">
                            <i class="fas fa-users me-1"></i> Manajemen Pengguna
                        </a>
                    </li>
                    <li class="breadcrumb-item active text-primary">
                        <i class="fas fa-user-plus me-1"></i> Tambah Pengguna
                    </li>
                </ol>
            </nav>

            <!-- Header -->
            <div class="page-header card border-0 shadow-sm mb-4">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="icon-wrapper bg-gradient-primary rounded-3 p-3 me-3 shadow-sm">
                            <i class="fas fa-user-plus text-white fs-4"></i>
                        </div>
                        <div>
                            <h1 class="h3 mb-1 fw-bold">Tambah Pengguna Baru</h1>
                            <p class="text-muted mb-0">Isi form di bawah untuk menambahkan pengguna baru ke sistem</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Form -->
            <div class="row">
                <div class="col-lg-8">
                    <div class="card border-0 shadow-sm">
                        <div class="card-header bg-white border-0 py-3">
                            <h5 class="fw-semibold mb-0">
                                <i class="fas fa-user-circle me-2 text-primary"></i> Informasi Pengguna
                            </h5>
                        </div>

                        <form method="POST" action="{{ route('users.store') }}" id="user-form">
                            @csrf

                            <div class="card-body">
                                @if ($errors->any())
                                    <div class="alert alert-danger border-danger border-opacity-25 mb-4">
                                        <div class="d-flex">
                                            <i class="fas fa-exclamation-circle mt-1 me-3"></i>
                                            <div>
                                                <strong class="d-block mb-2">Terjadi kesalahan!</strong>
                                                <ul class="mb-0 ps-3">
                                                    @foreach ($errors->all() as $error)
                                                        <li>{{ $error }}</li>
                                                    @endforeach
                                                </ul>
                                            </div>
                                        </div>
                                    </div>
                                @endif

                                <div class="row g-3">
                                    <!-- Nama Lengkap -->
                                    <div class="col-md-6">
                                        <label for="name" class="form-label fw-semibold">
                                            <i class="fas fa-user me-1 text-primary"></i> Nama Lengkap
                                            <span class="text-danger">*</span>
                                        </label>
                                        <div class="input-group">
                                            <span class="input-group-text bg-light border-end-0">
                                                <i class="fas fa-user text-muted"></i>
                                            </span>
                                            <input type="text" name="name" id="name"
                                                class="form-control border-start-0 @error('name') is-invalid @enderror"
                                                value="{{ old('name') }}" placeholder="Masukkan nama lengkap" required
                                                autofocus>
                                            @error('name')
                                                <div class="invalid-feedback">
                                                    {{ $message }}
                                                </div>
                                            @enderror
                                        </div>
                                        <small class="text-muted mt-1 d-block">
                                            Contoh: John Doe, Ahmad Budi, dll.
                                        </small>
                                    </div>

                                    <!-- Email -->
                                    <div class="col-md-6">
                                        <label for="email" class="form-label fw-semibold">
                                            <i class="fas fa-envelope me-1 text-primary"></i> Alamat Email
                                            <span class="text-danger">*</span>
                                        </label>
                                        <div class="input-group">
                                            <span class="input-group-text bg-light border-end-0">
                                                <i class="fas fa-at text-muted"></i>
                                            </span>
                                            <input type="email" name="email" id="email"
                                                class="form-control border-start-0 @error('email') is-invalid @enderror"
                                                value="{{ old('email') }}" placeholder="contoh@email.com" required>
                                            @error('email')
                                                <div class="invalid-feedback">
                                                    {{ $message }}
                                                </div>
                                            @enderror
                                        </div>
                                        <small class="text-muted mt-1 d-block">
                                            Email ini akan digunakan untuk login
                                        </small>
                                    </div>

                                    <!-- Password -->
                                    <div class="col-md-6">
                                        <label for="password" class="form-label fw-semibold">
                                            <i class="fas fa-lock me-1 text-primary"></i> Password
                                            <span class="text-danger">*</span>
                                        </label>
                                        <div class="input-group">
                                            <span class="input-group-text bg-light border-end-0">
                                                <i class="fas fa-key text-muted"></i>
                                            </span>
                                            <input type="password" name="password" id="password"
                                                class="form-control border-start-0 @error('password') is-invalid @enderror"
                                                placeholder="Minimal 8 karakter" required>
                                            <button type="button"
                                                class="input-group-text bg-light border-start-0 toggle-password"
                                                data-target="password" style="cursor: pointer;">
                                                <i class="fas fa-eye"></i>
                                            </button>
                                            @error('password')
                                                <div class="invalid-feedback">
                                                    {{ $message }}
                                                </div>
                                            @enderror
                                        </div>
                                        <div class="password-strength mt-2">
                                            <div class="progress" style="height: 4px;">
                                                <div class="progress-bar" id="password-strength-bar" style="width: 0%">
                                                </div>
                                            </div>
                                            <small class="text-muted" id="password-strength-text">
                                                Kekuatan password: -
                                            </small>
                                        </div>
                                    </div>

                                    <!-- Konfirmasi Password -->
                                    <div class="col-md-6">
                                        <label for="password_confirmation" class="form-label fw-semibold">
                                            <i class="fas fa-lock me-1 text-primary"></i> Konfirmasi Password
                                            <span class="text-danger">*</span>
                                        </label>
                                        <div class="input-group">
                                            <span class="input-group-text bg-light border-end-0">
                                                <i class="fas fa-key text-muted"></i>
                                            </span>
                                            <input type="password" name="password_confirmation"
                                                id="password_confirmation"
                                                class="form-control border-start-0 @error('password_confirmation') is-invalid @enderror"
                                                placeholder="Ulangi password" required>
                                            <button type="button"
                                                class="input-group-text bg-light border-start-0 toggle-password"
                                                data-target="password_confirmation" style="cursor: pointer;">
                                                <i class="fas fa-eye"></i>
                                            </button>
                                            @error('password_confirmation')
                                                <div class="invalid-feedback">
                                                    {{ $message }}
                                                </div>
                                            @enderror
                                        </div>
                                        <small class="text-muted mt-1 d-block">
                                            Harus sama dengan password di atas
                                        </small>
                                    </div>

                                    <!-- Role -->
                                    <div class="col-md-6">
                                        <label for="role" class="form-label fw-semibold">
                                            <i class="fas fa-user-tag me-1 text-primary"></i> Peran (Role)
                                            <span class="text-danger">*</span>
                                        </label>
                                        <div class="input-group">
                                            <span class="input-group-text bg-light border-end-0">
                                                <i class="fas fa-users-cog text-muted"></i>
                                            </span>
                                            <select name="role" id="role"
                                                class="form-select border-start-0 @error('role') is-invalid @enderror"
                                                required>
                                                <option value="">Pilih Peran</option>
                                                <option value="owner" {{ old('role') == 'owner' ? 'selected' : '' }}>
                                                    Owner (Administrator)
                                                </option>
                                                <option value="kasir" {{ old('role') == 'kasir' ? 'selected' : '' }}>
                                                    Kasir
                                                </option>
                                                <option value="kepala_gudang"
                                                    {{ old('role') == 'kepala_gudang' ? 'selected' : '' }}>
                                                    Kepala Gudang
                                                </option>
                                                <option value="logistik"
                                                    {{ old('role') == 'logistik' ? 'selected' : '' }}>
                                                    Staff Logistik
                                                </option>
                                                <option value="checker_barang"
                                                    {{ old('role') == 'checker_barang' ? 'selected' : '' }}>
                                                    Checker Barang
                                                </option>
                                                <option value="manager_toko_eceran"
                                                    {{ old('role') == 'manager_toko_eceran' ? 'selected' : '' }}>
                                                    Manager Toko Eceran
                                                </option>
                                            </select>
                                            @error('role')
                                                <div class="invalid-feedback">
                                                    {{ $message }}
                                                </div>
                                            @enderror
                                        </div>
                                        <small class="text-muted mt-1 d-block">
                                            Tentukan hak akses pengguna
                                        </small>
                                    </div>

                                    <!-- Jenis Toko -->
                                    <div class="col-md-6">
                                        <label for="store_type" class="form-label fw-semibold">
                                            <i class="fas fa-store me-1 text-primary"></i> Jenis Toko
                                            <span class="text-danger">*</span>
                                        </label>
                                        <div class="input-group">
                                            <span class="input-group-text bg-light border-end-0">
                                                <i class="fas fa-building text-muted"></i>
                                            </span>
                                            <select name="jenis_toko" id="store_type"
                                                class="form-select border-start-0 @error('jenis_toko') is-invalid @enderror"
                                                required>
                                                <option value="">Pilih Jenis Toko</option>
                                                <option value="grosir"
                                                    {{ old('jenis_toko') == 'grosir' ? 'selected' : '' }}>
                                                    Toko Grosir
                                                </option>
                                                <option value="eceran"
                                                    {{ old('jenis_toko') == 'eceran' ? 'selected' : '' }}>
                                                    Toko Eceran
                                                </option>
                                            </select>
                                            @error('jenis_toko')
                                                <div class="invalid-feedback">
                                                    {{ $message }}
                                                </div>
                                            @enderror
                                        </div>
                                        <small class="text-muted mt-1 d-block">
                                            Pilih jenis toko tempat pengguna bekerja
                                        </small>
                                    </div>

                                    <!-- Nomor Telepon -->
                                    <div class="col-md-6">
                                        <label for="phone" class="form-label fw-semibold">
                                            <i class="fas fa-phone me-1 text-primary"></i> Nomor Telepon
                                        </label>
                                        <div class="input-group">
                                            <span class="input-group-text bg-light border-end-0">
                                                <i class="fas fa-phone-alt text-muted"></i>
                                            </span>
                                            <input type="tel" name="phone" id="phone"
                                                class="form-control border-start-0 @error('phone') is-invalid @enderror"
                                                value="{{ old('phone') }}" placeholder="0812 3456 7890">
                                            @error('phone')
                                                <div class="invalid-feedback">
                                                    {{ $message }}
                                                </div>
                                            @enderror
                                        </div>
                                        <small class="text-muted mt-1 d-block">
                                            Opsional, untuk keperluan kontak
                                        </small>
                                    </div>

                                    <!-- Alamat -->
                                    <div class="col-md-6">
                                        <label for="address" class="form-label fw-semibold">
                                            <i class="fas fa-map-marker-alt me-1 text-primary"></i> Alamat
                                        </label>
                                        <div class="input-group">
                                            <span class="input-group-text bg-light border-end-0 align-items-start pt-3">
                                                <i class="fas fa-home text-muted"></i>
                                            </span>
                                            <textarea name="address" id="address" class="form-control border-start-0 @error('address') is-invalid @enderror"
                                                rows="1" placeholder="Masukkan alamat lengkap">{{ old('address') }}</textarea>
                                            @error('address')
                                                <div class="invalid-feedback">
                                                    {{ $message }}
                                                </div>
                                            @enderror
                                        </div>
                                        <small class="text-muted mt-1 d-block">
                                            Opsional, alamat tempat tinggal
                                        </small>
                                    </div>

                                    <!-- Status Aktif -->
                                    <div class="col-md-6">
                                        <label class="form-label fw-semibold">
                                            <i class="fas fa-toggle-on me-1 text-primary"></i> Status Akun
                                        </label>
                                        <div class="mt-2">
                                            <div class="form-check form-switch">
                                                <input type="hidden" name="is_active" value="0">
                                                <input type="checkbox" name="is_active" id="is_active"
                                                    class="form-check-input" value="1"
                                                    {{ old('is_active', true) ? 'checked' : '' }} role="switch">
                                                <label class="form-check-label fw-medium" for="is_active">
                                                    Aktifkan akun pengguna
                                                </label>
                                            </div>
                                            <small class="text-muted d-block mt-1">
                                                Pengguna dapat login jika status aktif
                                            </small>
                                        </div>
                                    </div>
                                </div>

                                <!-- Password Requirements -->
                                <div class="alert alert-info border-info border-opacity-25 mt-4">
                                    <div class="d-flex">
                                        <i class="fas fa-info-circle mt-1 me-3"></i>
                                        <div>
                                            <strong class="d-block mb-2">Persyaratan Password</strong>
                                            <ul class="mb-0 ps-3">
                                                <li>Minimal 8 karakter</li>
                                                <li>Disarankan kombinasi huruf besar, kecil, angka, dan simbol</li>
                                                <li>Jangan gunakan password yang mudah ditebak</li>
                                            </ul>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Form Footer -->
                            <div class="card-footer bg-white border-top py-3">
                                <div class="d-flex justify-content-between align-items-center">
                                    <a href="{{ route('users.index') }}" class="btn btn-outline-secondary">
                                        <i class="fas fa-arrow-left me-1"></i> Kembali
                                    </a>
                                    <div class="d-flex gap-2">
                                        <button type="reset" class="btn btn-outline-danger">
                                            <i class="fas fa-redo me-1"></i> Reset
                                        </button>
                                        <button type="submit" class="btn btn-primary" id="submit-btn">
                                            <i class="fas fa-save me-1"></i> Simpan Pengguna
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>

                <!-- Sidebar - Role Information -->
                <div class="col-lg-4">
                    <div class="sticky-top" style="top: 20px;">
                        <!-- Role Information -->
                        <div class="card border-0 shadow-sm mb-4">
                            <div class="card-header bg-white border-0 py-3">
                                <h5 class="fw-semibold mb-0">
                                    <i class="fas fa-info-circle me-2 text-primary"></i> Informasi Peran
                                </h5>
                            </div>
                            <div class="card-body">
                                <div class="mb-3">
                                    <h6 class="fw-semibold mb-2 text-primary">
                                        <i class="fas fa-crown me-1"></i> Owner
                                    </h6>
                                    <p class="text-muted small mb-0">
                                        Akses penuh ke semua fitur sistem. Dapat menambah, mengedit, dan menghapus data.
                                    </p>
                                </div>

                                <div class="mb-3">
                                    <h6 class="fw-semibold mb-2 text-primary">
                                        <i class="fas fa-cash-register me-1"></i> Kasir
                                    </h6>
                                    <p class="text-muted small mb-0">
                                        Dapat melakukan transaksi penjualan, melihat riwayat transaksi, dan mencetak struk.
                                    </p>
                                </div>

                                <div class="mb-3">
                                    <h6 class="fw-semibold mb-2 text-primary">
                                        <i class="fas fa-user-tie me-1"></i> Kepala Gudang
                                    </h6>
                                    <p class="text-muted small mb-0">
                                        Mengelola stok barang, penerimaan barang, dan pengelolaan gudang.
                                    </p>
                                </div>

                                <div class="mb-3">
                                    <h6 class="fw-semibold mb-2 text-primary">
                                        <i class="fas fa-truck-loading me-1"></i> Staff Logistik
                                    </h6>
                                    <p class="text-muted small mb-0">
                                        Menangani pengiriman barang, packing, dan koordinasi distribusi.
                                    </p>
                                </div>

                                <div class="mb-3">
                                    <h6 class="fw-semibold mb-2 text-primary">
                                        <i class="fas fa-clipboard-check me-1"></i> Checker Barang
                                    </h6>
                                    <p class="text-muted small mb-0">
                                        Memeriksa kualitas dan kuantitas barang masuk/keluar.
                                    </p>
                                </div>

                                <div class="mb-3">
                                    <h6 class="fw-semibold mb-2 text-primary">
                                        <i class="fas fa-store-alt me-1"></i> Manager Toko Eceran
                                    </h6>
                                    <p class="text-muted small mb-0">
                                        Mengelola operasional toko eceran, staff, dan laporan penjualan.
                                    </p>
                                </div>
                            </div>
                        </div>

                        <!-- Quick Tips -->
                        <div class="card border-0 shadow-sm">
                            <div class="card-header bg-white border-0 py-3">
                                <h5 class="fw-semibold mb-0">
                                    <i class="fas fa-lightbulb me-2 text-primary"></i> Tips Cepat
                                </h5>
                            </div>
                            <div class="card-body">
                                <ul class="list-unstyled mb-0">
                                    <li class="mb-2">
                                        <i class="fas fa-check-circle text-success me-2"></i>
                                        <span class="small">Pastikan email valid dan belum terdaftar</span>
                                    </li>
                                    <li class="mb-2">
                                        <i class="fas fa-check-circle text-success me-2"></i>
                                        <span class="small">Beri password yang kuat dan mudah diingat</span>
                                    </li>
                                    <li class="mb-2">
                                        <i class="fas fa-check-circle text-success me-2"></i>
                                        <span class="small">Pilih role sesuai dengan tugas pengguna</span>
                                    </li>
                                    <li class="mb-2">
                                        <i class="fas fa-check-circle text-success me-2"></i>
                                        <span class="small">Aktifkan akun jika pengguna langsung bekerja</span>
                                    </li>
                                    <li>
                                        <i class="fas fa-check-circle text-success me-2"></i>
                                        <span class="small">Simpan password di tempat yang aman</span>
                                    </li>
                                </ul>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Success Toast -->
    <div class="toast-container position-fixed top-0 end-0 p-3">
        <div id="successToast" class="toast" role="alert" aria-live="assertive" aria-atomic="true">
            <div class="toast-header bg-success text-white">
                <i class="fas fa-check-circle me-2"></i>
                <strong class="me-auto">Berhasil!</strong>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="toast"></button>
            </div>
            <div class="toast-body">
                <span id="toast-message">Pengguna berhasil ditambahkan</span>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Toggle password visibility
            document.querySelectorAll('.toggle-password').forEach(button => {
                button.addEventListener('click', function() {
                    const targetId = this.getAttribute('data-target');
                    const input = document.getElementById(targetId);
                    const icon = this.querySelector('i');

                    if (input.type === 'password') {
                        input.type = 'text';
                        icon.classList.remove('fa-eye');
                        icon.classList.add('fa-eye-slash');
                    } else {
                        input.type = 'password';
                        icon.classList.remove('fa-eye-slash');
                        icon.classList.add('fa-eye');
                    }
                });
            });

            // Password strength indicator
            const passwordInput = document.getElementById('password');
            const strengthBar = document.getElementById('password-strength-bar');
            const strengthText = document.getElementById('password-strength-text');

            if (passwordInput && strengthBar && strengthText) {
                passwordInput.addEventListener('input', function() {
                    const password = this.value;
                    let strength = 0;
                    let text = 'Sangat Lemah';
                    let color = '#dc3545';

                    // Check password criteria
                    if (password.length >= 8) strength += 25;
                    if (/[a-z]/.test(password)) strength += 25;
                    if (/[A-Z]/.test(password)) strength += 25;
                    if (/[0-9]/.test(password)) strength += 15;
                    if (/[^A-Za-z0-9]/.test(password)) strength += 10;

                    // Determine strength level
                    if (strength >= 80) {
                        text = 'Sangat Kuat';
                        color = '#198754';
                    } else if (strength >= 60) {
                        text = 'Kuat';
                        color = '#20c997';
                    } else if (strength >= 40) {
                        text = 'Cukup';
                        color = '#fd7e14';
                    } else if (strength >= 20) {
                        text = 'Lemah';
                        color = '#ffc107';
                    }

                    // Update UI
                    strengthBar.style.width = strength + '%';
                    strengthBar.style.backgroundColor = color;
                    strengthText.textContent = 'Kekuatan password: ' + text;
                    strengthText.style.color = color;
                });
            }

            // Auto-hide alerts after 5 seconds
            setTimeout(() => {
                const alerts = document.querySelectorAll('.alert');
                alerts.forEach(alert => {
                    if (!alert.classList.contains('alert-danger')) {
                        const bsAlert = new bootstrap.Alert(alert);
                        bsAlert.close();
                    }
                });
            }, 5000);

            // Form validation
            const form = document.getElementById('user-form');
            const submitBtn = document.getElementById('submit-btn');

            if (form && submitBtn) {
                form.addEventListener('submit', function(e) {
                    const password = document.getElementById('password').value;
                    const confirmPassword = document.getElementById('password_confirmation').value;

                    // Check password match
                    if (password !== confirmPassword) {
                        e.preventDefault();
                        alert('Password dan konfirmasi password tidak sama!');
                        document.getElementById('password_confirmation').focus();
                        return;
                    }

                    // Check password strength
                    if (password.length < 8) {
                        e.preventDefault();
                        alert('Password minimal 8 karakter!');
                        document.getElementById('password').focus();
                        return;
                    }

                    // Disable submit button to prevent double submission
                    submitBtn.disabled = true;
                    submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin me-1"></i> Menyimpan...';
                });
            }

            // Role and store type relationship
            const roleSelect = document.getElementById('role');
            const storeTypeSelect = document.getElementById('store_type');

            if (roleSelect && storeTypeSelect) {
                // Update store type based on role
                roleSelect.addEventListener('change', function() {
                    const role = this.value;

                    if (role === 'manager_toko_eceran') {
                        storeTypeSelect.value = 'eceran';
                        storeTypeSelect.disabled = true;
                    } else {
                        storeTypeSelect.disabled = false;
                    }
                });

                // Update available roles based on store type
                storeTypeSelect.addEventListener('change', function() {
                    const storeType = this.value;
                    const currentRole = roleSelect.value;

                    // Filter roles based on store type
                    Array.from(roleSelect.options).forEach(option => {
                        if (option.value === '') return;

                        if (storeType === 'eceran') {
                            option.hidden = !['owner', 'kasir', 'manager_toko_eceran'].includes(
                                option.value);
                        } else if (storeType === 'grosir') {
                            option.hidden = !['owner', 'kasir', 'kepala_gudang', 'logistik',
                                'checker_barang'
                            ].includes(option.value);
                        } else {
                            option.hidden = false;
                        }
                    });

                    // Reset role if not available for selected store type
                    if (roleSelect.options[roleSelect.selectedIndex].hidden) {
                        roleSelect.value = '';
                    }
                });

                // Initialize based on current values
                if (storeTypeSelect.value) {
                    storeTypeSelect.dispatchEvent(new Event('change'));
                }
            }

            // Phone number formatting
            const phoneInput = document.getElementById('phone');
            if (phoneInput) {
                phoneInput.addEventListener('input', function(e) {
                    let value = e.target.value.replace(/\D/g, '');

                    if (value.length > 0) {
                        value = value.match(new RegExp('.{1,4}', 'g')).join(' ');
                    }

                    e.target.value = value;
                });
            }

            // Auto-resize textarea
            const addressTextarea = document.getElementById('address');
            if (addressTextarea) {
                addressTextarea.addEventListener('input', function() {
                    this.style.height = 'auto';
                    this.style.height = (this.scrollHeight) + 'px';
                });
            }

            // Initialize tooltips
            const tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
            const tooltipList = tooltipTriggerList.map(function(tooltipTriggerEl) {
                return new bootstrap.Tooltip(tooltipTriggerEl);
            });

            // Show toast if redirected from successful creation
            if (window.location.search.includes('success=true')) {
                const toastEl = document.getElementById('successToast');
                const toast = new bootstrap.Toast(toastEl);
                toast.show();
            }
        });
    </script>
@endpush
