@extends('layouts.app')

@section('content')
    <div class="container-fluid">
        <div class="row mb-4">
            <div class="col-md-12">
                <h1 class="h3 font-weight-bold">📊 Laporan Penjualan</h1>
                <p class="text-muted">Monitor dan analisis data penjualan harian/bulanan</p>
            </div>
        </div>

        <!-- Filter Form -->
        <div class="card shadow-sm mb-4">
            <div class="card-header bg-light">
                <h5 class="mb-0">🔍 Filter Laporan</h5>
            </div>
            <div class="card-body">
                <form method="GET" class="row g-3">
                    <div class="col-md-4">
                        <label class="form-label">Tanggal:</label>
                        <input type="date" name="date" class="form-control" value="{{ request('date') }}">
                    </div>

                    <div class="col-md-4">
                        <label class="form-label">Bulan:</label>
                        <input type="month" name="month" class="form-control" value="{{ request('month') }}">
                    </div>

                    <div class="col-md-4 d-flex align-items-end">
                        <button type="submit" class="btn btn-primary me-2">
                            <i class="fas fa-filter"></i> Filter
                        </button>
                        <a href="{{ route('reports.sales') }}" class="btn btn-outline-secondary">
                            <i class="fas fa-redo"></i> Reset
                        </a>
                    </div>

                    <div class="col-12">
                        <small class="text-muted">* Kosongkan keduanya untuk melihat semua data</small>
                    </div>
                </form>
            </div>
        </div>

        <!-- Summary Cards -->
        <div class="row mb-4">
            <div class="col-md-4">
                <div class="card border-left-primary shadow-sm h-100">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <h6 class="text-uppercase text-muted mb-1">Total Omzet</h6>
                                <h3 class="font-weight-bold text-primary">Rp {{ number_format($total) }}</h3>
                            </div>
                            <div class="icon-circle bg-primary">
                                <i class="fas fa-money-bill-wave text-white"></i>
                            </div>
                        </div>
                        <div class="mt-2">
                            <span class="text-sm text-success">
                                <i class="fas fa-chart-line"></i> {{ count($transactions) }} Transaksi
                            </span>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-md-4">
                <div class="card border-left-success shadow-sm h-100">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <h6 class="text-uppercase text-muted mb-1">Rata-rata/Transaksi</h6>
                                <h3 class="font-weight-bold text-success">Rp
                                    {{ number_format(count($transactions) > 0 ? $total / count($transactions) : 0) }}</h3>
                            </div>
                            <div class="icon-circle bg-success">
                                <i class="fas fa-calculator text-white"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-md-4">
                <div class="card border-left-info shadow-sm h-100">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <h6 class="text-uppercase text-muted mb-1">Periode</h6>
                                <h3 class="font-weight-bold text-info">
                                    @if (request('date'))
                                        {{ \Carbon\Carbon::parse(request('date'))->format('d M Y') }}
                                    @elseif(request('month'))
                                        {{ \Carbon\Carbon::parse(request('month'))->format('M Y') }}
                                    @else
                                        Semua Waktu
                                    @endif
                                </h3>
                            </div>
                            <div class="icon-circle bg-info">
                                <i class="fas fa-calendar-alt text-white"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Data Table -->
        <div class="card shadow-sm">
            <div class="card-header bg-light d-flex justify-content-between align-items-center">
                <h5 class="mb-0">📋 Detail Transaksi</h5>
                <div>
                    @if (request('date') || request('month'))
                        <a href="{{ route('reports.export', request()->all()) }}" class="btn btn-success btn-sm">
                            <i class="fas fa-file-excel"></i> Export Excel
                        </a>
                    @endif
                    <a href="{{ route('reports.print', request()->all()) }}" class="btn btn-outline-primary btn-sm">
                        <i class="fas fa-print"></i> Print
                    </a>
                </div>
            </div>

            <div class="card-body">
                @if ($transactions->count() > 0)
                    <div class="table-responsive">
                        <table class="table table-hover table-striped">
                            <thead class="table-light">
                                <tr>
                                    <th>No</th>
                                    <th>Tanggal</th>
                                    <th>Invoice</th>
                                    <th>Kasir</th>
                                    <th>Total Transaksi</th>
                                    <th>Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($transactions as $index => $t)
                                    <tr>
                                        <td>{{ $loop->iteration }}</td>
                                        <td>
                                            <span class="badge bg-secondary">
                                                {{ $t->created_at->format('d/m/Y') }}
                                            </span>
                                            <br>
                                            <small class="text-muted">{{ $t->created_at->format('H:i') }}</small>
                                        </td>
                                        <td>
                                            <span
                                                class="font-monospace">#{{ str_pad($t->id, 6, '0', STR_PAD_LEFT) }}</span>
                                        </td>
                                        <td>
                                            <div class="d-flex align-items-center">
                                                <div class="avatar-sm me-2">
                                                    <div class="avatar-title bg-light rounded-circle text-primary">
                                                        {{ substr($t->user->name ?? 'K', 0, 1) }}
                                                    </div>
                                                </div>
                                                <span>{{ $t->user->name ?? 'Kasir Tidak Diketahui' }}</span>
                                            </div>
                                        </td>
                                        <td>
                                            <span class="fw-bold text-primary">Rp {{ number_format($t->total) }}</span>
                                        </td>
                                        <td>
                                            <button type="button" class="btn btn-sm btn-outline-info"
                                                data-bs-toggle="modal" data-bs-target="#detailModal{{ $t->id }}">
                                                <i class="fas fa-eye"></i> Detail
                                            </button>
                                        </td>
                                    </tr>

                                    <!-- Detail Modal -->
                                    <div class="modal fade" id="detailModal{{ $t->id }}" tabindex="-1">
                                        <div class="modal-dialog">
                                            <div class="modal-content">
                                                <div class="modal-header">
                                                    <h5 class="modal-title">Detail Transaksi #{{ $t->id }}</h5>
                                                    <button type="button" class="btn-close"
                                                        data-bs-dismiss="modal"></button>
                                                </div>
                                                <div class="modal-body">
                                                    <!-- Detail transaksi bisa ditambahkan di sini -->
                                                    <p>Detail item transaksi...</p>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                @endforeach
                            </tbody>
                            <tfoot class="table-light">
                                <tr>
                                    <th colspan="4" class="text-end">Total:</th>
                                    <th class="text-primary">Rp {{ number_format($total) }}</th>
                                    <th></th>
                                </tr>
                            </tfoot>
                        </table>
                    </div>

                    <!-- Pagination -->
                    <div class="d-flex justify-content-between align-items-center mt-3">
                        <div class="text-muted">
                            Menampilkan {{ $transactions->firstItem() ?? 0 }} - {{ $transactions->lastItem() ?? 0 }} dari
                            {{ $transactions->total() }} transaksi
                        </div>
                        <div>
                            {{ $transactions->links() }}
                        </div>
                    </div>
                @else
                    <div class="text-center py-5">
                        <i class="fas fa-chart-bar fa-4x text-muted mb-3"></i>
                        <h4 class="text-muted">Data Tidak Ditemukan</h4>
                        <p class="text-muted">Tidak ada transaksi pada periode yang dipilih.</p>
                    </div>
                @endif
            </div>
        </div>
    </div>

    <style>
        .icon-circle {
            width: 50px;
            height: 50px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.25rem;
        }

        .avatar-sm {
            width: 30px;
            height: 30px;
        }

        .table th {
            font-weight: 600;
            border-top: none;
        }
    </style>
@endsection
