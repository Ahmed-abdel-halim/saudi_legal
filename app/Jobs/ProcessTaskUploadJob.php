<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Storage;
use App\Models\AiTask;
use App\Services\DomainDetectionService;

class ProcessTaskUploadJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected string $filePath;
    protected int $clientId;

    /**
     * Create a new job instance.
     *
     * @param string $filePath The storage path to the CSV file
     * @param int $clientId The ID of the user uploading the tasks
     */
    public function __construct(string $filePath, int $clientId)
    {
        $this->filePath = $filePath;
        $this->clientId = $clientId;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        if (!Storage::exists($this->filePath)) {
            return;
        }

        $fullPath = Storage::path($this->filePath);
        $content = file_get_contents($fullPath);

        // Try to detect and convert encoding
        $targetEncoding = 'UTF-8';
        if (!mb_check_encoding($content, $targetEncoding)) {
            $converted = false;

            if (function_exists('iconv')) {
                try {
                    $encodingsToCheck = ['CP1256', 'WINDOWS-1256'];
                    foreach ($encodingsToCheck as $enc) {
                        $convertedContent = @iconv($enc, 'UTF-8//IGNORE', $content);
                        if ($convertedContent && mb_check_encoding($convertedContent, $targetEncoding)) {
                            $content = $convertedContent;
                            $converted = true;
                            break;
                        }
                    }
                }
                catch (\Throwable $e) {
                }
            }

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
                    }
                    catch (\Throwable $e) {
                    }
                }
            }

            if (!$converted) {
                $content = mb_convert_encoding($content, 'UTF-8', 'ISO-8859-1');
            }
        }

        // Create a temporary file stream in memory
        $handle = fopen('php://memory', 'r+');
        fwrite($handle, $content);
        rewind($handle);

        $bom = fread($handle, 3);
        if ($bom !== "\xEF\xBB\xBF") {
            rewind($handle);
        }
        $firstRow = fgetcsv($handle);

        if (!$firstRow) {
            fclose($handle);
            Storage::delete($this->filePath);
            return;
        }

        $normalizedHeader = array_map(fn($h) => strtolower(trim((string)$h)), $firstRow);

        $contentColumns = [
            'original_data', 'content', 'text', 'question', 'task', 'comment',
            'tweet text no handles', 'tweet_text_no_handles', 'tweet_text', 'tweet',
            'full_text', 'fulltext', 'message', 'description',
            'تغريدة', 'تعليق', 'سؤال', 'مهمة', 'نص', 'محتوى'
        ];

        $sentimentColumns = ['sentiment', 'feeling', 'tone', 'مشاعر', 'شعور', 'نبرة'];
        // by Ahmed abdelhalim
        $answerColumns = ['ai_suggestion', 'suggestion', 'answer', 'proposed_answer', 'الاجابة', 'الإجابة', 'الرد', 'المقترح'];
        $hasKnownHeaders = count(array_intersect($contentColumns, $normalizedHeader)) > 0;

        $domainDetector = new DomainDetectionService();

        $pickContentFromAssoc = function (array $assoc) use ($contentColumns): ?string {
            foreach ($contentColumns as $key) {
                if (array_key_exists($key, $assoc) && trim((string)$assoc[$key]) !== '') {
                    return (string)$assoc[$key];
                }
            }

            $exclude = ['task_id', 'id', 'expert_id', 'is_gold_standard', 'gold_answer', 'answer', 'submitted_at', 'task_type', 'confidence', 'confidence_level', 'impression', 'impressions', 'views'];
            foreach ($assoc as $key => $value) {
                if (in_array($key, $exclude, true)) {
                    continue;
                }
                $value = trim((string)$value);
                if ($value !== '') {
                    return $value;
                }
            }

            return null;
        };

        $pickContentFromRow = function (array $row): ?string {
            foreach ($row as $value) {
                $value = trim((string)$value);
                if ($value === '') {
                    continue;
                }
                if (preg_match('/\p{L}/u', $value)) {
                    return $value;
                }
            }

            foreach ($row as $value) {
                $value = trim((string)$value);
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

                $detectedDomain = $domainDetector->detectDomain($content);

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
                AiTask::create([
                    'task_type' => $assoc['task_type'] ?? 'text_analysis',
                    'original_data' => $content,
                    'ai_suggestion' => $suggestion,
                    'client_id' => $this->clientId,
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
        }
        else {
            $processRow = function (array $row) use (&$count, $pickContentFromRow, $domainDetector) {
                $content = $pickContentFromRow($row);
                if ($content === null) {
                    return;
                }

                $detectedDomain = $domainDetector->detectDomain($content);

                AiTask::create([
                    'task_type' => 'text_analysis',
                    'original_data' => $content,
                    'client_id' => $this->clientId,
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

        // Delete the file after successful processing
        Storage::delete($this->filePath);
    }
}
