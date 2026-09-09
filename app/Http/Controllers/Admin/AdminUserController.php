<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use App\Models\AiPackage;
use App\Models\AiSubscription;

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

        $users = $query->with('activeAiSubscription.package')->orderBy('created_at', 'desc')->paginate(20);

        $totalUsers      = User::where('role', '!=', 'super_admin')->count();
        $activeUsers     = User::where('role', '!=', 'super_admin')->where('is_active', 1)->count();
        $suspendedUsers  = User::where('role', '!=', 'super_admin')->where('is_active', 0)->count();
        $expertCount     = User::where('role', 'expert')->count();
        $companyCount    = User::where('role', 'company')->count();

        $packages = AiPackage::where('is_active', true)->orderBy('sort_order')->get(['id', 'name', 'billing_period']);

        return view('admin.users.index', compact(
            'users', 'totalUsers', 'activeUsers', 'suspendedUsers', 'expertCount', 'companyCount', 'packages'
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
     * تفعيل باقة ذكاء اصطناعي لمستخدم مباشرة من جدول المستخدمين.
     */
    public function grantAiSubscription(Request $request, $id)
    {
        $user = User::findOrFail($id);

        $validated = $request->validate([
            'ai_package_id' => 'required|exists:ai_packages,id',
            'duration_type' => 'required|in:lifetime,1_month,3_months,6_months,1_year',
            'notes'         => 'nullable|string|max:500',
        ]);

        $package = AiPackage::findOrFail($validated['ai_package_id']);

        $endsAt = match ($validated['duration_type']) {
            'lifetime' => null,
            '1_month'  => now()->addMonth(),
            '3_months' => now()->addMonths(3),
            '6_months' => now()->addMonths(6),
            '1_year'   => now()->addYear(),
        };

        $isUnlimited = ($validated['duration_type'] === 'lifetime')
            || $package->is_unlimited
            || $package->query_limit === -1;

        // إلغاء الاشتراكات النشطة السابقة
        AiSubscription::where('user_id', $user->id)
            ->where('status', 'active')
            ->update(['status' => 'cancelled']);

        AiSubscription::create([
            'user_id'       => $user->id,
            'ai_package_id' => $package->id,
            'status'        => 'active',
            'amount_paid'   => 0.00,
            'currency'      => 'SAR',
            'starts_at'     => now(),
            'ends_at'       => $endsAt,
            'queries_used'  => 0,
            'is_unlimited'  => $isUnlimited,
            'granted_by'    => auth()->user()->name ?? 'Admin',
            'notes'         => $validated['notes'] ?? 'تم التفعيل يدوياً من صفحة المستخدمين',
        ]);

        $durationLabel = match ($validated['duration_type']) {
            'lifetime' => 'مدى الحياة ♾️',
            '1_month'  => 'شهر',
            '3_months' => '3 أشهر',
            '6_months' => '6 أشهر',
            '1_year'   => 'سنة',
        };

        return back()->with('success',
            "✅ تم تفعيل [{$package->name}] للمستخدم [{$user->name}] لمدة: {$durationLabel}"
        );
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
