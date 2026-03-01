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
        if (!auth()->check()) {
            return redirect()->route('superadmin.login')
                ->with('error', 'Please log in via the Super Admin portal to access this area.');
        }

        $user = auth()->user();

        // Only superadmin role is allowed
        if ($user->role !== 'superadmin') {
            auth()->logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();
            return redirect()->route('superadmin.login')
                ->with('error', 'Access Denied — This portal is restricted to Super Administrators only.');
        }

        // Must be active
        if (!$user->is_active) {
            auth()->logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();
            return redirect()->route('superadmin.login')
                ->with('error', 'Your Super Admin account has been suspended. Contact your system administrator.');
        }

        return $next($request);
    }
}
