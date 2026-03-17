<?php
// app/Http/Controllers/Api/AuthApiController.php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Log;

class AuthApiController extends Controller
{
    /**
     * Login via email dan password
     */
    public function login(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email'       => 'required|email',
            'password'    => 'required|string',
            'device_name' => 'nullable|string|max:255',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validasi gagal',
                'errors'  => $validator->errors(),
            ], 422);
        }

        try {
            $user = User::where('email', $request->email)->first();

            if (!$user || !Hash::check($request->password, $user->password)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Email atau password salah',
                ], 401);
            }

            if (!$user->is_active) {
                return response()->json([
                    'success' => false,
                    'message' => 'Akun Anda tidak aktif. Silakan hubungi administrator.',
                ], 403);
            }

            // Update last login
            $user->update(['last_login_at' => now()]);

            // Buat token
            $deviceName = $request->device_name ?? $request->userAgent() ?? 'flutter-app';
            $token      = $user->createToken($deviceName)->plainTextToken;

            Log::info('User logged in via API', [
                'user_id' => $user->id,
                'email'   => $user->email,
                'device'  => $deviceName,
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Login berhasil',
                'data'    => [
                    'token'      => $token,
                    'token_type' => 'Bearer',
                    'user'       => $this->formatUserData($user),
                ],
            ], 200);

        } catch (\Exception $e) {
            Log::error('API Login error: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan server',
                'error'   => config('app.debug') ? $e->getMessage() : 'Internal server error',
            ], 500);
        }
    }

    /**
     * Login menggunakan face recognition
     */
    public function faceLogin(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'face_descriptor' => 'required|array',
            'device_name'     => 'nullable|string|max:255',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validasi gagal',
                'errors'  => $validator->errors(),
            ], 422);
        }

        try {
            $user = $this->findUserByFaceDescriptor($request->face_descriptor);

            if (!$user) {
                return response()->json([
                    'success' => false,
                    'message' => 'Wajah tidak dikenali',
                ], 401);
            }

            if (!$user->is_active) {
                return response()->json([
                    'success' => false,
                    'message' => 'Akun Anda tidak aktif. Silakan hubungi administrator.',
                ], 403);
            }

            $user->update(['last_login_at' => now()]);

            $deviceName = $request->device_name ?? $request->userAgent() ?? 'flutter-app';
            $token      = $user->createToken($deviceName)->plainTextToken;

            Log::info('User logged in via Face API', [
                'user_id' => $user->id,
                'email'   => $user->email,
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Face login berhasil',
                'data'    => [
                    'token'      => $token,
                    'token_type' => 'Bearer',
                    'user'       => $this->formatUserData($user),
                ],
            ], 200);

        } catch (\Exception $e) {
            Log::error('API Face Login error: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan server',
                'error'   => config('app.debug') ? $e->getMessage() : 'Internal server error',
            ], 500);
        }
    }

    /**
     * Get authenticated user profile
     */
    public function profile(Request $request)
    {
        try {
            return response()->json([
                'success' => true,
                'message' => 'Profile berhasil diambil',
                'data'    => $this->formatUserData($request->user()),
            ], 200);

        } catch (\Exception $e) {
            Log::error('API Profile error: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Gagal mengambil profile',
                'error'   => config('app.debug') ? $e->getMessage() : 'Internal server error',
            ], 500);
        }
    }

    /**
     * Logout — hapus token saat ini
     */
    public function logout(Request $request)
    {
        try {
            $request->user()->currentAccessToken()->delete();

            Log::info('User logged out via API', ['user_id' => $request->user()->id]);

            return response()->json([
                'success' => true,
                'message' => 'Logout berhasil',
            ], 200);

        } catch (\Exception $e) {
            Log::error('API Logout error: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Gagal logout',
                'error'   => config('app.debug') ? $e->getMessage() : 'Internal server error',
            ], 500);
        }
    }

    /**
     * Get registered faces (public)
     */
    public function getRegisteredFaces()
    {
        try {
            // Ambil user yang punya face_descriptor
            $users = User::whereNotNull('face_descriptor')
                ->where('is_active', true)
                ->get(['id', 'name', 'email', 'face_descriptor']);

            return response()->json([
                'success' => true,
                'data'    => $users,
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Face status (public)
     */
    public function faceStatus()
    {
        $count = User::whereNotNull('face_descriptor')->where('is_active', true)->count();

        return response()->json([
            'success'            => true,
            'registered_count'   => $count,
            'face_login_enabled' => $count > 0,
        ]);
    }

    // ── Private helpers ───────────────────────────────────────────────────────

    /**
     * Format data user untuk response
     * Tidak memakai relasi apapun — aman untuk semua konfigurasi User model
     */
    private function formatUserData(User $user): array
    {
        return [
            'id'            => $user->id,
            'name'          => $user->name,
            'email'         => $user->email,
            'role'          => $user->role,
            'phone'         => $user->phone         ?? null,
            'address'       => $user->address       ?? null,
            'gender'        => $user->gender        ?? null,
            'is_active'     => (bool) $user->is_active,
            'jenis_toko'    => $user->jenis_toko    ?? null,
            'avatar'        => $user->image
                                ? asset('storage/' . $user->image)
                                : null,
            'has_face'      => !empty($user->face_descriptor),
            'last_login_at' => $user->last_login_at
                                ? $user->last_login_at->format('d/m/Y H:i:s')
                                : null,
            'created_at'    => $user->created_at->format('d/m/Y H:i:s'),
        ];
    }

    /**
     * Cari user berdasarkan face descriptor (Euclidean distance)
     */
    private function findUserByFaceDescriptor(array $descriptor): ?User
    {
        $users = User::whereNotNull('face_descriptor')
            ->where('is_active', true)
            ->get();

        foreach ($users as $user) {
            try {
                $saved    = json_decode($user->face_descriptor, true);
                $distance = $this->euclideanDistance($descriptor, $saved);

                if ($distance < 0.5) {
                    return $user;
                }
            } catch (\Exception $e) {
                continue;
            }
        }

        return null;
    }

    /**
     * Hitung Euclidean distance antara dua descriptor
     */
    private function euclideanDistance(array $a, array $b): float
    {
        if (count($a) !== count($b)) return 1.0;

        $sum = 0.0;
        for ($i = 0; $i < count($a); $i++) {
            $sum += ($a[$i] - $b[$i]) ** 2;
        }

        return sqrt($sum);
    }
}
