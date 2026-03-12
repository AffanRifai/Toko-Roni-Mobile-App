<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class EnsureFaceRegistered
{
    public function handle(Request $request, Closure $next)
    {
        if (!Auth::check()) {
            return redirect()->route('login');
        }

        // Jika user mencoba akses fitur face recognition tapi belum registrasi
        if ($request->is('face/*') && !Auth::user()->hasFaceRegistered()) {
            return redirect()->route('profile.edit')
                ->with('warning', 'Anda perlu registrasi wajah terlebih dahulu untuk menggunakan fitur ini.');
        }

        return $next($request);
    }
}