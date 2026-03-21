<?php
// app/Http/Controllers/Api/TransactionApiController.php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Transaction;
use App\Models\TransactionItem;
use App\Models\Product;
use App\Models\Member;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class TransactionApiController extends Controller
{
    /**
     * Display a listing of transactions.
     */
    public function index(Request $request)
    {
        try {
            $query = Transaction::with(['member', 'user', 'items.product'])->latest();

            if ($request->filled('search')) {
                $search = $request->search;
                $query->where('invoice_number', 'LIKE', "%{$search}%")
                      ->orWhere('customer_name', 'LIKE', "%{$search}%");
            }

            if ($request->filled('status')) {
                $query->where('status', $request->status);
            }

            $perPage = $request->get('per_page', 20);
            $transactions = $query->paginate($perPage);

            return response()->json([
                'success' => true,
                'message' => 'Transactions retrieved successfully',
                'data' => $transactions
            ], 200);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'Failed to retrieve transactions: ' . $e->getMessage()], 500);
        }
    }

    /**
     * Get recent transactions
     */
    public function recent(Request $request)
    {
        try {
            $limit = $request->get('limit', 10);

            $transactions = Transaction::with(['member', 'user', 'items.product'])
                ->latest()
                ->limit($limit)
                ->get()
                ->map(function ($trx) {
                    // Nama produk dari item pertama
                    $firstItem   = $trx->items->first();
                    $productName = $firstItem?->product?->name ?? 'Berbagai produk';
                    if ($trx->items->count() > 1) {
                        $productName .= ' +' . ($trx->items->count() - 1) . ' lainnya';
                    }

                    // Status: cocokkan dengan format Flutter (success/gagal)
                    $status = strtolower($trx->payment_status ?? $trx->status ?? '');
                    $isSuccess = in_array($status, ['lunas', 'success', 'completed', 'paid']);

                    return [
                        'id'             => $trx->id,
                        'invoice_number' => $trx->invoice_number,
                        'product_name'   => $productName,
                        'customer_name'  => $trx->member?->nama ?? $trx->customer_name ?? 'Umum',
                        'total_amount'   => $trx->total_amount,
                        'payment_method' => $trx->payment_method,
                        'status'         => $isSuccess ? 'success' : 'pending',
                        'items_count'    => $trx->items->count(),
                        'created_at'     => $trx->created_at->toISOString(),
                        'created_by'     => $trx->user?->name ?? 'System',
                    ];
                });

            return response()->json([
                'success' => true, 
                'message' => 'Recent transactions retrieved successfully',
                'data' => $transactions
            ]);
        } catch (\Exception $e) {
            Log::error('Recent transactions error: ' . $e->getMessage());
            return response()->json(['success' => false, 'message' => 'Failed to get recent transactions: ' . $e->getMessage()], 500);
        }
    }

    /**
     * GET /api/v1/transactions/today-stats
     */
    public function todayStats()
    {
        try {
            $today = now()->format('Y-m-d');

            $stats = [
                'total_transactions' => Transaction::whereDate('created_at', $today)->count(),
                'total_amount' => (float) Transaction::whereDate('created_at', $today)->sum('total_amount'),
                'by_payment_method' => [
                    'cash' => Transaction::whereDate('created_at', $today)->where('payment_method', 'cash')->count(),
                    'credit' => Transaction::whereDate('created_at', $today)->where('payment_method', 'credit')->count(),
                    'transfer' => Transaction::whereDate('created_at', $today)->where('payment_method', 'transfer')->count(),
                ],
                'average_transaction' => (float) Transaction::whereDate('created_at', $today)->avg('total_amount'),
                'highest' => (float) Transaction::whereDate('created_at', $today)->max('total_amount'),
            ];

            return response()->json([
                'success' => true,
                'message' => 'Today stats retrieved successfully',
                'data' => $stats
            ], 200);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'Failed to get today stats'], 500);
        }
    }

    /**
     * Get transaction statistics
     */
    public function getStatistics(Request $request)
    {
        try {
            $today        = now()->toDateString();
            $startOfMonth = now()->startOfMonth()->toDateString();
            $endOfMonth   = now()->endOfMonth()->toDateString();

            return response()->json([
                'success' => true,
                'message' => 'Statistics retrieved successfully',
                'data'    => [
                    'today' => [
                        'count'  => Transaction::whereDate('created_at', $today)->count(),
                        'amount' => (float) Transaction::whereDate('created_at', $today)->sum('total_amount'),
                    ],
                    'this_month' => [
                        'count'  => Transaction::whereBetween('created_at', [$startOfMonth, $endOfMonth])->count(),
                        'amount' => (float) Transaction::whereBetween('created_at', [$startOfMonth, $endOfMonth])->sum('total_amount'),
                    ],
                    'total' => Transaction::count(),
                    'completed_count' => Transaction::where('status', 'completed')->count(),
                    'pending_count' => Transaction::where('status', 'pending')->count(),
                ],
            ]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'Failed to retrieve statistics'], 500);
        }
    }

    /**
     * GET /api/v1/transactions/{transaction}
     */
    public function show(Transaction $transaction)
    {
        $transaction->load(['user', 'member', 'items.product']);
        return response()->json(['success' => true, 'data' => $transaction]);
    }

    /**
     * POST /api/v1/transactions
     */
    public function store(Request $request)
    {
        return response()->json(['success' => false, 'message' => 'Not implemented'], 501);
    }

    /**
     * PUT /api/v1/transactions/{transaction}
     */
    public function update(Request $request, Transaction $transaction)
    {
        return response()->json(['success' => false, 'message' => 'Not implemented'], 501);
    }

    /**
     * DELETE /api/v1/transactions/{transaction}
     */
    public function destroy(Transaction $transaction)
    {
        return response()->json(['success' => false, 'message' => 'Not implemented'], 501);
    }

    /**
     * POST /api/v1/transactions/{transaction}/complete
     */
    public function complete(Request $request, Transaction $transaction)
    {
        $transaction->update(['status' => 'completed']);
        return response()->json(['success' => true, 'message' => 'Transaction marked as completed', 'data' => $transaction]);
    }

    /**
     * POST /api/v1/transactions/{transaction}/cancel
     */
    public function cancel(Request $request, Transaction $transaction)
    {
        $transaction->update(['status' => 'cancelled']);
        return response()->json(['success' => true, 'message' => 'Transaction cancelled', 'data' => $transaction]);
    }

    /**
     * GET /api/v1/transactions/{transaction}/items
     */
    public function getItems(Transaction $transaction)
    {
        $transaction->load('items.product');
        return response()->json(['success' => true, 'data' => $transaction->items]);
    }

    /**
     * GET /api/v1/transactions/{transaction}/receipt
     */
    public function getReceipt(Transaction $transaction)
    {
        return response()->json(['success' => false, 'message' => 'Not implemented'], 501);
    }

    /**
     * GET /api/v1/transactions/export/csv 
     */
    public function export(Request $request)
    {
        return response()->json(['success' => false, 'message' => 'Not implemented'], 501);
    }
}
