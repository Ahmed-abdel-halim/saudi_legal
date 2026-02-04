<?php

namespace App\Http\Controllers;

use App\Models\AiTask;
use App\Models\GovernanceLog;
use App\Models\TaskConsensus;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ClientDashboardController extends Controller
{
    public function getMetrics()
    {
        // 1. Real-time Accuracy Meter
        $accuracyMetrics = TaskConsensus::select('consensus_type', DB::raw('count(*) as total'))
            ->groupBy('consensus_type')
            ->get()
            ->mapWithKeys(function ($item) {
                return [$item->consensus_type => $item->total];
            });

        $totalConsensus = $accuracyMetrics->sum();
        $accuracyStats = [
            'perfect_match' => $totalConsensus > 0 ? round(($accuracyMetrics['perfect_match'] ?? 0) / $totalConsensus * 100, 1) : 0,
            'majority_vote' => $totalConsensus > 0 ? round(($accuracyMetrics['majority_vote'] ?? 0) / $totalConsensus * 100, 1) : 0,
            'conflict' => $totalConsensus > 0 ? round(($accuracyMetrics['conflict'] ?? 0) / $totalConsensus * 100, 1) : 0,
        ];

        // 2. Fraud Detection Log (Recent Failures & Bans)
        $fraudLogs = GovernanceLog::whereIn('event_type', ['gold_task_failed', 'expert_banned', 'trust_score_warning'])
            ->with(['expert:id,name,email,trust_score'])
            ->latest()
            ->take(10)
            ->get();

        // 3. Conflict Samples
        $conflicts = TaskConsensus::where('consensus_type', 'conflict')
            ->with('task:id,original_data')
            ->latest()
            ->take(5)
            ->get()
            ->map(function ($consensus) {
                return [
                    'task_id' => $consensus->task_id,
                    'original_data' => $consensus->task->original_data,
                    'expert_answers' => json_decode($consensus->expert_answers),
                    'conflict_time' => $consensus->created_at,
                ];
            });

        // 4. Live Expert Tracking
        $activeExperts = User::where('role', 'expert') // Assuming 'expert' role string
            ->where('is_active', true)
            ->select('id', 'name', 'trust_score', 'gold_tasks_completed', 'gold_tasks_failed')
            ->orderBy('trust_score', 'desc')
            ->take(20)
            ->get();

        return response()->json([
            'accuracy_stats' => $accuracyStats,
            'fraud_logs' => $fraudLogs,
            'conflicts' => $conflicts,
            'active_experts' => $activeExperts,
        ]);
    }
}
