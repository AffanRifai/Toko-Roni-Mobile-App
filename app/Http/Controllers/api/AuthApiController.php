<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\User;

class AuthApiController extends Controller
{

    /*
    |--------------------------------------------------------------------------
    | LOGIN EMAIL PASSWORD
    |--------------------------------------------------------------------------
    */
    public function login(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'password' => 'required'
        ]);

        if (!Auth::attempt($request->only('email','password'))) {

            return response()->json([
                'status' => false,
                'message' => 'Email atau password salah'
            ],401);
        }

        $user = Auth::user();

        if(!$user->is_active){
            return response()->json([
                'status'=>false,
                'message'=>'User tidak aktif'
            ],403);
        }

        $token = $user->createToken('flutter-token')->plainTextToken;

        return response()->json([
            'status'=>true,
            'message'=>'Login berhasil',
            'token'=>$token,
            'user'=>$user
        ]);
    }


    /*
    |--------------------------------------------------------------------------
    | FACE LOGIN
    |--------------------------------------------------------------------------
    */
    public function faceLogin(Request $request)
    {
        $request->validate([
            'face_descriptor' => 'required|array'
        ]);

        $descriptor = $request->face_descriptor;

        $users = User::hasFaceRegistered()->get();

        foreach ($users as $user) {

            $savedDescriptor = $user->getFaceDescriptorArray();

            $distance = $this->calculateDistance($descriptor,$savedDescriptor);

            if ($distance < 0.5) {

                $token = $user->createToken('flutter-token')->plainTextToken;

                return response()->json([
                    'status'=>true,
                    'message'=>'Face login berhasil',
                    'token'=>$token,
                    'user'=>$user
                ]);
            }
        }

        return response()->json([
            'status'=>false,
            'message'=>'Wajah tidak dikenali'
        ],401);
    }


    /*
    |--------------------------------------------------------------------------
    | PROFILE
    |--------------------------------------------------------------------------
    */
    public function profile(Request $request)
    {
        return response()->json([
            'status'=>true,
            'user'=>$request->user()
        ]);
    }


    /*
    |--------------------------------------------------------------------------
    | LOGOUT
    |--------------------------------------------------------------------------
    */
    public function logout(Request $request)
    {
        $request->user()->tokens()->delete();

        return response()->json([
            'status'=>true,
            'message'=>'Logout berhasil'
        ]);
    }


    /*
    |--------------------------------------------------------------------------
    | HITUNG FACE DISTANCE
    |--------------------------------------------------------------------------
    */
    private function calculateDistance($a,$b)
    {
        if(!$a || !$b){
            return 1;
        }

        $sum = 0;

        for ($i=0;$i<count($a);$i++) {
            $sum += pow($a[$i]-$b[$i],2);
        }

        return sqrt($sum);
    }

}
