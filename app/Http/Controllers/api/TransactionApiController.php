<?php
// app/Http/Controllers/Api/TransactionApiController.php
// Add these methods to your existing TransactionController

namespace App\Http\Controllers;

use App\Models\Transaction;
use App\Models\Member;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class TransactionController extends Controller
{
    /**
     * Get recent transactions
     */
    public function recentTransactions(Request $request)
    {
        try {
            $limit = $request->get('limit', 10);

            $transactions = Transaction::with(['member', 'user', 'items.product'])
                ->latest()
                ->limit($limit)
                ->get()
                ->map(function ($transaction) {
                    return [
                        'id' => $transaction->id,
                        'invoice_number' => $transaction->invoice_number,
                        'customer_name' => $transaction->member->nama ?? $transaction->customer_name,
                        'total_amount' => $transaction->total_amount,
                        'formatted_total' => 'Rp ' . number_format($transaction->total_amount, 0, ',', '.'),
                        'payment_method' => $transaction->payment_method,
                        'status' => $transaction->status,
                        'status_badge' => $this->getStatusBadge($transaction->status),
                        'items_count' => $transaction->items->count(),
                        'created_at' => $transaction->created_at->format('d/m/Y H:i'),
                        'created_by' => $transaction->user->name ?? 'System'
                    ];
                });

            return response()->json([
                'success' => true,
                'message' => 'Recent transactions retrieved successfully',
                'data' => $transactions
            ], 200);
        } catch (\Exception $e) {
            Log::error('Recent transactions error: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Failed to get recent transactions',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get today's transaction statistics
     */
    public function todayStats(Request $request)
    {
        try {
            $today = now()->format('Y-m-d');

            $stats = [
                'total_transactions' => Transaction::whereDate('created_at', $today)->count(),
                'total_amount' => (float) Transaction::whereDate('created_at', $today)->sum('total_amount'),
                'formatted_total' => 'Rp ' . number_format(Transaction::whereDate('created_at', $today)->sum('total_amount'), 0, ',', '.'),
                'by_payment_method' => [
                    'cash' => Transaction::whereDate('created_at', $today)->where('payment_method', 'cash')->count(),
                    'credit' => Transaction::whereDate('created_at', $today)->where('payment_method', 'credit')->count(),
                    'transfer' => Transaction::whereDate('created_at', $today)->where('payment_method', 'transfer')->count(),
                ],
                'by_status' => [
                    'pending' => Transaction::whereDate('created_at', $today)->where('status', 'pending')->count(),
                    'processing' => Transaction::whereDate('created_at', $today)->where('status', 'processing')->count(),
                    'completed' => Transaction::whereDate('created_at', $today)->where('status', 'completed')->count(),
                ],
                'average_transaction' => (float) Transaction::whereDate('created_at', $today)->avg('total_amount'),
                'highest_transaction' => (float) Transaction::whereDate('created_at', $today)->max('total_amount'),
            ];

            return response()->json([
                'success' => true,
                'message' => 'Today stats retrieved successfully',
                'data' => $stats
            ], 200);
        } catch (\Exception $e) {
            Log::error('Today stats error: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Failed to get today stats',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Search transactions
     */
    public function search(Request $request)
    {
        try {
            $search = $request->get('q', '');
            $limit = $request->get('limit', 10);

            $transactions = Transaction::with(['member'])
                ->where(function($query) use ($search) {
                    $query->where('invoice_number', 'like', "%{$search}%")
                          ->orWhere('customer_name', 'like', "%{$search}%")
                          ->orWhereHas('member', function($q) use ($search) {
                              $q->where('nama', 'like', "%{$search}%")
                                ->orWhere('kode_member', 'like', "%{$search}%");
                          });
                })
                ->latest()
                ->limit($limit)
                ->get()
                ->map(function ($transaction) {
                    return [
                        'id' => $transaction->id,
                        'invoice_number' => $transaction->invoice_number,
                        'customer' => $transaction->member->nama ?? $transaction->customer_name,
                        'total' => $transaction->total_amount,
                        'formatted_total' => 'Rp ' . number_format($transaction->total_amount, 0, ',', '.'),
                        'status' => $transaction->status,
                        'payment_method' => $transaction->payment_method,
                        'date' => $transaction->created_at->format('d/m/Y'),
                    ];
                });

            return response()->json([
                'success' => true,
                'message' => 'Search results retrieved successfully',
                'data' => $transactions
            ], 200);
        } catch (\Exception $e) {
            Log::error('Transaction search error: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Failed to search transactions',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get status badge class
     */
    private function getStatusBadge($status)
    {
        $badges = [
            'pending' => 'warning',
            'processing' => 'info',
            'completed' => 'success',
            'cancelled' => 'danger'
        ];

        return $badges[$status] ?? 'secondary';
    }
}
