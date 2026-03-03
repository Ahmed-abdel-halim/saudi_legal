<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class RestrictImpersonation
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        if ($request->hasCookie('impersonation_token') || session()->has('impersonated_user_id')) {
            // Define routes that are strictly forbidden while impersonating
            $restrictedRoutes = [
                'dashboard.settings.update',
                'client.dashboard.settings.update', // assuming there might be other settings updates
                // add more sensitive routes here as needed
            ];

            if ($request->routeIs($restrictedRoutes)) {
                abort(403, 'This highly sensitive action is disabled during impersonation for security reasons.');
            }
        }

        return $next($request);
    }
}
