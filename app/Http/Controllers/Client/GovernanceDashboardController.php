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
        // إيقاف حدود الوقت والذاكرة لتجنب مشكلة الـ Timeout مع الملفات الضخمة
        set_time_limit(0);
        ini_set('memory_limit', '-1');

        $request->validate([
            'csv_file' => 'required|file|max:153600' // 150MB
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

                    // Create the AI Task for governance tracking
                    $aiTask = \App\Models\AiTask::create([
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

                    // NEW: If the domain is legal (or law), sync to LegalTask table for the Expert Workbench
                    if (in_array($detectedDomain, ['law', 'legal', 'محاماة', 'قانون'])) {
                        
                        $caseNumber = $assoc['case_number'] ?? null;
                        
                        if ($caseNumber) {
                            $arabicNums = ['٠', '١', '٢', '٣', '٤', '٥', '٦', '٧', '٨', '٩'];
                            $englishNums = ['0', '1', '2', '3', '4', '5', '6', '7', '8', '9'];
                            $caseNumber = str_replace($arabicNums, $englishNums, $caseNumber);
                            // Normalize case number by removing any non-numeric characters (dots, dashes, etc)
                            $caseNumber = preg_replace('/[^0-9]/', '', $caseNumber);
                        }

                        $year = $assoc['year'] ?? '';
                        $caseRef = $caseNumber ? "حكم رقم {$caseNumber}" . ($year ? " لعام {$year}هـ" : "") : null;

                        $originalCaseText = null;
                        if ($caseNumber) {
                            // Search using the clean numeric part to ensure match
                            $originalTask = \App\Models\LegalTask::where('case_reference', 'REGEXP', '[[:<:]]' . $caseNumber . '[[:>:]]')
                                ->where('status', 'completed')
                                ->latest()
                                ->first();
                            
                            if (!$originalTask) {
                                // Fallback: try a simpler like search if REGEXP fails on some DB engines
                                $originalTask = \App\Models\LegalTask::where('case_reference', 'LIKE', "%{$caseNumber}%")
                                    ->where('status', 'completed')
                                    ->latest()
                                    ->first();
                            }

                            if ($originalTask) {
                                $originalCaseText = $originalTask->case_text;
                            }
                        }

                        $question = $assoc['question'] ?? $content;
                        $instruction = $assoc['instruction'] ?? '';
                        
                        $articleNum = 'غير محدد';
                        $sysName = 'نظام غير محدد';
                        $articleText = 'يرجى البحث يدوياً عن نص المادة...';
                        
                        // استخراج "المادة 29 من نظام الإثبات"
                        if (preg_match('/المادة\s+([0-9٠-٩]+)\s+من\s+([^.)،]+)/u', $instruction, $matches)) {
                            $articleNum = $matches[1];
                            $sysName = trim($matches[2]);
                            
                            $arabicNums = ['٠', '١', '٢', '٣', '٤', '٥', '٦', '٧', '٨', '٩'];
                            $englishNums = ['0', '1', '2', '3', '4', '5', '6', '7', '8', '9'];
                            $articleNum = str_replace($arabicNums, $englishNums, $articleNum);
                            
                            $ordinal = $this->numberToArabicOrdinal((int)$articleNum);
                            
                            // Search with stricter system name matching
                            $exactArticle = \App\Models\LegalArticle::where('legislation_title', 'LIKE', "%{$sysName}%")
                                            ->where('article_title', 'LIKE', "%المادة {$ordinal}%")
                                            ->orderByRaw("LENGTH(legislation_title) ASC") // Prefer shorter titles (closer matches)
                                            ->first();
                            
                            if (!$exactArticle) {
                                // Fallback to digit search if ordinal words fail
                                $exactArticle = \App\Models\LegalArticle::where('legislation_title', 'LIKE', "%{$sysName}%")
                                            ->where('article_title', 'LIKE', "%المادة {$articleNum}%")
                                            ->orderByRaw("LENGTH(legislation_title) ASC")
                                            ->first();
                            }
                            
                            if ($exactArticle) {
                                $articleText = strip_tags($exactArticle->content);
                                $sysName = $exactArticle->legislation_title; // Use the official title from DB
                            }
                        }

                        \App\Models\LegalTask::create([
                            'task_type' => 'verification',
                            'status' => 'pending',
                            'question' => $question,
                            'proposed_answer' => $suggestion ?? 'لا يوجد رد مقترح حالياً.',
                            'case_text' => $originalCaseText ?? $suggestion,
                            'case_reference' => $caseRef,
                            'law_article_number' => $articleNum,
                            'law_system_name' => $sysName,
                            'law_article_text' => $articleText,
                            'domain' => 'law',
                            'source_file' => $file->getClientOriginalName(),
                            'expert_comment' => "مهمة مستوردة من لوحة الحوكمة - العميل #{$user->id}"
                        ]);
                    }
                    
                    $count++;
                    
                    // Limit to 5000 records for now as per user request
                    if ($count >= 5000) break;
                }
            } else {
                $processRow = function (array $row) use (&$count, $pickContentFromRow, $user, $domainDetector, $file) {
                    if ($count >= 5000) return false;
                    $content = $pickContentFromRow($row);
                    if ($content === null) {
                        return;
                    }
                    
                    // Intelligent domain detection
                    $detectedDomain = $domainDetector->detectDomain($content);
                    
                    $aiTask = \App\Models\AiTask::create([
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

                    // NEW: Sync to LegalTask table for Expert Workbench
                    if (in_array($detectedDomain, ['law', 'legal', 'محاماة', 'قانون'])) {
                        
                        // محاولة استخراج تفاصيل القضية من الملفات المشابهة لـ Radiif_Cleaned_Sample
                        $question = count($row) > 1 ? $row[1] : $content;
                        $csvAnswer = count($row) > 2 ? $row[2] : 'لا يوجد رد مقترح حالياً.';
                        
                        $caseNumber = (count($row) > 3) ? trim($row[3]) : null;
                        // تحويل الأرقام العربية إلى إنجليزية لضمان تطابق البحث في قاعدة البيانات
                        if ($caseNumber) {
                            $arabicNums = ['٠', '١', '٢', '٣', '٤', '٥', '٦', '٧', '٨', '٩'];
                            $englishNums = ['0', '1', '2', '3', '4', '5', '6', '7', '8', '9'];
                            $caseNumber = str_replace($arabicNums, $englishNums, $caseNumber);
                        }

                        $caseRef = ($caseNumber && count($row) > 4) ? "حكم رقم {$caseNumber} لعام {$row[4]}هـ" : null;

                        // سحب نص الحكم الأصلي من الـ 5000 قضية المرفوعة مسبقاً
                        $originalCaseText = null;
                        if ($caseNumber) {
                            $originalTask = \App\Models\LegalTask::where('case_reference', 'LIKE', "%{$caseNumber}%")
                                ->where('status', 'completed')
                                ->first();
                            if ($originalTask) {
                                $originalCaseText = $originalTask->case_text;
                            }
                        }

                        \App\Models\LegalTask::create([
                            'task_type' => 'verification',
                            'status' => 'pending',
                            'question' => $question,
                            'proposed_answer' => $csvAnswer,
                            'case_text' => $originalCaseText ?? $csvAnswer, // لو ملقاش الحكم الأصلي يحط الإجابة كاحتياطي
                            'case_reference' => $caseRef,
                            'domain' => 'law',
                            'source_file' => $file->getClientOriginalName(),
                            'expert_comment' => "مهمة مستوردة من لوحة الحوكمة - العميل #{$user->id}"
                        ]);
                    }

                    $count++;
                    if ($count >= 5000) return false;
                };

                $result = $processRow($firstRow);
                if ($result !== false) {
                    while (($row = fgetcsv($handle)) !== false) {
                        if ($processRow($row) === false) {
                            break;
                        }
                    }
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
    /**
     * Convert number to Arabic ordinal words (e.g., 29 -> التاسعة والعشرون)
     */
    private function numberToArabicOrdinal($number)
    {
        $ordinals = [
            1 => 'الأولى', 2 => 'الثانية', 3 => 'الثالثة', 4 => 'الرابعة', 5 => 'الخامسة',
            6 => 'السادسة', 7 => 'السابعة', 8 => 'الثامنة', 9 => 'التاسعة', 10 => 'العاشرة',
            11 => 'الحادية عشرة', 12 => 'الثانية عشرة', 13 => 'الثالثة عشرة', 14 => 'الرابعة عشرة', 15 => 'الخامسة عشرة',
            16 => 'السادسة عشرة', 17 => 'السابعة عشرة', 18 => 'الثامنة عشرة', 19 => 'التاسعة عشرة', 20 => 'العشرون',
            30 => 'الثلاثون', 40 => 'الأربعون', 50 => 'الخمسون', 60 => 'الستون', 70 => 'السبعون', 80 => 'الثمانون', 90 => 'التسعون', 100 => 'المائة'
        ];

        if (isset($ordinals[$number])) {
            return $ordinals[$number];
        }

        if ($number > 20 && $number < 100) {
            $ones = $number % 10;
            $tens = floor($number / 10) * 10;
            
            $onesMap = [
                1 => 'الحادية', 2 => 'الثانية', 3 => 'الثالثة', 4 => 'الرابعة', 5 => 'الخامسة',
                6 => 'السادسة', 7 => 'السابعة', 8 => 'الثامنة', 9 => 'التاسعة'
            ];

            if (isset($onesMap[$ones]) && isset($ordinals[$tens])) {
                return $onesMap[$ones] . ' و' . $ordinals[$tens];
            }
        }

        return (string)$number;
    }
}
