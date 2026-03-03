<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureUserIsExpert
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (!auth()->check()) {
            return redirect()->route('login');
        }

        // Superadmins who land here are not impersonating — send them to the admin dashboard
        if (auth()->user()->role === 'superadmin') {
            return redirect()->route('admin.dashboard');
        }

        if (auth()->user()->role !== 'expert' && auth()->user()->role !== 'freelancer') {
            return redirect()->route('dashboard')->with('error', 'Access denied. This area is for experts only.');
        }

        return $next($request);
    }
}
