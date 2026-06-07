<?php
// app/Http/Controllers/Api/TransactionApiController.php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Transaction;
use App\Models\TransactionItem;
use App\Models\Product;
use App\Models\Member;
use App\Models\Receivable;
use App\Models\User;
use App\Notifications\ReceivableCreatedNotification;
use App\Notifications\TransactionCreatedNotification;
use App\Notifications\TransactionDeletedNotification;
use App\Services\NotificationRoutingService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;

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
                $query->where(function ($q) use ($search) {
                    $q->where('invoice_number', 'LIKE', "%{$search}%")
                        ->orWhere('customer_name', 'LIKE', "%{$search}%")
                        ->orWhere('customer_phone', 'LIKE', "%{$search}%")
                        ->orWhereHas('user', function ($uq) use ($search) {
                            $uq->where('name', 'LIKE', "%{$search}%");
                        })
                        ->orWhereHas('member', function ($mq) use ($search) {
                            $mq->where('nama', 'LIKE', "%{$search}%")
                                ->orWhere('kode_member', 'LIKE', "%{$search}%");
                        });
                });
            }

            if ($request->filled('status')) {
                $query->where('payment_status', $request->status);
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

                    // Status transaksi: transaksi dianggap berhasil jika tidak dibatalkan/gagal.
                    // payment_status tetap dikirim terpisah untuk info LUNAS/BELUM LUNAS.
                    $transactionStatus = strtolower((string) ($trx->status ?? ''));
                    $isFailedTransaction = in_array($transactionStatus, ['cancelled', 'canceled', 'failed', 'void'], true);
                    $isSuccess = !$isFailedTransaction;

                    return [
                        'id'             => $trx->id,
                        'invoice_number' => $trx->invoice_number,
                        'product_name'   => $productName,
                        'customer_name'  => $trx->member?->nama ?? $trx->customer_name ?? 'Umum',
                        'total_amount'   => $trx->total_amount,
                        'payment_method' => $trx->payment_method,
                        'payment_status' => $trx->payment_status,
                        'transaction_status' => $trx->status,
                        'is_success'     => $isSuccess,
                        'status'         => $isSuccess ? 'success' : 'failed',
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
        $payload = $request->all();

        // Kompatibilitas: support items berbentuk string JSON
        if (isset($payload['items']) && is_string($payload['items'])) {
            $decodedItems = json_decode($payload['items'], true);
            if (json_last_error() === JSON_ERROR_NONE && is_array($decodedItems)) {
                $payload['items'] = $decodedItems;
            }
        }

        $validator = Validator::make($payload, [
            'customer_name' => 'required|string|max:255',
            'customer_phone' => 'nullable|string|max:30',
            'member_id' => 'nullable|integer|exists:members,id',
            'payment_method' => 'required|string',
            'discount' => 'nullable|numeric|min:0',
            'discount_percent' => 'nullable|numeric|min:0|max:100',
            'cash_received' => 'nullable|numeric|min:0',
            'due_date' => 'nullable|date',
            'notes' => 'nullable|string',
            'items' => 'required|array|min:1',
            'items.*.product_id' => 'required|integer|exists:products,id',
            'items.*.qty' => 'required|integer|min:1',
            'items.*.price' => 'nullable|numeric|min:0',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validasi gagal',
                'errors' => $validator->errors(),
            ], 422);
        }

        $validated = $validator->validated();
        $paymentMethod = $this->normalizePaymentMethod($validated['payment_method'] ?? '');
        if ($paymentMethod === null) {
            return response()->json([
                'success' => false,
                'message' => 'Metode pembayaran tidak didukung',
            ], 422);
        }

        $isCredit = $paymentMethod === 'credit_card';
        if ($isCredit && empty($validated['member_id'])) {
            return response()->json([
                'success' => false,
                'message' => 'Transaksi hutang/kredit wajib memilih member',
            ], 422);
        }

        DB::beginTransaction();
        try {
            $currentUser = auth()->user();
            $productIds = collect($validated['items'])->pluck('product_id')->unique()->values();
            $products = Product::whereIn('id', $productIds)->lockForUpdate()->get()->keyBy('id');

            $normalizedItems = [];
            $subtotal = 0.0;
            $createdReceivable = null;

            foreach ($validated['items'] as $item) {
                $productId = (int) $item['product_id'];
                $qty = (int) $item['qty'];
                $product = $products->get($productId);

                if (!$product) {
                    throw new \RuntimeException("Produk dengan ID {$productId} tidak ditemukan");
                }
                if (!$product->is_active) {
                    throw new \RuntimeException("Produk {$product->name} tidak aktif");
                }
                if ((int) $product->stock < $qty) {
                    throw new \RuntimeException("Stok {$product->name} tidak mencukupi. Tersedia: {$product->stock}");
                }

                $price = array_key_exists('price', $item)
                    ? (float) $item['price']
                    : (float) $product->price;
                $lineSubtotal = $price * $qty;
                $subtotal += $lineSubtotal;

                $normalizedItems[] = [
                    'product' => $product,
                    'qty' => $qty,
                    'price' => $price,
                    'subtotal' => $lineSubtotal,
                ];
            }

            $discountPercent = (float) ($validated['discount_percent'] ?? 0);
            $discountAmount = $discountPercent > 0
                ? round(($subtotal * $discountPercent) / 100, 2)
                : (float) ($validated['discount'] ?? 0);

            if ($discountAmount < 0) {
                $discountAmount = 0;
            }
            if ($discountAmount > $subtotal) {
                $discountAmount = $subtotal;
            }

            $totalAmount = round($subtotal - $discountAmount, 2);
            $cashReceived = (float) ($validated['cash_received'] ?? 0);
            $change = 0.0;

            if ($paymentMethod === 'cash') {
                if ($cashReceived < $totalAmount) {
                    throw new \RuntimeException('Uang diterima kurang dari total pembayaran');
                }
                $change = round($cashReceived - $totalAmount, 2);
            }

            $member = null;
            if (!empty($validated['member_id'])) {
                $member = Member::lockForUpdate()->find($validated['member_id']);
                if (!$member) {
                    throw new \RuntimeException('Member tidak ditemukan');
                }
            }

            if ($isCredit) {
                $sisaLimit = (float) $member->limit_kredit - (float) $member->total_piutang;
                if ($totalAmount > $sisaLimit) {
                    throw new \RuntimeException('Melebihi limit kredit member');
                }
            }

            $dueDate = $isCredit
                ? ($validated['due_date'] ?? now()->addDays(30)->toDateString())
                : null;

            $transaction = Transaction::create([
                'invoice_number' => $this->generateInvoiceNumber(),
                'customer_name' => $validated['customer_name'],
                'customer_phone' => $validated['customer_phone'] ?? null,
                'discount' => $discountAmount,
                'payment_method' => $paymentMethod,
                'cash_received' => $cashReceived,
                'total_amount' => $totalAmount,
                'change' => $change,
                'user_id' => auth()->id(),
                'member_id' => $validated['member_id'] ?? null,
                'payment_status' => $isCredit ? 'BELUM LUNAS' : 'LUNAS',
                'due_date' => $dueDate,
                'notes' => $validated['notes'] ?? null,
            ]);

            foreach ($normalizedItems as $line) {
                TransactionItem::create([
                    'transaction_id' => $transaction->id,
                    'product_id' => $line['product']->id,
                    'qty' => $line['qty'],
                    'price' => $line['price'],
                    'subtotal' => $line['subtotal'],
                ]);

                $line['product']->decrement('stock', $line['qty']);
            }

            if ($isCredit && $member) {
                $createdReceivable = Receivable::create([
                    'no_piutang' => $this->generateReceivableNumber(),
                    'member_id' => $member->id,
                    'transaction_id' => $transaction->id,
                    'invoice_number' => $transaction->invoice_number,
                    'tanggal_transaksi' => now()->toDateString(),
                    'total_piutang' => $totalAmount,
                    'sisa_piutang' => $totalAmount,
                    'jatuh_tempo' => $dueDate,
                    'status' => 'BELUM LUNAS',
                    'keterangan' => 'Piutang dari transaksi kasir mobile',
                ]);

                $member->increment('total_piutang', $totalAmount);
            }

            DB::commit();

            $transaction->load(['user', 'member', 'items.product.category', 'receivable']);
            if ($currentUser instanceof User) {
                $this->notifyByEvent(
                    eventKey: 'transaction.created',
                    notification: new TransactionCreatedNotification(
                        $transaction,
                        $validated['items'],
                        $currentUser,
                        $this->notificationRouter()->metaForEvent('transaction.created')
                    ),
                    actor: $currentUser
                );

                if ($createdReceivable instanceof Receivable) {
                    $this->notifyByEvent(
                        eventKey: 'receivable.created',
                        notification: new ReceivableCreatedNotification(
                            $createdReceivable,
                            $transaction,
                            $currentUser,
                            $this->notificationRouter()->metaForEvent('receivable.created')
                        ),
                        actor: $currentUser
                    );
                }
            }

            return response()->json([
                'success' => true,
                'message' => 'Transaksi berhasil dibuat',
                'data' => $transaction,
            ], 201);
        } catch (\RuntimeException $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 422);
        } catch (\Throwable $e) {
            DB::rollBack();
            Log::error('Transaction API store error', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'payload' => $payload,
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan saat membuat transaksi',
            ], 500);
        }
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
        DB::beginTransaction();
        try {
            $transaction->load(['items.product', 'member', 'receivable']);
            $invoice = $transaction->invoice_number;
            $actor = auth()->user();

            foreach ($transaction->items as $item) {
                if ($item->product) {
                    $item->product->increment('stock', (int) $item->qty);
                }
            }

            if ($transaction->member && $transaction->payment_method === 'credit_card') {
                $currentPiutang = (float) $transaction->member->total_piutang;
                $amountToSubtract = min($currentPiutang, (float) $transaction->total_amount);
                $transaction->member->decrement('total_piutang', $amountToSubtract);
            }

            if ($transaction->receivable) {
                $transaction->receivable->delete();
            }

            $transaction->delete();

            DB::commit();
            if ($actor instanceof User) {
                $this->notifyByEvent(
                    eventKey: 'transaction.deleted',
                    notification: new TransactionDeletedNotification(
                        $invoice,
                        $actor,
                        $this->notificationRouter()->metaForEvent('transaction.deleted')
                    ),
                    actor: $actor
                );
            }

            return response()->json([
                'success' => true,
                'message' => "Transaksi {$invoice} berhasil dihapus",
            ]);
        } catch (\Throwable $e) {
            DB::rollBack();
            Log::error('Transaction API destroy error', [
                'transaction_id' => $transaction->id ?? null,
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Gagal menghapus transaksi',
            ], 500);
        }
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

    private function generateInvoiceNumber(): string
    {
        $prefix = 'INV';
        $date = now()->format('Ymd');
        $last = Transaction::where('invoice_number', 'like', $prefix . $date . '%')
            ->orderBy('invoice_number', 'desc')
            ->first();

        if ($last) {
            $lastNum = (int) substr($last->invoice_number, -4);
            $nextNum = str_pad((string) ($lastNum + 1), 4, '0', STR_PAD_LEFT);
        } else {
            $nextNum = '0001';
        }

        return $prefix . $date . $nextNum;
    }

    private function generateReceivableNumber(): string
    {
        $prefix = 'PTG';
        $date = now()->format('Ymd');
        $last = Receivable::where('no_piutang', 'like', $prefix . $date . '%')
            ->orderBy('no_piutang', 'desc')
            ->first();

        if ($last) {
            $lastNum = (int) substr($last->no_piutang, -4);
            $nextNum = str_pad((string) ($lastNum + 1), 4, '0', STR_PAD_LEFT);
        } else {
            $nextNum = '0001';
        }

        return $prefix . $date . $nextNum;
    }

    private function normalizePaymentMethod(string $method): ?string
    {
        $normalized = strtolower(trim($method));
        $map = [
            'tunai' => 'cash',
            'cash' => 'cash',
            'debit' => 'debit_card',
            'debit_card' => 'debit_card',
            'kredit' => 'credit_card',
            'credit' => 'credit_card',
            'credit_card' => 'credit_card',
            'hutang' => 'credit_card',
            'e-wallet' => 'e_wallet',
            'e_wallet' => 'e_wallet',
            'ewallet' => 'e_wallet',
            'transfer' => 'transfer',
        ];

        return $map[$normalized] ?? null;
    }

    private function notificationRouter(): NotificationRoutingService
    {
        return app(NotificationRoutingService::class);
    }

    private function notifyByEvent(string $eventKey, $notification, User $actor): void
    {
        try {
            $this->notificationRouter()->send($eventKey, $notification, $actor);
        } catch (\Throwable $e) {
            Log::warning('Transaction API notification failed: ' . $e->getMessage());
        }
    }
}
