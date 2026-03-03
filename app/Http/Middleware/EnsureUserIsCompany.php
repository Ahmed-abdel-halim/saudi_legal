<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureUserIsCompany
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

        $user = auth()->user();

        // Superadmins who land here are not impersonating — send them to the admin dashboard
        if ($user->role === 'superadmin') {
            return redirect()->route('admin.dashboard');
        }

        // If user is an expert, redirect them to their own dashboard
        if ($user->role === 'expert') {
            return redirect()->route('dashboard.expert')->with('error', 'Access denied. You are redirected to your dashboard.');
        }

        // Optional: Strict check for supplier/company roles if needed
        // if (!in_array($user->role, ['supplier', 'requester', 'company'])) {
        //    abort(403, 'Unauthorized');
        // }

        return $next($request);
    }
}
