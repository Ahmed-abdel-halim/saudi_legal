<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AiPackage;
use App\Models\AiSubscription;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class AdminAiPackageController extends Controller
{
    // ─── Index ─────────────────────────────────────────────────────────────────

    public function index()
    {
        $packages = AiPackage::withCount('activeSubscriptions')
                             ->orderBy('sort_order')
                             ->get();

        $stats = [
            'total_packages'      => AiPackage::count(),
            'active_packages'     => AiPackage::where('is_active', true)->count(),
            'total_subscriptions' => AiSubscription::where('status', 'active')->count(),
            'total_revenue'       => AiSubscription::where('status', 'active')->sum('amount_paid'),
        ];

        $recentSubscriptions = AiSubscription::with(['user', 'package'])
            ->latest()
            ->take(10)
            ->get();

        return view('admin.ai_packages.index', compact('packages', 'stats', 'recentSubscriptions'));
    }

    // ─── Create ────────────────────────────────────────────────────────────────

    public function create()
    {
        return view('admin.ai_packages.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name'           => 'required|string|max:100',
            'description'    => 'nullable|string|max:500',
            'price'          => 'required|numeric|min:0',
            'billing_period' => 'required|in:monthly,yearly,lifetime',
            'query_limit'    => 'required|integer|min:-1',
            'is_unlimited'   => 'boolean',
            'is_popular'     => 'boolean',
            'is_free'        => 'boolean',
            'badge_text'     => 'nullable|string|max:60',
            'color_scheme'   => 'required|in:emerald,indigo,gold,slate',
            'sort_order'     => 'integer|min:0',
            'stripe_price_id' => 'nullable|string|max:100',
            'features'       => 'nullable|array',
            'features.*'     => 'string|max:200',
        ]);

        // Clean empty features
        if (isset($validated['features'])) {
            $validated['features'] = array_values(array_filter($validated['features']));
        }

        $validated['is_unlimited'] = $request->boolean('is_unlimited');
        $validated['is_popular']   = $request->boolean('is_popular');
        $validated['is_free']      = $request->boolean('is_free');
        $validated['is_active']    = true;

        AiPackage::create($validated);

        return redirect()->route('admin.ai_packages.index')
                         ->with('success', 'تم إنشاء الباقة بنجاح ✅');
    }

    // ─── Edit ──────────────────────────────────────────────────────────────────

    public function edit(AiPackage $aiPackage)
    {
        return view('admin.ai_packages.edit', compact('aiPackage'));
    }

    public function update(Request $request, AiPackage $aiPackage)
    {
        $validated = $request->validate([
            'name'           => 'required|string|max:100',
            'description'    => 'nullable|string|max:500',
            'price'          => 'required|numeric|min:0',
            'billing_period' => 'required|in:monthly,yearly,lifetime',
            'query_limit'    => 'required|integer|min:-1',
            'is_unlimited'   => 'boolean',
            'is_popular'     => 'boolean',
            'is_free'        => 'boolean',
            'badge_text'     => 'nullable|string|max:60',
            'color_scheme'   => 'required|in:emerald,indigo,gold,slate',
            'sort_order'     => 'integer|min:0',
            'stripe_price_id' => 'nullable|string|max:100',
            'features'       => 'nullable|array',
            'features.*'     => 'string|max:200',
        ]);

        if (isset($validated['features'])) {
            $validated['features'] = array_values(array_filter($validated['features']));
        }

        $validated['is_unlimited'] = $request->boolean('is_unlimited');
        $validated['is_popular']   = $request->boolean('is_popular');
        $validated['is_free']      = $request->boolean('is_free');

        $aiPackage->update($validated);

        return redirect()->route('admin.ai_packages.index')
                         ->with('success', 'تم تحديث الباقة بنجاح ✅');
    }

    // ─── Toggle Active ─────────────────────────────────────────────────────────

    public function toggleActive(AiPackage $aiPackage)
    {
        $aiPackage->update(['is_active' => !$aiPackage->is_active]);

        return response()->json([
            'success'   => true,
            'is_active' => $aiPackage->is_active,
            'message'   => $aiPackage->is_active ? 'تم تفعيل الباقة' : 'تم إيقاف الباقة',
        ]);
    }

    // ─── Delete ────────────────────────────────────────────────────────────────

    public function destroy(AiPackage $aiPackage)
    {
        if ($aiPackage->activeSubscriptions()->exists()) {
            return back()->with('error', 'لا يمكن حذف باقة تحتوي على مشتركين نشطين ❌');
        }

        $aiPackage->delete();
        return redirect()->route('admin.ai_packages.index')
                         ->with('success', 'تم حذف الباقة بنجاح');
    }

    // ─── Subscriptions List ────────────────────────────────────────────────────

    public function subscriptions(Request $request)
    {
        $subscriptions = AiSubscription::with(['user', 'package'])
            ->when($request->status, fn($q) => $q->where('status', $request->status))
            ->when($request->package_id, fn($q) => $q->where('ai_package_id', $request->package_id))
            ->when($request->search, function ($q) use ($request) {
                $q->whereHas('user', fn($uq) => $uq->where('name', 'like', "%{$request->search}%")
                    ->orWhere('email', 'like', "%{$request->search}%"));
            })
            ->latest()
            ->paginate(20);

        $packages = AiPackage::orderBy('sort_order')->get(['id', 'name']);

        // لنموذج التفعيل اليدوي (يشمل جميع المستخدمين حتى يتسنى للمدير تفعيل باقة لحسابه أو لأي مستخدم آخر)
        $users = \App\Models\User::orderBy('name')
            ->get(['id', 'name', 'email']);

        return view('admin.ai_packages.subscriptions', compact('subscriptions', 'packages', 'users'));
    }

    // ─── Grant Subscription Manually ──────────────────────────────────────────

    public function grantSubscription(Request $request)
    {
        $validated = $request->validate([
            'user_id'      => 'required|exists:users,id',
            'ai_package_id' => 'required|exists:ai_packages,id',
            'duration_type' => 'required|in:lifetime,1_month,3_months,6_months,1_year',
            'notes'        => 'nullable|string|max:500',
        ]);

        $user    = \App\Models\User::findOrFail($validated['user_id']);
        $package = AiPackage::findOrFail($validated['ai_package_id']);

        // احسب تاريخ الانتهاء
        $endsAt = match ($validated['duration_type']) {
            'lifetime'  => null,
            '1_month'   => now()->addMonth(),
            '3_months'  => now()->addMonths(3),
            '6_months'  => now()->addMonths(6),
            '1_year'    => now()->addYear(),
        };

        $isUnlimited = ($validated['duration_type'] === 'lifetime')
            || $package->is_unlimited
            || $package->query_limit === -1;

        // إلغاء أي اشتراكات نشطة سابقة
        AiSubscription::where('user_id', $user->id)
            ->where('status', 'active')
            ->update(['status' => 'cancelled']);

        // إنشاء الاشتراك الجديد
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
            'notes'         => $validated['notes'] ?? 'تم التفعيل يدوياً من لوحة التحكم',
        ]);

        $durationLabel = match ($validated['duration_type']) {
            'lifetime' => 'مدى الحياة ♾️',
            '1_month'  => 'شهر واحد',
            '3_months' => '3 أشهر',
            '6_months' => '6 أشهر',
            '1_year'   => 'سنة كاملة',
        };

        return back()->with('success',
            "✅ تم تفعيل باقة [{$package->name}] للمستخدم [{$user->name}] لمدة: {$durationLabel}"
        );
    }

    // ─── Revoke Subscription ───────────────────────────────────────────────────

    public function revokeSubscription(AiSubscription $subscription)
    {
        $userName = $subscription->user->name ?? 'المستخدم';
        $subscription->update(['status' => 'cancelled']);

        return back()->with('success', "🚫 تم إلغاء اشتراك [{$userName}] بنجاح.");
    }

    // ─── Delete Subscription ───────────────────────────────────────────────────

    public function destroySubscription(AiSubscription $subscription)
    {
        $userName = $subscription->user->name ?? 'المستخدم';
        $subscription->delete();

        return back()->with('success', "🗑️ تم حذف سجل الاشتراك للمستخدم [{$userName}].");
    }
}
