<?php
// app/Http/Controllers/Api/AuthApiController.php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
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
            'email' => 'required|email',
            'password' => 'required|string',
            'device_name' => 'nullable|string|max:255',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation errors',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            $user = User::where('email', $request->email)->first();

            if (!$user || !Hash::check($request->password, $user->password)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Email atau password salah'
                ], 401);
            }

            if (!$user->is_active) {
                return response()->json([
                    'success' => false,
                    'message' => 'Akun Anda tidak aktif. Silakan hubungi administrator.'
                ], 403);
            }

            // Update last login
            $user->update(['last_login_at' => now()]);

            // Create token
            $deviceName = $request->device_name ?? $request->userAgent() ?? 'unknown';
            $token = $user->createToken($deviceName)->plainTextToken;

            // Load relationships
            $user->load(['faceRegistration']);

            Log::info('User logged in via API:', [
                'user_id' => $user->id,
                'email' => $user->email,
                'device' => $deviceName
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Login berhasil',
                'data' => [
                    'user' => $this->formatUserData($user),
                    'token' => $token,
                    'token_type' => 'Bearer'
                ]
            ], 200);
        } catch (\Exception $e) {
            Log::error('API Login error: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan server',
                'error' => $e->getMessage()
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
            'device_name' => 'nullable|string|max:255',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation errors',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            // Find user by face descriptor
            // This is a simplified version - you'll need to implement actual face matching
            $user = $this->findUserByFaceDescriptor($request->face_descriptor);

            if (!$user) {
                return response()->json([
                    'success' => false,
                    'message' => 'Wajah tidak dikenali'
                ], 401);
            }

            if (!$user->is_active) {
                return response()->json([
                    'success' => false,
                    'message' => 'Akun Anda tidak aktif. Silakan hubungi administrator.'
                ], 403);
            }

            // Update last login
            $user->update(['last_login_at' => now()]);

            // Create token
            $deviceName = $request->device_name ?? $request->userAgent() ?? 'unknown';
            $token = $user->createToken($deviceName)->plainTextToken;

            // Load relationships
            $user->load(['faceRegistration']);

            Log::info('User logged in via Face API:', [
                'user_id' => $user->id,
                'email' => $user->email,
                'device' => $deviceName
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Face login berhasil',
                'data' => [
                    'user' => $this->formatUserData($user),
                    'token' => $token,
                    'token_type' => 'Bearer'
                ]
            ], 200);
        } catch (\Exception $e) {
            Log::error('API Face Login error: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan server',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get authenticated user profile
     */
    public function profile(Request $request)
    {
        try {
            $user = $request->user()->load(['faceRegistration']);

            return response()->json([
                'success' => true,
                'message' => 'Profile retrieved successfully',
                'data' => $this->formatUserData($user)
            ], 200);
        } catch (\Exception $e) {
            Log::error('API Profile error: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Failed to get profile',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Logout user
     */
    public function logout(Request $request)
    {
        try {
            // Revoke current access token
            $request->user()->currentAccessToken()->delete();

            Log::info('User logged out via API:', [
                'user_id' => $request->user()->id
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Logout berhasil'
            ], 200);
        } catch (\Exception $e) {
            Log::error('API Logout error: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Gagal logout',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Format user data for response
     */
    private function formatUserData($user)
    {
        return [
            'id' => $user->id,
            'name' => $user->name,
            'email' => $user->email,
            'role' => $user->role,
            'phone' => $user->phone,
            'avatar' => $user->avatar ? asset('storage/' . $user->avatar) : null,
            'is_active' => $user->is_active,
            'has_face_registration' => $user->faceRegistration ? true : false,
            'last_login_at' => $user->last_login_at ? $user->last_login_at->format('d/m/Y H:i:s') : null,
            'created_at' => $user->created_at->format('d/m/Y H:i:s'),
        ];
    }

    /**
     * Find user by face descriptor
     * This is a placeholder - implement actual face matching logic
     */
    private function findUserByFaceDescriptor($descriptor)
    {
        // Implement actual face matching logic here
        // This could involve comparing with stored face descriptors
        // For now, return null as placeholder
        return null;
    }
}
