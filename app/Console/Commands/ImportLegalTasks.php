<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\LegalTask;
use Illuminate\Support\Facades\DB;

class ImportLegalTasks extends Command
{
    protected $signature = 'import:legal-tasks {file}';
    protected $description = 'Import legal tasks from a JSONL file';

    public function handle()
    {
        $filePath = $this->argument('file');

        if (!file_exists($filePath)) {
            $this->error("File not found: {$filePath}");
            return;
        }

        $this->info("Starting import from {$filePath}...");

        $handle = fopen($filePath, 'r');
        $count = 0;
        $batch = [];
        $batchSize = 500; // زيادة حجم الـ Batch لسرعة أكبر
        
        $linkingService = new \App\Services\LegalLinkingService();

        while (($line = fgets($handle)) !== false) {
            $data = json_decode($line, true);
            if (!$data) continue;

            $messages = $data['messages'] ?? [];
            $metadata = $data['case_metadata'] ?? [];

            $question = '';
            $answer = '';
            foreach ($messages as $msg) {
                if ($msg['role'] === 'user') $question = $msg['content'];
                if ($msg['role'] === 'assistant') $answer = $msg['content'];
            }

            // الربط اليدوي قبل الإدخال المجمع لضمان سرعة الأداء مع الحفاظ على الربط
            $articleNum = 'غير محدد';
            $sysName = 'نظام سعودي';
            $articleText = 'يرجى مراجعة نص المادة يدوياً.';
            
            try {
                $match = $linkingService->findBestMatch($question . ' ' . $answer);
                if ($match['confidence'] > 50) {
                    $articleNum = $match['article_number'];
                    $sysName = $match['system_name'];
                    $articleText = $match['article_text'];
                }
            } catch (\Exception $e) {}

            $batch[] = [
                'task_type' => 'consultation',
                'status' => 'completed',
                'question' => $question,
                'correct_answer' => $answer,
                'case_reference' => isset($metadata['case_number']) ? "حكم رقم {$metadata['case_number']} لعام {$metadata['year']}هـ" : null,
                'case_text' => $answer,
                'law_article_number' => $articleNum,
                'law_system_name' => $sysName,
                'law_article_text' => $articleText,
                'domain' => 'law',
                'source_file' => basename($filePath),
                'created_at' => now(),
                'updated_at' => now()
            ];

            $count++;

            if (count($batch) >= $batchSize) {
                LegalTask::insert($batch);
                $batch = [];
                $this->info("Imported {$count} records...");
            }
        }

        if (!empty($batch)) {
            LegalTask::insert($batch);
        }

        fclose($handle);
        $this->info("Import completed! Total records: {$count}");
    }
}
