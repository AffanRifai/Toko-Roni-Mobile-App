<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class RoleMiddleware
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next, ...$roles): mixed
    {
        try {
            // Check authentication
            if (!Auth::check()) {
                return $this->unauthorizedResponse($request, 'Authentication required');
            }

            $user = Auth::user();

            // Check if user is active
            if (!$this->isUserActive($user)) {
                Auth::logout();
                return $this->unauthorizedResponse($request, 'Account is inactive');
            }

            // Check role permissions
            if (!$this->hasRequiredRole($user, $roles)) {
                Log::warning('Unauthorized access attempt', [
                    'user_id' => $user->id,
                    'user_role' => $user->role,
                    'required_roles' => $roles,
                    'path' => $request->path(),
                    'method' => $request->method()
                ]);

                return $this->forbiddenResponse($request, $user->role, $roles);
            }

            // Add user info to request for convenience
            $request->merge([
                'current_user' => $user,
                'user_role' => $user->role
            ]);

            return $next($request);

        } catch (\Exception $e) {
            Log::error('RoleMiddleware error: ' . $e->getMessage());

            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Internal server error'
                ], 500);
            }

            abort(500, 'Internal server error');
        }
    }

    /**
     * Check if user is active.
     */
    private function isUserActive($user): bool
    {
        // Check if user has is_active property
        if (property_exists($user, 'is_active')) {
            return (bool) $user->is_active;
        }

        // Check if user has status property
        if (property_exists($user, 'status')) {
            return $user->status === 'active';
        }

        return true; // Default to true if no status property
    }

    /**
     * Check if user has required role.
     */
    private function hasRequiredRole($user, array $requiredRoles): bool
    {
        // Allow owner to access everything
        if ($user->role === 'owner') {
            return true;
        }

        // Check if user role is in required roles
        return in_array($user->role, $requiredRoles);
    }

    /**
     * Generate unauthorized response.
     */
    private function unauthorizedResponse(Request $request, string $message)
    {
        if ($request->expectsJson() || $request->is('api/*')) {
            return response()->json([
                'success' => false,
                'message' => $message,
                'error' => 'UNAUTHENTICATED'
            ], 401);
        }

        return redirect()
            ->route('login')
            ->with('error', $message);
    }

    /**
     * Generate forbidden response.
     */
    private function forbiddenResponse(Request $request, string $userRole, array $requiredRoles)
    {
        $allowedRoles = implode(', ', $requiredRoles);
        $message = "Access denied. Your role ($userRole) does not have permission.";

        if ($request->expectsJson() || $request->is('api/*')) {
            return response()->json([
                'success' => false,
                'message' => $message,
                'error' => 'FORBIDDEN',
                'user_role' => $userRole,
                'required_roles' => $requiredRoles
            ], 403);
        }

        // For web requests, check if user is trying to access role-specific dashboard
        if ($request->is('dashboard/*')) {
            return redirect()
                ->route('dashboard')
                ->with('error', $message);
        }

        // For other web requests
        return redirect()
            ->back()
            ->with('error', $message)
            ->withInput();
    }
}
