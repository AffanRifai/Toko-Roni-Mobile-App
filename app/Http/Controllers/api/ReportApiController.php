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
     * Export PDF (Mock)
     */
    public function exportPdf()
    {
        return response()->json(['success' => true, 'message' => 'PDF being generated'], 200);
    }

    /**
     * Export Excel (Mock)
     */
    public function exportExcel()
    {
        return response()->json(['success' => true, 'message' => 'Excel being generated'], 200);
    }
}
