<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;

class FaceController extends Controller
{
    /**
     * Show face registration page
     */
    public function showRegistrationForm($id)
    {
        $user = User::findOrFail($id);

        // Cek hak akses - hanya owner dan user itu sendiri yang bisa
        if (auth()->user()->role !== 'owner' && auth()->id() !== $user->id) {
            abort(403, 'Akses ditolak. Hanya owner atau user sendiri yang bisa registrasi wajah.');
        }

        return view('users.face-registration', compact('user'));
    }

    /**
     * Store face data for user
     */
    public function store(Request $request, $id)
    {
        $user = User::findOrFail($id);

        // Validasi akses
        if (auth()->user()->role !== 'owner' && auth()->id() !== $user->id) {
            return response()->json([
                'success' => false,
                'message' => 'Akses ditolak. Hanya owner atau user sendiri yang bisa registrasi wajah.'
            ], 403);
        }

        // Validasi data
        $request->validate([
            'descriptor' => 'required|array',
            'descriptor.*' => 'numeric'
        ]);

        try {
            // Convert descriptor array to JSON string
            $descriptor = json_encode($request->descriptor);

            // Calculate face score based on descriptor quality
            $score = $this->calculateFaceScore(count($request->descriptor), $request->descriptor);

            // Update user face data
            $user->face_descriptor = $descriptor;
            $user->face_score = $score;
            $user->face_registered_at = now();
            $user->save();

            return response()->json([
                'success' => true,
                'message' => 'Wajah berhasil diregistrasi!',
                'score' => $score,
                'registered_at' => $user->face_registered_at->format('d/m/Y H:i:s')
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal menyimpan data wajah: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Calculate face score based on descriptor quality
     */
    private function calculateFaceScore($length, $descriptor)
    {
        // Base score based on descriptor length
        $lengthScore = min($length / 128, 1.0); // Normalize to 0-1

        // Check for invalid values
        $validValues = count(array_filter($descriptor, function($val) {
            return is_numeric($val) && $val !== 0;
        }));
        $validityScore = $validValues / $length;

        // Check variance (more variance = better recognition)
        $mean = array_sum($descriptor) / $length;
        $variance = 0;
        foreach ($descriptor as $value) {
            $variance += pow($value - $mean, 2);
        }
        $variance /= $length;
        $varianceScore = min($variance / 0.1, 1.0); // Normalize variance

        // Weighted average
        $finalScore = ($lengthScore * 0.4) + ($validityScore * 0.3) + ($varianceScore * 0.3);

        // Ensure score is between 0.5 and 1.0
        $finalScore = max(0.5, min(1.0, $finalScore));

        // Round to 2 decimal places
        return round($finalScore, 2);
    }

    /**
     * Reset face registration
     */
    public function destroy($id)
    {
        $user = User::findOrFail($id);

        // Validasi akses
        if (auth()->user()->role !== 'owner' && auth()->id() !== $user->id) {
            return response()->json([
                'success' => false,
                'message' => 'Akses ditolak. Hanya owner atau user sendiri yang bisa reset wajah.'
            ], 403);
        }

        try {
            // Reset face data
            $user->face_descriptor = null;
            $user->face_score = null;
            $user->face_registered_at = null;
            $user->save();

            return response()->json([
                'success' => true,
                'message' => 'Registrasi wajah berhasil direset.'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal mereset registrasi: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Check if user has registered face
     */
    public function check($id)
    {
        $user = User::findOrFail($id);

        return response()->json([
            'has_face' => !empty($user->face_descriptor),
            'score' => $user->face_score,
            'registered_at' => $user->face_registered_at
        ]);
    }
}
