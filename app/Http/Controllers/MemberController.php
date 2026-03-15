<?php
// app/Http/Controllers/MemberController.php
/** @noinspection PhpUndefinedMethodInspection */
/** @noinspection PhpUndefinedVariableInspection */

namespace App\Http\Controllers;

use App\Models\Member;
use App\Models\User;
use App\Models\Transaction;
use App\Models\Receivable;
use App\Notifications\MemberCreatedNotification;
use App\Notifications\MemberUpdatedNotification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;


class MemberController extends Controller
{
     /**
     * Display a listing of members.
     */
    public function index(Request $request)
    {
        $query = Member::query();

        // Search
        if ($request->has('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('kode_member', 'like', "%{$search}%")
                  ->orWhere('nama', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%")
                  ->orWhere('no_telepon', 'like', "%{$search}%");
            });
        }

        // Filter tipe member
        if ($request->has('tipe') && $request->tipe !== 'all') {
            $query->where('tipe_member', $request->tipe);
        }

        // Filter status
        if ($request->has('status') && $request->status !== 'all') {
            $query->where('is_active', $request->status === 'active');
        }

        $members = $query->latest()->paginate(20);

        // Stats
        $stats = [
            'total' => Member::count(),
            'active' => Member::where('is_active', true)->count(),
            'total_piutang' => Member::sum('total_piutang'),
            'total_limit' => Member::sum('limit_kredit'),
        ];

        return view('members.index', compact('members', 'stats'));
    }

    /**
     * Show form for creating new member.
     */
    public function create()
    {
        return view('members.create');
    }

    /**
     * Store a newly created member.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'nama' => 'required|string|max:255',
            'email' => 'nullable|email|unique:members',
            'no_telepon' => 'nullable|string|max:20',
            'alamat' => 'nullable|string',
            'tipe_member' => 'required|in:biasa,gold,platinum',
            'limit_kredit' => 'required|numeric|min:0',
            'is_active' => 'boolean',
        ]);

        try {
            DB::beginTransaction();

            // Set tanggal registrasi
            $validated['tanggal_registrasi'] = now();

            // Kode member akan di-generate otomatis oleh boot method
            $member = Member::create($validated);

            // KIRIM NOTIFIKASI
            $this->sendMemberCreatedNotifications($member);

            DB::commit();

            return redirect()->route('members.index')
                ->with('success', 'Member ' . $member->nama . ' berhasil ditambahkan dengan kode ' . $member->kode_member);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error creating member: ' . $e->getMessage());
            return back()->withInput()->with('error', 'Gagal menambahkan member: ' . $e->getMessage());
        }
    }

    /**
     * Send notifications when member is created
     */
    private function sendMemberCreatedNotifications($member)
    {
        try {
            $currentUser = auth()->user();

            // 1. Kirim ke semua user dengan role owner
            $owners = User::where('role', 'owner')->get();

            foreach ($owners as $owner) {
                if ($owner->id != $currentUser->id) {
                    $owner->notify(new MemberCreatedNotification($member, $currentUser));
                    Log::info('Notifikasi member terkirim ke owner:', [
                        'owner_id' => $owner->id,
                        'member_id' => $member->id
                    ]);
                }
            }

            // 2. Kirim ke diri sendiri (pembuat)
            $currentUser->notify(new MemberCreatedNotification($member, $currentUser));
            Log::info('Notifikasi member terkirim ke diri sendiri:', [
                'user_id' => $currentUser->id,
                'member_id' => $member->id
            ]);

        } catch (\Exception $e) {
            Log::error('Gagal mengirim notifikasi member: ' . $e->getMessage());
        }
    }

    /**
     * Display member details.
     */
    public function show(Member $member)
    {
        // Load relationships
        $member->load(['receivables' => function($q) {
            $q->latest()->limit(10);
        }, 'transactions' => function($q) {
            $q->latest()->limit(10);
        }]);

        // Hitung statistik member dengan pengecekan null
        $stats = [
            'total_transaksi' => $member->transactions()->count(),
            'total_belanja' => $member->transactions()->sum('total_amount') ?? 0,
            'transaksi_kredit' => $member->receivables()->count(),
            'sisa_limit' => $member->limit_kredit - $member->total_piutang,
            'rata_rata_belanja' => $member->transactions()->avg('total_amount') ?? 0,
        ];

        return view('members.show', compact('member', 'stats'));
    }

    /**
     * Show form for editing member.
     */
    public function edit(Member $member)
    {
        return view('members.edit', compact('member'));
    }

    /**
     * Update member.
     */
    public function update(Request $request, Member $member)
    {
        $validated = $request->validate([
            'nama' => 'required|string|max:255',
            'email' => 'nullable|email|unique:members,email,' . $member->id,
            'no_telepon' => 'nullable|string|max:20',
            'alamat' => 'nullable|string',
            'tipe_member' => 'required|in:biasa,gold,platinum',
            'limit_kredit' => 'required|numeric|min:0',
            'is_active' => 'boolean',
        ]);

        try {
            DB::beginTransaction();

            // Catat perubahan sebelum update
            $oldData = $member->toArray();
            $changes = [];

            foreach ($validated as $key => $value) {
                if (isset($oldData[$key]) && $oldData[$key] != $value) {
                    $changes[$key] = [
                        'old' => $oldData[$key],
                        'new' => $value
                    ];
                }
            }

            $member->update($validated);

            // Kirim notifikasi jika ada perubahan
            if (!empty($changes)) {
                $this->sendMemberUpdatedNotifications($member, $changes);
            }

            DB::commit();

            return redirect()->route('members.show', $member)
                ->with('success', 'Data member berhasil diperbarui');

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error updating member: ' . $e->getMessage());
            return back()->withInput()->with('error', 'Gagal memperbarui member: ' . $e->getMessage());
        }
    }

    /**
     * Send notifications when member is updated
     */
    private function sendMemberUpdatedNotifications($member, $changes)
    {
        try {
            $currentUser = auth()->user();

            // 1. Kirim ke semua user dengan role owner
            $owners = User::where('role', 'owner')
                ->where('id', '!=', $currentUser->id)
                ->get();

            foreach ($owners as $owner) {
                $owner->notify(new MemberUpdatedNotification($member, $currentUser, $changes));
                Log::info('Notifikasi update member terkirim ke owner:', [
                    'owner_id' => $owner->id,
                    'member_id' => $member->id
                ]);
            }

            // 2. Kirim ke diri sendiri (pembuat update)
            $currentUser->notify(new MemberUpdatedNotification($member, $currentUser, $changes));
            Log::info('Notifikasi update member terkirim ke diri sendiri:', [
                'user_id' => $currentUser->id,
                'member_id' => $member->id
            ]);

        } catch (\Exception $e) {
            Log::error('Gagal mengirim notifikasi update member: ' . $e->getMessage());
        }
    }

    /**
     * Toggle member status (active/inactive)
     */
    public function toggleStatus(Member $member)
    {
        try {
            $oldStatus = $member->is_active;
            $member->update(['is_active' => !$member->is_active]);

            $status = $member->is_active ? 'diaktifkan' : 'dinonaktifkan';

            // Kirim notifikasi untuk perubahan status
            $changes = [
                'is_active' => [
                    'old' => $oldStatus,
                    'new' => $member->is_active
                ]
            ];

            $this->sendMemberUpdatedNotifications($member, $changes);

            return redirect()->back()
                ->with('success', "Member {$member->nama} berhasil {$status}");

        } catch (\Exception $e) {
            Log::error('Error toggling member status: ' . $e->getMessage());
            return redirect()->back()
                ->with('error', 'Gagal mengubah status member');
        }
    }

    /**
     * Display member's receivables (piutang)
     */
    public function receivables(Member $member)
    {
        $receivables = $member->receivables()
            ->with('transaction')
            ->latest()
            ->paginate(15);

        return view('members.receivables', compact('member', 'receivables'));
    }

    /**
     * Display member's transaction history
     */
    public function transactions(Member $member)
    {
        $transactions = $member->transactions()
            ->with('user')
            ->latest()
            ->paginate(15);

        return view('members.transactions', compact('member', 'transactions'));
    }

    /**
     * API: Search members for dropdown
     */
    public function search(Request $request)
    {
        $search = $request->get('q', '');

        $members = Member::where('is_active', true)
            ->where(function($query) use ($search) {
                $query->where('kode_member', 'like', "%{$search}%")
                      ->orWhere('nama', 'like', "%{$search}%")
                      ->orWhere('no_telepon', 'like', "%{$search}%");
            })
            ->limit(10)
            ->get()
            ->map(function($member) {
                return [
                    'id' => $member->id,
                    'text' => $member->kode_member . ' - ' . $member->nama,
                    'kode' => $member->kode_member,
                    'nama' => $member->nama,
                    'limit' => $member->limit_kredit,
                    'piutang' => $member->total_piutang,
                    'sisa_limit' => $member->limit_kredit - $member->total_piutang,
                ];
            });

        return response()->json($members);
    }

    /**
     * API: Get member data for AJAX
     */
    public function getMemberData(Member $member)
    {
        return response()->json([
            'success' => true,
            'member' => [
                'id' => $member->id,
                'kode' => $member->kode_member,
                'nama' => $member->nama,
                'tipe' => $member->tipe_member,
                'limit' => $member->limit_kredit,
                'piutang' => $member->total_piutang,
                'sisa_limit' => $member->limit_kredit - $member->total_piutang,
                'is_active' => $member->is_active,
            ]
        ]);
    }
}
