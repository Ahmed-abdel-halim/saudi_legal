<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureUserIsSuperAdmin
{
    /**
     * Handle an incoming request.
     * Ensure the user is authenticated and has the 'superadmin' role.
     * If not authenticated → redirect to the dedicated superadmin login portal.
     * If authenticated but wrong role → logout and redirect with error.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $cookieValue = $request->cookie('superadmin_token');

        // No cookie → not logged in to the admin portal
        if (! $cookieValue) {
            return redirect()->route('superadmin.login')
                ->with('error', 'Please log in via the Super Admin portal to access this area.');
        }

        // Resolve identity DIRECTLY from the Sanctum token — bypass auth()->user() entirely.
        // HandleImpersonation may have set an impersonated (company/expert) user on the web
        // guard BEFORE this middleware runs. If we relied on auth()->user() we would get
        // the impersonated user, causing a false "Access Denied" redirect.
        $accessToken = \Laravel\Sanctum\PersonalAccessToken::findToken($cookieValue);

        if (! $accessToken
            || $accessToken->name          !== 'superadmin_token'
            || $accessToken->tokenable_type !== 'App\Models\User'
        ) {
            return redirect()->route('superadmin.login')
                ->withCookie(cookie()->forget('superadmin_token'))
                ->with('error', 'Your session has expired. Please log in again.');
        }

        $user = $accessToken->tokenable;

        if (! $user) {
            return redirect()->route('superadmin.login')
                ->withCookie(cookie()->forget('superadmin_token'))
                ->with('error', 'Your session has expired. Please log in again.');
        }

        // Only superadmin role is allowed
        if ($user->role !== 'superadmin') {
            return redirect()->route('superadmin.login')
                ->withCookie(cookie()->forget('superadmin_token'))
                ->with('error', 'Access Denied — This portal is restricted to Super Administrators only.');
        }

        // Must be active
        if (! $user->is_active) {
            return redirect()->route('superadmin.login')
                ->withCookie(cookie()->forget('superadmin_token'))
                ->with('error', 'Your Super Admin account has been suspended. Contact your system administrator.');
        }

        // Explicitly restore the web guard to the SUPERADMIN so admin controllers
        // always see the correct identity, even when an impersonation_token is also present.
        \Illuminate\Support\Facades\Auth::guard('web')->setUser($user);

        return $next($request);
    }
}
