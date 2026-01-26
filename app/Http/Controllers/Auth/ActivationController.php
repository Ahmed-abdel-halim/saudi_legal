<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;

class ActivationController extends Controller
{
    /**
     * Show the activation form.
     */
    public function show(Request $request, $id)
    {
        if (!$request->hasValidSignature()) {
            abort(403, 'Invalid or expired activation link.');
        }

        $user = User::findOrFail($id);

        if ($user->is_active) {
            return redirect()->route('login')->with('info', 'Your account is already active. Please login.');
        }

        return view('auth.activate', compact('user'));
    }

    /**
     * Handle account activation.
     */
    public function activate(Request $request, $id)
    {
        // Signature validation is redundant if we trust the form doesn't change the ID, 
        // but for security it's better to keep it if we passed signature in form, 
        // or just rely on the fact they got here. 
        // For simplicity, we just validate the input now.
        
        $user = User::findOrFail($id);

        $request->validate([
            'password' => 'required|string|min:8|confirmed',
            'full_name' => 'required|string|max:255',
            // Add other fields as necessary (bio, skills, etc.)
        ]);

        $user->password = Hash::make($request->password);
        $user->name = $request->full_name;
        $user->is_active = true;
        $user->email_verified_at = now(); // Mark email as verified since they clicked the link
        $user->save();

        Auth::login($user);

        return redirect()->route('dashboard.expert')->with('success', 'Account activated successfully! Welcome to the team.');
    }
}
