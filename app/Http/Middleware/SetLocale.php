<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Session;
use Symfony\Component\HttpFoundation\Response;

class SetLocale
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Get supported locales
        $supportedLocales = ['ar', 'en'];

        // Check if lang parameter is in the request
        if ($request->has('lang')) {
            $locale = $request->get('lang');

            // Validate locale
            if (in_array($locale, $supportedLocales)) {
                // Set locale
                App::setLocale($locale);

                // Store in session for persistence
                Session::put('locale', $locale);
            }
        } else {
            // If no lang parameter, check session
            $sessionLocale = Session::get('locale');

            if ($sessionLocale && in_array($sessionLocale, $supportedLocales)) {
                App::setLocale($sessionLocale);
            } else {
                // Default to Arabic if no preference is set
                App::setLocale('ar');
            }
        }

        return $next($request);
    }
}
