<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\View\View;

class AuthenticatedSessionController extends Controller
{
    /**
     * Display the login view.
     */
    public function create(): View
    {
        // Get statistics for face recognition
        $totalUsers = User::count();
        $faceRegisteredUsers = User::whereNotNull('face_descriptor')
            ->where('face_score', '>=', 0.5)
            ->where('is_active', true)
            ->count();

        // Get face descriptors for registered users
        $faceDescriptors = User::whereNotNull('face_descriptor')
            ->where('face_score', '>=', 0.5)
            ->where('is_active', true)
            ->get()
            ->map(function ($user) {
                $descriptorArray = null;

                // Try to parse descriptor from JSON string
                if (!empty($user->face_descriptor)) {
                    try {
                        $parsed = json_decode($user->face_descriptor, true);
                        if (is_array($parsed)) {
                            $descriptorArray = $parsed;
                        }
                    } catch (\Exception $e) {
                        Log::error("Failed to parse face descriptor for user {$user->id}: " . $e->getMessage());
                    }
                }

                // Return only if we have a valid 128-value descriptor
                if ($descriptorArray && is_array($descriptorArray) && count($descriptorArray) === 128) {
                    return [
                        'id' => $user->id,
                        'name' => $user->name,
                        'email' => $user->email,
                        'descriptor' => $descriptorArray,
                        'score' => $user->face_score ?? 0.95
                    ];
                }

                return null;
            })
            ->filter()
            ->values();

        // Get users available for face registration (only for admin/owner)
        $usersForRegistration = collect();

        if (auth()->check() && in_array(auth()->user()->role, ['owner', 'admin'])) {
            $usersForRegistration = User::where('is_active', true)
                ->where(function ($query) {
                    $query->whereNull('face_descriptor')
                        ->orWhere('face_score', '<', 0.5);
                })
                ->select('id', 'name', 'email', 'role', 'face_registered_at', 'face_score')
                ->orderBy('name')
                ->get();
        }

        return view('auth.login', compact(
            'totalUsers',
            'faceRegisteredUsers',
            'faceDescriptors',
            'usersForRegistration'
        ));
    }

    /**
     * Handle an incoming authentication request.
     */
    public function store(LoginRequest $request): RedirectResponse
    {
        $request->authenticate();

        $request->session()->regenerate();

        // // Log login activity
        // Log::info('User login via password', [
        //     'user_id' => auth()->id(),
        //     'email' => auth()->user()->email,
        //     'ip_address' => $request->ip(),
        //     'user_agent' => $request->userAgent()
        // ]);

        // Redirect based on role
        return $this->redirectBasedOnRole();
    }

    /**
     * Handle face recognition login.
     */
    public function faceLogin(Request $request)
    {
        try {
            $request->validate([
                'user_id' => 'required|integer|exists:users,id',
            ]);

            $user = User::findOrFail($request->user_id);

            // Check if user has face descriptor
            if (!$user->face_descriptor) {
                return response()->json([
                    'success' => false,
                    'message' => 'User belum terdaftar untuk face recognition'
                ], 400);
            }

            // Check if user is active
            if (!$user->is_active) {
                return response()->json([
                    'success' => false,
                    'message' => 'Akun ini tidak aktif'
                ], 400);
            }

            // Login user
            Auth::login($user, $request->boolean('remember', false));

            $request->session()->regenerate();

            // // Log login activity
            // Log::info('User login via face recognition', [
            //     'user_id' => $user->id,
            //     'email' => $user->email,
            //     'ip_address' => $request->ip(),
            //     'user_agent' => $request->userAgent()
            // ]);

            return response()->json([
                'success' => true,
                'message' => 'Login berhasil',
                'redirect' => $this->getDashboardUrl($user->role)
            ]);
        } catch (\Exception $e) {
            Log::error('Face login error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan saat login dengan wajah'
            ], 500);
        }
    }

    /**
     * Handle face registration.
     */
    public function faceRegister(Request $request)
    {
        try {
            // Validate only admin/owner can register faces
            if (!auth()->check() || !in_array(auth()->user()->role, ['owner', 'admin'])) {
                return response()->json([
                    'success' => false,
                    'message' => 'Anda tidak memiliki izin untuk registrasi wajah'
                ], 403);
            }

            $request->validate([
                'user_id' => 'required|integer|exists:users,id',
                'face_descriptor' => 'required|string',
                'face_score' => 'required|numeric|min:0|max:1',
            ]);

            $user = User::findOrFail($request->user_id);

            // Parse face descriptor
            $faceDescriptor = json_decode($request->face_descriptor, true);

            if (!is_array($faceDescriptor) || count($faceDescriptor) !== 128) {
                return response()->json([
                    'success' => false,
                    'message' => 'Face descriptor tidak valid (harus 128 values)'
                ], 400);
            }

            // Save face descriptor to database
            $user->update([
                'face_descriptor' => $request->face_descriptor,
                'face_score' => $request->face_score,
                'face_registered_at' => now(),
            ]);

            // Log registration activity
            Log::info('Face registered for user', [
                'admin_id' => auth()->id(),
                'admin_email' => auth()->user()->email,
                'user_id' => $user->id,
                'user_email' => $user->email,
                'score' => $request->face_score
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Wajah berhasil didaftarkan untuk ' . $user->name
            ]);
        } catch (\Exception $e) {
            Log::error('Face registration error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan saat registrasi wajah: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Compare face with registered users.
     */
    public function compareFace(Request $request)
    {
        try {
            $request->validate([
                'descriptor' => 'required|array',
                'descriptor.*' => 'required|numeric',
                'threshold' => 'numeric|min:0|max:1'
            ]);

            $inputDescriptor = $request->descriptor;
            $threshold = $request->threshold ?? 0.6;

            if (count($inputDescriptor) !== 128) {
                return response()->json([
                    'success' => false,
                    'message' => 'Face descriptor must have 128 values'
                ], 400);
            }

            // Get all users with face descriptors
            $users = User::whereNotNull('face_descriptor')
                ->where('face_score', '>=', 0.5)
                ->where('is_active', true)
                ->get(['id', 'name', 'email', 'face_descriptor', 'face_score']);

            $bestMatch = null;
            $bestDistance = PHP_FLOAT_MAX;

            foreach ($users as $user) {
                try {
                    $storedDescriptor = json_decode($user->face_descriptor, true);

                    if (!is_array($storedDescriptor) || count($storedDescriptor) !== 128) {
                        continue;
                    }

                    // Calculate Euclidean distance
                    $distance = $this->calculateEuclideanDistance($inputDescriptor, $storedDescriptor);

                    if ($distance < $bestDistance && $distance < $threshold) {
                        $bestDistance = $distance;
                        $bestMatch = $user;
                    }
                } catch (\Exception $e) {
                    continue;
                }
            }

            if ($bestMatch) {
                return response()->json([
                    'success' => true,
                    'matched' => true,
                    'user' => [
                        'id' => $bestMatch->id,
                        'name' => $bestMatch->name,
                        'email' => $bestMatch->email,
                        'distance' => round($bestDistance, 4),
                        'score' => $bestMatch->face_score
                    ]
                ]);
            }

            return response()->json([
                'success' => true,
                'matched' => false,
                'message' => 'Wajah tidak dikenali'
            ]);
        } catch (\Exception $e) {
            Log::error('Face comparison error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan sistem: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get face recognition status.
     */
    public function faceStatus(Request $request)
    {
        try {
            $user = auth()->user();

            if (!$user) {
                return response()->json([
                    'success' => false,
                    'message' => 'User not authenticated'
                ], 401);
            }

            return response()->json([
                'success' => true,
                'has_face_registered' => !empty($user->face_descriptor),
                'face_score' => $user->face_score,
                'face_registered_at' => $user->face_registered_at,
                'is_active' => $user->is_active
            ]);
        } catch (\Exception $e) {
            Log::error('Face status error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error getting face status'
            ], 500);
        }
    }

    /**
     * Get users for face registration.
     */
    public function getUsersForRegistration(Request $request)
    {
        try {
            if (!auth()->check() || !in_array(auth()->user()->role, ['owner', 'admin'])) {
                return response()->json([
                    'success' => false,
                    'message' => 'Unauthorized'
                ], 403);
            }

            $users = User::where('is_active', true)
                ->where(function ($query) {
                    $query->whereNull('face_descriptor')
                        ->orWhere('face_score', '<', 0.5);
                })
                ->select('id', 'name', 'email', 'role', 'face_registered_at', 'face_score')
                ->orderBy('name')
                ->get();

            return response()->json([
                'success' => true,
                'users' => $users
            ]);
        } catch (\Exception $e) {
            Log::error('Get users for registration error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan sistem'
            ], 500);
        }
    }

    /**
     * Get all registered faces.
     */
    public function getRegisteredFaces(Request $request)
    {
        try {
            $users = User::whereNotNull('face_descriptor')
                ->where('face_score', '>=', 0.5)
                ->where('is_active', true)
                ->get()
                ->map(function ($user) {
                    try {
                        $descriptorArray = json_decode($user->face_descriptor, true);

                        if (!is_array($descriptorArray) || count($descriptorArray) !== 128) {
                            return null;
                        }

                        return [
                            'id' => $user->id,
                            'name' => $user->name,
                            'email' => $user->email,
                            'descriptor' => $descriptorArray,
                            'score' => $user->face_score
                        ];
                    } catch (\Exception $e) {
                        return null;
                    }
                })
                ->filter()
                ->values();

            return response()->json([
                'success' => true,
                'faces' => $users,
                'count' => $users->count()
            ]);
        } catch (\Exception $e) {
            Log::error('Get registered faces error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error loading face data'
            ], 500);
        }
    }

    /**
     * Destroy an authenticated session.
     */
    public function destroy(Request $request): RedirectResponse
    {
        $user = auth()->user();

        if ($user) {
            // Log logout activity
            Log::info('User logged out', [
                'user_id' => $user->id,
                'email' => $user->email
            ]);
        }

        Auth::guard('web')->logout();

        $request->session()->invalidate();

        $request->session()->regenerateToken();

        return redirect('/');
    }

    /**
     * Calculate Euclidean distance between two descriptors.
     */
    private function calculateEuclideanDistance(array $desc1, array $desc2): float
    {
        $sum = 0;
        $count = min(count($desc1), count($desc2));

        for ($i = 0; $i < $count; $i++) {
            $diff = $desc1[$i] - $desc2[$i];
            $sum += $diff * $diff;
        }

        return sqrt($sum);
    }

    /**
     * Redirect user based on their role.
     */
    private function redirectBasedOnRole(): RedirectResponse
    {
        $user = auth()->user();
        $dashboardUrl = $this->getDashboardUrl($user->role);

        return redirect()->intended($dashboardUrl);
    }

    /**
     * Get dashboard URL based on role.
     */
    private function getDashboardUrl(string $role): string
    {
        return match($role) {
            'owner' => route('dashboard.owner'),
            'kasir' => route('dashboard.kasir'),
            'gudang' => route('dashboard.gudang'),
            'logistik' => route('dashboard.logistik'),
            default => route('dashboard'),
        };
    }
}
