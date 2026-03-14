@extends('layouts.app')

@section('title', 'Detail Pengguna')

@section('content')
<div class="container-fluid px-4">

    {{-- ================= PAGE HEADER ================= --}}
    <div class="page-header mb-4">
        <div class="row align-items-center">
            <div class="col">
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb mb-2">
                        <li class="breadcrumb-item">
                            <a href="{{ route('dashboard') }}" class="text-decoration-none text-muted">Dashboard</a>
                        </li>
                        <li class="breadcrumb-item">
                            <a href="{{ route('users.index') }}" class="text-decoration-none text-muted">Pengguna</a>
                        </li>
                        <li class="breadcrumb-item active text-primary">Detail Pengguna</li>
                    </ol>
                </nav>

                <div class="d-flex align-items-center">
                    <div class="icon-wrapper bg-primary-soft rounded-circle p-3 me-3">
                        <i class="fas fa-user-circle text-primary fs-4"></i>
                    </div>
                    <div>
                        <h1 class="h2 mb-1">Detail Pengguna</h1>
                        <p class="text-muted mb-0">Informasi lengkap pengguna sistem</p>
                    </div>
                </div>
            </div>

            <div class="col-auto">
                <div class="d-flex gap-2">
                    <a href="{{ route('users.index') }}" class="btn btn-outline-secondary">
                        <i class="fas fa-arrow-left me-2"></i>Kembali
                    </a>
                    <a href="{{ route('users.edit', $user->id) }}" class="btn btn-warning">
                        <i class="fas fa-edit me-2"></i>Edit
                    </a>
                </div>
            </div>
        </div>
    </div>

    <div class="row">

        {{-- ================= LEFT COLUMN ================= --}}
        <div class="col-lg-8">
            <div class="row g-4">

                {{-- ===== PROFILE CARD ===== --}}
                <div class="col-12">
                    <div class="card border-0 shadow-sm">
                        <div class="card-header bg-white border-0">
                            <h6 class="fw-semibold mb-0">
                                <i class="fas fa-id-card me-2 text-primary"></i>Profil Pengguna
                            </h6>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-lg-3 text-center mb-4 mb-lg-0">
                                    <div class="avatar-wrapper mx-auto" style="width:160px">
                                        @if($user->image && file_exists(public_path('storage/'.$user->image)))
                                            <img src="{{ asset('storage/'.$user->image) }}"
                                                 class="rounded-circle shadow border"
                                                 width="160" height="160">
                                        @else
                                            <div class="avatar-placeholder rounded-circle">
                                                {{ strtoupper(substr($user->name,0,1)) }}
                                            </div>
                                        @endif
                                        <span class="status-badge bg-{{ $user->is_active?'success':'danger' }}">
                                            {{ $user->is_active?'Aktif':'Nonaktif' }}
                                        </span>
                                    </div>
                                </div>

                                <div class="col-lg-9">
                                    <div class="row g-3">
                                        <div class="col-md-6">
                                            <label class="form-label">Nama</label>
                                            <div class="fw-semibold">{{ $user->name }}</div>
                                        </div>
                                        <div class="col-md-6">
                                            <label class="form-label">Email</label>
                                            <div class="fw-semibold">{{ $user->email }}</div>
                                        </div>
                                        <div class="col-md-6">
                                            <label class="form-label">Telepon</label>
                                            <div>{{ $user->phone ?? '-' }}</div>
                                        </div>
                                        <div class="col-md-6">
                                            <label class="form-label">Role</label>
                                            <div class="fw-semibold text-capitalize">{{ $user->role }}</div>
                                        </div>
                                        <div class="col-md-12">
                                            <label class="form-label">Alamat</label>
                                            <div>{{ $user->address ?? '-' }}</div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- ===== ACTIVITY ===== --}}
                <div class="col-12">
                    <div class="card border-0 shadow-sm">
                        <div class="card-header bg-white border-0">
                            <h6 class="fw-semibold mb-0">
                                <i class="fas fa-history me-2 text-primary"></i>Aktivitas
                            </h6>
                        </div>
                        <div class="card-body">
                            <ul class="timeline">
                                <li>
                                    <strong>Bergabung</strong>
                                    <span>{{ $user->created_at->format('d M Y H:i') }}</span>
                                </li>
                                @if($user->last_login_at)
                                <li>
                                    <strong>Login Terakhir</strong>
                                    <span>{{ $user->last_login_at->format('d M Y H:i') }}</span>
                                </li>
                                @endif
                            </ul>
                        </div>
                    </div>
                </div>

            </div>
        </div>

        {{-- ================= RIGHT COLUMN ================= --}}
        <div class="col-lg-4">
            <div class="sticky-top" style="top:20px">

                {{-- ===== QUICK ACTIONS (FINAL) ===== --}}
                <div class="card border-0 shadow-sm mb-4">
                    <div class="card-header bg-white border-0">
                        <h6 class="fw-semibold mb-0">
                            <i class="fas fa-bolt me-2 text-primary"></i>Aksi Cepat
                        </h6>
                    </div>

                    <div class="card-body">
                        <div class="actions-grid">

                            @if($user->id !== auth()->id())
                            <form action="{{ route('users.toggle-status',$user->id) }}" method="POST">
                                @csrf
                                <button type="submit" class="action-card action-danger">
                                    <div class="action-icon">
                                        <i class="fas fa-ban"></i>
                                    </div>
                                    <div>
                                        <strong>{{ $user->is_active?'Nonaktifkan':'Aktifkan' }}</strong>
                                        <div class="text-muted small">Status akun</div>
                                    </div>
                                </button>
                            </form>
                            @endif

                            <a href="mailto:{{ $user->email }}" class="action-card action-primary">
                                <div class="action-icon"><i class="fas fa-envelope"></i></div>
                                <div>
                                    <strong>Email</strong>
                                    <div class="text-muted small">Kirim pesan</div>
                                </div>
                            </a>

                            @if($user->phone)
                            <a href="tel:{{ $user->phone }}" class="action-card action-success">
                                <div class="action-icon"><i class="fas fa-phone"></i></div>
                                <div>
                                    <strong>Telepon</strong>
                                    <div class="text-muted small">Hubungi</div>
                                </div>
                            </a>
                            @endif

                            <button onclick="window.print()" class="action-card action-dark">
                                <div class="action-icon"><i class="fas fa-print"></i></div>
                                <div>
                                    <strong>Cetak</strong>
                                    <div class="text-muted small">Detail user</div>
                                </div>
                            </button>

                        </div>
                    </div>
                </div>

            </div>
        </div>

    </div>
</div>
@endsection

@push('styles')
<style>
:root{
    --primary:#4f46e5;
    --success:#10b981;
    --danger:#ef4444;
    --dark:#111827;
    --gray:#6b7280;
}

/* Avatar */
.avatar-placeholder{
    width:160px;height:160px;
    display:flex;align-items:center;justify-content:center;
    border-radius:50%;
    background:linear-gradient(135deg,#667eea,#764ba2);
    color:#fff;font-size:3rem;font-weight:700;
}
.status-badge{
    position:absolute;bottom:0;right:0;
    padding:4px 10px;border-radius:999px;
    font-size:.7rem;color:#fff;
}

/* Timeline */
.timeline{padding-left:0;list-style:none}
.timeline li{margin-bottom:10px;font-size:.9rem}

/* ===== QUICK ACTIONS STYLE ===== */
.actions-grid{
    display:grid;
    grid-template-columns:repeat(2,1fr);
    gap:14px;
}
.action-card{
    display:flex;align-items:center;gap:12px;
    padding:14px;border-radius:14px;
    background:#fff;border:1px solid #e5e7eb;
    transition:.25s;text-decoration:none;
    width:100%;cursor:pointer;
}
.action-card:hover{
    transform:translateY(-2px);
    box-shadow:0 10px 25px rgba(0,0,0,.08);
}
.action-icon{
    width:44px;height:44px;border-radius:12px;
    display:flex;align-items:center;justify-content:center;
    color:#fff;font-size:18px;
}
.action-primary .action-icon{background:var(--primary)}
.action-success .action-icon{background:var(--success)}
.action-danger  .action-icon{background:var(--danger)}
.action-dark    .action-icon{background:var(--dark)}

@media(max-width:576px){
    .actions-grid{grid-template-columns:1fr}
}
</style>
@endpush
