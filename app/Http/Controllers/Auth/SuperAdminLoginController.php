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
    public function showLoginForm(Request $request)
    {
        if ($request->hasCookie('superadmin_token')) {
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

        $user = \App\Models\User::where('email', $credentials['email'])->first();

        if ($user && \Illuminate\Support\Facades\Hash::check($credentials['password'], $user->password)) {
            // Role gate — only superadmin is allowed through this portal
            if ($user->role !== 'superadmin') {
                // Count this as a failed attempt nonetheless
                RateLimiter::hit($throttleKey, 600);

                return back()->withErrors([
                    'email' => 'Access Denied — This portal is restricted to Super Administrators only.',
                ])->onlyInput('email');
            }

            // Check account is active
            if (!$user->is_active) {
                return back()->withErrors([
                    'email' => 'This Super Admin account has been suspended.',
                ])->onlyInput('email');
            }

            RateLimiter::clear($throttleKey);
            
            // Generate Sanctum Token
            $token = $user->createToken('superadmin_token')->plainTextToken;
            $cookie = cookie('superadmin_token', $token, 1440); // 24 hours

            return redirect()->intended(route('admin.dashboard'))->withCookie($cookie);
        }

        // Failed attempt
        RateLimiter::hit($throttleKey, 600);

        return back()->withErrors([
            'email' => 'The credentials you provided do not match our records.',
        ])->onlyInput('email');
    }
}
