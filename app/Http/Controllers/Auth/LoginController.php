<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class LoginController extends Controller
{
    /**
     * Handle an incoming authentication request.
     */
    public function store(Request $request)
    {
        $credentials = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required'],
        ]);

        if (Auth::attempt($credentials, $request->boolean('remember'))) {
            $request->session()->regenerate();
            $user = Auth::user();

            // Enforce OTP Email Verification for new / unverified users
            if (!$user->hasVerifiedEmail()) {
                Auth::logout();
                session(['verify_otp_user_id' => $user->id, 'email' => $user->email]);
                \App\Http\Controllers\Auth\OtpVerificationController::generateAndSendOtp($user);
                
                return redirect()->route('verify-otp')->with('success', __('auth.OTP_SENT', [], app()->getLocale()) ?? 'Please verify your email to login. A new code has been sent.');
            }

            // Redirect based on role

            if ($user->role === 'superadmin' || $user->role === 'admin') {
                return redirect()->intended(route('admin.dashboard'));
            }

            if ($user->role === 'expert' || $user->role === 'freelancer') {
                return redirect()->intended(route('freelancer.dashboard'));
            }

            if ($user->role === 'supplier' || $user->role === 'company') {
                return redirect()->intended(route('dashboard'));
            }

            // Default fallback
            return redirect()->intended(route('dashboard'));
        }

        return back()->withErrors([
            'email' => __('auth.ERROR_INVALID_CREDENTIALS', [], app()->getLocale()),
        ])->onlyInput('email');
    }
}
