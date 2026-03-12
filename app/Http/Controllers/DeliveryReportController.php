<?php

namespace App\Http\Controllers;

use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use App\Models\Delivery;
use App\Models\User;
use App\Models\Vehicle;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;

class DeliveryReportController extends Controller
{
    public function exportPdf(Request $request)
    {
        // Gunakan relasi yang benar: user (bukan driver) karena hanya ada user_id
        $query = Delivery::with(['transaction', 'user', 'vehicle']);

        // Filter search (kode, invoice, tujuan)
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('delivery_code', 'like', "%{$search}%")
                  ->orWhere('destination', 'like', "%{$search}%")
                  ->orWhere('origin', 'like', "%{$search}%")
                  ->orWhereHas('transaction', function($subq) use ($search) {
                      $subq->where('invoice_number', 'like', "%{$search}%");
                  });
            });
        }

        // Filter status
        if ($request->filled('status') && $request->status != 'all') {
            $query->where('status', $request->status);
        }

        // Filter driver (gunakan user_id)
        if ($request->filled('driver_id') && $request->driver_id != 'all') {
            $query->where('user_id', $request->driver_id);
        }

        // Filter date range
        if ($request->filled('start_date')) {
            $query->whereDate('created_at', '>=', $request->start_date);
        }
        if ($request->filled('end_date')) {
            $query->whereDate('created_at', '<=', $request->end_date);
        }

        // Sorting
        $sort = $request->input('sort', 'latest');
        switch ($sort) {
            case 'oldest':
                $query->oldest();
                break;
            default:
                $query->latest();
                break;
        }

        $deliveries = $query->get();

        // Hitung statistik
        $stats = [
            'total' => $deliveries->count(),
            'pending' => $deliveries->where('status', 'pending')->count(),
            'processing' => $deliveries->where('status', 'processing')->count(),
            'assigned' => $deliveries->where('status', 'assigned')->count(),
            'picked_up' => $deliveries->where('status', 'picked_up')->count(),
            'on_delivery' => $deliveries->where('status', 'on_delivery')->count(),
            'delivered' => $deliveries->where('status', 'delivered')->count(),
            'failed' => $deliveries->where('status', 'failed')->count(),
            'cancelled' => $deliveries->where('status', 'cancelled')->count(),
        ];

        // Tentukan periode
        $startDate = '-';
        $endDate = '-';
        
        if ($deliveries->count() > 0) {
            $firstDelivery = $deliveries->sortBy('created_at')->first();
            $lastDelivery = $deliveries->sortByDesc('created_at')->first();
            
            if ($firstDelivery) {
                $startDate = Carbon::parse($firstDelivery->created_at)->format('d/m/Y');
            }
            if ($lastDelivery) {
                $endDate = Carbon::parse($lastDelivery->created_at)->format('d/m/Y');
            }
        }

        // Tentukan tahun periode
        $tahunAwal = $deliveries->count() > 0 
            ? Carbon::parse($deliveries->sortBy('created_at')->first()->created_at)->format('Y')
            : '-';
        $tahunAkhir = $deliveries->count() > 0 
            ? Carbon::parse($deliveries->sortByDesc('created_at')->first()->created_at)->format('Y')
            : '-';
        
        if ($tahunAwal == $tahunAkhir || $tahunAkhir == '-') {
            $periodeText = $tahunAwal;
        } else {
            $periodeText = $tahunAwal . ' - ' . $tahunAkhir;
        }

        // Ambil nama user yang mencetak
        $generatedBy = 'System';
        if (Auth::check()) {
            $generatedBy = Auth::user()->name ?? 'System';
        }

        $pdf = Pdf::loadView('Delivery.reports.delivery-pdf', [
            'deliveries' => $deliveries,
            'stats' => $stats,
            'startDate' => $startDate,
            'endDate' => $endDate,
            'periodeText' => $periodeText,
            'filters' => [
                'search' => $request->search,
                'status' => $request->status,
                'driver_id' => $request->driver_id,
                'start_date' => $request->start_date,
                'end_date' => $request->end_date,
            ],
            'generatedAt' => Carbon::now()->format('d/m/Y H:i:s'),
            'generatedBy' => $generatedBy
        ])->setPaper('A4', 'landscape');

        return $pdf->download('laporan-pengiriman-' . Carbon::now()->format('Y-m-d-H-i-s') . '.pdf');
    }

    public function getDrivers()
    {
        // Ambil user yang menjadi driver/kurir
        $drivers = User::where('role', 'driver')->orWhere('role', 'kurir')->get();
        return response()->json($drivers);
    }

    public function getVehicles()
    {
        $vehicles = Vehicle::all();
        return response()->json($vehicles);
    }
}