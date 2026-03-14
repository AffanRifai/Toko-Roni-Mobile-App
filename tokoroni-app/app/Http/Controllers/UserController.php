<?php
/** @noinspection PhpUndefinedMethodInspection */
/** @noinspection PhpUndefinedVariableInspection */

namespace App\Http\Controllers;

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

class UserController extends Controller
{
    /* =======================
     * INDEX
     * ======================= */
    public function index(Request $request)
    {
        $totalUsers  = User::count();
        $activeUsers = User::where('is_active', true)->count();

        $stats = [
            'total'   => $totalUsers,
            'active'  => $activeUsers,
            'inactive' => User::where('is_active', false)->count(),
            'owner'   => User::where('role', 'owner')->count(),
            'kasir'   => User::where('role', 'kasir')->count(),
            'kepala_gudang'  => User::where('role', 'kepala_gudang')->count(),
            'logistik' => User::where('role', 'logistik')->count(),
            'checker_barang' => User::where('role', 'checker_barang')->count(),
            'manager' => User::where('role', 'manager')->count(),
            'active_percentage' => $totalUsers > 0
                ? round(($activeUsers / $totalUsers) * 100, 1)
                : 0,
            'face_registered' => User::whereNotNull('face_descriptor')->count()
        ];

        $query = User::query();

        if ($request->filled('role')) {
            $query->where('role', $request->role);
        }

        if ($request->filled('jenis_toko')) {
            $query->where('jenis_toko', $request->jenis_toko);
        }

        if ($request->filled('status')) {
            $query->where('is_active', $request->status);
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%$search%")
                    ->orWhere('email', 'like', "%$search%")
                    ->orWhere('phone', 'like', "%$search%");
            });
        }

        $query->orderBy(
            match ($request->get('sort')) {
                'oldest'    => 'created_at',
                'name_asc'  => 'name',
                'name_desc' => 'name',
                default     => 'created_at'
            },
            in_array($request->get('sort'), ['oldest', 'name_asc']) ? 'asc' : 'desc'
        );

        $users = $query->paginate($request->get('per_page', 15));

        return view('users.index', compact('users', 'stats'));
    }

    /* =======================
     * FACE RECOGNITION API METHODS
     * ======================= */

    /**
     * API: Get all users for face registration dropdown
     */
    public function getUsersForFaceRegistration()
    {
        try {
            $users = User::where('is_active', true)
                ->select('id', 'name', 'email', 'role')
                ->get();

            return response()->json([
                'success' => true,
                'data' => $users
            ]);
        } catch (\Exception $e) {
            Log::error('Error getting users for face registration: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to load users'
            ], 500);
        }
    }

    /**
     * API: Get face descriptors for login matching
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
                'data' => $users
            ]);
        } catch (\Exception $e) {
            Log::error('Error getting face descriptors: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to load face data'
            ], 500);
        }
    }

    /**
     * API: Face login authentication
     */
    public function faceLogin(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'descriptor' => 'required|array',
                'descriptor.*' => 'numeric'
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Invalid face descriptor format'
                ], 400);
            }

            $inputDescriptor = $request->descriptor;

            // Validate descriptor length
            if (count($inputDescriptor) !== 128) {
                return response()->json([
                    'success' => false,
                    'message' => 'Invalid descriptor length. Expected 128 values.'
                ], 400);
            }

            $bestMatch = null;
            $bestDistance = PHP_FLOAT_MAX;

            // Get all active users with face descriptors
            $users = User::where('is_active', true)
                ->whereNotNull('face_descriptor')
                ->whereNotNull('face_registered_at')
                ->get();

            foreach ($users as $user) {
                $storedDescriptor = json_decode($user->face_descriptor, true);

                if (!$storedDescriptor || !is_array($storedDescriptor) || count($storedDescriptor) !== 128) {
                    continue;
                }

                // Calculate Euclidean distance
                $distance = $this->calculateEuclideanDistance($inputDescriptor, $storedDescriptor);

                // Check if this is a better match
                if ($distance < $bestDistance) {
                    $bestDistance = $distance;
                    $bestMatch = $user;
                }
            }

            // Threshold for face matching (0.6 = 60% similarity)
            $threshold = config('face_recognition.threshold', 0.6);

            if ($bestMatch && $bestDistance < $threshold) {
                // Convert distance to similarity score (0-1 scale)
                $similarityScore = 1 - ($bestDistance / $threshold);

                // Login the user
                Auth::login($bestMatch);

                // Log the successful face login
                Log::info('Face login successful', [
                    'user_id' => $bestMatch->id,
                    'email' => $bestMatch->email,
                    'similarity_score' => $similarityScore,
                    'distance' => $bestDistance,
                    'ip_address' => $request->ip()
                ]);

                return response()->json([
                    'success' => true,
                    'user' => [
                        'id' => $bestMatch->id,
                        'name' => $bestMatch->name,
                        'email' => $bestMatch->email,
                        'role' => $bestMatch->role
                    ],
                    'similarity_score' => round($similarityScore, 4),
                    'distance' => $bestDistance,
                    'redirect' => $this->getRedirectUrl($bestMatch->role)
                ]);
            }

            // No match found
            Log::warning('Face login failed - no match found', [
                'ip_address' => $request->ip(),
                'best_distance' => $bestDistance ?? 'N/A',
                'threshold' => $threshold
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Face not recognized. Please try again or use password login.',
                'best_distance' => $bestDistance,
                'threshold' => $threshold
            ], 401);

        } catch (\Exception $e) {
            Log::error('Face login error: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Face recognition failed. Please try again.'
            ], 500);
        }
    }

    /**
     * Calculate Euclidean distance between two descriptors
     */
    private function calculateEuclideanDistance(array $desc1, array $desc2): float
    {
        $sum = 0;
        $count = min(count($desc1), count($desc2));

        if ($count !== 128) {
            return PHP_FLOAT_MAX; // Invalid descriptor length
        }

        for ($i = 0; $i < $count; $i++) {
            $diff = $desc1[$i] - $desc2[$i];
            $sum += $diff * $diff;
        }

        return sqrt($sum);
    }

    /**
     * Get redirect URL based on user role
     */
    private function getRedirectUrl($role): string
    {
        return match($role) {
            'owner' => route('dashboard'),
            'manager' => route('dashboard'),
            'kasir' => route('transactions.create'),
            'kepala_gudang' => route('products.index'),
            'checker_barang' => route('products.index'),
            'logistik' => route('delivery.index'),
            default => route('dashboard')
        };
    }

    /**
     * API: Register new face
     */
    public function registerFace(Request $request)
    {
        try {
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

            // Save face data
            $descriptor = json_encode($request->descriptor);
            $score = $this->calculateFaceScore(count($request->descriptor));

            $user->face_descriptor = $descriptor;
            $user->face_score = $score;
            $user->face_registered_at = now();
            $user->save();

            // Save image if provided
            if ($request->filled('image')) {
                $this->saveFaceImage($user->id, $request->image);
            }

            Log::info('Face registered successfully', [
                'user_id' => $user->id,
                'email' => $user->email,
                'score' => $score
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Face registered successfully',
                'user' => [
                    'id' => $user->id,
                    'name' => $user->name,
                    'email' => $user->email
                ],
                'score' => $score,
                'registered_at' => $user->face_registered_at->format('d/m/Y H:i:s')
            ]);

        } catch (\Exception $e) {
            Log::error('Face registration error: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Failed to register face: ' . $e->getMessage()
            ], 500);
        }
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
    private function calculateFaceScore($descriptorLength): float
    {
        // FaceAPI produces 128-length descriptors
        if ($descriptorLength === 128) {
            return 0.95; // High quality descriptor
        } elseif ($descriptorLength >= 100) {
            return 0.85;
        } elseif ($descriptorLength >= 75) {
            return 0.70;
        } else {
            return 0.50; // Low quality descriptor
        }
    }

    /**
     * API: Delete face registration
     */
    public function deleteFaceRegistration($id)
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

            Log::info('Face registration deleted', [
                'user_id' => $user->id,
                'email' => $user->email
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Face registration deleted successfully'
            ]);

        } catch (\Exception $e) {
            Log::error('Error deleting face registration: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Failed to delete face registration: ' . $e->getMessage()
            ], 500);
        }
    }

    /* =======================
     * CREATE
     * ======================= */
    public function create()
    {
        return view('users.create');
    }

    /* =======================
     * STORE
     * ======================= */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name'       => 'required|string|max:255',
            'email'      => 'required|email|unique:users,email',
            'password'   => 'required|min:8|confirmed',
            'role'       => 'required|in:owner,kasir,kepala_gudang,logistik,checker_barang,manager',
            'jenis_toko' => 'required|in:grosir,eceran',
            'phone'      => 'nullable|string|max:20',
            'address'    => 'nullable|string|max:500',
            'is_active'  => 'nullable|boolean',
            'image'      => 'nullable|image|max:2048'
        ]);

        // Validasi logic role x jenis toko
        if ($validated['role'] === 'kepala_gudang' && $validated['jenis_toko'] === 'eceran') {
            return back()->withErrors([
                'jenis_toko' => 'Role Kepala Gudang hanya untuk toko grosir'
            ])->withInput();
        }

        $validated['password']  = Hash::make($validated['password']);
        $validated['is_active'] = $request->boolean('is_active', true);

        if ($request->hasFile('image')) {
            $validated['image'] = $request->file('image')->store('users', 'public');
        }
        
        // Buat user baru
        $user = User::create($validated);

        // KIRIM NOTIFIKASI
        try {
            // 1. Kirim ke semua user dengan role owner dan manager
            $owners = User::whereIn('role', ['owner', 'manager'])->where('id', '!=', auth()->id())->get();
            
            foreach ($owners as $owner) {
                $owner->notify(new UserCreatedNotification($user, auth()->user()));
                Log::info('Notifikasi terkirim ke owner/manager:', [
                    'owner_id' => $owner->id,
                    'user_id' => $user->id
                ]);
            }
            
            // 2. Kirim ke user yang baru dibuat (jika aktif)
            if ($user->is_active) {
                $user->notify(new UserCreatedNotification($user, auth()->user()));
                Log::info('Notifikasi terkirim ke user baru:', ['user_id' => $user->id]);
            }
            
            // 3. KIRIM KE DIRI SENDIRI (USER YANG SEDANG LOGIN)
            $currentUser = auth()->user();
            
            // Kirim notifikasi ke diri sendiri
            $currentUser->notify(new UserCreatedNotification($user, $currentUser));
            Log::info('Notifikasi terkirim ke diri sendiri:', [
                'user_id' => $currentUser->id,
                'user_name' => $currentUser->name,
                'action' => 'membuat user baru',
                'new_user_id' => $user->id
            ]);
            
        } catch (\Exception $e) {
            Log::error('Gagal mengirim notifikasi: ' . $e->getMessage());
        }

        return redirect()->route('users.index')->with('success', 'User berhasil ditambahkan');
    }
    
    /* =======================
     * SHOW
     * ======================= */
    public function show(User $user)
    {
        return view('users.show', compact('user'));
    }

    /* =======================
     * EDIT
     * ======================= */
    public function edit(User $user)
    {
        return view('users.edit', compact('user'));
    }

    /* =======================
     * UPDATE
     * ======================= */
    public function update(Request $request, User $user)
    {
        $validated = $request->validate([
            'name'       => 'required|string|max:255',
            'email'      => ['required', 'email', Rule::unique('users')->ignore($user->id)],
            'role'       => 'required|in:owner,kasir,kepala_gudang,logistik,checker_barang,manager',
            'jenis_toko' => 'required|in:grosir,eceran',
            'password'   => 'nullable|min:8|confirmed',
            'phone'      => 'nullable|string|max:20',
            'address'    => 'nullable|string|max:500',
            'is_active'  => 'nullable|boolean',
            'image'      => 'nullable|image|max:2048'
        ]);

        if ($validated['role'] === 'kepala_gudang' && $validated['jenis_toko'] === 'eceran') {
            return back()->withErrors([
                'jenis_toko' => 'Role Kepala Gudang hanya untuk toko grosir'
            ])->withInput();
        }

        if ($request->filled('password')) {
            $validated['password'] = Hash::make($request->password);
        } else {
            unset($validated['password']);
        }

        $validated['is_active'] = $request->boolean('is_active', $user->is_active);

        if ($request->hasFile('image')) {
            if ($user->image && Storage::disk('public')->exists($user->image)) {
                Storage::disk('public')->delete($user->image);
            }
            $validated['image'] = $request->file('image')->store('users', 'public');
        }

        // Catat perubahan sebelum update
        $oldData = $user->toArray();
        $changes = [];
        
        foreach ($validated as $key => $value) {
            if ($key != 'password' && isset($oldData[$key]) && $oldData[$key] != $value) {
                $changes[$key] = [
                    'old' => $oldData[$key],
                    'new' => $value
                ];
            }
        }

        $user->update($validated);
        
        // Kirim notifikasi jika ada perubahan
        if (!empty($changes)) {
            // 1. Kirim ke semua owner dan manager
            $owners = User::whereIn('role', ['owner', 'manager'])
                ->where('id', '!=', auth()->id())
                ->get();
            
            foreach ($owners as $owner) {
                $owner->notify(new UserUpdatedNotification($user, auth()->user(), $changes));
                Log::info('Notifikasi update terkirim ke owner/manager:', [
                    'owner_id' => $owner->id,
                    'user_id' => $user->id
                ]);
            }

            // 2. Kirim ke user yang diupdate (jika bukan dirinya sendiri)
            if ($user->id != auth()->id()) {
                $user->notify(new UserUpdatedNotification($user, auth()->user(), $changes));
                Log::info('Notifikasi update terkirim ke user yang diupdate:', ['user_id' => $user->id]);
            }
            
            // 3. KIRIM KE DIRI SENDIRI
            $currentUser = auth()->user();
            
            $currentUser->notify(new UserUpdatedNotification($user, $currentUser, $changes));
            Log::info('Notifikasi update terkirim ke diri sendiri:', [
                'user_id' => $currentUser->id,
                'user_name' => $currentUser->name,
                'action' => 'mengupdate user',
                'updated_user_id' => $user->id,
                'changes' => array_keys($changes)
            ]);
        }

        return redirect()->route('users.index')->with('success', 'User berhasil diperbarui');
    }

    /* =======================
     * DELETE
     * ======================= */
    public function destroy(User $user)
    {
        if ($user->role === 'owner') {
            return back()->with('error', 'Owner tidak bisa dihapus');
        }

        if ($user->id === auth()->id()) {
            return back()->with('error', 'Tidak bisa menghapus akun sendiri');
        }

        if ($user->image && Storage::disk('public')->exists($user->image)) {
            Storage::disk('public')->delete($user->image);
        }

        $user->delete();

        return redirect()->route('users.index')->with('success', 'User berhasil dihapus');
    }

    /* =======================
     * TOGGLE STATUS
     * ======================= */
    public function toggleStatus(User $user)
    {
        if ($user->id === auth()->id()) {
            return back()->with('error', 'Tidak bisa mengubah status akun sendiri');
        }

        $user->update(['is_active' => !$user->is_active]);

        return back()->with(
            'success',
            'User berhasil ' . ($user->is_active ? 'diaktifkan' : 'dinonaktifkan')
        );
    }

    /* =======================
     * BULK ACTION
     * ======================= */
    public function bulkActivate(Request $request)
    {
        User::whereIn('id', $request->ids)->update(['is_active' => true]);
        return response()->json(['success' => true]);
    }

    public function bulkDeactivate(Request $request)
    {
        User::whereIn('id', $request->ids)->update(['is_active' => false]);
        return response()->json(['success' => true]);
    }

    /* =======================
     * FACE REGISTRATION METHODS (Web)
     * ======================= */

    /**
     * Show face registration page
     */
    public function faceRegistration($id)
    {
        $user = User::findOrFail($id);
        return view('users.face-registration', compact('user'));
    }

    /**
     * Store face data (Web version)
     */
    public function storeFaceData(Request $request, $id)
    {
        $request->validate([
            'descriptor' => 'required|array',
            'descriptor.*' => 'numeric'
        ]);

        try {
            $user = User::findOrFail($id);

            // Convert descriptor array to JSON string
            $descriptor = json_encode($request->descriptor);

            // Calculate face score automatically
            $score = $this->calculateFaceScore(count($request->descriptor));

            // Update user face data
            $user->face_descriptor = $descriptor;
            $user->face_score = $score;
            $user->face_registered_at = now();
            $user->save();

            return response()->json([
                'success' => true,
                'message' => 'Wajah berhasil diregistrasi',
                'score' => $score,
                'registered_at' => $user->face_registered_at->format('d/m/Y H:i')
            ]);

        } catch (\Exception $e) {
            Log::error('Error storing face data: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Gagal menyimpan data wajah: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Destroy face data (Web version)
     */
    public function destroyFaceData($id)
    {
        try {
            $user = User::findOrFail($id);

            // Reset face data
            $user->face_descriptor = null;
            $user->face_score = null;
            $user->face_registered_at = null;
            $user->save();

            return response()->json([
                'success' => true,
                'message' => 'Registrasi wajah berhasil direset'
            ]);

        } catch (\Exception $e) {
            Log::error('Error destroying face data: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Gagal mereset registrasi: ' . $e->getMessage()
            ], 500);
        }
    }

    /* =======================
     * PROFILE SETTINGS
     * ======================= */
    public function profile()
    {
        return view('profile.settings', ['user' => auth()->user()]);
    }

    public function updateProfile(Request $request)
    {
        $user = auth()->user();

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => ['required', 'email', Rule::unique('users')->ignore($user->id)],
            'phone' => 'nullable|string|max:20',
            'address' => 'nullable|string|max:500',
            'image' => 'nullable|image|max:2048'
        ]);

        if ($request->hasFile('image')) {
            if ($user->image && Storage::disk('public')->exists($user->image)) {
                Storage::disk('public')->delete($user->image);
            }
            $validated['image'] = $request->file('image')->store('users', 'public');
        }

        $user->update($validated);

        return back()->with('success', 'Profile berhasil diperbarui');
    }
}