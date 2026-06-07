<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Transaction;
use App\Models\Product;
use App\Models\Member;
use App\Models\Receivable;
use App\Models\ReceivablePayment;
use App\Models\Delivery;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;
use Barryvdh\DomPDF\Facade\Pdf;

class ReportApiController extends Controller
{
    /**
     * DASHBOARD LAPORAN UTAMA (Summary)
     */
    public function salesSummary(Request $request)
    {
        try {
            $startDate = $request->get('start_date', Carbon::now()->startOfMonth());
            $endDate = $request->get('end_date', Carbon::now()->endOfMonth());

            $transactions = Transaction::whereBetween('created_at', [$startDate, $endDate])->get();

            $summary = [
                'total_revenue' => $transactions->sum('total_amount'),
                'total_count' => $transactions->count(),
                'average_transaction' => $transactions->avg('total_amount') ?? 0,
            ];

            return response()->json(['success' => true, 'data' => $summary], 200);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'Error'], 500);
        }
    }

    /**
     * Daily sales
     */
    public function dailySales()
    {
        try {
            $today = Carbon::today();
            $revenue = Transaction::whereDate('created_at', $today)->sum('total_amount');
            return response()->json(['success' => true, 'data' => ['revenue' => $revenue]], 200);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'Error'], 500);
        }
    }

    /**
     * Monthly sales
     */
    public function monthlySales()
    {
        try {
            $month = Carbon::now()->month;
            $revenue = Transaction::whereMonth('created_at', $month)->sum('total_amount');
            return response()->json(['success' => true, 'data' => ['revenue' => $revenue]], 200);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'Error'], 500);
        }
    }

    /**
     * Yearly sales
     */
    public function yearlySales()
    {
        try {
            $year = Carbon::now()->year;
            $revenue = Transaction::whereYear('created_at', $year)->sum('total_amount');
            return response()->json(['success' => true, 'data' => ['revenue' => $revenue]], 200);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'Error'], 500);
        }
    }

    /**
     * Sales by payment method
     */
    public function salesByPayment()
    {
        try {
            $data = Transaction::select('payment_method', DB::raw('SUM(total_amount) as total'))
                ->groupBy('payment_method')
                ->get();
            return response()->json(['success' => true, 'data' => $data], 200);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'Error'], 500);
        }
    }

    /**
     * Best selling products
     */
    public function bestSelling()
    {
        try {
            $products = DB::table('transaction_items')
                ->select('product_id', DB::raw('SUM(qty) as total_sold'))
                ->groupBy('product_id')
                ->orderByDesc('total_sold')
                ->limit(10)
                ->get();
            return response()->json(['success' => true, 'data' => $products], 200);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'Error'], 500);
        }
    }

    /**
     * Stock report
     */
    public function stockReport()
    {
        try {
            $lowStock = Product::where('stock', '<=', 10)->get();
            return response()->json(['success' => true, 'data' => $lowStock], 200);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'Error'], 500);
        }
    }

    /**
     * Top spenders
     */
    public function topSpenders()
    {
        try {
            $members = Member::withSum('transactions', 'total_amount')
                ->orderByDesc('transactions_sum_total_amount')
                ->limit(10)
                ->get();
            return response()->json(['success' => true, 'data' => $members], 200);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'Error'], 500);
        }
    }

    /**
     * Piutang report
     */
    public function piutangReport()
    {
        try {
            $totalPiutang = Receivable::where('status', 'BELUM LUNAS')->sum('sisa_piutang');
            return response()->json(['success' => true, 'data' => ['total_piutang' => $totalPiutang]], 200);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'Error'], 500);
        }
    }

    /**
     * Delivery performance
     */
    public function deliveryPerformance()
    {
        try {
            $stats = [
                'total' => Delivery::count(),
                'delivered' => Delivery::where('status', 'delivered')->count(),
            ];
            return response()->json(['success' => true, 'data' => $stats], 200);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'Error'], 500);
        }
    }

    /**
     * Export sales PDF
     */
    public function exportPdf(Request $request)
    {
        try {
            $user = auth()->user();
            if (!$user || !in_array($user->role, ['owner', 'manager', 'kepala_gudang', 'kasir', 'admin'])) {
                return response()->json([
                    'success' => false,
                    'message' => 'Anda tidak memiliki akses ke laporan PDF',
                ], 403);
            }

            $query = Transaction::with(['user', 'items.product', 'member']);

            if ($request->filled('date')) {
                $query->whereDate('created_at', $request->date);
            }

            if ($request->filled('month')) {
                $date = Carbon::parse($request->month . '-01');
                $query->whereMonth('created_at', $date->month)
                    ->whereYear('created_at', $date->year);
            }

            $sort = $request->get('sort', 'latest');
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
            $grandTotal = $transactions->sum('total_amount');
            $totalItems = $transactions->sum(function ($trx) {
                return $trx->items ? $trx->items->sum('qty') : 0;
            });
            $totalCount = $transactions->count();
            $maxTransaction = $transactions->max('total_amount');
            $minTransaction = $transactions->min('total_amount');
            $averageTransaction = $totalCount > 0 ? $grandTotal / $totalCount : 0;

            $startDate = '-';
            $endDate = '-';
            if ($request->filled('date')) {
                $startDate = Carbon::parse($request->date)->format('d-m-Y');
                $endDate = $startDate;
            } elseif ($request->filled('month')) {
                $date = Carbon::parse($request->month . '-01');
                $startDate = $date->copy()->startOfMonth()->format('d-m-Y');
                $endDate = $date->copy()->endOfMonth()->format('d-m-Y');
            } elseif ($transactions->count() > 0) {
                $first = $transactions->sortBy('created_at')->first();
                $last = $transactions->sortByDesc('created_at')->first();
                $startDate = $first ? Carbon::parse($first->created_at)->format('d-m-Y') : '-';
                $endDate = $last ? Carbon::parse($last->created_at)->format('d-m-Y') : '-';
            }

            $paymentMethods = [
                'tunai' => 0,
                'transfer' => 0,
                'kredit' => 0,
                'e_wallet' => 0,
                'qris' => 0,
            ];

            foreach ($transactions as $trx) {
                $method = strtolower($trx->payment_method ?? '');
                if (isset($paymentMethods[$method])) {
                    $paymentMethods[$method]++;
                } else {
                    if (!isset($paymentMethods['lainnya'])) {
                        $paymentMethods['lainnya'] = 0;
                    }
                    $paymentMethods['lainnya']++;
                }
            }

            $paymentStatus = [
                'LUNAS' => $transactions->where('payment_status', 'LUNAS')->count(),
                'BELUM LUNAS' => $transactions->where('payment_status', 'BELUM LUNAS')->count(),
            ];

            $deliveryStats = [
                'need' => $transactions->where('need_delivery', true)->count(),
                'not_need' => $transactions->where('need_delivery', false)->count(),
            ];

            $pdf = Pdf::loadView('reports.exports.sales-pdf', [
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
                'paymentMethods' => $paymentMethods,
                'paymentStatus' => $paymentStatus,
                'deliveryStats' => $deliveryStats,
                'generatedAt' => Carbon::now()->translatedFormat('d-m-Y H:i:s'),
            ])->setPaper('A4', 'landscape');

            return $pdf->download('laporan-penjualan-' . Carbon::now()->format('Y-m-d-H-i-s') . '.pdf');
        } catch (\Exception $e) {
            Log::error('API export PDF error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Gagal mengekspor PDF: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Export Excel (Mock)
     */
    public function exportExcel()
    {
        return response()->json(['success' => true, 'message' => 'Excel being generated'], 200);
    }
}
