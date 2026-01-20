<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Auth;
use App\Models\User;

class RegisterCompanyController extends Controller
{
    /**
     * Show the company registration form.
     */
    public function showRegistrationForm(Request $request)
    {
        // If user is already authenticated, redirect to dashboard
        if (Auth::check()) {
            return redirect()->route('dashboard');
        }

        return view('auth.register-company', [
            'type' => $request->get('type', 'supplier')
        ]);
    }

    /**
     * Handle company registration.
     */
    public function handleRegistration(Request $request)
    {
        // Validate the request
        $validator = Validator::make($request->all(), [
            'full-name' => 'required|string|max:255',
            'work-email' => 'required|email|max:255|unique:users,email',
            'password' => 'required|string|min:8|confirmed',
            'company-name' => 'required|string|max:255',
            'cr-number' => 'required|string|max:50',
            'industry' => 'required|string',
            'company-size' => 'required|string',
            'terms' => 'required|accepted',
            'registration-type' => 'nullable|string',
        ], [
            'full-name.required' => __('auth.AUTH_FULL_NAME') . ' ' . __('auth.required', [], app()->getLocale()),
            'work-email.required' => __('auth.AUTH_EMAIL_LABEL') . ' ' . __('auth.required', [], app()->getLocale()),
            'work-email.email' => __('auth.ERROR_INVALID_EMAIL', [], app()->getLocale()),
            'work-email.unique' => __('auth.ERROR_EMAIL_EXISTS', [], app()->getLocale()),
            'password.required' => __('auth.AUTH_PASSWORD_LABEL') . ' ' . __('auth.required', [], app()->getLocale()),
            'password.min' => __('auth.ERROR_PASSWORD_MIN', [], app()->getLocale()),
            'password.confirmed' => __('auth.ERROR_PASSWORD_CONFIRMATION', [], app()->getLocale()),
            'company-name.required' => __('auth.AUTH_COMPANY_NAME') . ' ' . __('auth.required', [], app()->getLocale()),
            'cr-number.required' => __('auth.AUTH_CR_NUMBER') . ' ' . __('auth.required', [], app()->getLocale()),
            'industry.required' => __('auth.AUTH_INDUSTRY') . ' ' . __('auth.required', [], app()->getLocale()),
            'company-size.required' => __('auth.AUTH_COMPANY_SIZE') . ' ' . __('auth.required', [], app()->getLocale()),
            'terms.required' => __('auth.ERROR_TERMS_REQUIRED', [], app()->getLocale()),
            'terms.accepted' => __('auth.ERROR_TERMS_REQUIRED', [], app()->getLocale()),
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        try {
            // Create user
            $user = User::create([
                'name' => $request->input('full-name'),
                'email' => $request->input('work-email'),
                'password' => Hash::make($request->input('password')),
            ]);

            // Here you would typically create a Company model and associate it with the user
            // For now, we'll just log the user in and redirect

            // Log the user in
            Auth::login($user);

            // Redirect to dashboard or welcome page
            return redirect()->route('dashboard')->with('success', __('auth.REGISTRATION_SUCCESS', [], app()->getLocale()));
        } catch (\Exception $e) {
            return redirect()->back()
                ->withErrors(['error' => __('auth.ERROR_GENERIC', [], app()->getLocale())])
                ->withInput();
        }
    }
}
