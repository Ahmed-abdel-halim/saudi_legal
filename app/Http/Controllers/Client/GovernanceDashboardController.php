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
        $liveTracking = $this->getLiveTrackingStats();
        $conflicts = $this->getRecentConflicts();

        // Fetch tasks for the authenticated user (Client)
        $tasks = \App\Models\AiTask::where('client_id', auth()->id())
            ->latest()
            ->paginate(10);

        return view('client.governance.dashboard', compact('metrics', 'fraudLogs', 'conflicts', 'liveTracking', 'tasks'));
    }

    /**
     * Upload and create tasks from CSV
     */
    public function uploadTasks(\Illuminate\Http\Request $request)
    {
        $request->validate([
            'csv_file' => 'required|file|mimes:csv,txt|max:51200'
        ]);

        $user = auth()->user();
        $file = $request->file('csv_file');
        
        try {
            $handle = fopen($file->getRealPath(), 'r');
            $header = fgetcsv($handle); // Assuming first row is header
            
            if (!$header) {
                throw new \Exception("Empty CSV file");
            }

            $count = 0;
            while (($row = fgetcsv($handle)) !== false) {
                if (!empty($row[0])) {
                     \App\Models\AiTask::create([
                        'task_type' => 'text_correction',
                        'original_data' => $row[0],
                        'client_id' => $user->id,
                        'status' => 'Pending',
                        'consensus_status' => 'pending',
                        'required_responses' => 3
                    ]);
                    $count++;
                }
            }
            
            fclose($handle);
            
            return redirect()->route('client.governance.dashboard')
                ->with('success', "Successfully uploaded {$count} tasks.");
                
        } catch (\Exception $e) {
            return back()->with('error', 'Error uploading tasks: ' . $e->getMessage());
        }
    }

    /**
     * Get live tracking stats (Active Experts, Trust Scores, etc.)
     */
    private function getLiveTrackingStats()
    {
        $experts = \App\Models\User::where('role', 'expert')->get();
        
        return [
            'total_experts' => $experts->count(),
            'active_experts' => \App\Models\TaskAssignment::active()->distinct('expert_id')->count('expert_id'),
            'avg_trust_score' => round($experts->avg('trust_score') ?? 100, 1),
            'banned_experts' => $experts->where('is_banned', true)->count(),
            'gold_pass_rate' => $experts->sum('gold_tasks_completed') > 0 
                ? round(($experts->sum('gold_tasks_completed') / ($experts->sum('gold_tasks_completed') + $experts->sum('gold_tasks_failed'))) * 100, 1)
                : 100,
        ];
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
