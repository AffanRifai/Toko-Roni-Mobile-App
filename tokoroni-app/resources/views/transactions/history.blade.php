@extends('layouts.app')

@section('title', 'Riwayat Transaksi')

<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css"
    integrity="sha512-iBBXm8fW90+nuLcSKlbmrPcLa0OT92xO1BIsZ+ywDWZCvqsWgccV3gFoRBv0z+8dLJgyAHIhR35VZc2oM/gI1w=="
    crossorigin="anonymous" referrerpolicy="no-referrer" />
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.3/font/bootstrap-icons.css">
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css"
    integrity="sha384-GLhlTQ8iRABdZLl6O3oVMWSktQOp6b7In1Zl3/Jr59b6EGGoI1aFkw7cmDA6j6gD" crossorigin="anonymous">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css"
    integrity="sha512-B4qRvQcF7qBAPzrmnIfiKbAxEYJxdA/H3HRIcPr9MAbdKh3zNu3sLfWDogmsaBqNKdT/l/BXZsGd689mg=="
    crossorigin="anonymous" referrerpolicy="no-referrer" />
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css">
    <script src="https://cdn.jsdelivr.net/npm/@tailwindcss/browser@4"></script>

@section('content')
    <div class="container-fluid px-3 px-md-4">

        {{-- Header --}}
        <div class="d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center mb-4">
            <div class="mb-3 mb-md-0">
                <div class="d-flex align-items-center gap-3">
                    <div class="icon-wrapper bg-primary bg-opacity-10 rounded-circle p-3">
                        <i class="fas fa-history text-primary fs-4"></i>
                    </div>
                    <div>
                        <h1 class="fw-bold mb-1">Riwayat Transaksi</h1>
                        <p class="text-muted mb-0">Daftar lengkap transaksi yang telah tercatat</p>
                    </div>
                </div>
            </div>

            <div class="d-flex gap-2">
                <div class="dropdown">
                    <button class="btn btn-outline-primary dropdown-toggle" type="button" data-bs-toggle="dropdown">
                        <i class="fas fa-filter me-2"></i>
                        Filter
                    </button>
                    <ul class="dropdown-menu">
                        <li><a class="dropdown-item" href="{{ request()->fullUrlWithQuery(['filter' => 'today']) }}">Hari
                                Ini</a></li>
                        <li><a class="dropdown-item" href="{{ request()->fullUrlWithQuery(['filter' => 'week']) }}">Minggu
                                Ini</a></li>
                        <li><a class="dropdown-item" href="{{ request()->fullUrlWithQuery(['filter' => 'month']) }}">Bulan
                                Ini</a></li>
                        <li><a class="dropdown-item" href="{{ request()->fullUrlWithQuery(['filter' => 'all']) }}">Semua</a>
                        </li>
                    </ul>
                </div>

                <button class="btn btn-primary" onclick="window.print()">
                    <i class="fas fa-print me-2"></i>
                    Cetak
                </button>
            </div>
        </div>

        {{-- Summary Cards --}}
        <div class="row g-3 mb-4">
            <div class="col-md-3">
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-body p-3 p-md-4">
                        <div class="d-flex align-items-center">
                            <div class="icon-wrapper bg-primary bg-opacity-10 rounded-circle p-2 me-3">
                                <i class="fas fa-shopping-cart text-primary"></i>
                            </div>
                            <div>
                                <h6 class="text-muted mb-1">Total Transaksi</h6>
                                <h4 class="fw-bold mb-0">{{ $transactions->total() }}</h4>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-md-3">
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-body p-3 p-md-4">
                        <div class="d-flex align-items-center">
                            <div class="icon-wrapper bg-success bg-opacity-10 rounded-circle p-2 me-3">
                                <i class="fas fa-money-bill-wave text-success"></i>
                            </div>
                            <div>
                                <h6 class="text-muted mb-1">Total Pendapatan</h6>
                                <h4 class="fw-bold mb-0">Rp {{ number_format($totalRevenue, 0, ',', '.') }}</h4>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-md-3">
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-body p-3 p-md-4">
                        <div class="d-flex align-items-center">
                            <div class="icon-wrapper bg-info bg-opacity-10 rounded-circle p-2 me-3">
                                <i class="fas fa-box text-info"></i>
                            </div>
                            <div>
                                <h6 class="text-muted mb-1">Rata-rata/Transaksi</h6>
                                <h4 class="fw-bold mb-0">Rp {{ number_format($averageTransaction, 0, ',', '.') }}</h4>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-md-3">
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-body p-3 p-md-4">
                        <div class="d-flex align-items-center">
                            <div class="icon-wrapper bg-warning bg-opacity-10 rounded-circle p-2 me-3">
                                <i class="fas fa-calendar-alt text-warning"></i>
                            </div>
                            <div>
                                <h6 class="text-muted mb-1">Transaksi Hari Ini</h6>
                                <h4 class="fw-bold mb-0">{{ $todayTransactions }}</h4>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Search and Filter --}}
        <div class="card border-0 shadow-sm mb-4">
            <div class="card-body p-3 p-md-4">
                <div class="row g-3">
                    <div class="col-md-8">
                        <div class="input-group">
                            <span class="input-group-text bg-transparent border-end-0">
                                <i class="fas fa-search text-muted"></i>
                            </span>
                            <input type="text" class="form-control border-start-0" id="searchInput"
                                placeholder="Cari berdasarkan kasir, invoice, atau tanggal..." onkeyup="filterTable()">
                            <button class="btn btn-outline-secondary" type="button" onclick="clearSearch()">
                                <i class="fas fa-times"></i>
                            </button>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="input-group">
                            <span class="input-group-text bg-transparent border-end-0">
                                <i class="fas fa-calendar text-muted"></i>
                            </span>
                            <input type="date" class="form-control border-start-0" id="dateFilter"
                                onchange="filterByDate()">
                            <button class="btn btn-outline-secondary" type="button" onclick="clearDateFilter()">
                                <i class="fas fa-times"></i>
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Transactions Table --}}
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-transparent border-bottom py-3">
                <div class="d-flex justify-content-between align-items-center">
                    <h5 class="fw-bold mb-0">
                        <i class="fas fa-list me-2"></i>
                        Daftar Transaksi
                    </h5>
                    <div class="text-muted">
                        Menampilkan {{ $transactions->count() }} dari {{ $transactions->total() }} transaksi
                    </div>
                </div>
            </div>

            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0" id="transactionsTable">
                        <thead class="table-light">
                            <tr>
                                <th class="ps-4" style="width: 5%">#</th>
                                <th style="width: 15%">Invoice</th>
                                <th style="width: 20%">Tanggal</th>
                                <th style="width: 15%">Kasir</th>
                                <th class="text-center" style="width: 15%">Status</th>
                                <th class="text-center" style="width: 15%">Metode</th>
                                <th class="text-end pe-4" style="width: 15%">Total</th>
                            </tr>
                        </thead>

                        <tbody>
                            @forelse ($transactions as $t)
                                <tr class="transaction-row" data-invoice="{{ $t->invoice_number }}"
                                    data-cashier="{{ strtolower($t->user->name ?? '') }}"
                                    data-date="{{ $t->created_at->format('Y-m-d') }}" data-status="{{ $t->status }}"
                                    onclick="window.location.href='{{ route('transactions.show', $t) }}'"
                                    style="cursor: pointer;">
                                    <td class="ps-4">
                                        <span
                                            class="text-muted">{{ ($transactions->currentPage() - 1) * $transactions->perPage() + $loop->iteration }}</span>
                                    </td>
                                    <td>
                                        <div class="d-flex align-items-center">
                                            <div class="invoice-icon bg-primary bg-opacity-10 rounded-circle p-2 me-3">
                                                <i class="fas fa-receipt text-primary"></i>
                                            </div>
                                            <div>
                                                <div class="fw-semibold">{{ $t->invoice_number }}</div>
                                                <small class="text-muted">ID: {{ $t->id }}</small>
                                            </div>
                                        </div>
                                    </td>
                                    <td>
                                        <div class="d-flex flex-column">
                                            <span class="fw-medium">{{ $t->created_at->format('d M Y') }}</span>
                                            <small class="text-muted">{{ $t->created_at->format('H:i') }}</small>
                                        </div>
                                    </td>
                                    <td>
                                        <div class="d-flex align-items-center">
                                            <div class="user-avatar bg-secondary bg-opacity-10 rounded-circle p-2 me-2">
                                                <i class="fas fa-user text-secondary"></i>
                                            </div>
                                            <span class="fw-medium">{{ $t->user->name ?? '-' }}</span>
                                        </div>
                                    </td>
                                    <td class="text-center">
                                        @php
                                            $statusColors = [
                                                'completed' => 'success',
                                                'pending' => 'warning',
                                                'cancelled' => 'danger',
                                            ];
                                            $statusIcons = [
                                                'completed' => 'check-circle',
                                                'pending' => 'clock',
                                                'cancelled' => 'times-circle',
                                            ];
                                        @endphp
                                        <span
                                            class="badge bg-{{ $statusColors[$t->status] ?? 'secondary' }}-subtle
                                           text-{{ $statusColors[$t->status] ?? 'secondary' }}
                                           border border-{{ $statusColors[$t->status] ?? 'secondary' }}-subtle
                                           py-2 px-3">
                                            <i class="fas fa-{{ $statusIcons[$t->status] ?? 'circle' }} me-1"></i>
                                            {{ ucfirst($t->status) }}
                                        </span>
                                    </td>
                                    <td class="text-center">
                                        @php
                                            $paymentColors = [
                                                'cash' => 'success',
                                                'debit_card' => 'primary',
                                                'credit_card' => 'danger',
                                                'e_wallet' => 'info',
                                                'transfer' => 'warning',
                                            ];
                                            $paymentIcons = [
                                                'cash' => 'money-bill-wave',
                                                'debit_card' => 'credit-card',
                                                'credit_card' => 'credit-card',
                                                'e_wallet' => 'mobile-alt',
                                                'transfer' => 'university',
                                            ];
                                        @endphp
                                        <span
                                            class="badge bg-{{ $paymentColors[$t->payment_method] ?? 'secondary' }}-subtle
                                           text-{{ $paymentColors[$t->payment_method] ?? 'secondary' }}
                                           border border-{{ $paymentColors[$t->payment_method] ?? 'secondary' }}-subtle
                                           py-1 px-2">
                                            <i
                                                class="fas fa-{{ $paymentIcons[$t->payment_method] ?? 'credit-card' }} me-1"></i>
                                            {{ ucfirst(str_replace('_', ' ', $t->payment_method)) }}
                                        </span>
                                         @if($t->payment_method === 'credit_card')
                                            <div class="text-danger mt-1 fw-bold">⚠ Hutang</div>
                                        @endif
                                    </td>
                                    <td class="text-end pe-4">
                                        <div class="d-flex flex-column align-items-end">
                                            <span class="fw-bold fs-5">Rp
                                                {{ number_format($t->total_amount, 0, ',', '.') }}</span>
                                            <small class="text-muted">{{ $t->items->count() }} item</small>
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="7" class="text-center py-5">
                                        <div class="py-4">
                                            <div class="empty-state-icon mb-3">
                                                <i class="fas fa-shopping-cart fa-3x text-muted"></i>
                                            </div>
                                            <h5 class="text-muted mb-2">Belum ada transaksi</h5>
                                            <p class="text-muted mb-3">Mulai lakukan transaksi pertama Anda</p>
                                            <a href="{{ route('transactions.create') }}" class="btn btn-primary">
                                                <i class="fas fa-plus-circle me-2"></i>
                                                Buat Transaksi Baru
                                            </a>
                                        </div>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                @if ($transactions->hasPages())
                    <div class="card-footer bg-transparent border-top py-3">
                        <div class="d-flex justify-content-between align-items-center">
                            <div class="text-muted">
                                Menampilkan {{ $transactions->firstItem() }} - {{ $transactions->lastItem() }} dari
                                {{ $transactions->total() }} transaksi
                            </div>
                            <nav>
                                {{ $transactions->links() }}
                            </nav>
                        </div>
                    </div>
                @endif
            </div>
        </div>

    </div>

    <style>
        /* Base Styles */
        :root {
            --primary: #4361ee;
            --primary-light: rgba(67, 97, 238, 0.1);
            --secondary: #6c757d;
            --success: #28a745;
            --success-light: rgba(40, 167, 69, 0.1);
            --info: #17a2b8;
            --warning: #ffc107;
            --danger: #dc3545;
            --light: #f8f9fa;
            --dark: #343a40;
            --border-radius: 12px;
            --shadow: 0 2px 10px rgba(0, 0, 0, 0.08);
        }

        body {
            background-color: #f8fafc;
        }

        /* Card Styles */
        .card {
            border-radius: var(--border-radius);
            border: none;
            transition: transform 0.3s ease, box-shadow 0.3s ease;
        }

        .card:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 20px rgba(0, 0, 0, 0.12);
        }

        /* Icon Wrappers */
        .icon-wrapper {
            width: 48px;
            height: 48px;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .invoice-icon,
        .user-avatar {
            width: 40px;
            height: 40px;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        /* Table Styles */
        .table {
            --bs-table-bg: transparent;
            margin-bottom: 0;
        }

        .table thead th {
            border-bottom: 2px solid rgba(0, 0, 0, 0.05);
            font-weight: 600;
            color: var(--dark);
            text-transform: uppercase;
            font-size: 0.8rem;
            letter-spacing: 0.5px;
            padding: 1rem 0.75rem;
        }

        .table tbody tr {
            transition: background-color 0.2s ease;
            border-bottom: 1px solid rgba(0, 0, 0, 0.05);
        }

        .table tbody tr:hover {
            background-color: rgba(67, 97, 238, 0.05);
        }

        .table tbody tr:last-child {
            border-bottom: none;
        }

        .table tbody td {
            padding: 1rem 0.75rem;
            vertical-align: middle;
        }

        /* Badge Styles */
        .badge {
            font-weight: 500;
            border-radius: 6px;
        }

        /* Input Styles */
        .input-group .form-control:focus,
        .input-group .form-control:focus-within {
            border-color: var(--primary);
            box-shadow: 0 0 0 0.25rem rgba(67, 97, 238, 0.25);
        }

        /* Empty State */
        .empty-state-icon {
            opacity: 0.5;
        }

        /* Pagination */
        .pagination {
            margin-bottom: 0;
        }

        .page-link {
            border: none;
            color: var(--primary);
            padding: 0.5rem 0.75rem;
            margin: 0 0.25rem;
            border-radius: 6px !important;
        }

        .page-link:hover {
            background-color: var(--primary-light);
            color: var(--primary);
        }

        .page-item.active .page-link {
            background-color: var(--primary);
            border-color: var(--primary);
        }

        /* Responsive */
        @media (max-width: 768px) {
            .container-fluid {
                padding-left: 1rem;
                padding-right: 1rem;
            }

            .table-responsive {
                font-size: 0.85rem;
            }

            .table thead th,
            .table tbody td {
                padding: 0.75rem 0.5rem;
            }

            .icon-wrapper {
                width: 40px;
                height: 40px;
            }

            .card-body {
                padding: 1rem !important;
            }
        }

        /* Animation */
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

        .card {
            animation: fadeIn 0.5s ease-out;
        }

        /* Print Styles */
        @media print {

            .card-header,
            .card-footer,
            .btn,
            .dropdown,
            .input-group,
            .summary-cards {
                display: none !important;
            }

            .card {
                border: none !important;
                box-shadow: none !important;
            }

            body {
                background: white !important;
            }

            .table {
                font-size: 12px;
            }
        }

        /* Custom Scrollbar */
        ::-webkit-scrollbar {
            width: 6px;
        }

        ::-webkit-scrollbar-track {
            background: #f1f1f1;
            border-radius: 3px;
        }

        ::-webkit-scrollbar-thumb {
            background: #c1c1c1;
            border-radius: 3px;
        }

        ::-webkit-scrollbar-thumb:hover {
            background: #a8a8a8;
        }
    </style>

    <script>
        // Filter table by search input
        function filterTable() {
            const input = document.getElementById('searchInput');
            const filter = input.value.toLowerCase();
            const rows = document.querySelectorAll('.transaction-row');

            rows.forEach(row => {
                const invoice = row.getAttribute('data-invoice') || '';
                const cashier = row.getAttribute('data-cashier') || '';
                const text = row.textContent.toLowerCase();

                if (invoice.includes(filter) || cashier.includes(filter) || text.includes(filter)) {
                    row.style.display = '';
                } else {
                    row.style.display = 'none';
                }
            });
        }

        // Filter by date
        function filterByDate() {
            const date = document.getElementById('dateFilter').value;
            const rows = document.querySelectorAll('.transaction-row');

            rows.forEach(row => {
                const rowDate = row.getAttribute('data-date') || '';

                if (!date || rowDate === date) {
                    row.style.display = '';
                } else {
                    row.style.display = 'none';
                }
            });
        }

        // Clear search
        function clearSearch() {
            document.getElementById('searchInput').value = '';
            filterTable();
        }

        // Clear date filter
        function clearDateFilter() {
            document.getElementById('dateFilter').value = '';
            filterByDate();
        }

        // Add row click effect
        document.addEventListener('DOMContentLoaded', function() {
            const rows = document.querySelectorAll('.transaction-row');

            rows.forEach(row => {
                row.addEventListener('click', function(e) {
                    // Don't trigger if clicking on a link or button inside
                    if (e.target.tagName === 'A' || e.target.tagName === 'BUTTON' || e.target
                        .closest('a') || e.target.closest('button')) {
                        return;
                    }

                    // Add visual feedback
                    this.style.backgroundColor = 'rgba(67, 97, 238, 0.1)';
                    setTimeout(() => {
                        this.style.backgroundColor = '';
                    }, 300);
                });
            });

            // Highlight today's transactions
            const today = new Date().toISOString().split('T')[0];
            rows.forEach(row => {
                if (row.getAttribute('data-date') === today) {
                    row.classList.add('today-transaction');
                }
            });
        });
    </script>

    @php
        // Pastikan untuk menambahkan variabel berikut di controller Anda:
        // $totalRevenue = Transaction::sum('total');
        // $averageTransaction = Transaction::avg('total');
        // $todayTransactions = Transaction::whereDate('created_at', today())->count();
    @endphp
@endsection
