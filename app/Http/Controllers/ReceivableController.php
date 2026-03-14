<?php

namespace App\Http\Controllers;

use App\Models\Receivable;
use App\Models\ReceivablePayment;
use App\Models\Member;
use App\Models\Transaction;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Carbon\Carbon;

class ReceivableController extends Controller
{
    /**
     * Display a listing of receivables.
     */
    public function index(Request $request)
    {
        $query = Receivable::with(['member', 'transaction'])->latest();

        // Filter by status
        if ($request->has('status') && $request->status !== 'all') {
            $query->where('status', $request->status);
        }

        // Filter by member
        if ($request->has('member_id') && $request->member_id !== 'all') {
            $query->where('member_id', $request->member_id);
        }

        // Filter by date range
        if ($request->has('start_date') && $request->has('end_date')) {
            $query->whereBetween('tanggal_transaksi', [
                Carbon::parse($request->start_date)->startOfDay(),
                Carbon::parse($request->end_date)->endOfDay()
            ]);
        }

        // Search by invoice or member name
        if ($request->has('search')) {
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

        // Filter overdue
        if ($request->has('overdue') && $request->overdue) {
            $query->where('status', 'BELUM LUNAS')
                  ->whereDate('jatuh_tempo', '<', now());
        }

        $receivables = $query->paginate(20)->withQueryString();

        // Get members for filter dropdown
        $members = Member::where('is_active', true)->orderBy('nama')->get();

        // Statistics
        $stats = [
            'total_piutang' => Receivable::where('status', 'BELUM LUNAS')->sum('sisa_piutang'),
            'jumlah_piutang' => Receivable::where('status', 'BELUM LUNAS')->count(),
            'total_lunas' => Receivable::where('status', 'LUNAS')->sum('total_piutang'),
            'overdue_count' => Receivable::where('status', 'BELUM LUNAS')
                ->whereDate('jatuh_tempo', '<', now())
                ->count(),
            'total_member' => Receivable::where('status', 'BELUM LUNAS')
                ->distinct('member_id')
                ->count('member_id'),
        ];

        return view('receivables.index', compact('receivables', 'members', 'stats'));
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

            // Calculate remaining limit
            $sisaLimit = $receivable->member->limit_kredit - $receivable->member->total_piutang;

            // Get payment summary
            $paymentSummary = [
                'total_paid' => $receivable->payments->sum('jumlah_bayar'),
                'remaining' => $receivable->sisa_piutang,
                'payment_count' => $receivable->payments->count(),
                'last_payment' => $receivable->payments->first(),
            ];

            return view('receivables.show', compact('receivable', 'sisaLimit', 'paymentSummary'));

        } catch (\Exception $e) {
            Log::error('Error showing receivable: ' . $e->getMessage());
            return redirect()->route('receivables.index')
                ->with('error', 'Data piutang tidak ditemukan');
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
        ], [
            'jumlah_bayar.required' => 'Jumlah bayar wajib diisi',
            'jumlah_bayar.min' => 'Jumlah bayar minimal Rp 1.000',
            'metode_bayar.required' => 'Metode pembayaran wajib dipilih',
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        DB::beginTransaction();

        try {
            $receivable = Receivable::with('member')->findOrFail($id);

            // Check if already paid
            if ($receivable->status === 'LUNAS') {
                throw new \Exception('Piutang ini sudah lunas');
            }

            // Check payment amount
            if ($request->jumlah_bayar > $receivable->sisa_piutang) {
                throw new \Exception('Jumlah bayar melebihi sisa piutang (Rp ' .
                    number_format($receivable->sisa_piutang, 0, ',', '.') . ')');
            }

            // Create payment record
            $payment = ReceivablePayment::create([
                'receivable_id' => $receivable->id,
                'tanggal_bayar' => now(),
                'jumlah_bayar' => $request->jumlah_bayar,
                'metode_bayar' => $request->metode_bayar,
                'keterangan' => $request->keterangan,
                'kasir_id' => auth()->id(),
            ]);

            // Update receivable
            $receivable->sisa_piutang -= $request->jumlah_bayar;

            if ($receivable->sisa_piutang <= 0) {
                $receivable->status = 'LUNAS';
                $receivable->sisa_piutang = 0;

                // Update transaction status
                Transaction::where('id', $receivable->transaction_id)
                    ->update(['payment_status' => 'LUNAS']);
            }

            $receivable->save();

            // Update member's total piutang
            $member = $receivable->member;
            $member->total_piutang -= $request->jumlah_bayar;
            $member->save();

            DB::commit();

            Log::info('Payment recorded:', [
                'receivable' => $receivable->no_piutang,
                'amount' => $request->jumlah_bayar,
                'kasir' => auth()->user()->name,
                'remaining' => $receivable->sisa_piutang
            ]);

            $message = 'Pembayaran berhasil dicatat.';
            if ($receivable->status === 'LUNAS') {
                $message = 'Pembayaran lunas! Piutang telah diselesaikan.';
            } else {
                $message .= ' Sisa piutang: Rp ' . number_format($receivable->sisa_piutang, 0, ',', '.');
            }

            return redirect()->route('receivables.show', $receivable)
                ->with('success', $message);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Payment failed: ' . $e->getMessage());

            return redirect()->back()
                ->with('error', 'Gagal memproses pembayaran: ' . $e->getMessage())
                ->withInput();
        }
    }

    /**
     * Get payment history for a receivable (API/JSON).
     */
    public function paymentHistory($id)
    {
        try {
            $receivable = Receivable::findOrFail($id);

            $payments = $receivable->payments()
                ->with('kasir')
                ->latest()
                ->get()
                ->map(function($payment) {
                    return [
                        'id' => $payment->id,
                        'tanggal' => $payment->tanggal_bayar->format('d/m/Y H:i'),
                        'jumlah' => 'Rp ' . number_format($payment->jumlah_bayar, 0, ',', '.'),
                        'metode' => ucfirst($payment->metode_bayar),
                        'kasir' => $payment->kasir->name ?? '-',
                        'keterangan' => $payment->keterangan,
                    ];
                });

            return response()->json([
                'success' => true,
                'data' => $payments
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal mengambil riwayat pembayaran'
            ], 500);
        }
    }

    /**
     * Export receivables data.
     */
    public function export($format)
    {
        try {
            $receivables = Receivable::with(['member', 'transaction'])
                ->latest()
                ->get();

            if ($format === 'excel') {
                // TODO: Implement Excel export
                return redirect()->back()
                    ->with('info', 'Fitur export Excel akan segera tersedia');
            }

            if ($format === 'pdf') {
                // TODO: Implement PDF export
                return redirect()->back()
                    ->with('info', 'Fitur export PDF akan segera tersedia');
            }

            return redirect()->back()
                ->with('error', 'Format export tidak didukung');

        } catch (\Exception $e) {
            Log::error('Export failed: ' . $e->getMessage());
            return redirect()->back()
                ->with('error', 'Gagal melakukan export data');
        }
    }

    /**
     * Get receivable summary statistics (API/JSON).
     */
    public function getSummary()
    {
        try {
            $today = now()->toDateString();

            $summary = [
                'total_piutang' => Receivable::where('status', 'BELUM LUNAS')->sum('sisa_piutang'),
                'jumlah_piutang' => Receivable::where('status', 'BELUM LUNAS')->count(),
                'total_member_berhutang' => Receivable::where('status', 'BELUM LUNAS')
                    ->distinct('member_id')
                    ->count('member_id'),
                'overdue' => Receivable::where('status', 'BELUM LUNAS')
                    ->whereDate('jatuh_tempo', '<', now())
                    ->count(),
                'pembayaran_hari_ini' => ReceivablePayment::whereDate('created_at', $today)
                    ->sum('jumlah_bayar'),
                'pembayaran_bulan_ini' => ReceivablePayment::whereMonth('created_at', now()->month)
                    ->whereYear('created_at', now()->year)
                    ->sum('jumlah_bayar'),
                'rata_rata_piutang' => Receivable::where('status', 'BELUM LUNAS')
                    ->avg('sisa_piutang') ?? 0,
            ];

            return response()->json([
                'success' => true,
                'data' => $summary
            ]);

        } catch (\Exception $e) {
            Log::error('Error getting summary: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Gagal mengambil statistik'
            ], 500);
        }
    }

    /**
     * Get receivables by member (API/JSON).
     */
    public function getByMember($memberId)
    {
        try {
            $member = Member::findOrFail($memberId);

            $receivables = Receivable::where('member_id', $memberId)
                ->with('transaction')
                ->latest()
                ->get()
                ->map(function($receivable) {
                    return [
                        'id' => $receivable->id,
                        'no_piutang' => $receivable->no_piutang,
                        'invoice' => $receivable->invoice_number,
                        'tanggal' => $receivable->tanggal_transaksi->format('d/m/Y'),
                        'total' => 'Rp ' . number_format($receivable->total_piutang, 0, ',', '.'),
                        'sisa' => 'Rp ' . number_format($receivable->sisa_piutang, 0, ',', '.'),
                        'jatuh_tempo' => $receivable->jatuh_tempo ? $receivable->jatuh_tempo->format('d/m/Y') : '-',
                        'status' => $receivable->status,
                        'status_badge' => $receivable->status === 'LUNAS' ? 'success' : 'warning',
                    ];
                });

            return response()->json([
                'success' => true,
                'member' => [
                    'id' => $member->id,
                    'nama' => $member->nama,
                    'kode' => $member->kode_member,
                ],
                'data' => $receivables
            ]);

        } catch (\Exception $e) {
            Log::error('Error getting member receivables: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Gagal mengambil data piutang member'
            ], 500);
        }
    }

    /**
     * Print receivable details.
     */
    public function print($id)
    {
        try {
            $receivable = Receivable::with([
                'member',
                'transaction.items.product',
                'payments' => function($query) {
                    $query->with('kasir')->latest();
                }
            ])->findOrFail($id);

            return view('receivables.print', compact('receivable'));

        } catch (\Exception $e) {
            Log::error('Error printing receivable: ' . $e->getMessage());
            return redirect()->route('receivables.index')
                ->with('error', 'Data piutang tidak ditemukan');
        }
    }

    /**
     * Generate receipt number (helper method).
     */
    private function generateReceiptNumber()
    {
        $prefix = 'RCT';
        $date = now()->format('Ymd');
        $last = ReceivablePayment::where('id', 'like', $prefix . $date . '%')
            ->orderBy('id', 'desc')
            ->first();

        if ($last) {
            $lastNum = (int) substr($last->id, -4);
            $newNum = str_pad($lastNum + 1, 4, '0', STR_PAD_LEFT);
        } else {
            $newNum = '0001';
        }

        return $prefix . $date . $newNum;
    }
}
