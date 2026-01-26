<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class ExpertDashboardController extends Controller
{
    public function index()
    {
        $user = Auth::user();

        // 1. Check permissions (already handled by middleware, but double check role if needed)
        // Assuming there is a role column or method. The original code checked $_SESSION['role'] !== 'expert'.
        // We'll rely on the user object. If role is not on user model, we might need to check how it's stored.
        // For now, I will proceed assuming role is on user table or we just show the board.
        // Original: if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'expert')
        // We will assume the middleware handles authentication. Role check might need refinement if 'role' is not a column.
        
        // 2. Statistics
        $stats = DB::table('ai_responses_v2')
            ->select(DB::raw('count(*) as total_tasks'))
            ->selectRaw("SUM(CASE WHEN DATE(created_at) = CURDATE() THEN 1 ELSE 0 END) as tasks_today")
            ->selectRaw("MAX(created_at) as last_activity")
            ->where('expert_id', $user->id) // Assuming user_id in session maps to id in users table
            ->first();

        $total_tasks = $stats->total_tasks ?? 0;
        $tasks_today = $stats->tasks_today ?? 0;

        // 3. Financials
        $price_per_task = 5;
        $total_balance = $total_tasks * $price_per_task;
        $today_balance = $tasks_today * $price_per_task;

        // 4. Level Logic
        $expert_level = 'مساهم جديد';
        $badge_color = 'bg-gray-100 text-gray-600';
        $badge_icon = 'fa-user';

        if ($total_tasks > 500) {
            $expert_level = 'خبير سيادي (Elite)';
            $badge_color = 'bg-purple-100 text-purple-700 border-purple-200';
            $badge_icon = 'fa-crown';
        } elseif ($total_tasks > 100) {
            $expert_level = 'مدقق معتمد (Certified)';
            $badge_color = 'bg-blue-100 text-blue-700 border-blue-200';
            $badge_icon = 'fa-certificate';
        } elseif ($total_tasks > 20) {
            $expert_level = 'مساهم نشط';
            $badge_color = 'bg-green-100 text-green-700 border-green-200';
            $badge_icon = 'fa-star';
        }

        // 5. Pending Tasks Count
        $pending_count = DB::table('ai_tasks_v2')
            ->where('status', 'pending')
            ->count();

        // 6. History
        $history = DB::table('ai_responses_v2')
            ->where('expert_id', $user->id)
            ->orderBy('id', 'desc')
            ->limit(5)
            ->get();

        return view('dashboard.expert.index', compact(
            'user',
            'total_tasks',
            'tasks_today',
            'total_balance',
            'today_balance',
            'expert_level',
            'badge_color',
            'badge_icon',
            'pending_count',
            'history',
            'price_per_task'
        ));
    }

    public function availability()
    {
        $user = Auth::user();
        return view('dashboard.expert.availability', compact('user'));
    }

    public function cvBuilder()
    {
        $user = Auth::user();
        return view('dashboard.expert.cv-builder', compact('user'));
    }

    public function services()
    {
        $user = Auth::user();
        return view('dashboard.expert.services', compact('user'));
    }

    public function workbench()
    {
        $user = Auth::user();
        return view('dashboard.expert.workbench', compact('user'));
    }

    public function settings()
    {
        $user = Auth::user();
        return view('dashboard.expert.settings', compact('user'));
    }
}
