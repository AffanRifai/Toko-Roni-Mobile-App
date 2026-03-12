<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Laporan Pengiriman</title>
    <style>
        body {
            font-family: DejaVu Sans, sans-serif;
            font-size: 11px;
            margin: 20px;
            color: #333;
            background-color: #fff;
        }
        
        /* HEADER */
        .header {
            text-align: center;
            margin-bottom: 20px;
            border-bottom: 2px solid #2c3e50;
            padding-bottom: 10px;
        }
        .header h2 {
            margin: 0;
            font-size: 24px;
            font-weight: bold;
            text-transform: uppercase;
            color: #2c3e50;
        }
        .company-info {
            text-align: center;
            margin-top: 5px;
            font-size: 11px;
            color: #555;
        }
        .company-info strong {
            font-size: 14px;
            color: #2c3e50;
        }
        
        /* INFO SECTION */
        .info {
            margin-bottom: 20px;
            padding: 15px;
            background-color: #f8f9fa;
            border: 1px solid #dee2e6;
            border-radius: 8px;
            font-size: 11px;
        }
        .info-row {
            display: flex;
            margin-bottom: 5px;
        }
        .info-label {
            width: 100px;
            font-weight: bold;
            color: #495057;
        }
        .info-value {
            color: #2c3e50;
        }
        
        /* STATISTICS CARDS - DIUBAH MENJADI TABEL SEPERTI GAMBAR */
        .stats-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
            border: 1px solid #dee2e6;
        }
        .stats-table td {
            padding: 10px;
            border: 1px solid #dee2e6;
            text-align: center;
            vertical-align: middle;
        }
        .stats-label {
            font-size: 10px;
            font-weight: bold;
            color: #6c757d;
            text-transform: uppercase;
            background-color: #f8f9fa;
        }
        .stats-value {
            font-size: 18px;
            font-weight: 800;
            color: #2c3e50;
        }
        .stats-table tr:first-child td:first-child { background-color: #e9ecef; } /* Total */
        .stats-table tr:first-child td:last-child { background-color: #fff3cd; } /* Pending */
        .stats-table tr:nth-child(2) td:first-child { background-color: #cfe2ff; } /* Processing */
        .stats-table tr:nth-child(2) td:last-child { background-color: #e2d5f1; } /* Assigned */
        .stats-table tr:nth-child(3) td:first-child { background-color: #d1e7dd; } /* Picked Up */
        .stats-table tr:nth-child(3) td:last-child { background-color: #ffe5d0; } /* On Delivery */
        .stats-table tr:nth-child(4) td:first-child { background-color: #d1e7dd; } /* Terkirim */
        .stats-table tr:nth-child(4) td:last-child { background-color: #f8d7da; } /* Gagal */
        .stats-table tr:last-child td:first-child { background-color: #e9ecef; } /* Dibatalkan */
        
        /* TOTAL ITEM INFO */
        .total-info {
            text-align: center;
            margin: 20px 0;
            margin-top: 150px;
            padding: 12px;
            background-color: #2c3e50;
            color: white;
            font-size: 13px;
            font-weight: bold;
            border-radius: 8px;
        }
        .total-info span {
            margin: 0 15px;
        }
        
        /* TABLE */
        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
            font-size: 10px;
            border: 1px solid #dee2e6;
        }
        th {
            background-color: #2c3e50;
            color: white;
            padding: 10px 6px;
            font-size: 10px;
            font-weight: 600;
            text-transform: uppercase;
            border: 1px solid #1a252f;
            text-align: center;
        }
        td {
            padding: 8px 6px;
            border: 1px solid #dee2e6;
            vertical-align: middle;
        }
        tbody tr:nth-child(even) {
            background-color: #f8f9fa;
        }
        
        /* BADGE - DIPERBAIKI AGAR INLINE */
        .badge {
            display: inline-block;  /* Diubah dari flex ke inline-block */
            padding: 4px 8px;
            border-radius: 4px;
            font-size: 9px;
            font-weight: 600;
            text-align: center;
            min-width: 70px;
            border: 1px solid transparent;
            white-space: nowrap;    /* Mencegah teks turun ke bawah */
        }
        .badge-pending { background-color: #fff3cd; color: #856404; border-color: #ffeeba; }
        .badge-processing { background-color: #cfe2ff; color: #052c65; border-color: #b6d4fe; }
        .badge-assigned { background-color: #e2d5f1; color: #4a1b6d; border-color: #d3c5e8; }
        .badge-picked_up { background-color: #d1e7dd; color: #0a3622; border-color: #badbcc; }
        .badge-on_delivery { background-color: #ffe5d0; color: #a14f1a; border-color: #ffd8b5; }
        .badge-delivered { background-color: #d1e7dd; color: #0a3622; border-color: #badbcc; }
        .badge-failed { background-color: #f8d7da; color: #842029; border-color: #f5c2c7; }
        .badge-cancelled { background-color: #e9ecef; color: #495057; border-color: #d3d9df; }
        
        /* DRIVER INFO */
        .driver-info {
            display: flex;
            align-items: center;
            gap: 8px;
        }
        .driver-avatar {
            width: 24px;
            height: 24px;
            border-radius: 50%;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 11px;
            font-weight: bold;
        }
        .driver-name {
            font-weight: 600;
            color: #2c3e50;
        }
        
        /* CODE STYLE */
        .code {
            font-family: 'Courier New', monospace;
            font-weight: 600;
            color: #2c3e50;
            background-color: #f8f9fa;
            padding: 3px 6px;
            border-radius: 4px;
        }
        
        /* FOOTER */
        .footer {
            margin-top: 20px;
            padding-top: 10px;
            border-top: 1px dashed #adb5bd;
            text-align: right;
            font-size: 10px;
            color: #6c757d;
        }
        
        /* UTILITIES */
        .text-center { text-align: center; }
        .text-right { text-align: right; }
        .font-bold { font-weight: bold; }
    </style>
</head>
<body>

    <!-- HEADER -->
    <div class="header">
        <h2>LAPORAN PENGIRIMAN</h2>
        <div class="company-info">
            <strong>{{ config('app.name') }}</strong>
        </div>
    </div>

    <!-- INFO PERIODE -->
    <div class="info">
        <div class="info-row">
            <span class="info-label">Periode:</span>
            <span class="info-value"><strong>{{ $periodeText }}</strong></span>
        </div>
        <div class="info-row">
            <span class="info-label">Tanggal Cetak:</span>
            <span class="info-value">{{ $generatedAt }}</span>
        </div>
    </div>

    <!-- RINGKASAN STATISTIK - MENGGUNAKAN TABEL SEPERTI GAMBAR -->
    <table class="stats-table">
        <tr>
            <td>
                <div class="stats-label">TOTALE</div>
                <div class="stats-value">{{ $stats['total'] }}</div>
            </td>
            <td>
                <div class="stats-label">PENDING</div>
                <div class="stats-value">{{ $stats['pending'] }}</div>
            </td>
        </tr>
        <tr>
            <td>
                <div class="stats-label">PROCESSING</div>
                <div class="stats-value">{{ $stats['processing'] }}</div>
            </td>
            <td>
                <div class="stats-label">ASSIGNED</div>
                <div class="stats-value">{{ $stats['assigned'] }}</div>
            </td>
        </tr>
        <tr>
            <td>
                <div class="stats-label">PICKED UP</div>
                <div class="stats-value">{{ $stats['picked_up'] }}</div>
            </td>
            <td>
                <div class="stats-label">ON DELIVERY</div>
                <div class="stats-value">{{ $stats['on_delivery'] }}</div>
            </td>
        </tr>
        <tr>
            <td>
                <div class="stats-label">TERKIRIM</div>
                <div class="stats-value">{{ $stats['delivered'] }}</div>
            </td>
            <td>
                <div class="stats-label">GAGAL</div>
                <div class="stats-value">{{ $stats['failed'] }}</div>
            </td>
        </tr>
        <tr>
            <td>
                <div class="stats-label">DIBATALKAN</div>
                <div class="stats-value">{{ $stats['cancelled'] }}</div>
            </td>
            <td></td>
        </tr>
    </table>

    <!-- TOTAL ITEM & PENGIRIMAN -->
    <div class="total-info">
        <span> Total Item: {{ $deliveries->sum('total_items') }} item</span>
        <span> Total Pengiriman: {{ $stats['total'] }} pengiriman</span>
    </div>

    <!-- TABEL DETAIL PENGIRIMAN -->
     <br>
    <table>
        <thead>
            <tr>
                <th width="5%">NO</th>
                <th width="12%">KODE</th>
                <th width="12%">INVOICE</th>
                <th width="18%">TUJUAN</th>
                <th width="12%">KURIR</th>
                <th width="10%">KENDARAAN</th>
                <th width="10%">STATUS</th>
                <th width="10%">TGL DIBUAT</th>
                <th width="11%">ESTIMASI</th>
            </tr>
        </thead>
        <tbody>
            @forelse($deliveries as $delivery)
                <tr>
                    <td class="text-center">{{ $loop->iteration }}</td>
                    <td class="text-center">
                        <span class="code">{{ $delivery->delivery_code }}</span>
                    </td>
                    <td class="text-center">{{ $delivery->transaction->invoice_number ?? '-' }}</td>
                    <td>{{ Str::limit($delivery->destination ?? '-', 30) }}</td>
                    <td>
                        @if($delivery->user)
                            <div class="driver-info">
                                <div class="driver-avatar">
                                    {{ substr($delivery->user->name, 0, 1) }}
                                </div>
                                <span class="driver-name">{{ $delivery->user->name }}</span>
                            </div>
                        @else
                            <span class="badge badge-pending">Belum Assign</span>
                        @endif
                    </td>
                    <td class="text-center">{{ $delivery->vehicle->name ?? '-' }}</td>
                    <td class="text-center">
                        @php
                            $statusClass = match($delivery->status) {
                                'pending' => 'badge-pending',
                                'processing' => 'badge-processing',
                                'assigned' => 'badge-assigned',
                                'picked_up' => 'badge-picked_up',
                                'on_delivery' => 'badge-on_delivery',
                                'delivered' => 'badge-delivered',
                                'failed' => 'badge-failed',
                                'cancelled' => 'badge-cancelled',
                                default => 'badge-pending'
                            };
                            $statusText = match($delivery->status) {
                                'delivered' => 'Terkirim',
                                'on_delivery' => 'Dalam Perjalanan',
                                'picked_up' => 'Telah Diambil',
                                'assigned' => 'Ditugaskan',
                                'processing' => 'Diproses',
                                'pending' => 'Menunggu',
                                'failed' => 'Gagal',
                                'cancelled' => 'Dibatalkan',
                                default => ucwords(str_replace('_', ' ', $delivery->status))
                            };
                        @endphp
                        <span class="badge {{ $statusClass }}">
                            {{ $statusText }}
                        </span>
                    </td>
                    <td class="text-center">{{ \Carbon\Carbon::parse($delivery->created_at)->format('d/m/Y') }}</td>
                    <td class="text-center">
                        {{ $delivery->estimated_delivery_time ? \Carbon\Carbon::parse($delivery->estimated_delivery_time)->format('d/m/Y') : '-' }}
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="9" class="text-center" style="padding: 30px;">
                        Tidak ada data pengiriman
                    </td>
                </tr>
            @endforelse
        </tbody>
    </table>

    <!-- FOOTER -->
    <div class="footer">
        Dicetak pada: {{ $generatedAt }}
    </div>

</body>
</html>