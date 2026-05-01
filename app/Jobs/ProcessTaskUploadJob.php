<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use App\Models\AiTask;
use App\Models\LegalTask;
use App\Models\ClientQuestion;
use App\Models\LegalArticle;
use App\Services\DomainDetectionService;
use App\Services\LegalLinkingService;
use App\Services\LegalReferenceService;
use PhpOffice\PhpSpreadsheet\IOFactory;

class ProcessTaskUploadJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected string $filePath;
    protected int $userId;
    protected ?int $companyId;
    protected string $originalFileName;

    public $timeout = 3600; // 1 hour timeout for large files

    /**
     * Create a new job instance.
     */
    public function __construct(string $filePath, int $userId, ?int $companyId, string $originalFileName)
    {
        $this->filePath = $filePath;
        $this->userId = $userId;
        $this->companyId = $companyId;
        $this->originalFileName = $originalFileName;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        $fullPath = Storage::path($this->filePath);
        if (!file_exists($fullPath)) {
            Log::error("Job failed: File not found at {$fullPath}");
            return;
        }

        $extension = strtolower(pathinfo($this->originalFileName, PATHINFO_EXTENSION));

        try {
            $allRows = [];
            // Check if it's Excel
            if ($extension === 'xlsx' || $extension === 'xls') {
                $spreadsheet = IOFactory::load($fullPath);
                $allRows = $spreadsheet->getActiveSheet()->toArray();
            } 
            else {
                // Peek at the first few bytes to see if it's JSON/JSONL
                $handle = fopen($fullPath, 'r');
                $firstChars = fread($handle, 10);
                rewind($handle);
                
                $isJsonFormat = ($extension === 'jsonl' || $extension === 'json' || str_contains($firstChars, '{') || str_contains($firstChars, '['));

                if ($isJsonFormat) {
                    if ($extension === 'json') {
                        $content = file_get_contents($fullPath);
                        $jsonData = json_decode($content, true);
                        $allRows = is_array($jsonData) ? (isset($jsonData[0]) ? $jsonData : [$jsonData]) : [];
                    } else {
                        // Treat as JSONL
                        while (($line = fgets($handle)) !== false) {
                            $line = trim($line);
                            if (empty($line)) continue;
                            $jsonData = json_decode($line, true);
                            if ($jsonData) $allRows[] = $jsonData;
                        }
                    }
                    fclose($handle);
                } else {
                    fclose($handle);
                    // Standard CSV/Text processing
                    $content = file_get_contents($fullPath);
                // Encoding detection logic
                $targetEncoding = 'UTF-8';
                if (!mb_check_encoding($content, $targetEncoding)) {
                    $converted = false;
                    $encodingsToCheck = ['CP1256', 'WINDOWS-1256', 'ISO-8859-6'];
                    foreach ($encodingsToCheck as $enc) {
                        try {
                            $convertedContent = @mb_convert_encoding($content, $targetEncoding, $enc);
                            if ($convertedContent && mb_check_encoding($convertedContent, $targetEncoding)) {
                                $content = $convertedContent;
                                $converted = true;
                                break;
                            }
                        } catch (\Throwable $e) {}
                    }
                    if (!$converted) $content = mb_convert_encoding($content, 'UTF-8', 'ISO-8859-1');
                }

                    $delimiter = ",";
                    $firstLine = strtok($content, "\r\n");
                    if ($firstLine) {
                        $delims = [",", ";", "\t", "|"];
                        $counts = [];
                        foreach ($delims as $d) { $counts[$d] = substr_count($firstLine, $d); }
                        arsort($counts);
                        $delimiter = key($counts);
                    }

                    $handle = fopen('php://memory', 'r+');
                    fwrite($handle, $content);
                    rewind($handle);
                    $bom = fread($handle, 3);
                    if ($bom !== "\xEF\xBB\xBF") rewind($handle);

                    while (($row = fgetcsv($handle, 0, $delimiter)) !== false) {
                        $allRows[] = $row;
                    }
                    fclose($handle);
                }
            }

            if (empty($allRows)) return;

            $isAssoc = is_array($allRows[0]) && count(array_filter(array_keys($allRows[0]), 'is_string')) > 0;
            $headers = $isAssoc ? [] : array_map(fn($h) => strtolower(trim((string) $h)), $allRows[0]);
            $dataRows = $isAssoc ? $allRows : array_slice($allRows, 1);

            $contentColumns = ['original_data', 'content', 'text', 'question', 'task', 'comment', 'full_text', 'message', 'description', 'تغريدة', 'تعليق', 'سؤال', 'مهمة', 'نص', 'محتوى'];
            $sentimentColumns = ['sentiment', 'feeling', 'tone', 'مشاعر', 'شعور', 'نبرة'];
            $answerColumns = ['ai_suggestion', 'suggestion', 'answer', 'proposed_answer', 'الاجابة', 'الإجابة', 'الرد', 'المقترح'];
            
            $hasKnownHeaders = $isAssoc || (count(array_intersect($contentColumns, $headers)) > 0);
            $domainDetector = new DomainDetectionService();
            $referenceService = new LegalReferenceService();
            $linkingService = new LegalLinkingService();

            $count = 0;
            foreach ($dataRows as $index => $row) {
                if (empty($row) || (is_array($row) && count(array_filter($row)) == 0)) continue;

                try {
                    if ($isAssoc) {
                        $assoc = $row;
                    } else {
                        $assoc = $hasKnownHeaders ? array_combine(array_slice($headers, 0, count($row)), array_slice($row, 0, count($headers))) : [];
                    }
                    
                    // Pick content
                    $content = null;
                    if ($isAssoc) {
                        $content = $assoc['question'] ?? ($assoc['content'] ?? ($assoc['text'] ?? null));
                    } elseif ($hasKnownHeaders) {
                        foreach ($contentColumns as $col) { if (isset($assoc[$col]) && trim($assoc[$col]) !== '') { $content = $assoc[$col]; break; } }
                    } else {
                        foreach ($row as $val) { if (trim($val) !== '' && preg_match('/\p{L}/u', $val)) { $content = $val; break; } }
                    }

                    if (!$content) continue;

                    $targetCompanyId = $assoc['company_id'] ?? ($this->companyId ?? 1);
                    if (!DB::table('companies')->where('company_id', $targetCompanyId)->exists()) $targetCompanyId = 1;

                    $detectedDomain = $domainDetector->detectDomain($content);
                    
                    $sentiment = null;
                    foreach ($sentimentColumns as $col) { if (isset($assoc[$col]) && !empty($assoc[$col])) { $sentiment = $assoc[$col]; break; } }

                    $suggestion = null;
                    foreach ($answerColumns as $col) { if (isset($assoc[$col]) && !empty($assoc[$col])) { $suggestion = $assoc[$col]; break; } }

                    $clientQuestion = ClientQuestion::create([
                        'company_id' => $targetCompanyId, 'user_id' => $this->userId, 'question' => $content,
                        'context' => $assoc['context'] ?? ($assoc['instruction'] ?? null), 'status' => 'pending', 'domain' => $detectedDomain
                    ]);

                    $aiTask = AiTask::create([
                        'task_type' => $assoc['task_type'] ?? 'text_analysis', 'original_data' => $content, 'ai_suggestion' => $suggestion,
                        'client_id' => $this->userId, 'status' => 'pending', 'consensus_status' => 'pending', 'required_responses' => 3,
                        'task_domain' => $detectedDomain, 'sentiment' => $sentiment, 'allow_all_roles' => true
                    ]);

                    if (in_array($detectedDomain, ['law', 'legal', 'محاماة', 'قانون'])) {
                        $question = $assoc['question'] ?? $content;
                        $suggestion = $assoc['answer'] ?? $suggestion;
                        $instruction = $assoc['instruction'] ?? $assoc['context'] ?? '';
                        
                        $caseNumber = $assoc['case_number'] ?? null;
                        $caseYear = $assoc['year'] ?? '1445';

                        // Support for the specific JSONL metadata structure
                        if ($isAssoc && isset($assoc['metadata'])) {
                            $caseNumber = $assoc['metadata']['case_number'] ?? $caseNumber;
                            $caseYear = $assoc['metadata']['year'] ?? $caseYear;
                        }
                        if (!$caseNumber && !empty($suggestion)) {
                            if (preg_match('/(?:حكم|قضية|صك|قرار)\s*(?:رقم)?\s*([\d\x{0660}-\x{0669}]+)/u', $suggestion, $matches)) $caseNumber = $matches[1];
                        }

                        $originalCaseText = null;
                        if ($caseNumber) {
                            $cleanNumber = str_replace(['٠', '١', '٢', '٣', '٤', '٥', '٦', '٧', '٨', '٩'], ['0', '1', '2', '3', '4', '5', '6', '7', '8', '9'], $caseNumber);
                            $cleanNumber = preg_replace('/[^0-9]/', '', $cleanNumber);

                            $judgment = \App\Models\LegalJudgment::where('case_number', $cleanNumber)->orWhere('case_number', 'LIKE', "%{$cleanNumber}%")->first();
                            if (!$judgment) $judgment = \App\Models\LegalJudgment::where('case_text', 'LIKE', "%{$cleanNumber}%")->first();
                            
                            if ($judgment) $originalCaseText = $judgment->case_text;
                            else {
                                $originalTask = LegalTask::where('case_reference', 'LIKE', "%{$cleanNumber}%")->whereNotNull('case_text')->latest()->first();
                                if ($originalTask) $originalCaseText = $originalTask->case_text;
                            }
                        }

                        $articleNum = 'غير محدد'; $sysName = 'نظام غير محدد'; $articleText = 'يرجى البحث يدوياً...';
                        try {
                            $textToSearch = $question . " " . ($originalCaseText ?? $suggestion) . " " . $instruction;
                            $bestArticles = $referenceService->getMentionedArticles($textToSearch);
                            if ($bestArticles->isNotEmpty()) {
                                $firstArticle = $bestArticles->first();
                                $articleNum = preg_replace('/[^0-9]/', '', $firstArticle->article_title) ?: '1';
                                $sysName = $firstArticle->legislation_title;
                                $articleText = strip_tags($firstArticle->content);
                            } else {
                                $match = $linkingService->findBestMatch($textToSearch);
                                if ($match['confidence'] > 40) {
                                    $articleNum = $match['article_number']; $sysName = $match['system_name']; $articleText = $match['article_text'];
                                }
                            }
                        } catch (\Exception $e) {}

                        LegalTask::create([
                            'task_id' => $aiTask->id, 'source_type' => 'client_question', 'source_id' => $clientQuestion->id,
                            'task_type' => 'verification', 'status' => 'pending', 'question' => $question,
                            'proposed_answer' => $suggestion ?? 'لا يوجد رد مقترح حالياً.', 'case_text' => $originalCaseText,
                            'case_reference' => $caseNumber ? "حكم رقم {$caseNumber} لعام {$caseYear}هـ" : null,
                            'law_article_number' => $articleNum, 'law_system_name' => $sysName, 'law_article_text' => $articleText,
                            'domain' => 'law', 'source_file' => $this->originalFileName
                        ]);
                    }
                    $count++;
                } catch (\Exception $e) {
                    Log::error("Row processing error at index {$index}: " . $e->getMessage());
                }
            }

            Log::info("Job completed: Processed {$count} tasks from {$this->originalFileName}");
            Storage::delete($this->filePath);

        } catch (\Exception $e) {
            Log::error("Job failure: " . $e->getMessage());
        }
    }
}
