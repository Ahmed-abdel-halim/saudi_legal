<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class HandleImpersonation
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // 1. Check for impersonation token first (highest priority)
        $impersonationToken = $request->cookie('impersonation_token');

        if ($impersonationToken) {
            $accessToken = \Laravel\Sanctum\PersonalAccessToken::findToken($impersonationToken);

            if ($accessToken && $accessToken->tokenable_type === 'App\Models\User' && $accessToken->name === 'impersonation_token') {
                $user = $accessToken->tokenable;

                if ($user) {
                    // Explicitly target the 'web' guard so the built-in 'auth'
                    // middleware (which checks the 'web' SessionGuard) recognises
                    // the impersonated user without a session lookup.
                    Auth::guard('web')->setUser($user);
                    return $next($request); // Exit early if impersonation is active
                }
            }
        }

        // 2. Fallback to check for native superadmin token
        $superadminToken = $request->cookie('superadmin_token');

        if ($superadminToken) {
            $accessToken = \Laravel\Sanctum\PersonalAccessToken::findToken($superadminToken);

            if ($accessToken && $accessToken->tokenable_type === 'App\Models\User' && $accessToken->name === 'superadmin_token') {
                $user = $accessToken->tokenable;

                if ($user && $user->role === 'superadmin' && $user->is_active) {
                    Auth::guard('web')->setUser($user);
                }
            }
        }

        return $next($request);
    }
}
