<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

class FaceRecognitionController extends Controller
{
    /**
     * Handle face recognition login
     */
    public function login(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'user_id' => 'required|integer|exists:users,id',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'User tidak valid'
                ], 400);
            }

            $user = User::findOrFail($request->user_id);

            // Cek apakah user memiliki face descriptor
            if (!$user->face_descriptor) {
                return response()->json([
                    'success' => false,
                    'message' => 'User belum terdaftar untuk face recognition'
                ], 400);
            }

            // Login user
            Auth::login($user, $request->remember ?? false);

            // Log aktivitas login
            activity()
                ->causedBy($user)
                ->withProperties(['method' => 'face_recognition'])
                ->log('User logged in via face recognition');

            return response()->json([
                'success' => true,
                'message' => 'Login berhasil',
                'redirect' => route('dashboard')
            ]);

        } catch (\Exception $e) {
            \Log::error('Face login error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan sistem'
            ], 500);
        }
    }

    /**
     * Handle face registration
     */
    public function register(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'user_id' => 'required|integer|exists:users,id',
                'face_descriptor' => 'required|string',
                'face_score' => 'required|numeric|min:0|max:1',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Data tidak valid',
                    'errors' => $validator->errors()
                ], 400);
            }

            $user = User::findOrFail($request->user_id);

            // Validasi apakah user memiliki hak untuk registrasi wajah
            if (!auth()->check() || !in_array(auth()->user()->role, ['owner', 'admin'])) {
                return response()->json([
                    'success' => false,
                    'message' => 'Anda tidak memiliki izin untuk registrasi wajah'
                ], 403);
            }

            // Parse face descriptor
            $faceDescriptor = json_decode($request->face_descriptor);
            
            if (!is_array($faceDescriptor) || count($faceDescriptor) !== 128) {
                return response()->json([
                    'success' => false,
                    'message' => 'Face descriptor tidak valid'
                ], 400);
            }

            // Simpan face descriptor ke database
            $user->update([
                'face_descriptor' => $request->face_descriptor,
                'face_score' => $request->face_score,
                'face_registered_at' => now(),
            ]);

            // Log aktivitas registrasi
            activity()
                ->causedBy(auth()->user())
                ->performedOn($user)
                ->withProperties(['score' => $request->face_score])
                ->log('Registered face for user');

            return response()->json([
                'success' => true,
                'message' => 'Wajah berhasil didaftarkan!',
                'user' => [
                    'id' => $user->id,
                    'name' => $user->name,
                    'email' => $user->email
                ]
            ]);

        } catch (\Exception $e) {
            \Log::error('Face registration error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan sistem'
            ], 500);
        }
    }

    /**
     * Compare face descriptors (untuk matching di frontend)
     */
    public function compareFace(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'descriptor' => 'required|array',
                'threshold' => 'numeric|min:0|max:1'
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Data tidak valid'
                ], 400);
            }

            $inputDescriptor = $request->descriptor;
            $threshold = $request->threshold ?? 0.6;

            // Ambil semua user yang memiliki face descriptor
            $users = User::whereNotNull('face_descriptor')
                        ->where('is_active', true)
                        ->get();

            $bestMatch = null;
            $bestDistance = PHP_FLOAT_MAX;

            foreach ($users as $user) {
                $storedDescriptor = json_decode($user->face_descriptor, true);
                
                // Hitung Euclidean distance
                $distance = $this->calculateEuclideanDistance($inputDescriptor, $storedDescriptor);
                
                if ($distance < $bestDistance && $distance < $threshold) {
                    $bestDistance = $distance;
                    $bestMatch = $user;
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
                        'distance' => $bestDistance
                    ]
                ]);
            }

            return response()->json([
                'success' => true,
                'matched' => false,
                'message' => 'Wajah tidak dikenali'
            ]);

        } catch (\Exception $e) {
            \Log::error('Face comparison error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan sistem'
            ], 500);
        }
    }

    /**
     * Get users available for face registration
     */
    public function getUsersForRegistration()
    {
        try {
            if (!auth()->check() || !in_array(auth()->user()->role, ['owner', 'admin'])) {
                return response()->json([
                    'success' => false,
                    'message' => 'Unauthorized'
                ], 403);
            }

            $users = User::where(function($query) {
                            $query->whereNull('face_descriptor')
                                  ->orWhere('face_score', '<', 0.5);
                        })
                        ->where('is_active', true)
                        ->select('id', 'name', 'email', 'role', 'face_registered_at')
                        ->orderBy('name')
                        ->get();

            return response()->json([
                'success' => true,
                'users' => $users
            ]);

        } catch (\Exception $e) {
            \Log::error('Get users error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan sistem'
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
        
        for ($i = 0; $i < $count; $i++) {
            $diff = $desc1[$i] - $desc2[$i];
            $sum += $diff * $diff;
        }
        
        return sqrt($sum);
    }
}