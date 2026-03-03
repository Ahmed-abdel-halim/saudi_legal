<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class FreelancerMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (!Auth::check()) {
            return redirect()->route('login');
        }

        // Superadmins who land here are not impersonating — send them to the admin dashboard
        if (Auth::user()->role === 'superadmin') {
            return redirect()->route('admin.dashboard');
        }

        if (Auth::user()->role !== 'freelancer' && Auth::user()->role !== 'expert') {
            return redirect('/');
        }

        return $next($request);
    }
}
