<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class ForceJsonResponse
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Force the Accept header to be application/json
        $request->headers->set('Accept', 'application/json');

        $response = $next($request);

        // If response is not a JSON response, maybe we should force it, 
        // but typically Laravel handles it well if the Accept header is present.
        return $response;
    }
}
