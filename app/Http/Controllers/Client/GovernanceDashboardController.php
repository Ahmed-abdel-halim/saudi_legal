<?php

namespace App\Http\Controllers\Client;

use App\Http\Controllers\Controller;
use App\Models\TaskConsensus;
use App\Models\GovernanceLog;
use Illuminate\Support\Facades\DB;

class GovernanceDashboardController extends Controller
{
    /**
     * Display the governance dashboard
     */
    public function index()
    {
        $metrics = $this->getAccuracyMetrics();
        $fraudLogs = $this->getFraudDetectionLogs();
        $conflicts = $this->getRecentConflicts();

        return view('client.governance.dashboard', compact('metrics', 'fraudLogs', 'conflicts'));
    }

    /**
     * Get accuracy metrics for consensus
     */
    private function getAccuracyMetrics(): array
    {
        $total = TaskConsensus::count();

        if ($total === 0) {
            return [
                'perfect_consensus_pct' => 0,
                'majority_vote_pct' => 0,
                'conflict_pct' => 0,
                'total_tasks' => 0
            ];
        }

        $stats = TaskConsensus::select('consensus_type', DB::raw('count(*) as count'))
            ->groupBy('consensus_type')
            ->pluck('count', 'consensus_type')
            ->toArray();

        return [
            'perfect_consensus_pct' => round(($stats['perfect_match'] ?? 0) / $total * 100, 2),
            'majority_vote_pct' => round(($stats['majority_vote'] ?? 0) / $total * 100, 2),
            'conflict_pct' => round(($stats['conflict'] ?? 0) / $total * 100, 2),
            'total_tasks' => $total
        ];
    }

    /**
     * Get fraud detection and governance event logs
     */
    private function getFraudDetectionLogs()
    {
        return GovernanceLog::with('expert', 'task')
            ->whereIn('event_type', ['gold_task_failed', 'trust_score_warning', 'expert_banned'])
            ->latest()
            ->limit(50)
            ->get()
            ->map(function ($log) {
                return [
                    'timestamp' => $log->created_at,
                    'expert_id' => $log->expert_id,
                    'expert_name' => $log->expert->full_name ?? 'Unknown',
                    'event' => $log->event_type,
                    'description' => $this->formatLogDescription($log),
                    'trust_score_change' => $log->trust_score_before && $log->trust_score_after
                        ? ($log->trust_score_after - $log->trust_score_before)
                        : null
                ];
            });
    }

    /**
     * Get recent conflict samples
     */
    private function getRecentConflicts()
    {
        return TaskConsensus::with('task')
            ->where('consensus_type', 'conflict')
            ->latest()
            ->limit(20)
            ->get()
            ->map(function ($consensus) {
                return [
                    'task_id' => $consensus->task_id,
                    'task_type' => $consensus->task->task_type ?? 'Unknown',
                    'expert_answers' => $consensus->expert_answers,
                    'conflict_notes' => $consensus->conflict_notes,
                    'resolved' => $consensus->resolved_at !== null,
                    'resolved_by' => $consensus->resolvedBy->full_name ?? null,
                    'created_at' => $consensus->created_at
                ];
            });
    }

    /**
     * Format governance log description
     */
    private function formatLogDescription(GovernanceLog $log): string
    {
        return match($log->event_type) {
            'gold_task_failed' => "Expert #{$log->expert_id} failed Gold Question #{$log->task_id}",
            'trust_score_warning' => "Expert #{$log->expert_id} trust score warning (Score: {$log->trust_score_after}%)",
            'expert_banned' => "Expert #{$log->expert_id} auto-banned (Trust score: {$log->trust_score_after}%)",
            default => $log->event_type
        };
    }
    /**
     * Analyze uploaded CSV file
     */
    public function analyzeCsv(\Illuminate\Http\Request $request, \App\Services\CsvAnalysisService $analysisService)
    {
        $request->validate([
            'csv_file' => 'required|file|mimes:csv,txt|max:51200' // 50MB
        ]);

        try {
            $results = $analysisService->analyze($request->file('csv_file'));
            
            return redirect()->route('client.governance.dashboard')
                ->with('analysis_results', $results)
                ->with('success', app()->getLocale() == 'ar' ? 'تم تحليل الملف بنجاح' : 'File analyzed successfully');
                
        } catch (\Exception $e) {
            return redirect()->route('client.governance.dashboard')
                ->with('error', $e->getMessage());
        }
    }
}
