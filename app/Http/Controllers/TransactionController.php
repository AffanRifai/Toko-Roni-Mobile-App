<?php
// app/Http/Controllers/TransactionController.php

namespace App\Http\Controllers;

use App\Models\Transaction;
use App\Models\TransactionItem;
use App\Models\Product;
use App\Models\Category;
use App\Models\Member;
use App\Models\Receivable;
use App\Models\User;
use App\Notifications\TransactionCreatedNotification;
use App\Notifications\TransactionUpdatedNotification;
use App\Notifications\TransactionDeletedNotification;
use App\Notifications\ReceivableCreatedNotification;
use App\Notifications\PaymentReceivedNotification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Carbon\Carbon;

class TransactionController extends Controller
{
    /**
     * Display a listing of transactions.
     */
    public function index(Request $request)
    {
        $query = Transaction::with(['user', 'member'])->latest();

        // Filter tanggal
        if ($request->has('filter')) {
            switch ($request->filter) {
                case 'today':
                    $query->whereDate('created_at', today());
                    break;
                case 'week':
                    $query->whereBetween('created_at', [
                        now()->startOfWeek(),
                        now()->endOfWeek()
                    ]);
                    break;
                case 'month':
                    $query->whereMonth('created_at', now()->month)
                        ->whereYear('created_at', now()->year);
                    break;
                case 'year':
                    $query->whereYear('created_at', now()->year);
                    break;
            }
        }

        // Filter status pembayaran
        if ($request->has('payment_status') && $request->payment_status !== 'all') {
            $query->where('payment_status', $request->payment_status);
        }

        // Filter metode pembayaran
        if ($request->has('payment_method') && $request->payment_method !== 'all') {
            $query->where('payment_method', $request->payment_method);
        }

        // Filter member
        if ($request->has('member_id') && $request->member_id !== 'all') {
            $query->where('member_id', $request->member_id);
        }

        // Search
        if ($request->has('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('invoice_number', 'like', "%{$search}%")
                    ->orWhere('customer_name', 'like', "%{$search}%")
                    ->orWhere('customer_phone', 'like', "%{$search}%")
                    ->orWhereHas('user', function ($q) use ($search) {
                        $q->where('name', 'like', "%{$search}%");
                    })
                    ->orWhereHas('member', function ($q) use ($search) {
                        $q->where('nama', 'like', "%{$search}%")
                            ->orWhere('kode_member', 'like', "%{$search}%");
                    });
            });
        }

        // Filter tanggal custom
        if ($request->has('start_date') && $request->has('end_date')) {
            $query->whereBetween('created_at', [
                Carbon::parse($request->start_date)->startOfDay(),
                Carbon::parse($request->end_date)->endOfDay()
            ]);
        }

        $transactions = $query->paginate(20);

        // Data untuk filter dropdown
        $members = Member::where('is_active', true)->get();

        // Stats untuk dashboard
        $totalTransactions = Transaction::count();
        $totalRevenue = Transaction::sum('total_amount');
        $todayTransactions = Transaction::whereDate('created_at', today())->count();
        $todayRevenue = Transaction::whereDate('created_at', today())->sum('total_amount');
        $averageTransaction = Transaction::avg('total_amount') ?? 0;
        $totalCredit = Transaction::where('payment_method', 'credit_card')
            ->where('payment_status', 'BELUM LUNAS')
            ->sum('total_amount');
        $totalItems = TransactionItem::count();

        // Stats array untuk kemudahan
        $stats = [
            'total_transactions' => $totalTransactions,
            'total_revenue' => $totalRevenue,
            'today_transactions' => $todayTransactions,
            'today_revenue' => $todayRevenue,
            'avg_transaction' => $averageTransaction,
            'total_credit' => $totalCredit,
            'total_items' => $totalItems,
        ];

        return view('transactions.index', compact(
            'transactions',
            'stats',
            'members',
            'totalRevenue',
            'averageTransaction',
            'todayTransactions',
            'totalItems',
            'totalCredit',
            'todayRevenue',
            'totalTransactions'
        ));
    }

    /**
     * FUNGSI UNTUK MENAMPILKAN HALAMAN SEARCH (API)
     */
    public function search(Request $request)
    {
        $query = $request->get('q');

        $transactions = Transaction::with('items')
            ->where(function ($q) use ($query) {
                $q->where('invoice_number', 'like', "%{$query}%")
                    ->orWhere('customer_name', 'like', "%{$query}%")
                    ->orWhere('customer_phone', 'like', "%{$query}%");
            })
            ->whereDoesntHave('delivery') // Hanya transaksi yang belum punya pengiriman
            ->latest()
            ->limit(10)
            ->get()
            ->map(function ($transaction) {
                return [
                    'id' => $transaction->id,
                    'invoice_number' => $transaction->invoice_number,
                    'customer_name' => $transaction->customer_name,
                    'customer_phone' => $transaction->customer_phone,
                    'customer_address' => $transaction->customer_address,
                    'date' => $transaction->created_at->format('d/m/Y H:i'),
                    'total' => $transaction->total_amount,
                    'total_formatted' => number_format($transaction->total_amount, 0, ',', '.'),
                    'total_items' => $transaction->items->sum('qty'),
                ];
            });

        return response()->json($transactions);
    }

    /**
     * API: Transaksi terbaru (7 hari terakhir)
     * GET /api/v1/transactions/recent
     */
    public function recentTransactions()
    {
        try {
            $transactions = Transaction::with(['items.product'])
                ->whereBetween('created_at', [now()->subDays(7)->startOfDay(), now()->endOfDay()])
                ->latest()
                ->limit(10)
                ->get()
                ->map(function ($trx) {
                    // Ambil nama produk dari item pertama
                    $firstItem   = $trx->items->first();
                    $productName = $firstItem?->product?->name ?? 'Berbagai produk';

                    // Jika lebih dari 1 item, tambahkan keterangan
                    if ($trx->items->count() > 1) {
                        $productName .= ' +' . ($trx->items->count() - 1) . ' lainnya';
                    }

                    return [
                        'id'             => $trx->id,
                        'invoice_number' => $trx->invoice_number,
                        'product_name'   => $productName,
                        'total_amount'   => $trx->total_amount,
                        'status'         => strtolower($trx->payment_status) === 'lunas'
                            ? 'success'
                            : 'pending',
                        'created_at'     => $trx->created_at->toISOString(),
                    ];
                });

            return response()->json([
                'status' => true,
                'data'   => $transactions,
            ]);
        } catch (\Exception $e) {
            return response()->json(['status' => false, 'message' => $e->getMessage()], 500);
        }
    }


    /**
     * API: Statistik transaksi hari ini
     * GET /api/v1/transactions/today-stats
     */
    public function todayStats()
    {
        try {
            $today            = now()->toDateString();
            $totalTransaksi   = Transaction::whereDate('created_at', $today)->count();
            $totalPendapatan  = Transaction::whereDate('created_at', $today)->sum('total_amount');
            $totalItem        = TransactionItem::whereHas('transaction', function ($q) use ($today) {
                $q->whereDate('created_at', $today);
            })->sum('qty');

            return response()->json([
                'status' => true,
                'data'   => [
                    'total_transaksi'  => $totalTransaksi,
                    'total_pendapatan' => $totalPendapatan,
                    'total_item'       => $totalItem,
                ],
            ]);
        } catch (\Exception $e) {
            return response()->json(['status' => false, 'message' => $e->getMessage()], 500);
        }
    }

    /**
     * Show the form for creating a new transaction.
     */
    public function create()
    {
        $products = Product::where('is_active', 1)
            ->where('stock', '>', 0)
            ->with('category')
            ->get();

        $categories = Category::with(['products' => function ($q) {
            $q->where('is_active', 1)->where('stock', '>', 0);
        }])->get();

        $members = Member::where('is_active', true)->get();
        $invoiceNumber = 'INV' . date('YmdHis') . rand(100, 999);

        return view('transactions.create', compact('products', 'categories', 'invoiceNumber', 'members'));
    }

    /**
     * Generate invoice number.
     */
    private function generateInvoiceNumber()
    {
        $prefix = 'INV';
        $date = date('Ymd');
        $last = Transaction::where('invoice_number', 'like', $prefix . $date . '%')
            ->orderBy('invoice_number', 'desc')
            ->first();

        if ($last) {
            $lastNum = (int) substr($last->invoice_number, -4);
            $nextNum = str_pad($lastNum + 1, 4, '0', STR_PAD_LEFT);
        } else {
            $nextNum = '0001';
        }

        return $prefix . $date . $nextNum;
    }

    /**
     * GENERATE NOMOR PIUTANG
     */
    private function generateReceivableNumber()
    {
        $prefix = 'PTG';
        $date = date('Ymd');

        // Cari nomor terakhir untuk hari ini
        $lastReceivable = Receivable::where('no_piutang', 'LIKE', $prefix . $date . '%')
            ->orderBy('no_piutang', 'desc')
            ->first();

        if ($lastReceivable) {
            // Ambil 4 digit terakhir
            $lastNumber = (int) substr($lastReceivable->no_piutang, -4);
            $newNumber = str_pad($lastNumber + 1, 4, '0', STR_PAD_LEFT);
        } else {
            $newNumber = '0001';
        }

        $result = $prefix . $date . $newNumber;

        // Pastikan tidak kosong
        if (empty($result)) {
            $result = $prefix . $date . '0001';
        }

        Log::info('Generated receivable number: ' . $result);

        return $result;
    }

    /**
     * Validate member for credit transaction.
     */
    private function validateMemberForCredit($memberId, $totalAmount)
    {
        if (!$memberId) {
            throw new \Exception('Member harus dipilih untuk transaksi kredit');
        }

        $member = Member::find($memberId);
        if (!$member) {
            throw new \Exception('Member tidak ditemukan');
        }

        if (!$member->is_active) {
            throw new \Exception('Member tidak aktif');
        }

        $sisaLimit = $member->limit_kredit - $member->total_piutang;
        if ($totalAmount > $sisaLimit) {
            throw new \Exception(
                'Melebihi limit kredit. Sisa limit: Rp ' .
                    number_format($sisaLimit, 0, ',', '.')
            );
        }

        return $member;
    }

    /**
     * Store a newly created transaction.
     */
    public function store(Request $request)
    {
        DB::beginTransaction();

        try {
            // Validasi dasar
            $validator = Validator::make($request->all(), [
                'customer_name' => 'required|string',
                'payment_method' => 'required|in:cash,debit_card,credit_card,e_wallet,transfer',
                'items' => 'required|json',
                'total_amount' => 'required|numeric|min:0',
            ]);

            if ($validator->fails()) {
                return back()->withErrors($validator)->withInput();
            }

            // Decode items
            $items = json_decode($request->items, true);
            if (empty($items)) {
                throw new \Exception('Tidak ada item dalam transaksi');
            }

            // Generate invoice number
            $invoiceNumber = $this->generateInvoiceNumber();

            // Hitung kembalian untuk cash
            $change = 0;
            if ($request->payment_method === 'cash') {
                $change = $request->cash_received - $request->total_amount;
                if ($change < 0) {
                    throw new \Exception('Uang diterima kurang dari total pembayaran');
                }
            }

            // Simpan transaksi
            $transaction = Transaction::create([
                'invoice_number' => $invoiceNumber,
                'customer_name' => $request->customer_name,
                'customer_phone' => $request->customer_phone ?? null,
                'discount' => $request->discount ?? 0,
                'payment_method' => $request->payment_method,
                'cash_received' => $request->cash_received ?? 0,
                'total_amount' => $request->total_amount,
                'change' => $change,
                'user_id' => auth()->id(),
                'member_id' => $request->member_id ?? null,
                'payment_status' => ($request->payment_method === 'credit_card') ? 'BELUM LUNAS' : 'LUNAS',
                'due_date' => $request->due_date ?? null,
                'notes' => $request->notes ?? null,
            ]);

            // Validasi transaksi berhasil dibuat
            if (!$transaction || !$transaction->id) {
                throw new \Exception('Gagal menyimpan transaksi');
            }

            // Simpan item dan update stok
            foreach ($items as $item) {
                $product = Product::find($item['product_id']);
                if (!$product) {
                    throw new \Exception('Produk dengan ID ' . $item['product_id'] . ' tidak ditemukan');
                }

                if ($product->stock < $item['qty']) {
                    throw new \Exception('Stok ' . $product->name . ' tidak mencukupi. Tersedia: ' . $product->stock);
                }

                TransactionItem::create([
                    'transaction_id' => $transaction->id,
                    'product_id' => $product->id,
                    'qty' => $item['qty'],
                    'price' => $item['price'],
                    'subtotal' => $item['price'] * $item['qty'],
                ]);

                $product->decrement('stock', $item['qty']);
            }

            // Handle credit transaction - MEMBER
            $receivable = null;
            if ($request->payment_method === 'credit_card') {
                if (!$request->member_id) {
                    throw new \Exception('Member harus dipilih untuk transaksi kredit');
                }

                $member = Member::find($request->member_id);
                if (!$member) {
                    throw new \Exception('Member tidak ditemukan');
                }

                // Validasi limit
                $sisaLimit = $member->limit_kredit - $member->total_piutang;
                if ($request->total_amount > $sisaLimit) {
                    throw new \Exception('Melebihi limit kredit. Sisa limit: Rp ' . number_format($sisaLimit, 0, ',', '.'));
                }

                // Validasi due date
                if (!$request->due_date) {
                    throw new \Exception('Tanggal jatuh tempo harus diisi');
                }

                // Generate nomor piutang
                $noPiutang = $this->generateReceivableNumber();

                // Buat receivable
                $receivable = Receivable::create([
                    'no_piutang' => $noPiutang,
                    'member_id' => $member->id,
                    'transaction_id' => $transaction->id,
                    'invoice_number' => $invoiceNumber,
                    'tanggal_transaksi' => date('Y-m-d'),
                    'total_piutang' => $request->total_amount,
                    'sisa_piutang' => $request->total_amount,
                    'jatuh_tempo' => $request->due_date,
                    'status' => 'BELUM LUNAS',
                    'keterangan' => 'Transaksi kredit - ' . $request->customer_name,
                ]);

                // Update total piutang member
                $member->increment('total_piutang', $request->total_amount);
            }

            DB::commit();

            // KIRIM NOTIFIKASI
            $this->sendTransactionCreatedNotifications($transaction, $items, $receivable);

            // Log success
            Log::info('Transaction saved successfully:', [
                'id' => $transaction->id,
                'invoice' => $transaction->invoice_number,
                'redirect_to' => route('transactions.show', $transaction)
            ]);

            // Redirect ke halaman detail
            return redirect()->route('transactions.show', $transaction)
                ->with('success', 'Transaksi berhasil disimpan')
                ->with('invoice', $transaction->invoice_number);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Transaction Error: ' . $e->getMessage());
            Log::error('Stack trace: ' . $e->getTraceAsString());

            return back()->with('error', 'Error: ' . $e->getMessage())->withInput();
        }
    }

    /**
     * DETAIL TRANSAKSI
     */
    public function show($id)
    {
        try {
            $transaction = Transaction::with([
                'items.product',
                'user',
                'member',
                'receivable' => function ($query) {
                    $query->with('payments.kasir');
                }
            ])->findOrFail($id);

            Log::info('Showing transaction:', [
                'id' => $id,
                'invoice' => $transaction->invoice_number,
                'payment_method' => $transaction->payment_method,
                'payment_status' => $transaction->payment_status,
                'has_receivable' => $transaction->receivable ? 'yes' : 'no'
            ]);

            return view('transactions.show', compact('transaction'));
        } catch (\Exception $e) {
            Log::error('Transaction not found:', ['id' => $id, 'error' => $e->getMessage()]);
            return redirect()->route('transactions.index')
                ->with('error', 'Transaksi tidak ditemukan');
        }
    }

    /**
     * Show the form for editing the specified transaction.
     */
    public function edit(Transaction $transaction)
    {
        // Hanya transaksi dengan status tertentu yang bisa diedit
        if (!in_array($transaction->payment_status, ['LUNAS', 'BELUM LUNAS'])) {
            return redirect()->route('transactions.show', $transaction)
                ->with('error', 'Transaksi tidak dapat diedit');
        }

        $products = Product::where('is_active', 1)->get();
        $members = Member::where('is_active', true)->get();

        return view('transactions.edit', compact('transaction', 'products', 'members'));
    }

    /**
     * Update the specified transaction.
     */
    public function update(Request $request, Transaction $transaction)
    {
        DB::beginTransaction();

        try {
            // Validasi
            $validator = Validator::make($request->all(), [
                'customer_name' => 'required|string',
                'customer_phone' => 'nullable|string',
                'notes' => 'nullable|string',
            ]);

            if ($validator->fails()) {
                return back()->withErrors($validator)->withInput();
            }

            // Catat perubahan
            $oldData = $transaction->toArray();
            $changes = [];

            // Update data
            $transaction->update([
                'customer_name' => $request->customer_name,
                'customer_phone' => $request->customer_phone,
                'notes' => $request->notes,
            ]);

            // Catat perubahan untuk notifikasi
            foreach ($request->only(['customer_name', 'customer_phone', 'notes']) as $key => $value) {
                if (isset($oldData[$key]) && $oldData[$key] != $value) {
                    $changes[$key] = [
                        'old' => $oldData[$key],
                        'new' => $value
                    ];
                }
            }

            DB::commit();

            // Kirim notifikasi jika ada perubahan
            if (!empty($changes)) {
                $this->sendTransactionUpdatedNotifications($transaction, $changes);
            }

            return redirect()->route('transactions.show', $transaction)
                ->with('success', 'Data transaksi berhasil diperbarui');
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Transaction update failed: ' . $e->getMessage());
            return back()->with('error', 'Gagal memperbarui transaksi: ' . $e->getMessage());
        }
    }

    /**
     * Remove the specified transaction.
     */
    public function destroy(Transaction $transaction)
    {
        DB::beginTransaction();

        try {
            // Hanya transaksi tertentu yang bisa dihapus
            if ($transaction->payment_method === 'credit_card' && $transaction->receivable) {
                // Kembalikan stok
                foreach ($transaction->items as $item) {
                    $item->product->increment('stock', $item->qty);
                }

                // Kurangi piutang member
                if ($transaction->member) {
                    $transaction->member->decrement('total_piutang', $transaction->total_amount);
                }

                // Hapus receivable
                $transaction->receivable->delete();
            }

            $invoiceNumber = $transaction->invoice_number;
            $currentUser = auth()->user();

            $transaction->delete();

            DB::commit();

            // Kirim notifikasi penghapusan
            $this->sendTransactionDeletedNotifications($invoiceNumber, $currentUser);

            return redirect()->route('transactions.index')
                ->with('success', 'Transaksi ' . $invoiceNumber . ' berhasil dihapus');
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Transaction deletion failed: ' . $e->getMessage());
            return back()->with('error', 'Gagal menghapus transaksi: ' . $e->getMessage());
        }
    }

    /**
     * Print receipt.
     */
    public function printReceipt($id)
    {
        $transaction = Transaction::with(['items.product', 'user', 'member'])->findOrFail($id);
        return view('transactions.print', compact('transaction'));
    }

    /**
     * Get product data (AJAX).
     */
    public function getProduct($id)
    {
        try {
            $product = Product::findOrFail($id);
            return response()->json([
                'success' => true,
                'product' => [
                    'id' => $product->id,
                    'name' => $product->name,
                    'price' => $product->price,
                    'stock' => $product->stock,
                    'code' => $product->code,
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json(['success' => false], 404);
        }
    }

    /**
     * Get member data (AJAX).
     */
    public function getMember($id)
    {
        try {
            $member = Member::findOrFail($id);
            $sisaLimit = $member->limit_kredit - $member->total_piutang;

            return response()->json([
                'success' => true,
                'member' => [
                    'id' => $member->id,
                    'kode' => $member->kode_member,
                    'nama' => $member->nama,
                    'limit' => $member->limit_kredit,
                    'piutang' => $member->total_piutang,
                    'sisa_limit' => $sisaLimit,
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json(['success' => false], 404);
        }
    }

    // ==================== NOTIFICATION METHODS ====================

    /**
     * Send notifications when transaction is created
     */
    private function sendTransactionCreatedNotifications($transaction, $items, $receivable = null)
    {
        try {
            $currentUser = auth()->user();

            // 1. Kirim ke semua owner
            $owners = User::where('role', 'owner')->get();

            foreach ($owners as $owner) {
                if ($owner->id != $currentUser->id) {
                    $owner->notify(new TransactionCreatedNotification($transaction, $items, $currentUser));
                    Log::info('Notifikasi transaksi terkirim ke owner:', [
                        'owner_id' => $owner->id,
                        'invoice' => $transaction->invoice_number
                    ]);
                }
            }

            // 2. Kirim ke kasir lain (jika ada)
            $kasirs = User::where('role', 'kasir')
                ->where('id', '!=', $currentUser->id)
                ->get();

            foreach ($kasirs as $kasir) {
                $kasir->notify(new TransactionCreatedNotification($transaction, $items, $currentUser));
                Log::info('Notifikasi transaksi terkirim ke kasir:', [
                    'kasir_id' => $kasir->id,
                    'invoice' => $transaction->invoice_number
                ]);
            }

            // 3. Kirim ke diri sendiri
            $currentUser->notify(new TransactionCreatedNotification($transaction, $items, $currentUser));
            Log::info('Notifikasi transaksi terkirim ke diri sendiri:', [
                'user_id' => $currentUser->id,
                'invoice' => $transaction->invoice_number
            ]);

            // 4. Jika transaksi kredit, kirim notifikasi khusus ke owner
            if ($receivable && $transaction->payment_method === 'credit_card') {
                foreach ($owners as $owner) {
                    $owner->notify(new ReceivableCreatedNotification($receivable, $transaction, $currentUser));
                }

                // Juga kirim ke member yang bersangkutan (jika member punya user account)
                if ($transaction->member) {
                    // Jika member terdaftar sebagai user, kirim notifikasi
                    $memberUser = User::where('email', $transaction->member->email)->first();
                    if ($memberUser) {
                        $memberUser->notify(new ReceivableCreatedNotification($receivable, $transaction, $currentUser));
                    }
                }
            }
        } catch (\Exception $e) {
            Log::error('Gagal mengirim notifikasi transaksi: ' . $e->getMessage());
        }
    }

    /**
     * Send notifications when transaction is updated
     */
    private function sendTransactionUpdatedNotifications($transaction, $changes)
    {
        try {
            $currentUser = auth()->user();

            // 1. Kirim ke semua owner
            $owners = User::where('role', 'owner')->get();

            foreach ($owners as $owner) {
                if ($owner->id != $currentUser->id) {
                    $owner->notify(new TransactionUpdatedNotification($transaction, $currentUser, $changes));
                    Log::info('Notifikasi update transaksi terkirim ke owner:', [
                        'owner_id' => $owner->id,
                        'invoice' => $transaction->invoice_number
                    ]);
                }
            }

            // 2. Kirim ke kasir yang membuat transaksi (jika berbeda)
            if ($transaction->user_id && $transaction->user_id != $currentUser->id) {
                $creator = User::find($transaction->user_id);
                if ($creator) {
                    $creator->notify(new TransactionUpdatedNotification($transaction, $currentUser, $changes));
                    Log::info('Notifikasi update transaksi terkirim ke creator:', [
                        'creator_id' => $creator->id,
                        'invoice' => $transaction->invoice_number
                    ]);
                }
            }

            // 3. Kirim ke diri sendiri
            $currentUser->notify(new TransactionUpdatedNotification($transaction, $currentUser, $changes));
            Log::info('Notifikasi update transaksi terkirim ke diri sendiri:', [
                'user_id' => $currentUser->id,
                'invoice' => $transaction->invoice_number
            ]);
        } catch (\Exception $e) {
            Log::error('Gagal mengirim notifikasi update transaksi: ' . $e->getMessage());
        }
    }

    /**
     * Send notifications when transaction is deleted
     */
    private function sendTransactionDeletedNotifications($invoiceNumber, $deletedBy)
    {
        try {
            // 1. Kirim ke semua owner
            $owners = User::where('role', 'owner')
                ->where('id', '!=', $deletedBy->id)
                ->get();

            foreach ($owners as $owner) {
                $owner->notify(new TransactionDeletedNotification($invoiceNumber, $deletedBy));
                Log::info('Notifikasi hapus transaksi terkirim ke owner:', [
                    'owner_id' => $owner->id,
                    'invoice' => $invoiceNumber
                ]);
            }

            // 2. Kirim ke semua kasir
            $kasirs = User::where('role', 'kasir')
                ->where('id', '!=', $deletedBy->id)
                ->get();

            foreach ($kasirs as $kasir) {
                $kasir->notify(new TransactionDeletedNotification($invoiceNumber, $deletedBy));
                Log::info('Notifikasi hapus transaksi terkirim ke kasir:', [
                    'kasir_id' => $kasir->id,
                    'invoice' => $invoiceNumber
                ]);
            }

            // 3. Kirim ke diri sendiri
            $deletedBy->notify(new TransactionDeletedNotification($invoiceNumber, $deletedBy));
            Log::info('Notifikasi hapus transaksi terkirim ke diri sendiri:', [
                'user_id' => $deletedBy->id,
                'invoice' => $invoiceNumber
            ]);
        } catch (\Exception $e) {
            Log::error('Gagal mengirim notifikasi hapus transaksi: ' . $e->getMessage());
        }
    }
}
