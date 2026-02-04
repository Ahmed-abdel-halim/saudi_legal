<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Auth;
use App\Models\User;

class RegisterStudentController extends Controller
{
    /**
     * Show the student registration form.
     */
    public function showRegistrationForm()
    {
        // If user is already authenticated, redirect to dashboard
        if (Auth::check()) {
            return redirect()->route('dashboard.expert');
        }

        return view('auth.register-student');
    }

    /**
     * Handle student registration.
     */
    public function handleRegistration(Request $request)
    {
        // Validate the request
        $validator = Validator::make($request->all(), [
            'full-name' => 'required|string|max:255',
            'email' => 'required|email|max:255|unique:users,email',
            'password' => ['required', \Illuminate\Validation\Rules\Password::defaults(), 'confirmed'],
            'national-id' => 'required|string|max:20', // Adjust max length as needed
            'school-name' => 'required|string|max:255',
            'terms' => 'required|accepted',
        ], [
            'full-name.required' => 'الاسم الكامل مطلوب',
            'email.required' => 'البريد الإلكتروني مطلوب',
            'email.email' => 'البريد الإلكتروني غير صالح',
            'email.unique' => 'البريد الإلكتروني مسجل مسبقاً',
            'password.required' => 'كلمة المرور مطلوبة',
            'password.min' => 'كلمة المرور يجب أن تكون 8 أحرف على الأقل',
            'password.confirmed' => 'تأكيد كلمة المرور غير متطابق',
            'national-id.required' => 'رقم الهوية الوطنية مطلوب',
            'school-name.required' => 'اسم المدرسة أو الجامعة مطلوب',
            'terms.required' => 'يجب الموافقة على الشروط والأحكام',
            'terms.accepted' => 'يجب الموافقة على الشروط والأحكام',
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        try {
            // Create user
            // Create user explicitly to ensure role is set correctly
            $user = new User();
            $user->name = $request->input('full-name');
            $user->email = $request->input('email');
            $user->password = Hash::make($request->input('password'));
            $user->role = 'expert'; // Explicitly set role to expert as per requirement
            $user->national_id = $request->input('national-id');
            $user->school_name = $request->input('school-name');
            $user->is_active = true;
            $user->save();

            // Log the user in
            Auth::login($user);

            // Redirect to expert dashboard
            return redirect()->route('dashboard.expert')->with('success', 'تم التسجيل بنجاح! مرحباً بك في لوحة التحكم.');
        } catch (\Exception $e) {
            return redirect()->back()
                ->withErrors(['error' => 'حدث خطأ أثناء التسجيل: ' . $e->getMessage()])
                ->withInput();
        }
    }
}
