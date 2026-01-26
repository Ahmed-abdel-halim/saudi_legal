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
            'work-email' => 'required|email|max:255',
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

        // Manual uniqueness check to avoid Validator DB driver issues
        if (User::where('email', $request->input('work-email'))->exists()) {
            return redirect()->back()
                ->withErrors(['work-email' => __('auth.ERROR_EMAIL_EXISTS', [], app()->getLocale())])
                ->withInput();
        }

        try {
            // Create company first
            $company = \App\Models\Company::create([
                'name' => $request->input('company-name'),
                'cr_number' => $request->input('cr-number'),
                'industry' => $request->input('industry'),
                'size' => $request->input('company-size'),
                'is_supplier' => $request->input('registration-type') === 'supplier',
                'is_requester' => $request->input('registration-type') === 'requester',
                'status' => 'active',
            ]);

            // Create user and link to company
            $user = User::create([
                'name' => $request->input('full-name'),
                'email' => $request->input('work-email'),
                'password' => Hash::make($request->input('password')),
                'company_id' => $company->company_id,
                'role' => $request->input('registration-type') === 'supplier' ? 'supplier' : 'requester',
            ]);

            // Log the user in
            Auth::login($user);

            // Redirect to dashboard or welcome page
            return redirect()->route('dashboard')->with('success', __('auth.REGISTRATION_SUCCESS', [], app()->getLocale()));
        } catch (\Exception $e) {
            return redirect()->back()
                ->withErrors(['error' => __('auth.ERROR_GENERIC', [], app()->getLocale()) . ' ' . $e->getMessage()])
                ->withInput();
        }
    }
}
