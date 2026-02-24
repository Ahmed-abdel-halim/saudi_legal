<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Company;
use App\Models\ServicePurchase;
use App\Models\ProjectOffer;
use App\Models\SiteSetting;
use Carbon\Carbon;

class AdminDashboardController extends Controller
{
    public function index()
    {
        $now       = Carbon::now();
        $thisMonth = $now->month;
        $thisYear  = $now->year;
        $lastMonth = $now->copy()->subMonth();

        // ── helpers ────────────────────────────────────────────────────────
        $pctChange = function ($current, $previous): array {
            if ($previous == 0) {
                return ['value' => $current > 0 ? 100 : 0, 'up' => true];
            }
            $diff = (($current - $previous) / $previous) * 100;
            return ['value' => abs(round($diff, 1)), 'up' => $diff >= 0];
        };

        // ── KPI: Total Users (exclude superadmins) ────────────────────────
        $totalUsers   = User::where('role', '!=', 'superadmin')->count();
        $usersThisM   = User::where('role', '!=', 'superadmin')->whereYear('created_at', $thisYear)->whereMonth('created_at', $thisMonth)->count();
        $usersLastM   = User::where('role', '!=', 'superadmin')->whereYear('created_at', $lastMonth->year)->whereMonth('created_at', $lastMonth->month)->count();

        // ── KPI: Total Companies ───────────────────────────────────────────
        $totalCompanies   = Company::count();
        $companiesThisM   = Company::whereYear('created_at', $thisYear)->whereMonth('created_at', $thisMonth)->count();
        $companiesLastM   = Company::whereYear('created_at', $lastMonth->year)->whereMonth('created_at', $lastMonth->month)->count();

        // ── KPI: Revenue ───────────────────────────────────────────────────
        $totalRevenue   = ServicePurchase::sum('total_price') ?? 0;
        $revenueThisM   = ServicePurchase::whereYear('created_at', $thisYear)->whereMonth('created_at', $thisMonth)->sum('total_price') ?? 0;
        $revenueLastM   = ServicePurchase::whereYear('created_at', $lastMonth->year)->whereMonth('created_at', $lastMonth->month)->sum('total_price') ?? 0;

        // ── KPI: Disputes ─────────────────────────────────────────────────
        $activeDisputes   = ProjectOffer::where('service_status', 'disputed')->count()
                          + ServicePurchase::where('service_status', 'disputed')->count();
        $disputesThisM    = ProjectOffer::where('service_status', 'disputed')->whereYear('created_at', $thisYear)->whereMonth('created_at', $thisMonth)->count()
                          + ServicePurchase::where('service_status', 'disputed')->whereYear('created_at', $thisYear)->whereMonth('created_at', $thisMonth)->count();
        $disputesLastM    = ProjectOffer::where('service_status', 'disputed')->whereYear('created_at', $lastMonth->year)->whereMonth('created_at', $lastMonth->month)->count()
                          + ServicePurchase::where('service_status', 'disputed')->whereYear('created_at', $lastMonth->year)->whereMonth('created_at', $lastMonth->month)->count();

        $metrics = [
            'total_users'       => $totalUsers,
            'users_change'      => $pctChange($usersThisM, $usersLastM),

            'total_companies'   => $totalCompanies,
            'companies_change'  => $pctChange($companiesThisM, $companiesLastM),

            'total_revenue'     => $totalRevenue,
            'revenue_change'    => $pctChange($revenueThisM, $revenueLastM),

            'active_disputes'   => $activeDisputes,
            'disputes_change'   => $pctChange($disputesThisM, $disputesLastM),
        ];

        // ── Recent Users (exclude superadmins) ───────────────────────────
        $recentUsers = User::where('role', '!=', 'superadmin')->latest()->take(5)->get();

        // ── Revenue Chart (this year by month) ────────────────────────────
        $purchasesThisYear = ServicePurchase::whereYear('created_at', $thisYear)->get(['created_at', 'total_price']);
        $chartDataArray = array_fill(1, 12, 0);
        foreach ($purchasesThisYear as $purchase) {
            $m = (int) $purchase->created_at->format('n');
            $chartDataArray[$m] += $purchase->total_price;
        }
        $chartData   = array_values($chartDataArray);
        $chartLabels = [];
        for ($i = 1; $i <= 12; $i++) {
            $chartLabels[] = date('M', mktime(0, 0, 0, $i, 10));
        }

        // ── Activity Feed ─────────────────────────────────────────────────
        $latestPurchases = ServicePurchase::with(['expert', 'client'])->latest()->take(3)->get()->map(fn($p) => [
            'type'        => 'purchase',
            'title'       => __('admin.activity_new_purchase') ?? 'New Service Purchase',
            'description' => ($p->client->name ?? 'A client') . ' purchased ' . $p->hours_purchased . ' hours from ' . ($p->expert->name ?? 'an expert') . '.',
            'time'        => $p->created_at,
            'time_diff'   => $p->created_at?->diffForHumans() ?? 'Just now',
            'icon'        => 'fa-solid fa-cart-shopping',
            'icon_bg'     => 'bg-emerald-50 ring-emerald-50',
            'icon_color'  => 'text-emerald-500',
        ]);

        $latestDisputes = ProjectOffer::where('service_status', 'disputed')->with(['project.requester', 'expert'])->latest()->take(3)->get()->map(fn($d) => [
            'type'        => 'dispute',
            'title'       => __('admin.activity_dispute') ?? 'Dispute Escalated',
            'description' => 'A dispute was filed by ' . ($d->project->requester->name ?? 'A Client') . ' against ' . ($d->expert->name ?? 'an Expert') . '.',
            'time'        => $d->created_at,
            'time_diff'   => $d->created_at?->diffForHumans() ?? 'Just now',
            'icon'        => 'fa-solid fa-scale-balanced',
            'icon_bg'     => 'bg-red-50 ring-red-50',
            'icon_color'  => 'text-red-500',
        ]);

        $latestUsers = User::where('role', '!=', 'superadmin')->latest()->take(4)->get()->map(fn($u) => [
            'type'        => 'user',
            'title'       => __('admin.activity_user_joined') ?? 'New User Joined',
            'description' => $u->name . ' joined as a ' . ($u->role ?? 'user') . '.',
            'time'        => $u->created_at,
            'time_diff'   => $u->created_at?->diffForHumans() ?? 'Just now',
            'icon'        => 'fa-solid fa-user-plus',
            'icon_bg'     => 'bg-blue-50 ring-blue-50',
            'icon_color'  => 'text-blue-500',
        ]);

        $recentActivities = collect()
            ->concat($latestPurchases)
            ->concat($latestDisputes)
            ->concat($latestUsers)
            ->sortByDesc('time')
            ->take(6);

        // ── Maintenance mode from settings ────────────────────────────────
        $maintenanceMode = (bool) SiteSetting::get('maintenance_mode', false);

        return view('admin.dashboard.index', compact(
            'metrics', 'recentUsers', 'chartData', 'chartLabels', 'recentActivities', 'maintenanceMode'
        ));
    }
}
