<?php

namespace App\Http\Controllers;

use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use App\Models\Transaction;
use Carbon\Carbon;

class SalesReportController extends Controller
{
    public function index(Request $request)
    {
        $query = Transaction::with(['user', 'items', 'member']); // Tambahkan items

        // Filter tanggal
        if ($request->filled('date')) {
            $query->whereDate('created_at', $request->date);
        }

        // Filter bulan
        if ($request->filled('month')) {
            $date = Carbon::parse($request->month);
            $query->whereMonth('created_at', $date->month)
                ->whereYear('created_at', $date->year);
        }

        // Sorting
        $sort = $request->input('sort', 'latest');
        match ($sort) {
            'oldest'  => $query->oldest(),
            'highest' => $query->orderBy('total_amount', 'desc'), // Ubah total -> total_amount
            'lowest'  => $query->orderBy('total_amount', 'asc'),  // Ubah total -> total_amount
            default   => $query->latest(),
        };

        // Clone query untuk statistik
        $statQuery = clone $query;

        // Pagination
        $transactions = $query
            ->orderBy('created_at', 'desc')
            ->paginate(15)
            ->withQueryString();

        // Hitung total items untuk statistik
        $totalItems = 0;
        foreach ($transactions as $trx) {
            $totalItems += $trx->items->sum('quantity');
        }

        // Statistik GLOBAL
        $totalCount = $statQuery->count();
        $total = $statQuery->sum('total_amount'); // Ubah total -> total_amount
        $maxTransaction = $statQuery->max('total_amount'); // Ubah total -> total_amount
        $minTransaction = $statQuery->min('total_amount'); // Ubah total -> total_amount
        $averageTransaction = $totalCount > 0 ? $total / $totalCount : 0;

        // Periode
        $period = null;
        if ($request->filled('date')) {
            $period = Carbon::parse($request->date)->translatedFormat('d F Y');
        } elseif ($request->filled('month')) {
            $period = Carbon::parse($request->month)->translatedFormat('F Y');
        } else {
            $period = 'Semua Periode';
        }

        return view('reports.sales', compact(
            'transactions',
            'total',
            'totalCount',
            'maxTransaction',
            'minTransaction',
            'averageTransaction',
            'period',
            'totalItems'
        ));
    }

public function exportPdf(Request $request)
{
    $query = Transaction::with(['user', 'items', 'member']);

    // Filter tanggal
    if ($request->filled('date')) {
        $query->whereDate('created_at', $request->date);
    }

    // Filter bulan
    if ($request->filled('month')) {
        $date = Carbon::parse($request->month);
        $query->whereMonth('created_at', $date->month)
            ->whereYear('created_at', $date->year);
    }

    // Sorting
    $sort = $request->input('sort', 'latest');
    switch ($sort) {
        case 'oldest':
            $query->oldest();
            break;
        case 'highest':
            $query->orderBy('total_amount', 'desc');
            break;
        case 'lowest':
            $query->orderBy('total_amount', 'asc');
            break;
        default:
            $query->latest();
            break;
    }

    $transactions = $query->get();
    
    // Hitung total dan items (gunakan 'qty')
    $grandTotal = 0;
    $totalItems = 0;
    foreach ($transactions as $trx) {
        $grandTotal += $trx->total_amount;
        $totalItems += $trx->items ? $trx->items->sum('qty') : 0;
    }
    
    $totalCount = $transactions->count();
    $maxTransaction = $transactions->max('total_amount');
    $minTransaction = $transactions->min('total_amount');
    $averageTransaction = $totalCount > 0 ? $grandTotal / $totalCount : 0;

    // Tentukan periode dengan BENAR - PERBAIKAN INI
    $startDate = '-';
    $endDate = '-';
    
    if ($request->filled('date')) {
        // Filter by specific date
        $startDate = Carbon::parse($request->date)->format('d-m-Y');
        $endDate = $startDate;
    } elseif ($request->filled('month')) {
        // Filter by month
        $date = Carbon::parse($request->month);
        $startDate = $date->copy()->startOfMonth()->format('d-m-Y');
        $endDate = $date->copy()->endOfMonth()->format('d-m-Y');
    } else {
        // No filter - get date range from transactions
        if ($transactions->count() > 0) {
            $firstTransaction = $transactions->sortBy('created_at')->first();
            $lastTransaction = $transactions->sortByDesc('created_at')->first();
            
            if ($firstTransaction) {
                $startDate = Carbon::parse($firstTransaction->created_at)->format('d-m-Y');
            }
            if ($lastTransaction) {
                $endDate = Carbon::parse($lastTransaction->created_at)->format('d-m-Y');
            }
        }
    }

    // Hitung statistik metode pembayaran - PERBAIKAN INI
    $paymentMethods = [
        'tunai' => 0,
        'transfer' => 0,
        'kredit' => 0,
        'e_wallet' => 0, // Tambahkan e_wallet
        'qris' => 0,     // Tambahkan qris jika ada
    ];
    
    foreach ($transactions as $trx) {
        $method = strtolower($trx->payment_method ?? '');
        if (isset($paymentMethods[$method])) {
            $paymentMethods[$method]++;
        } else {
            // Jika metode tidak dikenal, tambahkan ke kategori 'lainnya'
            if (!isset($paymentMethods['lainnya'])) {
                $paymentMethods['lainnya'] = 0;
            }
            $paymentMethods['lainnya']++;
        }
    }

    // Hitung status pembayaran
    $paymentStatus = [
        'LUNAS' => $transactions->where('payment_status', 'LUNAS')->count(),
        'BELUM LUNAS' => $transactions->where('payment_status', 'BELUM LUNAS')->count(),
    ];

    // Hitung status pengiriman
    $deliveryStats = [
        'need' => $transactions->where('need_delivery', true)->count(),
        'not_need' => $transactions->where('need_delivery', false)->count(),
    ];

    $pdf = Pdf::loadView('reports.sales-pdf', [
        'transactions' => $transactions,
        'grandTotal' => $grandTotal,
        'total' => $grandTotal,
        'totalCount' => $totalCount,
        'totalItems' => $totalItems,
        'maxTransaction' => $maxTransaction,
        'minTransaction' => $minTransaction,
        'averageTransaction' => $averageTransaction,
        'startDate' => $startDate,
        'endDate' => $endDate,
        'period' => $startDate != '-' ? "$startDate s/d $endDate" : 'Semua Periode',
        'filterDate' => $request->date,
        'filterMonth' => $request->month,
        'filterSort' => $sort,
        'paymentMethods' => $paymentMethods, // Kirim data metode pembayaran
        'paymentStatus' => $paymentStatus,   // Kirim data status pembayaran
        'deliveryStats' => $deliveryStats,   // Kirim data pengiriman
        'generatedAt' => Carbon::now()->translatedFormat('d-m-Y H:i:s')
    ])->setPaper('A4', 'landscape');

    return $pdf->download('laporan-penjualan-' . Carbon::now()->format('Y-m-d-H-i-s') . '.pdf');
}

    public function getChartData(Request $request)
    {
        $query = Transaction::query();

        if ($request->filled('date')) {
            $query->whereDate('created_at', $request->date);
        }

        if ($request->filled('month')) {
            $date = Carbon::parse($request->month);
            $query->whereMonth('created_at', $date->month)
                ->whereYear('created_at', $date->year);
        }

        if ($request->filled('date')) {
            $data = $query->selectRaw('HOUR(created_at) as hour, SUM(total_amount) as total')
                ->groupBy('hour')
                ->orderBy('hour')
                ->get()
                ->map(function ($item) {
                    return [
                        'hour' => $item->hour . ':00',
                        'total' => $item->total
                    ];
                });
        } else {
            $startDate = $request->filled('month')
                ? Carbon::parse($request->month)->startOfMonth()
                : Carbon::now()->startOfMonth();
            $endDate = $request->filled('month')
                ? Carbon::parse($request->month)->endOfMonth()
                : Carbon::now()->endOfMonth();

            $data = $query->whereBetween('created_at', [$startDate, $endDate])
                ->selectRaw('DATE(created_at) as date, SUM(total_amount) as total')
                ->groupBy('date')
                ->orderBy('date')
                ->get()
                ->map(function ($item) {
                    return [
                        'date' => Carbon::parse($item->date)->format('d M'),
                        'total' => $item->total
                    ];
                });
        }

        return response()->json([
            'data' => $data,
            'labels' => $data->pluck($request->filled('date') ? 'hour' : 'date'),
            'values' => $data->pluck('total')
        ]);
    }
}