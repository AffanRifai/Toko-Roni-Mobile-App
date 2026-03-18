<?php
// app/Http/Controllers/Api/MemberApiController.php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Member;
use App\Models\User;
use App\Models\Transaction;
use App\Models\Receivable;
use App\Notifications\MemberCreatedNotification;
use App\Notifications\MemberUpdatedNotification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;

class MemberApiController extends Controller
{
    /**
     * Constructor
     */
    public function __construct()
    {
        // Middleware handled in routes/api.php
    }

    /**
     * Display a listing of members.
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function index(Request $request)
    {
        try {
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

            // Pagination
            $perPage = $request->get('per_page', 20);
            $members = $query->latest()->paginate($perPage);

            // Stats
            $stats = [
                'total' => Member::count(),
                'active' => Member::where('is_active', true)->count(),
                'total_piutang' => (float) Member::sum('total_piutang'),
                'total_limit' => (float) Member::sum('limit_kredit'),
            ];

            return response()->json([
                'success' => true,
                'message' => 'Members retrieved successfully',
                'data' => $members,
                'stats' => $stats
            ], 200);
        } catch (\Exception $e) {
            Log::error('API Member index error: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve members',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Store a newly created member.
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'nama' => 'required|string|max:255',
            'email' => 'nullable|email|unique:members',
            'no_telepon' => 'nullable|string|max:20',
            'alamat' => 'nullable|string',
            'tipe_member' => 'required|in:biasa,gold,platinum',
            'limit_kredit' => 'required|numeric|min:0',
            'is_active' => 'sometimes|boolean',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation errors',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            DB::beginTransaction();

            // Prepare data
            $data = $validator->validated();
            $data['tanggal_registrasi'] = now();
            if (!isset($data['is_active'])) {
                $data['is_active'] = true;
            }

            // Kode member akan di-generate otomatis oleh boot method
            $member = Member::create($data);

            // KIRIM NOTIFIKASI
            $this->sendMemberCreatedNotifications($member);

            DB::commit();

            Log::info('API Member created:', [
                'member_id' => $member->id,
                'kode_member' => $member->kode_member,
                'nama' => $member->nama
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Member berhasil ditambahkan',
                'data' => $member
            ], 201);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('API Error creating member: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Gagal menambahkan member',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Display the specified member.
     *
     * @param int $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function show($id)
    {
        try {
            $member = Member::with(['receivables' => function($q) {
                $q->latest()->limit(10);
            }, 'transactions' => function($q) {
                $q->latest()->limit(10);
            }])->find($id);

            if (!$member) {
                return response()->json([
                    'success' => false,
                    'message' => 'Member not found'
                ], 404);
            }

            // Hitung statistik member dengan pengecekan null
            $stats = [
                'total_transaksi' => $member->transactions()->count(),
                'total_belanja' => (float) ($member->transactions()->sum('total_amount') ?? 0),
                'transaksi_kredit' => $member->receivables()->count(),
                'sisa_limit' => (float) ($member->limit_kredit - $member->total_piutang),
                'rata_rata_belanja' => (float) ($member->transactions()->avg('total_amount') ?? 0),
            ];

            return response()->json([
                'success' => true,
                'message' => 'Member retrieved successfully',
                'data' => $member,
                'stats' => $stats
            ], 200);
        } catch (\Exception $e) {
            Log::error('API Member show error: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve member',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Update the specified member.
     *
     * @param Request $request
     * @param int $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function update(Request $request, $id)
    {
        $member = Member::find($id);

        if (!$member) {
            return response()->json([
                'success' => false,
                'message' => 'Member not found'
            ], 404);
        }

        $validator = Validator::make($request->all(), [
            'nama' => 'sometimes|required|string|max:255',
            'email' => 'nullable|email|unique:members,email,' . $member->id,
            'no_telepon' => 'nullable|string|max:20',
            'alamat' => 'nullable|string',
            'tipe_member' => 'sometimes|required|in:biasa,gold,platinum',
            'limit_kredit' => 'sometimes|required|numeric|min:0',
            'is_active' => 'sometimes|boolean',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation errors',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            DB::beginTransaction();

            // Catat perubahan sebelum update
            $oldData = $member->toArray();
            $updateData = $validator->validated();
            $changes = [];

            foreach ($updateData as $key => $value) {
                if (isset($oldData[$key]) && $oldData[$key] != $value) {
                    $changes[$key] = [
                        'old' => $oldData[$key],
                        'new' => $value
                    ];
                }
            }

            $member->update($updateData);

            // Kirim notifikasi jika ada perubahan
            if (!empty($changes)) {
                $this->sendMemberUpdatedNotifications($member, $changes);
            }

            DB::commit();

            Log::info('API Member updated:', [
                'member_id' => $member->id,
                'kode_member' => $member->kode_member,
                'changes' => $changes
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Data member berhasil diperbarui',
                'data' => $member->fresh(),
                'changes' => $changes
            ], 200);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('API Error updating member: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Gagal memperbarui member',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Remove the specified member.
     *
     * @param int $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function destroy($id)
    {
        $member = Member::find($id);

        if (!$member) {
            return response()->json([
                'success' => false,
                'message' => 'Member not found'
            ], 404);
        }

        try {
            // Check if member has transactions
            if ($member->transactions()->count() > 0) {
                return response()->json([
                    'success' => false,
                    'message' => 'Member tidak dapat dihapus karena memiliki riwayat transaksi'
                ], 400);
            }

            // Check if member has receivables
            if ($member->receivables()->count() > 0) {
                return response()->json([
                    'success' => false,
                    'message' => 'Member tidak dapat dihapus karena memiliki piutang'
                ], 400);
            }

            $memberData = [
                'id' => $member->id,
                'kode_member' => $member->kode_member,
                'nama' => $member->nama
            ];

            $member->delete();

            Log::info('API Member deleted:', $memberData);

            return response()->json([
                'success' => true,
                'message' => 'Member berhasil dihapus',
                'data' => $memberData
            ], 200);

        } catch (\Exception $e) {
            Log::error('API Error deleting member: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Gagal menghapus member',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Toggle member status (active/inactive)
     *
     * @param int $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function toggleStatus($id)
    {
        $member = Member::find($id);

        if (!$member) {
            return response()->json([
                'success' => false,
                'message' => 'Member not found'
            ], 404);
        }

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

            return response()->json([
                'success' => true,
                'message' => "Member {$member->nama} berhasil {$status}",
                'data' => [
                    'id' => $member->id,
                    'is_active' => $member->is_active,
                    'status_text' => $member->is_active ? 'Active' : 'Inactive'
                ]
            ], 200);

        } catch (\Exception $e) {
            Log::error('API Error toggling member status: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Gagal mengubah status member',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Display member's receivables (piutang)
     *
     * @param Request $request
     * @param int $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function receivables(Request $request, $id)
    {
        $member = Member::find($id);

        if (!$member) {
            return response()->json([
                'success' => false,
                'message' => 'Member not found'
            ], 404);
        }

        try {
            $query = $member->receivables()->with('transaction');

            // Filter by status
            if ($request->has('status') && $request->status !== 'all') {
                $query->where('status', $request->status);
            }

            $perPage = $request->get('per_page', 15);
            $receivables = $query->latest()->paginate($perPage);

            // Additional stats
            $stats = [
                'total_piutang' => (float) $member->total_piutang,
                'limit_kredit' => (float) $member->limit_kredit,
                'sisa_limit' => (float) ($member->limit_kredit - $member->total_piutang),
                'jumlah_piutang' => $member->receivables()->count(),
                'piutang_lunas' => $member->receivables()->where('status', 'lunas')->count(),
                'piutang_belum_lunas' => $member->receivables()->where('status', 'belum_lunas')->count(),
            ];

            return response()->json([
                'success' => true,
                'message' => 'Member receivables retrieved successfully',
                'data' => [
                    'member' => [
                        'id' => $member->id,
                        'kode_member' => $member->kode_member,
                        'nama' => $member->nama
                    ],
                    'receivables' => $receivables,
                    'stats' => $stats
                ]
            ], 200);
        } catch (\Exception $e) {
            Log::error('API Member receivables error: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve receivables',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Display member's transaction history
     *
     * @param Request $request
     * @param int $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function transactions(Request $request, $id)
    {
        $member = Member::find($id);

        if (!$member) {
            return response()->json([
                'success' => false,
                'message' => 'Member not found'
            ], 404);
        }

        try {
            $query = $member->transactions()->with('user');

            // Filter by date range
            if ($request->has('start_date') && $request->has('end_date')) {
                $query->whereBetween('created_at', [
                    $request->start_date . ' 00:00:00',
                    $request->end_date . ' 23:59:59'
                ]);
            }

            // Filter by payment method
            if ($request->has('payment_method') && $request->payment_method !== 'all') {
                $query->where('payment_method', $request->payment_method);
            }

            $perPage = $request->get('per_page', 15);
            $transactions = $query->latest()->paginate($perPage);

            // Transaction stats
            $stats = [
                'total_transaksi' => $member->transactions()->count(),
                'total_belanja' => (float) $member->transactions()->sum('total_amount'),
                'rata_rata_belanja' => (float) $member->transactions()->avg('total_amount'),
                'transaksi_tunai' => $member->transactions()->where('payment_method', 'cash')->count(),
                'transaksi_kredit' => $member->transactions()->where('payment_method', 'credit')->count(),
                'transaksi_transfer' => $member->transactions()->where('payment_method', 'transfer')->count(),
            ];

            return response()->json([
                'success' => true,
                'message' => 'Member transactions retrieved successfully',
                'data' => [
                    'member' => [
                        'id' => $member->id,
                        'kode_member' => $member->kode_member,
                        'nama' => $member->nama
                    ],
                    'transactions' => $transactions,
                    'stats' => $stats
                ]
            ], 200);
        } catch (\Exception $e) {
            Log::error('API Member transactions error: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve transactions',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Search members for dropdown/autocomplete
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function search(Request $request)
    {
        try {
            $search = $request->get('q', '');
            $limit = $request->get('limit', 10);

            $members = Member::where('is_active', true)
                ->where(function($query) use ($search) {
                    $query->where('kode_member', 'like', "%{$search}%")
                          ->orWhere('nama', 'like', "%{$search}%")
                          ->orWhere('no_telepon', 'like', "%{$search}%")
                          ->orWhere('email', 'like', "%{$search}%");
                })
                ->limit($limit)
                ->get()
                ->map(function($member) {
                    return [
                        'id' => $member->id,
                        'text' => $member->kode_member . ' - ' . $member->nama,
                        'kode_member' => $member->kode_member,
                        'nama' => $member->nama,
                        'email' => $member->email,
                        'no_telepon' => $member->no_telepon,
                        'tipe_member' => $member->tipe_member,
                        'limit_kredit' => (float) $member->limit_kredit,
                        'total_piutang' => (float) $member->total_piutang,
                        'sisa_limit' => (float) ($member->limit_kredit - $member->total_piutang),
                        'is_active' => $member->is_active,
                    ];
                });

            return response()->json([
                'success' => true,
                'message' => 'Members retrieved successfully',
                'data' => $members
            ], 200);
        } catch (\Exception $e) {
            Log::error('API Member search error: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Failed to search members',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get member data for AJAX
     *
     * @param int $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function getMemberData($id)
    {
        try {
            $member = Member::find($id);

            if (!$member) {
                return response()->json([
                    'success' => false,
                    'message' => 'Member not found'
                ], 404);
            }

            return response()->json([
                'success' => true,
                'message' => 'Member data retrieved successfully',
                'data' => [
                    'id' => $member->id,
                    'kode_member' => $member->kode_member,
                    'nama' => $member->nama,
                    'email' => $member->email,
                    'no_telepon' => $member->no_telepon,
                    'alamat' => $member->alamat,
                    'tipe_member' => $member->tipe_member,
                    'limit_kredit' => (float) $member->limit_kredit,
                    'total_piutang' => (float) $member->total_piutang,
                    'sisa_limit' => (float) ($member->limit_kredit - $member->total_piutang),
                    'tanggal_registrasi' => $member->tanggal_registrasi ? $member->tanggal_registrasi->format('Y-m-d') : null,
                    'is_active' => $member->is_active,
                    'terdaftar_pada' => $member->created_at ? $member->created_at->format('d/m/Y H:i') : null,
                    'terakhir_update' => $member->updated_at ? $member->updated_at->format('d/m/Y H:i') : null,
                ]
            ], 200);
        } catch (\Exception $e) {
            Log::error('API Get member data error: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Failed to get member data',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get member statistics
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function getStatistics()
    {
        try {
            $stats = [
                'total' => Member::count(),
                'active' => Member::where('is_active', true)->count(),
                'inactive' => Member::where('is_active', false)->count(),
                'by_type' => [
                    'biasa' => Member::where('tipe_member', 'biasa')->count(),
                    'gold' => Member::where('tipe_member', 'gold')->count(),
                    'platinum' => Member::where('tipe_member', 'platinum')->count(),
                ],
                'financial' => [
                    'total_piutang' => (float) Member::sum('total_piutang'),
                    'total_limit' => (float) Member::sum('limit_kredit'),
                    'rata_rata_piutang' => (float) Member::avg('total_piutang'),
                    'rata_rata_limit' => (float) Member::avg('limit_kredit'),
                ],
                'new_members_today' => Member::whereDate('created_at', today())->count(),
                'new_members_this_month' => Member::whereMonth('created_at', now()->month)->count(),
                'new_members_this_year' => Member::whereYear('created_at', now()->year)->count(),
            ];

            return response()->json([
                'success' => true,
                'message' => 'Member statistics retrieved successfully',
                'data' => $stats
            ], 200);
        } catch (\Exception $e) {
            Log::error('API Member statistics error: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Failed to get member statistics',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Bulk update member status
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function bulkUpdate(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'member_ids' => 'required|array|min:1',
            'member_ids.*' => 'exists:members,id',
            'action' => 'required|in:activate,deactivate,update_type',
            'tipe_member' => 'required_if:action,update_type|in:biasa,gold,platinum',
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
            $members = Member::whereIn('id', $request->member_ids)->get();
            $updatedCount = 0;
            $failedIds = [];

            foreach ($members as $member) {
                try {
                    $oldData = $member->toArray();
                    $changes = [];

                    switch ($request->action) {
                        case 'activate':
                            if (!$member->is_active) {
                                $member->update(['is_active' => true]);
                                $changes['is_active'] = ['old' => false, 'new' => true];
                            }
                            break;
                        case 'deactivate':
                            if ($member->is_active) {
                                $member->update(['is_active' => false]);
                                $changes['is_active'] = ['old' => true, 'new' => false];
                            }
                            break;
                        case 'update_type':
                            if ($member->tipe_member != $request->tipe_member) {
                                $oldType = $member->tipe_member;
                                $member->update(['tipe_member' => $request->tipe_member]);
                                $changes['tipe_member'] = ['old' => $oldType, 'new' => $request->tipe_member];
                            }
                            break;
                    }

                    if (!empty($changes)) {
                        $this->sendMemberUpdatedNotifications($member, $changes);
                        $updatedCount++;
                    }
                } catch (\Exception $e) {
                    $failedIds[] = $member->id;
                    Log::error('Bulk update failed for member ' . $member->id . ': ' . $e->getMessage());
                }
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => "Berhasil memperbarui {$updatedCount} member",
                'data' => [
                    'updated_count' => $updatedCount,
                    'failed_ids' => $failedIds
                ]
            ], 200);
        } catch (\Exception $e) {
            DB::rollBack();

            return response()->json([
                'success' => false,
                'message' => 'Gagal melakukan bulk update',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Export members to CSV
     *
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\StreamedResponse|\Illuminate\Http\JsonResponse
     */
    public function export(Request $request)
    {
        try {
            $query = Member::query();

            // Apply filters
            if ($request->has('tipe') && $request->tipe !== 'all') {
                $query->where('tipe_member', $request->tipe);
            }

            if ($request->has('status') && $request->status !== 'all') {
                $query->where('is_active', $request->status === 'active');
            }

            $members = $query->get();

            $filename = 'members_export_' . now()->format('Ymd_His') . '.csv';

            $headers = [
                'Content-Type' => 'text/csv',
                'Content-Disposition' => 'attachment; filename="' . $filename . '"',
            ];

            $callback = function() use ($members) {
                $file = fopen('php://output', 'w');

                // Add CSV headers
                fputcsv($file, [
                    'Kode Member',
                    'Nama',
                    'Email',
                    'No Telepon',
                    'Alamat',
                    'Tipe Member',
                    'Limit Kredit',
                    'Total Piutang',
                    'Sisa Limit',
                    'Status',
                    'Tanggal Registrasi',
                    'Terdaftar Pada'
                ]);

                // Add data rows
                foreach ($members as $member) {
                    fputcsv($file, [
                        $member->kode_member,
                        $member->nama,
                        $member->email ?? '-',
                        $member->no_telepon ?? '-',
                        $member->alamat ?? '-',
                        ucfirst($member->tipe_member),
                        number_format($member->limit_kredit, 0, ',', '.'),
                        number_format($member->total_piutang, 0, ',', '.'),
                        number_format($member->limit_kredit - $member->total_piutang, 0, ',', '.'),
                        $member->is_active ? 'Active' : 'Inactive',
                        $member->tanggal_registrasi ? $member->tanggal_registrasi->format('d/m/Y') : '-',
                        $member->created_at->format('d/m/Y H:i')
                    ]);
                }

                fclose($file);
            };

            return response()->stream($callback, 200, $headers);
        } catch (\Exception $e) {
            Log::error('API Member export error: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Failed to export members',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    // ==================== NOTIFICATION METHODS ====================

    /**
     * Send notifications when member is created
     */
    private function sendMemberCreatedNotifications($member)
    {
        try {
            $currentUser = auth()->user();

            if (!$currentUser) {
                return;
            }

            // 1. Kirim ke semua user dengan role owner
            $owners = User::where('role', 'owner')->get();

            foreach ($owners as $owner) {
                if ($owner->id != $currentUser->id) {
                    $owner->notify(new MemberCreatedNotification($member, $currentUser));
                    Log::info('API Notifikasi member terkirim ke owner:', [
                        'owner_id' => $owner->id,
                        'member_id' => $member->id
                    ]);
                }
            }

            // 2. Kirim ke diri sendiri (pembuat)
            $currentUser->notify(new MemberCreatedNotification($member, $currentUser));
            Log::info('API Notifikasi member terkirim ke diri sendiri:', [
                'user_id' => $currentUser->id,
                'member_id' => $member->id
            ]);

        } catch (\Exception $e) {
            Log::error('API Gagal mengirim notifikasi member: ' . $e->getMessage());
        }
    }

    /**
     * Send notifications when member is updated
     */
    private function sendMemberUpdatedNotifications($member, $changes)
    {
        try {
            $currentUser = auth()->user();

            if (!$currentUser) {
                return;
            }

            // 1. Kirim ke semua user dengan role owner
            $owners = User::where('role', 'owner')
                ->where('id', '!=', $currentUser->id)
                ->get();

            foreach ($owners as $owner) {
                $owner->notify(new MemberUpdatedNotification($member, $currentUser, $changes));
                Log::info('API Notifikasi update member terkirim ke owner:', [
                    'owner_id' => $owner->id,
                    'member_id' => $member->id
                ]);
            }

            // 2. Kirim ke diri sendiri (pembuat update)
            $currentUser->notify(new MemberUpdatedNotification($member, $currentUser, $changes));
            Log::info('API Notifikasi update member terkirim ke diri sendiri:', [
                'user_id' => $currentUser->id,
                'member_id' => $member->id
            ]);

        } catch (\Exception $e) {
            Log::error('API Gagal mengirim notifikasi update member: ' . $e->getMessage());
        }
    }
}
