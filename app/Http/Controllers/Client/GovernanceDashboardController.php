<?php

namespace App\Http\Controllers\Client;

use App\Http\Controllers\Controller;
use App\Models\LegalTask;
use App\Models\AiTask;
use App\Models\ClientQuestion;
use App\Models\LegalArticle;
use App\Models\TaskConsensus;
use App\Models\GovernanceLog;
use App\Models\TaskAssignment;
use App\Models\User;
use App\Services\DomainDetectionService;
use App\Services\LegalLinkingService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use PhpOffice\PhpSpreadsheet\IOFactory;

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
        $tasks = AiTask::where('client_id', auth()->id())
            ->latest()
            ->paginate(10);

        return view('client.governance.dashboard', compact('metrics', 'fraudLogs', 'conflicts', 'liveTracking', 'tasks'));
    }

    /**
     * Upload and create tasks from CSV or Excel
     */
    public function uploadTasks(Request $request)
    {
        \Log::info('Task upload request received', ['user_id' => auth()->id()]);

        try {
            // Simplified validation: just check it's a file and within size
            // The MIME check often fails on servers for .jsonl files
            $request->validate([
                'csv_file' => 'required|file|max:204800' // 200MB
            ]);

            if (!$request->hasFile('csv_file')) {
                \Log::warning('Upload attempt without csv_file field');
                return response()->json(['success' => false, 'message' => 'No file field detected.'], 400);
            }

            $user = auth()->user();
            $file = $request->file('csv_file');
            $originalName = $file->getClientOriginalName();
            
            \Log::info('File details', [
                'name' => $originalName,
                'size' => $file->getSize(),
                'mime' => $file->getMimeType()
            ]);

            // حفظ الملف بشكل مؤقت للمعالجة في الخلفية
            $path = $file->storeAs('temp_uploads', time() . '_' . $originalName);

            // إرسال المهمة للمعالجة في الخلفية
            \App\Jobs\ProcessTaskUploadJob::dispatch(
                $path,
                $user->id,
                $user->company_id,
                $originalName
            );

            \Log::info('Job dispatched for file: ' . $originalName);

            if ($request->ajax()) {
                return response()->json(['success' => true, 'message' => "بدأت عملية معالجة الملف في الخلفية."]);
            }

            return redirect()->route('client.governance.dashboard')
                ->with('success', "بدأت عملية معالجة الملف في الخلفية. ستظهر المهام هنا تدريجياً خلال دقائق قليلة.");

        } catch (\Illuminate\Validation\ValidationException $e) {
            \Log::warning('Validation failed for upload', $e->errors());
            return response()->json(['success' => false, 'message' => 'خطأ في التحقق: ' . implode(', ', collect($e->errors())->flatten()->toArray())], 422);
        } catch (\Exception $e) {
            \Log::error("Upload Error: " . $e->getMessage(), ['trace' => $e->getTraceAsString()]);
            if ($request->ajax()) {
                return response()->json(['success' => false, 'message' => 'خطأ في السيرفر: ' . $e->getMessage()], 500);
            }
            return back()->with('error', 'خطأ في السيرفر: ' . $e->getMessage());
        }
    }

    private function pickContentFromAssoc(array $assoc, array $contentColumns): ?string {
        foreach ($contentColumns as $key) {
            if (array_key_exists($key, $assoc) && trim((string) $assoc[$key]) !== '') {
                return (string) $assoc[$key];
            }
        }
        $exclude = ['task_id', 'id', 'expert_id', 'is_gold_standard', 'gold_answer', 'answer', 'submitted_at', 'task_type', 'confidence', 'confidence_level', 'impression', 'impressions', 'views'];
        foreach ($assoc as $key => $value) {
            if (in_array($key, $exclude, true)) continue;
            $value = trim((string) $value);
            if ($value !== '') return $value;
        }
        return null;
    }

    private function pickContentFromRow(array $row): ?string {
        foreach ($row as $value) {
            $value = trim((string) $value);
            if ($value !== '' && preg_match('/\p{L}/u', $value)) return $value;
        }
        foreach ($row as $value) {
            $value = trim((string) $value);
            if ($value !== '') return $value;
        }
        return null;
    }

    private function getLiveTrackingStats()
    {
        $experts = User::where('role', 'expert')->get();
        return [
            'total_experts' => $experts->count(),
            'active_experts' => TaskAssignment::active()->distinct('expert_id')->count('expert_id'),
            'avg_trust_score' => round($experts->avg('trust_score') ?? 100, 1),
            'banned_experts' => $experts->where('is_banned', true)->count(),
            'gold_pass_rate' => $experts->sum('gold_tasks_completed') > 0
                ? round(($experts->sum('gold_tasks_completed') / ($experts->sum('gold_tasks_completed') + $experts->sum('gold_tasks_failed'))) * 100, 1)
                : 100,
        ];
    }

    private function getAccuracyMetrics(): array
    {
        $total = TaskConsensus::count();
        if ($total === 0) return ['perfect_consensus_pct' => 0, 'majority_vote_pct' => 0, 'conflict_pct' => 0, 'total_tasks' => 0];
        $stats = TaskConsensus::select('consensus_type', DB::raw('count(*) as count'))->groupBy('consensus_type')->pluck('count', 'consensus_type')->toArray();
        return [
            'perfect_consensus_pct' => round(($stats['perfect_match'] ?? 0) / $total * 100, 2),
            'majority_vote_pct' => round(($stats['majority_vote'] ?? 0) / $total * 100, 2),
            'conflict_pct' => round(($stats['conflict'] ?? 0) / $total * 100, 2),
            'total_tasks' => $total
        ];
    }

    private function getFraudDetectionLogs()
    {
        return GovernanceLog::with('expert', 'task')->whereIn('event_type', ['gold_task_failed', 'trust_score_warning', 'expert_banned'])->latest()->limit(50)->get()->map(function ($log) {
            return [
                'timestamp' => $log->created_at,
                'expert_id' => $log->expert_id,
                'expert_name' => $log->expert->full_name ?? 'Unknown',
                'event' => $log->event_type,
                'description' => $this->formatLogDescription($log),
                'trust_score_change' => $log->trust_score_before && $log->trust_score_after ? ($log->trust_score_after - $log->trust_score_before) : null
            ];
        });
    }

    private function getRecentConflicts()
    {
        return TaskConsensus::with('task')->where('consensus_type', 'conflict')->latest()->limit(20)->get()->map(function ($consensus) {
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

    private function formatLogDescription(GovernanceLog $log): string
    {
        return match ($log->event_type) {
            'gold_task_failed' => "Expert #{$log->expert_id} failed Gold Question #{$log->task_id}",
            'trust_score_warning' => "Expert #{$log->expert_id} trust score warning (Score: {$log->trust_score_after}%)",
            'expert_banned' => "Expert #{$log->expert_id} auto-banned (Trust score: {$log->trust_score_after}%)",
            default => $log->event_type
        };
    }

    public function analyzeCsv(Request $request, \App\Services\CsvAnalysisService $analysisService)
    {
        $request->validate(['csv_file' => 'required|file|mimes:csv,txt|max:51200']);
        try {
            $results = $analysisService->analyze($request->file('csv_file'));
            return redirect()->route('client.governance.dashboard')->with('analysis_results', $results)->with('success', 'File analyzed successfully');
        } catch (\Exception $e) {
            return redirect()->route('client.governance.dashboard')->with('error', $e->getMessage());
        }
    }

    public function updateTask(Request $request, $id)
    {
        $task = AiTask::where('client_id', auth()->id())->findOrFail($id);
        if ($task->status !== 'pending') return back()->with('error', 'Cannot edit a task that is in progress or completed.');
        $request->validate(['original_data' => 'required|string|max:5000']);
        $task->update(['original_data' => $request->original_data]);
        return back()->with('success', 'Task updated successfully.');
    }

    public function deleteTask($id)
    {
        $task = AiTask::where('client_id', auth()->id())->findOrFail($id);
        if ($task->status !== 'pending') return back()->with('error', 'Cannot delete a task that is in progress or completed.');
        $task->delete();
        return back()->with('success', 'Task deleted successfully.');
    }

    public function duplicateTask($id)
    {
        $task = AiTask::where('client_id', auth()->id())->findOrFail($id);
        $newTask = $task->replicate();
        $newTask->status = 'pending';
        $newTask->consensus_status = 'pending';
        $newTask->created_at = now();
        $newTask->updated_at = now();
        $newTask->save();
        return back()->with('success', 'Task duplicated successfully.');
    }

    public function deleteAllTasks()
    {
        $count = AiTask::where('client_id', auth()->id())->count();
        AiTask::where('client_id', auth()->id())->delete();
        return back()->with('success', "All tasks ($count) deleted successfully.");
    }

    private function numberToArabicOrdinal($number)
    {
        $ordinals = [
            1 => 'الأولى', 2 => 'الثانية', 3 => 'الثالثة', 4 => 'الرابعة', 5 => 'الخامسة',
            6 => 'السادسة', 7 => 'السابعة', 8 => 'الثامنة', 9 => 'التاسعة', 10 => 'العاشرة',
            11 => 'الحادية عشرة', 12 => 'الثانية عشرة', 13 => 'الثالثة عشرة', 14 => 'الرابعة عشرة', 15 => 'الخامسة عشرة',
            16 => 'السادسة عشرة', 17 => 'السابعة عشرة', 18 => 'الثامنة عشرة', 19 => 'التاسعة عشرة', 20 => 'العشرون',
            30 => 'الثلاثون', 40 => 'الأربعون', 50 => 'الخمسون', 60 => 'الستون', 70 => 'السبعون', 80 => 'الثمانون', 90 => 'التسعون', 100 => 'المائة',
            116 => 'السادسة عشرة بعد المائة', 164 => 'الرابعة والستون بعد المائة'
        ];
        if (isset($ordinals[$number])) return $ordinals[$number];
        if ($number > 20 && $number < 100) {
            $ones = $number % 10; $tens = floor($number / 10) * 10;
            $onesMap = [1 => 'الحادية', 2 => 'الثانية', 3 => 'الثالثة', 4 => 'الرابعة', 5 => 'الخامسة', 6 => 'السادسة', 7 => 'السابعة', 8 => 'الثامنة', 9 => 'التاسعة'];
            if (isset($onesMap[$ones]) && isset($ordinals[$tens])) return $onesMap[$ones] . ' و' . $ordinals[$tens];
        }
        return (string)$number;
    }
}
