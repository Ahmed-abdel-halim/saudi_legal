<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use App\Models\EmailOtp;
use App\Notifications\SendOtpVerification;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class OtpVerificationController extends Controller
{
    /**
     * Display the OTP verification view.
     */
    public function show(Request $request)
    {
        $userId = session('verify_otp_user_id');
        if (!$userId) {
            return redirect()->route('login');
        }

        $user = User::findOrFail($userId);
        
        // Ensure email isn't already verified
        if ($user->hasVerifiedEmail()) {
            Auth::login($user);
            return $this->redirectBasedOnRole($user);
        }

        return view('auth.verify-email-otp', ['email' => $user->email]);
    }

    /**
     * Validate the OTP code submitted by the user.
     */
    public function verify(Request $request)
    {
        $request->validate([
            'otp_code' => 'required|string|size:6',
        ]);

        $userId = session('verify_otp_user_id');
        if (!$userId) {
            return redirect()->route('login')->withErrors(['email' => __('auth.SESSION_EXPIRED', [], app()->getLocale()) ?? 'Session expired. Please log in again.']);
        }

        $user = User::findOrFail($userId);

        $otpRecord = EmailOtp::where('user_id', $user->id)
            ->where('otp_code', $request->otp_code)
            ->latest()
            ->first();

        if (!$otpRecord) {
            return back()->withErrors(['otp_code' => __('auth.INVALID_OTP', [], app()->getLocale()) ?? 'Invalid OTP code.']);
        }

        if (Carbon::now()->isAfter($otpRecord->expires_at)) {
            return back()->withErrors(['otp_code' => __('auth.EXPIRED_OTP', [], app()->getLocale()) ?? 'This OTP has expired. Please request a new one.']);
        }

        // OTP is valid
        $otpRecord->update(['verified_at' => Carbon::now()]);
        
        $user->email_verified_at = Carbon::now();
        $user->save();

        // Log user in
        session()->forget('verify_otp_user_id');
        Auth::login($user);

        return $this->redirectBasedOnRole($user);
    }

    /**
     * Resend the OTP code.
     */
    public function resend(Request $request)
    {
        $userId = session('verify_otp_user_id');
        if (!$userId) {
            return redirect()->route('login');
        }

        $user = User::findOrFail($userId);

        // Optional: Rate limiting here
        // Example: Check if the last OTP was requested less than 1 minute ago
        $lastOtp = EmailOtp::where('user_id', $user->id)->latest()->first();
        if ($lastOtp && Carbon::now()->diffInSeconds($lastOtp->created_at) < 60) {
            return back()->withErrors(['resend' => __('auth.OTP_THROTTLE', [], app()->getLocale()) ?? 'Please wait before requesting a new code.']);
        }

        self::generateAndSendOtp($user);

        return back()->with('success', __('auth.OTP_RESENT', [], app()->getLocale()) ?? 'A new verification code has been sent to your email.');
    }

    /**
     * Static helper to generate and send the OTP.
     */
    public static function generateAndSendOtp(User $user)
    {
        $code = str_pad(mt_rand(0, 999999), 6, '0', STR_PAD_LEFT);

        // Store in DB
        EmailOtp::create([
            'user_id' => $user->id,
            'otp_code' => $code,
            'expires_at' => Carbon::now()->addMinutes(10), // 10 minutes expiry
        ]);

        // Send Email
        $user->notify(new SendOtpVerification($code));
    }

    /**
     * Helper to route the user after successful login/verification.
     */
    private function redirectBasedOnRole($user)
    {
        if ($user->role === 'expert' || $user->role === 'freelancer') {
            return redirect()->intended(route('dashboard.expert'))->with('success', __('auth.VERIFICATION_SUCCESS', [], app()->getLocale()) ?? 'Email verified successfully!');
        }

        if ($user->role === 'supplier' || $user->role === 'company') {
            return redirect()->intended(route('dashboard'))->with('success', __('auth.VERIFICATION_SUCCESS', [], app()->getLocale()) ?? 'Email verified successfully!');
        }

        return redirect()->intended(route('home'));
    }
}
