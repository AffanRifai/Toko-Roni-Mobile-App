<?php

namespace App\Http\Middleware;

use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken as Middleware;
use Illuminate\Session\TokenMismatchException;
use Closure;
use Illuminate\Http\Request;

class VerifyCsrfToken extends Middleware
{
    /**
     * The URIs that should be excluded from CSRF verification.
     *
     * @var array
     */
    protected $except = [
        // Route untuk face login (public, tanpa CSRF)
        'face-login',
        'api/face-login',
        'face-compare',
        'api/face-compare',
        'registered-faces',
        'api/registered-faces',

        // CSRF token refresh (harus tanpa CSRF)
        'csrf-token',
        'api/csrf-token',

        // Webhook routes (jika ada)
        'webhook/*',
        'api/webhook/*',

        // API routes (jika menggunakan API)
        'api/*',
    ];

    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     *
     * @throws \Illuminate\Session\TokenMismatchException
     */
    public function handle($request, Closure $next)
    {
        // Log untuk debugging (hapus di production)
        if (app()->environment('local')) {
            $this->logRequest($request);
        }

        try {
            return parent::handle($request, $next);
        } catch (TokenMismatchException $e) {
            return $this->handleTokenMismatch($request, $e);
        }
    }

    /**
     * Handle token mismatch exception.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \TokenMismatchException  $exception
     * @return mixed
     */
    protected function handleTokenMismatch($request, $exception)
    {
        // Log error
        \Log::warning('CSRF Token Mismatch', [
            'url' => $request->fullUrl(),
            'method' => $request->method(),
            'ip' => $request->ip(),
            'user_agent' => $request->userAgent(),
            'session_id' => $request->session()->getId(),
        ]);

        // Cek apakah request mengharapkan JSON
        if ($request->expectsJson() || $request->ajax()) {
            return response()->json([
                'success' => false,
                'message' => 'Token CSRF tidak valid. Silakan muat ulang halaman.',
                'csrf_error' => true,
                'requires_refresh' => true
            ], 419);
        }

        // Untuk form submission, redirect back dengan error
        if ($request->isMethod('post')) {
            return redirect()->back()
                ->withInput($request->except('_token'))
                ->withErrors([
                    'csrf' => 'Sesi telah berakhir. Silakan coba lagi.'
                ]);
        }

        // Default: throw exception
        throw $exception;
    }

    /**
     * Log request details for debugging.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return void
     */
    protected function logRequest($request)
    {
        $exceptPatterns = $this->except;

        foreach ($exceptPatterns as $pattern) {
            if ($request->is($pattern)) {
                \Log::info('CSRF Excluded Route Access', [
                    'url' => $request->fullUrl(),
                    'pattern' => $pattern,
                    'method' => $request->method()
                ]);
                break;
            }
        }
    }

    /**
     * Determine if the request has a valid CSRF token.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return bool
     */
    protected function tokensMatch($request)
    {
        // Jika route di-exclude, selalu return true
        if ($this->inExceptArray($request)) {
            return true;
        }

        $token = $this->getTokenFromRequest($request);
        $sessionToken = $request->session()->token();

        // Log token mismatch untuk debugging
        if (app()->environment('local') && !hash_equals($sessionToken, $token)) {
            \Log::warning('CSRF Token Comparison Failed', [
                'session_token_length' => strlen($sessionToken),
                'request_token_length' => strlen($token),
                'session_token_prefix' => substr($sessionToken, 0, 10) . '...',
                'request_token_prefix' => substr($token, 0, 10) . '...',
                'url' => $request->fullUrl(),
                'method' => $request->method()
            ]);
        }

        return hash_equals($sessionToken, $token);
    }
}
