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
     * GET /api/v1/transactions
     */
    public function index(Request $request)
    {
        try {
            $query = Transaction::with(['user', 'member'])->latest();

            if ($request->filled('search')) {
                $s = $request->search;
                $query->where(function ($q) use ($s) {
                    $q->where('invoice_number', 'like', "%{$s}%")
                        ->orWhere('customer_name', 'like', "%{$s}%");
                });
            }

            return response()->json([
                'success' => true,
                'data'    => $query->paginate($request->get('per_page', 20)),
            ]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    /**
     * GET /api/v1/transactions/recent
     * ⚠️ Method harus bernama recent sesuai route api.php
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

            return response()->json(['success' => true, 'data' => $transactions]);
        } catch (\Exception $e) {
            Log::error('Recent transactions error: ' . $e->getMessage());
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    /**
     * GET /api/v1/transactions/today-stats
     */
    public function todayStats(Request $request)
    {
        try {
            $today = now()->toDateString();

            return response()->json([
                'success' => true,
                'data'    => [
                    'total_transactions' => Transaction::whereDate('created_at', $today)->count(),
                    'total_amount'       => (float) Transaction::whereDate('created_at', $today)->sum('total_amount'),
                    'average'            => (float) Transaction::whereDate('created_at', $today)->avg('total_amount'),
                    'highest'            => (float) Transaction::whereDate('created_at', $today)->max('total_amount'),
                ],
            ]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    /**
     * GET /api/v1/transactions/statistics
     */
    public function getStatistics(Request $request)
    {
        try {
            $today        = now()->toDateString();
            $startOfMonth = now()->startOfMonth()->toDateString();
            $endOfMonth   = now()->endOfMonth()->toDateString();

            return response()->json([
                'success' => true,
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
                ],
            ]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
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
    public function complete(Transaction $transaction)
    {
        return response()->json(['success' => false, 'message' => 'Not implemented'], 501);
    }

    /**
     * POST /api/v1/transactions/{transaction}/cancel
     */
    public function cancel(Transaction $transaction)
    {
        return response()->json(['success' => false, 'message' => 'Not implemented'], 501);
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
        $transaction->load(['user', 'member', 'items.product']);
        return response()->json(['success' => true, 'data' => $transaction]);
    }

    /**
     * GET /api/v1/transactions/export/csv
     */
    public function export()
    {
        return response()->json(['success' => false, 'message' => 'Not implemented'], 501);
    }
}
