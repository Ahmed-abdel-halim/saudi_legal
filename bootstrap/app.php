<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        channels: __DIR__.'/../routes/channels.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        // Exclude Stripe webhook from CSRF verification — signature check in controller provides security
        $middleware->validateCsrfTokens(except: [
            '/stripe/webhook',
            '/stripe/ai-subscription/webhook',
            'api/whatsapp/webhook', // Twilio يُرسل POST بدون CSRF token، التحقق يتم عبر X-Twilio-Signature
        ]);

        $middleware->web(append: [
            \App\Http\Middleware\SetLocale::class,
            \App\Http\Middleware\HandleImpersonation::class,
            \App\Http\Middleware\RestrictImpersonation::class,
            \App\Http\Middleware\CaptureIntendedUrl::class,
        ]);

        $middleware->alias([
            'expert'      => \App\Http\Middleware\EnsureUserIsExpert::class,
            'company'     => \App\Http\Middleware\EnsureUserIsCompany::class,
            'freelancer'  => \App\Http\Middleware\FreelancerMiddleware::class,
            'admin'       => \App\Http\Middleware\EnsureUserIsAdmin::class,
            'superadmin'  => \App\Http\Middleware\EnsureUserIsSuperAdmin::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        $exceptions->render(function (\Illuminate\Session\TokenMismatchException $e, \Illuminate\Http\Request $request) {
            return redirect()->back()->withInput($request->except('password', 'password_confirmation', '_token'))
                ->with('error', 'Your session has expired. Please try again.');
        });
    })->create();
