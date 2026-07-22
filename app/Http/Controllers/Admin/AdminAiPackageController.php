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
            ->latest()
            ->paginate(20);

        $packages = AiPackage::orderBy('sort_order')->get(['id', 'name']);

        return view('admin.ai_packages.subscriptions', compact('subscriptions', 'packages'));
    }
}
