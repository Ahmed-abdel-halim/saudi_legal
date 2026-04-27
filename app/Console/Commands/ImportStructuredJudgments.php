<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\LegalJudgment;
use PhpOffice\PhpSpreadsheet\IOFactory;

class ImportStructuredJudgments extends Command
{
    protected $signature = 'import:structured-judgments';
    protected $description = 'Import judgments from Excel into the dedicated legal_judgments table';

    public function handle()
    {
        $filePath = base_path('judgments(2).xlsx');

        if (!file_exists($filePath)) {
            $this->error("File not found: {$filePath}");
            return;
        }

        $this->info("Loading Excel file...");

        try {
            $spreadsheet = IOFactory::load($filePath);
            $worksheet = $spreadsheet->getActiveSheet();
            $rows = $worksheet->toArray();
            
            array_shift($rows); // Skip header if exists
            
            $count = 0;
            $batch = [];

            foreach ($rows as $row) {
                if (empty($row[10])) continue;

                $courtName = $row[3] ?? 'المحكمة';
                $date = $row[4] ?? '';
                $title = $row[2] ?? '';
                
                preg_match('/(?:رقم\s*)([0-9٠-٩]+)/u', $title, $matches);
                $caseNumber = $matches[1] ?? 'غير محدد';

                $caseText = str_replace(['<br />', '<br>', '<br/>'], "\n", $row[10]);
                $caseText = strip_tags($caseText);

                // 1. Create the Judgment Record
                $judgment = LegalJudgment::create([
                    'case_number' => $caseNumber,
                    'court_name' => $courtName,
                    'judgment_date' => $date,
                    'case_text' => $caseText,
                    'law_system' => $row[1] ?? 'نظام غير محدد',
                    'source_file' => 'judgments(2).xlsx',
                    'metadata' => ['original_row' => $row]
                ]);

                // 2. Create the Work Task linked to this judgment
                \App\Models\LegalTask::create([
                    'source_type' => 'judgment',
                    'source_id' => $judgment->id,
                    'task_type' => 'verification',
                    'status' => 'pending',
                    'question' => $row[2] ?? 'مراجعة منطوق حكم',
                    'proposed_answer' => $row[9] ?? 'لا يوجد رد مقترح',
                    'case_text' => $caseText,
                    'case_reference' => "حكم رقم {$caseNumber} - {$courtName}",
                    'domain' => 'law',
                    'source_file' => 'judgments(2).xlsx'
                ]);

                $count++;
                if ($count % 50 == 0) {
                    $this->info("Imported {$count} records...");
                }
            }

            $this->info("Done! Total imported to legal_judgments: {$count}");
            
        } catch (\Exception $e) {
            $this->error("Error: " . $e->getMessage());
        }
    }
}
