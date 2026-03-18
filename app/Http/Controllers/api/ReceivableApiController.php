<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Receivable;
use App\Models\ReceivablePayment;
use App\Models\Member;
use App\Models\Transaction;
use App\Models\User;
use App\Notifications\PaymentReceivedNotification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Carbon\Carbon;

class ReceivableApiController extends Controller
{
    /**
     * Constructor
     */
    public function __construct()
    {
        // Middleware handled in routes/api.php
    }

    /**
     * Display a listing of receivables.
     */
    public function index(Request $request)
    {
        try {
            $query = Receivable::with(['member', 'transaction'])->latest();

            // Filter by status
            if ($request->has('status') && $request->status !== 'all') {
                $query->where('status', $request->status);
            }

            // Filter by member
            if ($request->has('member_id') && $request->member_id !== 'all') {
                $query->where('member_id', $request->member_id);
            }

            // Search functionality
            if ($request->filled('search')) {
                $search = $request->search;
                $query->where(function($q) use ($search) {
                    $q->where('invoice_number', 'like', "%{$search}%")
                      ->orWhere('no_piutang', 'like', "%{$search}%")
                      ->orWhereHas('member', function($q) use ($search) {
                          $q->where('nama', 'like', "%{$search}%")
                            ->orWhere('kode_member', 'like', "%{$search}%");
                      });
                });
            }

            $perPage = $request->get('per_page', 20);
            $receivables = $query->paginate($perPage);

            return response()->json([
                'success' => true,
                'message' => 'Receivables retrieved successfully',
                'data' => $receivables
            ], 200);

        } catch (\Exception $e) {
            Log::error('API Receivable index error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve receivables',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Display the specified receivable.
     */
    public function show($id)
    {
        try {
            $receivable = Receivable::with([
                'member',
                'transaction.items.product',
                'payments' => function($query) {
                    $query->with('kasir')->latest();
                }
            ])->findOrFail($id);

            return response()->json([
                'success' => true,
                'message' => 'Receivable details retrieved successfully',
                'data' => $receivable
            ], 200);

        } catch (\Exception $e) {
            Log::error('API Receivable show error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Receivable not found',
                'error' => $e->getMessage()
            ], 404);
        }
    }

    /**
     * Process payment for receivable.
     */
    public function pay(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'jumlah_bayar' => 'required|numeric|min:1000',
            'metode_bayar' => 'required|in:tunai,transfer',
            'keterangan' => 'nullable|string|max:255',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation errors',
                'errors' => $validator->errors()
            ], 422);
        }

        DB::beginTransaction();

        try {
            $receivable = Receivable::with('member')->findOrFail($id);

            if ($receivable->status === 'LUNAS') {
                return response()->json(['success' => false, 'message' => 'Piutang sudah lunas'], 400);
            }

            if ($request->jumlah_bayar > $receivable->sisa_piutang) {
                return response()->json([
                    'success' => false, 
                    'message' => 'Jumlah bayar melebihi sisa piutang (Rp ' . number_format($receivable->sisa_piutang, 0, ',', '.') . ')'
                ], 400);
            }

            $payment = ReceivablePayment::create([
                'receivable_id' => $receivable->id,
                'tanggal_bayar' => now(),
                'jumlah_bayar' => $request->jumlah_bayar,
                'metode_bayar' => $request->metode_bayar,
                'keterangan' => $request->keterangan,
                'kasir_id' => auth()->id(),
            ]);

            $receivable->sisa_piutang -= $request->jumlah_bayar;

            if ($receivable->sisa_piutang <= 0) {
                $receivable->status = 'LUNAS';
                $receivable->sisa_piutang = 0;

                if ($receivable->transaction_id) {
                    Transaction::where('id', $receivable->transaction_id)
                        ->update(['payment_status' => 'LUNAS']);
                }
            }

            $receivable->save();

            $member = $receivable->member;
            if ($member) {
                $member->total_piutang = Receivable::where('member_id', $member->id)
                    ->where('status', '!=', 'LUNAS')
                    ->sum('sisa_piutang');
                $member->save();
            }

            DB::commit();

            // Notifications
            try {
                $users = User::whereIn('role', ['owner', 'admin', 'kasir'])->get();
                $receivedBy = auth()->user();
                foreach ($users as $user) {
                    $user->notify(new PaymentReceivedNotification($receivable, $payment, $receivedBy));
                }
            } catch (\Exception $e) {
                Log::error('API Payment notification error: ' . $e->getMessage());
            }

            return response()->json([
                'success' => true,
                'message' => 'Payment processed successfully',
                'data' => [
                    'receivable' => $receivable->fresh(),
                    'payment' => $payment
                ]
            ], 200);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('API Receivable payment error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to process payment',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get payment history for a receivable.
     */
    public function paymentHistory($id)
    {
        try {
            $receivable = Receivable::findOrFail($id);
            $payments = $receivable->payments()->with('kasir')->latest()->get();

            return response()->json([
                'success' => true,
                'message' => 'Payment history retrieved successfully',
                'data' => $payments
            ], 200);

        } catch (\Exception $e) {
            Log::error('API Receivable payment history error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve payment history',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get receivable summary statistics.
     */
    public function getStatistics()
    {
        try {
            $stats = [
                'total_piutang' => Receivable::where('status', 'BELUM LUNAS')->sum('sisa_piutang'),
                'jumlah_piutang' => Receivable::where('status', 'BELUM LUNAS')->count(),
                'overdue_count' => Receivable::where('status', 'BELUM LUNAS')
                    ->whereDate('jatuh_tempo', '<', now())
                    ->count(),
            ];

            return response()->json([
                'success' => true,
                'message' => 'Receivable statistics retrieved successfully',
                'data' => $stats
            ], 200);

        } catch (\Exception $e) {
            Log::error('API Receivable statistics error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve statistics',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
