<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ServicePurchase;
use App\Models\ProjectOffer;
use Illuminate\Http\Request;

class AdminFinancialController extends Controller
{
    /**
     * Display the financials index page.
     */
    public function index(Request $request)
    {
        // Get hourly service purchases
        $purchases = ServicePurchase::with(['expert', 'client'])
            ->latest()
            ->take(50)
            ->get()
            ->map(function ($item) {
                return [
                    'id' => 'HP-' . $item->id,
                    'type' => 'Hourly Service',
                    'amount' => $item->total_price,
                    'status' => $item->payment_status ?? 'paid',
                    'date' => $item->created_at,
                    'from' => $item->client->name ?? 'Unknown',
                    'to' => $item->expert->name ?? 'Unknown',
                ];
            });

        // Get project offers accepted
        $offers = ProjectOffer::with(['expert', 'project.requester'])
            ->whereIn('service_status', ['in_progress', 'completed'])
            ->latest()
            ->take(50)
            ->get()
            ->map(function ($item) {
                return [
                    'id' => 'PO-' . $item->id,
                    'type' => 'Project Milestone',
                    'amount' => $item->expert_amount,
                    'status' => $item->payment_status ?? 'escrow',
                    'date' => $item->created_at,
                    'from' => $item->project->requester->name ?? 'Unknown',
                    'to' => $item->expert->name ?? 'Unknown',
                ];
            });

        // Combine and sort
        $transactions = $purchases->concat($offers)
            ->sortByDesc('date')
            ->values();

        // Stats
        $totalRevenue = $purchases->sum('total_price') + $offers->sum('amount');
        $platformFeePercent = 0.15; // 15% platform fee assumption
        
        $stats = [
            'total_volume' => $totalRevenue,
            'platform_revenue' => $totalRevenue * $platformFeePercent,
            'pending_payouts' => $transactions->whereIn('status', ['escrow', 'pending'])->sum('amount'),
            'completed_payouts' => $transactions->where('status', 'paid')->sum('amount'),
        ];

        return view('admin.financials.index', compact('transactions', 'stats'));
    }
}
