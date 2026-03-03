<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\ImpersonationLog;
use Illuminate\Http\Request;

class ImpersonationController extends Controller
{
    /**
     * Start impersonating a user.
     *
     * @param \Illuminate\Http\Request $request
     * @param \App\Models\User $user
     * @return \Illuminate\Http\RedirectResponse
     */
    public function start(Request $request, User $user)
    {
        // 1. Prevent recursion (admin impersonating a superadmin)
        if ($user->role === 'superadmin') {
            return back()->with('error', 'Cannot impersonate a Super Administrator.');
        }

        // 2. Log it
        $log = ImpersonationLog::create([
            'admin_id' => auth()->id(),
            'user_id' => $user->id,
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
        ]);

        // 3. Generate a Sanctum Token for the user to impersonate them securely without rotating the Super Admin's CSRF session
        $token = $user->createToken('impersonation_token')->plainTextToken;
        
        // 4. Set the impersonation details in a secure, encrypted cookie
        $cookie = cookie('impersonation_token', $token, 120); // 2 hours
        session()->put('impersonated_user_id', $user->id); // We store the target ID in the admin session merely for reference in UI if needed
        session()->put('impersonation_log_id', $log->id);

        // Determine redirect route based on user type
        if ($user->role === 'expert' || $user->role === 'freelancer') {
            return redirect()->route('freelancer.dashboard')->with('success', 'You are now impersonating ' . $user->name)->withCookie($cookie);
        } elseif ($user->role === 'company' || $user->role === 'client' || $user->role === 'student' || $user->role === 'supplier') {
             return redirect()->route('dashboard')->with('success', 'You are now impersonating ' . $user->name)->withCookie($cookie);
        } else {
             return redirect()->route('home')->with('success', 'You are now impersonating ' . $user->name)->withCookie($cookie);
        }
    }

    /**
     * Stop impersonating and return to admin.
     *
     * @return \Illuminate\Http\RedirectResponse
     */
    public function stop(Request $request)
    {
        $rawToken = $request->cookie('impersonation_token');

        if (! $rawToken) {
            return redirect()->route('admin.dashboard');
        }

        // Revoke directly via Sanctum – no dependency on Auth::user() state
        $accessToken = \Laravel\Sanctum\PersonalAccessToken::findToken($rawToken);
        if ($accessToken) {
            $accessToken->delete();
        }

        $logId = session()->pull('impersonation_log_id');
        session()->forget('impersonated_user_id');

        if ($logId) {
            ImpersonationLog::where('id', $logId)->update(['ended_at' => now()]);
        }

        return redirect()->route('admin.dashboard')
            ->with('success', 'Welcome back to admin portal.')
            ->withCookie(cookie()->forget('impersonation_token'));
    }
}
