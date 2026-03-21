<?php
// app/Http/Controllers/Api/UserApiController.php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Notifications\UserCreatedNotification;
use App\Notifications\UserUpdatedNotification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;

class UserApiController extends Controller
{
    /**
     * Constructor
     */
    public function __construct()
    {
        // Middleware handled in routes/api.php
    }

    /* =======================
     * USER MANAGEMENT
     * ======================= */

    /**
     * Display a listing of users.
     */
    public function index(Request $request)
    {
        try {
            $query = User::query();

            // Filter by role
            if ($request->filled('role')) {
                $query->where('role', $request->role);
            }

            // Filter by status
            if ($request->filled('status')) {
                $query->where('is_active', $request->status === 'active');
            }

            // Filter by jenis_toko
            if ($request->filled('jenis_toko')) {
                $query->where('jenis_toko', $request->jenis_toko);
            }

            // Search
            if ($request->filled('search')) {
                $search = $request->search;
                $query->where(function ($q) use ($search) {
                    $q->where('name', 'like', "%{$search}%")
                        ->orWhere('email', 'like', "%{$search}%")
                        ->orWhere('phone', 'like', "%{$search}%");
                });
            }

            // Sorting
            $sortField = $request->get('sort_by', 'created_at');
            $sortOrder = $request->get('sort_order', 'desc');

            if (in_array($sortField, ['name', 'email', 'role', 'created_at', 'last_login_at'])) {
                $query->orderBy($sortField, $sortOrder);
            }

            // Pagination
            $perPage = $request->get('per_page', 15);
            $users = $query->paginate($perPage);

            // Transform data
            $users->getCollection()->transform(function ($user) {
                return $this->formatUserData($user);
            });

            // Statistics
            $stats = $this->getUserStatistics();

            return response()->json([
                'success' => true,
                'message' => 'Users retrieved successfully',
                'data' => $users,
                'stats' => $stats
            ], 200);

        } catch (\Exception $e) {
            Log::error('API User index error: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve users',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Store a newly created user.
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'password' => 'required|string|min:8|confirmed',
            'role' => 'required|in:owner,kasir,gudang,logistik,checker_barang',
            'jenis_toko' => 'required|in:grosir,eceran',
            'phone' => 'nullable|string|max:20',
            'address' => 'nullable|string|max:500',
            'is_active' => 'sometimes|boolean',
            'image' => 'nullable|image|max:2048'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation errors',
                'errors' => $validator->errors()
            ], 422);
        }

        // Validasi logic role x jenis toko
        if ($request->role === 'gudang' && $request->jenis_toko === 'eceran') {
            return response()->json([
                'success' => false,
                'message' => 'Role gudang hanya untuk toko grosir',
                'errors' => ['jenis_toko' => ['Role gudang hanya untuk toko grosir']]
            ], 422);
        }

        DB::beginTransaction();

        try {
            $data = $validator->validated();
            $data['password'] = Hash::make($data['password']);
            $data['is_active'] = $request->boolean('is_active', true);

            // Handle image upload
            if ($request->hasFile('image')) {
                $data['image'] = $request->file('image')->store('users', 'public');
            }

            $user = User::create($data);

            // Send notifications
            $this->sendUserCreatedNotifications($user);

            DB::commit();

            Log::info('API User created:', [
                'user_id' => $user->id,
                'email' => $user->email,
                'created_by' => auth()->id()
            ]);

            return response()->json([
                'success' => true,
                'message' => 'User berhasil ditambahkan',
                'data' => $this->formatUserData($user)
            ], 201);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('API User store error: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Gagal menambahkan user',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Display the specified user.
     */
    public function show($id)
    {
        try {
            $user = User::find($id);

            if (!$user) {
                return response()->json([
                    'success' => false,
                    'message' => 'User not found'
                ], 404);
            }

            return response()->json([
                'success' => true,
                'message' => 'User retrieved successfully',
                'data' => $this->formatUserData($user, true)
            ], 200);

        } catch (\Exception $e) {
            Log::error('API User show error: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve user',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Update the specified user.
     */
    public function update(Request $request, $id)
    {
        $user = User::find($id);

        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'User not found'
            ], 404);
        }

        $validator = Validator::make($request->all(), [
            'name' => 'sometimes|required|string|max:255',
            'email' => ['sometimes', 'required', 'email', Rule::unique('users')->ignore($user->id)],
            'role' => 'sometimes|required|in:owner,kasir,gudang,logistik,checker_barang',
            'jenis_toko' => 'sometimes|required|in:grosir,eceran',
            'password' => 'nullable|string|min:8|confirmed',
            'phone' => 'nullable|string|max:20',
            'address' => 'nullable|string|max:500',
            'is_active' => 'sometimes|boolean',
            'image' => 'nullable|image|max:2048'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation errors',
                'errors' => $validator->errors()
            ], 422);
        }

        // Validasi logic role x jenis toko
        if ($request->has('role') && $request->has('jenis_toko') &&
            $request->role === 'gudang' && $request->jenis_toko === 'eceran') {
            return response()->json([
                'success' => false,
                'message' => 'Role gudang hanya untuk toko grosir',
                'errors' => ['jenis_toko' => ['Role gudang hanya untuk toko grosir']]
            ], 422);
        }

        DB::beginTransaction();

        try {
            $oldData = $user->toArray();
            $data = $validator->validated();

            // Handle password
            if ($request->filled('password')) {
                $data['password'] = Hash::make($request->password);
            } else {
                unset($data['password']);
            }

            // Handle image
            if ($request->hasFile('image')) {
                // Delete old image
                if ($user->image && Storage::disk('public')->exists($user->image)) {
                    Storage::disk('public')->delete($user->image);
                }
                $data['image'] = $request->file('image')->store('users', 'public');
            }

            // Track changes for notification
            $changes = [];
            foreach ($data as $key => $value) {
                if ($key != 'password' && isset($oldData[$key]) && $oldData[$key] != $value) {
                    $changes[$key] = [
                        'old' => $oldData[$key],
                        'new' => $value
                    ];
                }
            }

            $user->update($data);

            // Send notifications if there are changes
            if (!empty($changes)) {
                $this->sendUserUpdatedNotifications($user, $changes);
            }

            DB::commit();

            Log::info('API User updated:', [
                'user_id' => $user->id,
                'email' => $user->email,
                'changes' => array_keys($changes),
                'updated_by' => auth()->id()
            ]);

            return response()->json([
                'success' => true,
                'message' => 'User berhasil diperbarui',
                'data' => $this->formatUserData($user),
                'changes' => $changes
            ], 200);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('API User update error: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Gagal memperbarui user',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Remove the specified user.
     */
    public function destroy($id)
    {
        try {
            $user = User::find($id);

            if (!$user) {
                return response()->json([
                    'success' => false,
                    'message' => 'User not found'
                ], 404);
            }

            // Prevent deletion of owner and self
            if ($user->role === 'owner') {
                return response()->json([
                    'success' => false,
                    'message' => 'Owner tidak bisa dihapus'
                ], 400);
            }

            if ($user->id === auth()->id()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Tidak bisa menghapus akun sendiri'
                ], 400);
            }

            // Delete user image
            if ($user->image && Storage::disk('public')->exists($user->image)) {
                Storage::disk('public')->delete($user->image);
            }

            // Delete face image if exists
            if ($user->image && strpos($user->image, 'face_') !== false) {
                Storage::disk('public')->delete($user->image);
            }

            $userData = [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email
            ];

            $user->delete();

            Log::info('API User deleted:', [
                'user_id' => $userData['id'],
                'email' => $userData['email'],
                'deleted_by' => auth()->id()
            ]);

            return response()->json([
                'success' => true,
                'message' => 'User berhasil dihapus',
                'data' => $userData
            ], 200);

        } catch (\Exception $e) {
            Log::error('API User delete error: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Gagal menghapus user',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /* =======================
     * FACE RECOGNITION API METHODS
     * ======================= */

    /**
     * Get all users for face registration (faceRegistration route)
     */
    public function faceRegistration()
    {
        try {
            $users = User::where('is_active', true)
                ->select('id', 'name', 'email', 'role')
                ->get()
                ->map(function ($user) {
                    return [
                        'id' => $user->id,
                        'name' => $user->name,
                        'email' => $user->email,
                        'role' => $user->role,
                        'role_text' => ucfirst($user->role),
                        'has_face' => $user->face_descriptor ? true : false,
                        'face_registered_at' => $user->face_registered_at ?
                            $user->face_registered_at->format('d/m/Y H:i') : null
                    ];
                });

            return response()->json([
                'success' => true,
                'data' => $users
            ], 200);

        } catch (\Exception $e) {
            Log::error('Error getting users for face registration: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to load users'
            ], 500);
        }
    }

    /**
     * Get face descriptors for login matching
     */
    public function getFaceDescriptors()
    {
        try {
            $users = User::where('is_active', true)
                ->whereNotNull('face_descriptor')
                ->whereNotNull('face_registered_at')
                ->get()
                ->map(function ($user) {
                    $descriptor = json_decode($user->face_descriptor, true);

                    // Validate descriptor format
                    if (!$descriptor || !is_array($descriptor) || count($descriptor) !== 128) {
                        return null;
                    }

                    return [
                        'user_id' => $user->id,
                        'name' => $user->name,
                        'email' => $user->email,
                        'descriptor' => $descriptor,
                        'score' => $user->face_score,
                        'registered_at' => $user->face_registered_at->toISOString()
                    ];
                })
                ->filter()
                ->values();

            return response()->json([
                'success' => true,
                'data' => $users,
                'total' => $users->count()
            ], 200);

        } catch (\Exception $e) {
            Log::error('Error getting face descriptors: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to load face data'
            ], 500);
        }
    }

    /**
     * Register new face
     */
    public function registerFace(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'user_id' => 'required|exists:users,id',
            'descriptor' => 'required|array',
            'descriptor.*' => 'numeric',
            'face_name' => 'nullable|string|max:255',
            'image' => 'nullable|string'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        DB::beginTransaction();

        try {
            $user = User::findOrFail($request->user_id);

            // Check if user already has face registered
            if ($user->face_descriptor) {
                return response()->json([
                    'success' => false,
                    'message' => 'User already has a registered face. Please delete existing face first.'
                ], 400);
            }

            // Validate descriptor length (FaceAPI produces 128-length descriptors)
            if (count($request->descriptor) !== 128) {
                return response()->json([
                    'success' => false,
                    'message' => 'Invalid face descriptor. Expected 128 values, got ' . count($request->descriptor)
                ], 400);
            }

            // Validate descriptor values are within expected range (-1 to 1)
            foreach ($request->descriptor as $value) {
                if ($value < -1 || $value > 1) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Invalid descriptor values. Expected values between -1 and 1.'
                    ], 400);
                }
            }

            // Save face data
            $descriptor = json_encode($request->descriptor);
            $score = $this->calculateFaceScore($request->descriptor);

            $user->face_descriptor = $descriptor;
            $user->face_score = $score;
            $user->face_registered_at = now();
            $user->save();

            // Save image if provided
            if ($request->filled('image')) {
                $this->saveFaceImage($user->id, $request->image);
            }

            DB::commit();

            Log::info('Face registered successfully via API', [
                'user_id' => $user->id,
                'email' => $user->email,
                'score' => $score,
                'registered_by' => auth()->id()
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Face registered successfully',
                'data' => [
                    'user_id' => $user->id,
                    'name' => $user->name,
                    'email' => $user->email,
                    'score' => $score,
                    'registered_at' => $user->face_registered_at->format('d/m/Y H:i:s')
                ]
            ], 201);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Face registration error: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Failed to register face: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Delete face registration (deleteFace route)
     */
    public function deleteFace($id)
    {
        try {
            $user = User::findOrFail($id);

            // Reset face data
            $user->face_descriptor = null;
            $user->face_score = null;
            $user->face_registered_at = null;

            // Also remove face image if exists
            if ($user->image && strpos($user->image, 'face_') !== false) {
                if (Storage::disk('public')->exists($user->image)) {
                    Storage::disk('public')->delete($user->image);
                }
                $user->image = null;
            }

            $user->save();

            Log::info('Face registration deleted via API', [
                'user_id' => $user->id,
                'email' => $user->email,
                'deleted_by' => auth()->id()
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Face registration deleted successfully'
            ], 200);

        } catch (\Exception $e) {
            Log::error('Error deleting face registration: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Failed to delete face registration: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Check face registration status
     */
    public function checkFaceStatus($id)
    {
        try {
            $user = User::findOrFail($id);

            return response()->json([
                'success' => true,
                'data' => [
                    'user_id' => $user->id,
                    'name' => $user->name,
                    'has_face' => $user->face_descriptor ? true : false,
                    'face_score' => $user->face_score,
                    'face_registered_at' => $user->face_registered_at ?
                        $user->face_registered_at->format('d/m/Y H:i:s') : null,
                    'face_image' => $user->image && strpos($user->image, 'face_') !== false ?
                        asset('storage/' . $user->image) : null
                ]
            ], 200);

        } catch (\Exception $e) {
            Log::error('Error checking face status: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Failed to check face status'
            ], 500);
        }
    }

    /* =======================
     * USER ACTIONS
     * ======================= */

    /**
     * Toggle user active status
     */
    public function toggleActive($id)
    {
        try {
            $user = User::find($id);

            if (!$user) {
                return response()->json([
                    'success' => false,
                    'message' => 'User not found'
                ], 404);
            }

            if ($user->id === auth()->id()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Tidak bisa mengubah status akun sendiri'
                ], 400);
            }

            $oldStatus = $user->is_active;
            $user->update(['is_active' => !$oldStatus]);

            $status = $user->is_active ? 'diaktifkan' : 'dinonaktifkan';

            // Send notification
            $changes = [
                'is_active' => [
                    'old' => $oldStatus,
                    'new' => $user->is_active
                ]
            ];
            $this->sendUserUpdatedNotifications($user, $changes);

            Log::info('User status toggled via API', [
                'user_id' => $user->id,
                'old_status' => $oldStatus,
                'new_status' => $user->is_active,
                'updated_by' => auth()->id()
            ]);

            return response()->json([
                'success' => true,
                'message' => "User berhasil {$status}",
                'data' => [
                    'id' => $user->id,
                    'name' => $user->name,
                    'is_active' => $user->is_active,
                    'status_text' => $user->is_active ? 'Active' : 'Inactive'
                ]
            ], 200);

        } catch (\Exception $e) {
            Log::error('Error toggling user status: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Gagal mengubah status user',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Change user role
     */
    public function changeRole(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'role' => 'required|in:owner,kasir,gudang,logistik,checker_barang',
            'jenis_toko' => 'required_if:role,gudang|in:grosir,eceran'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation errors',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            $user = User::find($id);

            if (!$user) {
                return response()->json([
                    'success' => false,
                    'message' => 'User not found'
                ], 404);
            }

            // Validate role and jenis_toko combination
            if ($request->role === 'gudang' && $request->jenis_toko === 'eceran') {
                return response()->json([
                    'success' => false,
                    'message' => 'Role gudang hanya untuk toko grosir',
                    'errors' => ['jenis_toko' => ['Role gudang hanya untuk toko grosir']]
                ], 400);
            }

            $oldRole = $user->role;
            $oldJenisToko = $user->jenis_toko;

            $user->role = $request->role;
            if ($request->has('jenis_toko')) {
                $user->jenis_toko = $request->jenis_toko;
            }
            $user->save();

            $changes = [];
            if ($oldRole != $user->role) {
                $changes['role'] = ['old' => $oldRole, 'new' => $user->role];
            }
            if ($oldJenisToko != $user->jenis_toko) {
                $changes['jenis_toko'] = ['old' => $oldJenisToko, 'new' => $user->jenis_toko];
            }

            // Send notification
            if (!empty($changes)) {
                $this->sendUserUpdatedNotifications($user, $changes);
            }

            Log::info('User role changed via API', [
                'user_id' => $user->id,
                'changes' => $changes,
                'updated_by' => auth()->id()
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Role user berhasil diperbarui',
                'data' => $this->formatUserData($user),
                'changes' => $changes
            ], 200);

        } catch (\Exception $e) {
            Log::error('Error changing user role: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Gagal mengubah role user',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Reset user password
     */
    public function resetPassword(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'new_password' => 'required|string|min:8|confirmed'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation errors',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            $user = User::find($id);

            if (!$user) {
                return response()->json([
                    'success' => false,
                    'message' => 'User not found'
                ], 404);
            }

            $user->password = Hash::make($request->new_password);
            $user->save();

            Log::info('User password reset via API', [
                'user_id' => $user->id,
                'email' => $user->email,
                'reset_by' => auth()->id()
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Password user berhasil direset'
            ], 200);

        } catch (\Exception $e) {
            Log::error('Error resetting user password: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Gagal mereset password',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /* =======================
     * BULK OPERATIONS
     * ======================= */

    /**
     * Bulk activate users
     */
    public function bulkActivate(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'user_ids' => 'required|array|min:1',
            'user_ids.*' => 'exists:users,id'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation errors',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            $count = User::whereIn('id', $request->user_ids)
                ->where('id', '!=', auth()->id()) // Exclude self
                ->update(['is_active' => true]);

            Log::info('Bulk activate users via API', [
                'user_ids' => $request->user_ids,
                'count' => $count,
                'updated_by' => auth()->id()
            ]);

            return response()->json([
                'success' => true,
                'message' => "{$count} user berhasil diaktifkan",
                'data' => ['updated_count' => $count]
            ], 200);

        } catch (\Exception $e) {
            Log::error('Error bulk activating users: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Gagal mengaktifkan user',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Bulk deactivate users
     */
    public function bulkDeactivate(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'user_ids' => 'required|array|min:1',
            'user_ids.*' => 'exists:users,id'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation errors',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            $count = User::whereIn('id', $request->user_ids)
                ->where('id', '!=', auth()->id()) // Exclude self
                ->update(['is_active' => false]);

            Log::info('Bulk deactivate users via API', [
                'user_ids' => $request->user_ids,
                'count' => $count,
                'updated_by' => auth()->id()
            ]);

            return response()->json([
                'success' => true,
                'message' => "{$count} user berhasil dinonaktifkan",
                'data' => ['updated_count' => $count]
            ], 200);

        } catch (\Exception $e) {
            Log::error('Error bulk deactivating users: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Gagal menonaktifkan user',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Bulk update user roles
     */
    public function bulkUpdateRole(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'user_ids' => 'required|array|min:1',
            'user_ids.*' => 'exists:users,id',
            'role' => 'required|in:owner,kasir,gudang,logistik,checker_barang'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation errors',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            $count = User::whereIn('id', $request->user_ids)
                ->where('id', '!=', auth()->id()) // Exclude self
                ->update(['role' => $request->role]);

            Log::info('Bulk update user roles via API', [
                'user_ids' => $request->user_ids,
                'role' => $request->role,
                'count' => $count,
                'updated_by' => auth()->id()
            ]);

            return response()->json([
                'success' => true,
                'message' => "{$count} user berhasil diperbarui rolenya menjadi {$request->role}",
                'data' => ['updated_count' => $count, 'role' => $request->role]
            ], 200);

        } catch (\Exception $e) {
            Log::error('Error bulk updating user roles: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Gagal memperbarui role user',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /* =======================
     * STATISTICS & EXPORT
     * ======================= */

    /**
     * Get user statistics
     */
    public function getStatistics()
    {
        try {
            $stats = $this->getUserStatistics();

            return response()->json([
                'success' => true,
                'message' => 'User statistics retrieved successfully',
                'data' => $stats
            ], 200);

        } catch (\Exception $e) {
            Log::error('Error getting user statistics: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Failed to get user statistics',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Export users to CSV
     */
    public function export(Request $request)
    {
        try {
            $query = User::query();

            // Apply filters
            if ($request->filled('role')) {
                $query->where('role', $request->role);
            }

            if ($request->filled('status')) {
                $query->where('is_active', $request->status === 'active');
            }

            $users = $query->get();

            $filename = 'users_export_' . now()->format('Ymd_His') . '.csv';

            $headers = [
                'Content-Type' => 'text/csv',
                'Content-Disposition' => 'attachment; filename="' . $filename . '"',
            ];

            $callback = function() use ($users) {
                $file = fopen('php://output', 'w');

                // Add CSV headers
                fputcsv($file, [
                    'ID',
                    'Name',
                    'Email',
                    'Role',
                    'Jenis Toko',
                    'Phone',
                    'Address',
                    'Status',
                    'Last Login',
                    'Has Face Registration',
                    'Face Score',
                    'Created At'
                ]);

                // Add data rows
                foreach ($users as $user) {
                    fputcsv($file, [
                        $user->id,
                        $user->name,
                        $user->email,
                        $user->role,
                        $user->jenis_toko,
                        $user->phone ?? '-',
                        $user->address ?? '-',
                        $user->is_active ? 'Active' : 'Inactive',
                        $user->last_login_at ? $user->last_login_at->format('d/m/Y H:i') : '-',
                        $user->face_descriptor ? 'Yes' : 'No',
                        $user->face_score ?? '-',
                        $user->created_at->format('d/m/Y H:i')
                    ]);
                }

                fclose($file);
            };

            return response()->stream($callback, 200, $headers);

        } catch (\Exception $e) {
            Log::error('Error exporting users: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Failed to export users',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /* =======================
     * PROFILE METHODS (for authenticated user)
     * ======================= */

    /**
     * Get current user profile
     */
    public function profile()
    {
        try {
            $user = auth()->user();

            return response()->json([
                'success' => true,
                'message' => 'Profile retrieved successfully',
                'data' => $this->formatUserData($user, true)
            ], 200);

        } catch (\Exception $e) {
            Log::error('Error getting profile: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Failed to get profile',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Update current user profile
     */
    public function updateProfile(Request $request)
    {
        $user = auth()->user();

        $validator = Validator::make($request->all(), [
            'name' => 'sometimes|required|string|max:255',
            'email' => ['sometimes', 'required', 'email', Rule::unique('users')->ignore($user->id)],
            'phone' => 'nullable|string|max:20',
            'address' => 'nullable|string|max:500',
            'image' => 'nullable|image|max:2048'
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
            $data = $validator->validated();

            // Handle image
            if ($request->hasFile('image')) {
                // Delete old image
                if ($user->image && Storage::disk('public')->exists($user->image)) {
                    Storage::disk('public')->delete($user->image);
                }
                $data['image'] = $request->file('image')->store('users', 'public');
            }

            $user->update($data);

            DB::commit();

            Log::info('User profile updated via API', [
                'user_id' => $user->id,
                'updated_fields' => array_keys($data)
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Profile berhasil diperbarui',
                'data' => $this->formatUserData($user)
            ], 200);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error updating profile: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Gagal memperbarui profile',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Change password for authenticated user
     */
    public function changePassword(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'current_password' => 'required|string',
            'new_password' => 'required|string|min:8|confirmed|different:current_password'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation errors',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            $user = auth()->user();

            // Check current password
            if (!Hash::check($request->current_password, $user->password)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Current password is incorrect'
                ], 400);
            }

            $user->password = Hash::make($request->new_password);
            $user->save();

            Log::info('User changed password via API', [
                'user_id' => $user->id
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Password berhasil diubah'
            ], 200);

        } catch (\Exception $e) {
            Log::error('Error changing password: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Gagal mengubah password',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /* =======================
     * HELPER METHODS
     * ======================= */

    /**
     * Format user data for API response
     */
    private function formatUserData($user, $detailed = false)
    {
        $data = [
            'id' => $user->id,
            'name' => $user->name,
            'email' => $user->email,
            'role' => $user->role,
            'role_text' => ucfirst($user->role),
            'jenis_toko' => $user->jenis_toko,
            'jenis_toko_text' => ucfirst($user->jenis_toko),
            'phone' => $user->phone,
            'address' => $user->address,
            'is_active' => $user->is_active,
            'status_text' => $user->is_active ? 'Active' : 'Inactive',
            'avatar' => $user->image ? asset('storage/' . $user->image) : null,
            'last_login_at' => $user->last_login_at ? $user->last_login_at->format('d/m/Y H:i:s') : null,
            'created_at' => $user->created_at->format('d/m/Y H:i:s'),
            'updated_at' => $user->updated_at->format('d/m/Y H:i:s'),
        ];

        if ($detailed) {
            $data['face_registration'] = [
                'has_face' => $user->face_descriptor ? true : false,
                'face_score' => $user->face_score,
                'face_registered_at' => $user->face_registered_at ?
                    $user->face_registered_at->format('d/m/Y H:i:s') : null,
                'face_image' => $user->image && strpos($user->image, 'face_') !== false ?
                    asset('storage/' . $user->image) : null
            ];

            $data['statistics'] = [
                'transactions_count' => $user->transactions()->count(),
                'deliveries_count' => $user->deliveries()->count(),
                'total_sales' => $user->transactions()->sum('total_amount')
            ];
        }

        return $data;
    }

    /**
     * Get user statistics
     */
    private function getUserStatistics()
    {
        $totalUsers = User::count();
        $activeUsers = User::where('is_active', true)->count();

        return [
            'total' => $totalUsers,
            'active' => $activeUsers,
            'inactive' => User::where('is_active', false)->count(),
            'by_role' => [
                'owner' => User::where('role', 'owner')->count(),
                'kasir' => User::where('role', 'kasir')->count(),
                'gudang' => User::where('role', 'gudang')->count(),
                'logistik' => User::where('role', 'logistik')->count(),
                'checker_barang' => User::where('role', 'checker_barang')->count(),
            ],
            'by_jenis_toko' => [
                'grosir' => User::where('jenis_toko', 'grosir')->count(),
                'eceran' => User::where('jenis_toko', 'eceran')->count(),
            ],
            'active_percentage' => $totalUsers > 0
                ? round(($activeUsers / $totalUsers) * 100, 1)
                : 0,
            'face_registered' => User::whereNotNull('face_descriptor')->count(),
            'face_registered_percentage' => $totalUsers > 0
                ? round((User::whereNotNull('face_descriptor')->count() / $totalUsers) * 100, 1)
                : 0,
            'online_now' => User::where('last_login_at', '>=', now()->subMinutes(5))->count(),
        ];
    }

    /**
     * Save face image from base64
     */
    private function saveFaceImage($userId, $base64Image): void
    {
        try {
            // Remove data URL prefix if present
            if (strpos($base64Image, 'base64,') !== false) {
                $base64Image = substr($base64Image, strpos($base64Image, ',') + 1);
            }

            $imageData = base64_decode($base64Image);

            if ($imageData === false) {
                throw new \Exception('Invalid base64 image data');
            }

            $fileName = 'face_' . $userId . '_' . time() . '.jpg';
            $path = 'faces/' . $fileName;

            Storage::disk('public')->put($path, $imageData);

            // Update user image path
            User::where('id', $userId)->update(['image' => $path]);

            Log::info('Face image saved', ['user_id' => $userId, 'path' => $path]);

        } catch (\Exception $e) {
            Log::error('Error saving face image: ' . $e->getMessage());
        }
    }

    /**
     * Calculate face score based on descriptor quality
     */
    private function calculateFaceScore($descriptor): float
    {
        if (!is_array($descriptor)) {
            return 0.5;
        }

        $length = count($descriptor);

        // FaceAPI produces 128-length descriptors
        if ($length === 128) {
            // Calculate variance to determine quality
            $mean = array_sum($descriptor) / $length;
            $variance = 0;
            foreach ($descriptor as $value) {
                $variance += pow($value - $mean, 2);
            }
            $variance /= $length;

            // Higher variance usually indicates better quality
            $score = min(0.95, 0.7 + ($variance * 10));
            return round($score, 2);
        } elseif ($length >= 100) {
            return 0.85;
        } elseif ($length >= 75) {
            return 0.70;
        } else {
            return 0.50; // Low quality descriptor
        }
    }

    /* =======================
     * NOTIFICATION METHODS
     * ======================= */

    /**
     * Send notifications when user is created
     */
    private function sendUserCreatedNotifications($user)
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
                $owner->notify(new UserCreatedNotification($user, $currentUser));
                Log::info('API Notifikasi user terkirim ke owner:', [
                    'owner_id' => $owner->id,
                    'user_id' => $user->id
                ]);
            }

            // 2. Kirim ke user yang baru dibuat (jika aktif)
            if ($user->is_active && $user->id != $currentUser->id) {
                $user->notify(new UserCreatedNotification($user, $currentUser));
                Log::info('API Notifikasi user terkirim ke user baru:', ['user_id' => $user->id]);
            }

            // 3. Kirim ke diri sendiri
            $currentUser->notify(new UserCreatedNotification($user, $currentUser));
            Log::info('API Notifikasi user terkirim ke diri sendiri:', [
                'user_id' => $currentUser->id,
                'user_name' => $currentUser->name,
                'action' => 'membuat user baru',
                'new_user_id' => $user->id
            ]);

        } catch (\Exception $e) {
            Log::error('API Gagal mengirim notifikasi user: ' . $e->getMessage());
        }
    }

    /**
     * Send notifications when user is updated
     */
    private function sendUserUpdatedNotifications($user, $changes)
    {
        try {
            $currentUser = auth()->user();

            if (!$currentUser) {
                return;
            }

            // 1. Kirim ke semua owner
            $owners = User::where('role', 'owner')
                ->where('id', '!=', $currentUser->id)
                ->get();

            foreach ($owners as $owner) {
                $owner->notify(new UserUpdatedNotification($user, $currentUser, $changes));
                Log::info('API Notifikasi update user terkirim ke owner:', [
                    'owner_id' => $owner->id,
                    'user_id' => $user->id
                ]);
            }

            // 2. Kirim ke user yang diupdate (jika bukan dirinya sendiri)
            if ($user->id != $currentUser->id) {
                $user->notify(new UserUpdatedNotification($user, $currentUser, $changes));
                Log::info('API Notifikasi update user terkirim ke user yang diupdate:', ['user_id' => $user->id]);
            }

            // 3. Kirim ke diri sendiri
            $currentUser->notify(new UserUpdatedNotification($user, $currentUser, $changes));
            Log::info('API Notifikasi update user terkirim ke diri sendiri:', [
                'user_id' => $currentUser->id,
                'user_name' => $currentUser->name,
                'action' => 'mengupdate user',
                'updated_user_id' => $user->id,
                'changes' => array_keys($changes)
            ]);

        } catch (\Exception $e) {
            Log::error('API Gagal mengirim notifikasi update user: ' . $e->getMessage());
        }
    }
}
