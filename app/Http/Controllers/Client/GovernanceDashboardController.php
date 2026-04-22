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
            // Read file content
            $content = file_get_contents($file->getRealPath());

            // Try to detect and convert encoding
            $targetEncoding = 'UTF-8';
            
            // First, strict check for UTF-8
            if (!mb_check_encoding($content, $targetEncoding)) {
                $converted = false;

                // 1. Try iconv with CP1256 (Windows standard for Arabic) - most likely to work
                if (function_exists('iconv')) {
                    try {
                        // CP1256 is the standard name on Windows, Windows-1256 on some *nix
                        $encodingsToCheck = ['CP1256', 'WINDOWS-1256'];
                        foreach ($encodingsToCheck as $enc) {
                            $convertedContent = @iconv($enc, 'UTF-8//IGNORE', $content);
                            if ($convertedContent && mb_check_encoding($convertedContent, $targetEncoding)) {
                                $content = $convertedContent;
                                $converted = true;
                                break;
                            }
                        }
                    } catch (\Throwable $e) {
                         // ignore
                    }
                }

                // 2. Fallback to mb_convert_encoding if iconv failed or missing
                if (!$converted) {
                    $encodingsToCheck = ['Windows-1256', 'ISO-8859-6'];
                    foreach ($encodingsToCheck as $enc) {
                        try {
                            $convertedContent = @mb_convert_encoding($content, $targetEncoding, $enc);
                            if ($convertedContent && mb_check_encoding($convertedContent, $targetEncoding)) {
                                $content = $convertedContent;
                                $converted = true;
                                break;
                            }
                        } catch (\Throwable $e) {
                            // ignore
                        }
                    }
                }
                
                // 3. Final Fallback: Treat as ISO-8859-1 (Latin1) and convert to UTF-8
                // This prevents "Incorrect string value" SQL errors but might show wrong chars if it was actually Arabic
                if (!$converted) {
                     $content = mb_convert_encoding($content, 'UTF-8', 'ISO-8859-1'); 
                }
            }

            // Create a temporary file stream in memory
            $handle = fopen('php://memory', 'r+');
            fwrite($handle, $content);
            rewind($handle);

            // Remove BOM if present
            $bom = fread($handle, 3);
            if ($bom !== "\xEF\xBB\xBF") {
                rewind($handle);
            }
            $firstRow = fgetcsv($handle);

            if (!$firstRow) {
                throw new \Exception("Empty CSV file");
            }

            $normalizedHeader = array_map(fn($h) => strtolower(trim((string) $h)), $firstRow);
            
            // Expanded column name detection for flexible CSV formats
            $contentColumns = [
                'original_data', 'content', 'text', 'question', 'task', 'comment',
                'tweet text no handles', 'tweet_text_no_handles', 'tweet_text', 'tweet',
                'full_text', 'fulltext', 'message', 'description',
                'تغريدة', 'تعليق', 'سؤال', 'مهمة', 'نص', 'محتوى'
            ];
            
            $impressionColumns = ['impression', 'impressions', 'views', 'مشاهدات', 'انطباعات'];
            $sentimentColumns = ['sentiment', 'feeling', 'tone', 'مشاعر', 'شعور', 'نبرة'];
            $answerColumns = ['ai_suggestion', 'suggestion', 'answer', 'proposed_answer', 'الاجابة', 'الإجابة', 'الرد', 'المقترح'];
            
            $hasKnownHeaders = count(array_intersect($contentColumns, $normalizedHeader)) > 0;

            // Initialize domain detection service
            $domainDetector = new \App\Services\DomainDetectionService();

            $pickContentFromAssoc = function (array $assoc) use ($contentColumns): ?string {
                foreach ($contentColumns as $key) {
                    if (array_key_exists($key, $assoc) && trim((string) $assoc[$key]) !== '') {
                        return (string) $assoc[$key];
                    }
                }

                $exclude = ['task_id', 'id', 'expert_id', 'is_gold_standard', 'gold_answer', 'answer', 'submitted_at', 'task_type', 'confidence', 'confidence_level', 'impression', 'impressions', 'views'];
                foreach ($assoc as $key => $value) {
                    if (in_array($key, $exclude, true)) {
                        continue;
                    }
                    $value = trim((string) $value);
                    if ($value !== '') {
                        return $value;
                    }
                }

                return null;
            };

            $pickContentFromRow = function (array $row): ?string {
                foreach ($row as $value) {
                    $value = trim((string) $value);
                    if ($value === '') {
                        continue;
                    }
                    if (preg_match('/\p{L}/u', $value)) {
                        return $value;
                    }
                }

                foreach ($row as $value) {
                    $value = trim((string) $value);
                    if ($value !== '') {
                        return $value;
                    }
                }

                return null;
            };

            $count = 0;
            if ($hasKnownHeaders) {
                $headers = $normalizedHeader;
                while (($row = fgetcsv($handle)) !== false) {
                    if (count($row) !== count($headers)) {
                        continue;
                    }
                    $assoc = array_combine($headers, $row);
                    
                    $content = $pickContentFromAssoc($assoc);

                    if ($content === null) {
                        continue;
                    }

                    // Intelligent domain detection from content
                    $detectedDomain = $domainDetector->detectDomain($content);
                    
                    // Extract sentiment if column exists
                    $sentiment = null;
                    foreach ($sentimentColumns as $col) {
                        if (isset($assoc[$col]) && !empty($assoc[$col])) {
                            $sentiment = $assoc[$col];
                            break;
                        }
                    }

                    $suggestion = null;
                    foreach ($answerColumns as $col) {
                        if (isset($assoc[$col]) && !empty($assoc[$col])) {
                            $suggestion = $assoc[$col];
                            break;
                        }
                    }

                    // by Ahmed abdelhalim
                    \App\Models\AiTask::create([
                        'task_type' => $assoc['task_type'] ?? 'text_analysis',
                        'original_data' => $content,
                        'ai_suggestion' => $suggestion,
                        'client_id' => $user->id,
                        'status' => 'pending',
                        'consensus_status' => 'pending',
                        'required_responses' => 3,
                        'task_domain' => $detectedDomain,
                        'sentiment' => $sentiment,
                        'allowed_roles' => [],
                        'allow_all_roles' => true
                    ]);
                    $count++;
                }
            } else {
                $processRow = function (array $row) use (&$count, $pickContentFromRow, $user, $domainDetector) {
                    $content = $pickContentFromRow($row);
                    if ($content === null) {
                        return;
                    }
                    
                    // Intelligent domain detection
                    $detectedDomain = $domainDetector->detectDomain($content);
                    
                    \App\Models\AiTask::create([
                        'task_type' => 'text_analysis',
                        'original_data' => $content,
                        'client_id' => $user->id,
                        'status' => 'pending',
                        'consensus_status' => 'pending',
                        'required_responses' => 3,
                        'task_domain' => $detectedDomain,
                        'sentiment' => null,
                        'allowed_roles' => [],
                        'allow_all_roles' => $detectedDomain === null
                    ]);
                    $count++;
                };

                $processRow($firstRow);
                while (($row = fgetcsv($handle)) !== false) {
                    $processRow($row);
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
        return match ($log->event_type) {
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

    /**
     * Update a task's content
     */
    public function updateTask(\Illuminate\Http\Request $request, $id)
    {
        $task = \App\Models\AiTask::where('client_id', auth()->id())->findOrFail($id);
        
        if ($task->status !== 'pending') {
            return back()->with('error', app()->getLocale() == 'ar' ? 'لا يمكن تعديل مهمة قيد العمل أو مكتملة' : 'Cannot edit a task that is in progress or completed.');
        }

        $request->validate([
            'original_data' => 'required|string|max:5000'
        ]);

        $task->update([
            'original_data' => $request->original_data
        ]);

        return back()->with('success', app()->getLocale() == 'ar' ? 'تم تحديث المهمة بنجاح' : 'Task updated successfully.');
    }

    /**
     * Delete a task
     */
    public function deleteTask($id)
    {
        $task = \App\Models\AiTask::where('client_id', auth()->id())->findOrFail($id);

        if ($task->status !== 'pending') {
            return back()->with('error', app()->getLocale() == 'ar' ? 'لا يمكن حذف مهمة قيد العمل أو مكتملة' : 'Cannot delete a task that is in progress or completed.');
        }

        $task->delete();

        return back()->with('success', app()->getLocale() == 'ar' ? 'تم حذف المهمة بنجاح' : 'Task deleted successfully.');
    }

    /**
     * Duplicate a task
     */
    public function duplicateTask($id)
    {
        $task = \App\Models\AiTask::where('client_id', auth()->id())->findOrFail($id);

        $newTask = $task->replicate();
        $newTask->status = 'pending';
        $newTask->consensus_status = 'pending';
        $newTask->created_at = now();
        $newTask->updated_at = now();
        $newTask->save();

        return back()->with('success', app()->getLocale() == 'ar' ? 'تم نسخ المهمة بنجاح' : 'Task duplicated successfully.');
    }
    /**
     * Delete ALL tasks for the current client
     */
    public function deleteAllTasks()
    {
        $count = \App\Models\AiTask::where('client_id', auth()->id())->count();
        
        \App\Models\AiTask::where('client_id', auth()->id())->delete();

        return back()->with('success', app()->getLocale() == 'ar' 
            ? "تم حذف جميع المهام ($count) بنجاح." 
            : "All tasks ($count) deleted successfully.");
    }
}
