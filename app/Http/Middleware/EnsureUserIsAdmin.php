<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureUserIsAdmin
{
    /**
     * Handle an incoming request.
     * Ensure the user is authenticated and has the 'admin' or 'superadmin' role.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (!auth()->check()) {
            return redirect()->route('login')->with('error', 'Please log in to access the admin panel.');
        }

        $user = auth()->user();

        // Check if the role is loosely admin or superadmin
        if ($user->role !== 'admin' && $user->role !== 'superadmin') {
            return redirect()->route('home')->with('error', 'You do not have permission to access the admin panel.');
        }

        // Must be active
        if (!$user->is_active) {
            auth()->logout();
            return redirect()->route('login')->with('error', 'Your admin account has been suspended.');
        }

        return $next($request);
    }
}
