<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CaptureIntendedUrl
{
    /**
     * Handle an incoming request.
     *
     * Automatically capture the intended URL before entering login/register flows
     * so that after authentication or registration, the user is redirected back to
     * the exact page they were visiting.
     */
    public function handle(Request $request, Closure $next): Response
    {
        if ($request->isMethod('GET') && !auth()->check()) {
            
            // 1. If explicit 'redirect' or 'return_to' query parameter is present
            if ($request->filled('redirect')) {
                $redirectTarget = $request->get('redirect');
                $url = str_starts_with($redirectTarget, '/') ? url($redirectTarget) : $redirectTarget;
                if (filter_var($url, FILTER_VALIDATE_URL)) {
                    session(['url.intended' => $url]);
                }
            } elseif ($request->filled('return_to')) {
                $redirectTarget = $request->get('return_to');
                $url = str_starts_with($redirectTarget, '/') ? url($redirectTarget) : $redirectTarget;
                if (filter_var($url, FILTER_VALIDATE_URL)) {
                    session(['url.intended' => $url]);
                }
            } else {
                // 2. If visiting an auth / login / registration page, capture the referer as intended URL
                $path = '/' . ltrim($request->path(), '/');
                $authPaths = [
                    '/login',
                    '/register',
                    '/register/company',
                    '/register/student',
                    '/freelancer/register',
                ];

                $isAuthRoute = false;
                foreach ($authPaths as $authPath) {
                    if ($path === $authPath || str_starts_with($path, $authPath)) {
                        $isAuthRoute = true;
                        break;
                    }
                }

                if ($isAuthRoute) {
                    $referer = $request->header('referer');
                    if ($referer && filter_var($referer, FILTER_VALIDATE_URL)) {
                        $refererHost = parse_url($referer, PHP_URL_HOST);
                        $currentHost = $request->getHost();

                        if ($refererHost === $currentHost) {
                            $refererPath = '/' . ltrim(parse_url($referer, PHP_URL_PATH) ?? '', '/');
                            
                            // Check if referer is NOT an auth page or logout
                            $isRefererAuth = false;
                            foreach ($authPaths as $authPath) {
                                if ($refererPath === $authPath || str_starts_with($refererPath, $authPath)) {
                                    $isRefererAuth = true;
                                    break;
                                }
                            }

                            if (!$isRefererAuth && !str_contains($refererPath, 'logout')) {
                                if (!session()->has('url.intended')) {
                                    session(['url.intended' => $referer]);
                                }
                            }
                        }
                    }
                }
            }
        }

        return $next($request);
    }
}
