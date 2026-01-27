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

            // Redirect based on role
            $user = Auth::user();

            if ($user->role === 'expert') {
                return redirect()->intended(route('dashboard.expert'));
            }

            if ($user->role === 'supplier') {
                return redirect()->intended(route('dashboard'));
            }

            // Default fallback for other roles (e.g., requester)
            return redirect()->intended(route('dashboard'));
        }

        return back()->withErrors([
            'email' => __('auth.ERROR_INVALID_CREDENTIALS', [], app()->getLocale()),
        ])->onlyInput('email');
    }
}
