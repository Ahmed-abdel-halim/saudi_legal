<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class SuperAdminLoginController extends Controller
{
    /**
     * Show the Super Admin login form.
     * If already authenticated as superadmin, redirect to admin dashboard.
     */
    public function showLoginForm()
    {
        if (Auth::check() && Auth::user()->role === 'superadmin') {
            return redirect()->route('admin.dashboard');
        }

        return view('auth.superadmin-login');
    }

    /**
     * Handle the Super Admin login attempt.
     * Rate-limited to 5 attempts per 10 minutes.
     */
    public function store(Request $request)
    {
        $request->validate([
            'email'    => ['required', 'email'],
            'password' => ['required', 'string'],
        ]);

        // Rate limiting — throttle key = email + IP
        $throttleKey = Str::transliterate(Str::lower($request->input('email')) . '|' . $request->ip());

        if (RateLimiter::tooManyAttempts($throttleKey, 5)) {
            $seconds = RateLimiter::availableIn($throttleKey);
            throw ValidationException::withMessages([
                'email' => __('Too many login attempts. Please wait :seconds second(s) before trying again.', ['seconds' => $seconds]),
            ]);
        }

        $credentials = $request->only('email', 'password');

        if (Auth::attempt($credentials, false)) {
            $user = Auth::user();

            // Role gate — only superadmin is allowed through this portal
            if ($user->role !== 'superadmin') {
                Auth::logout();
                $request->session()->invalidate();
                $request->session()->regenerateToken();

                // Count this as a failed attempt nonetheless
                RateLimiter::hit($throttleKey, 600);

                return back()->withErrors([
                    'email' => 'Access Denied — This portal is restricted to Super Administrators only.',
                ])->onlyInput('email');
            }

            // Check account is active
            if (!$user->is_active) {
                Auth::logout();
                $request->session()->invalidate();
                $request->session()->regenerateToken();

                return back()->withErrors([
                    'email' => 'This Super Admin account has been suspended.',
                ])->onlyInput('email');
            }

            RateLimiter::clear($throttleKey);
            $request->session()->regenerate();

            return redirect()->intended(route('admin.dashboard'));
        }

        // Failed attempt
        RateLimiter::hit($throttleKey, 600);

        return back()->withErrors([
            'email' => 'The credentials you provided do not match our records.',
        ])->onlyInput('email');
    }
}
