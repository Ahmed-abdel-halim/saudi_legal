<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;

class AdminUserController extends Controller
{
    /**
     * Display a listing of all platform users.
     */
    public function index(Request $request)
    {
        $query = User::query()->where('role', '!=', 'superadmin');

        // Role filter
        if ($request->filled('role')) {
            $query->where('role', $request->role);
        }

        // Status filter
        if ($request->filled('status')) {
            $query->where('is_active', $request->status === 'active' ? 1 : 0);
        }

        // Search by name or email
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%")
                  ->orWhere('phone', 'like', "%{$search}%");
            });
        }

        $users = $query->orderBy('created_at', 'desc')->paginate(20);

        $totalUsers      = User::where('role', '!=', 'super_admin')->count();
        $activeUsers     = User::where('role', '!=', 'super_admin')->where('is_active', 1)->count();
        $suspendedUsers  = User::where('role', '!=', 'super_admin')->where('is_active', 0)->count();
        $expertCount     = User::where('role', 'expert')->count();
        $companyCount    = User::where('role', 'company')->count();

        return view('admin.users.index', compact(
            'users', 'totalUsers', 'activeUsers', 'suspendedUsers', 'expertCount', 'companyCount'
        ));
    }

    /**
     * Toggle the active status of a user (suspend/activate).
     */
    public function toggleStatus(Request $request, $id)
    {
        $user = User::findOrFail($id);
        
        // Prevent Super Admin from suspending themselves
        if ($user->id === auth()->id()) {
            return back()->with('error', 'You cannot suspend your own account.');
        }

        $user->is_active = !$user->is_active;
        $user->save();

        $statusAction = $user->is_active ? 'activated' : 'suspended';

        return back()->with('success', "User successfully {$statusAction}.");
    }

    /**
     * Delete a user permanently.
     */
    public function destroy($id)
    {
        $user = User::findOrFail($id);

        if ($user->id === auth()->id()) {
            return back()->with('error', 'You cannot delete your own account.');
        }

        $user->delete();

        return back()->with('success', 'User permanently deleted from the system.');
    }
}
