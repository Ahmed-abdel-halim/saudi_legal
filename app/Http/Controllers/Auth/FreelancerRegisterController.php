<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules;

class FreelancerRegisterController extends Controller
{
    public function showRegistrationForm()
    {
        return view('auth.register-freelancer');
    }

    public function register(Request $request)
    {
        $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:'.User::class],
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
        ]);

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'role' => ($request->type === 'expert') ? 'expert' : 'freelancer', // Set role based on type
            'expert_domain' => ($request->type === 'expert') ? 'law' : null, // Set domain to 'law' for experts
            'is_active' => true,
        ]);

        session(['verify_otp_user_id' => $user->id, 'email' => $user->email]);
        \App\Http\Controllers\Auth\OtpVerificationController::generateAndSendOtp($user);

        return redirect()->route('verify-otp')->with('success', __('auth.OTP_SENT', [], app()->getLocale()) ?? 'Verification code sent to your email.');
    }
}
