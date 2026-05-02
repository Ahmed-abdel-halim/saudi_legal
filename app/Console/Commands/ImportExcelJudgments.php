<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\LegalTask;
use PhpOffice\PhpSpreadsheet\IOFactory;

class ImportExcelJudgments extends Command
{
    protected $signature = 'import:excel-judgments';
    protected $description = 'Import full judgments from judgments(2).xlsx into legal_tasks table';

    public function handle()
    {
        $filePath = base_path('judgments(2).xlsx');

        if (!file_exists($filePath)) {
            $this->error("File not found: {$filePath}");
            return;
        }

        $this->info("Loading Excel file (this might take a minute)...");

        try {
            $spreadsheet = IOFactory::load($filePath);
            $worksheet = $spreadsheet->getActiveSheet();
            $rows = $worksheet->toArray();

            $total = count($rows);
            $this->info("Found {$total} rows. Starting import...");

            $batch = [];
            $batchSize = 50;
            $count = 0;

            foreach ($rows as $index => $row) {
                // Skip empty rows or rows without case text
                if (empty($row[10])) {
                    continue;
                }

                $courtName = $row[3] ?? 'المحكمة';
                $date = $row[4] ?? '';

                $title = $row[2] ?? '';
                preg_match('/(?:رقم\s*)([0-9٠-٩]+)/u', $title, $matches);
                $caseNumber = $matches[1] ?? 'غير محدد';

                // Convert Arabic numerals to English for consistent storage
                $arabicNums = ['٠', '١', '٢', '٣', '٤', '٥', '٦', '٧', '٨', '٩'];
                $englishNums = ['0', '1', '2', '3', '4', '5', '6', '7', '8', '9'];
                $caseNumber = str_replace($arabicNums, $englishNums, $caseNumber);

                // Convert <br /> to actual newlines for better reading in the Workbench/Chatbot
                $caseText = str_replace(['<br />', '<br>', '<br/>'], "\n", $row[10]);
                $caseText = strip_tags($caseText); // Remove any remaining HTML

                $caseRef = "حكم رقم {$caseNumber} صادر من {$courtName} بتاريخ {$date}";
                $caseRef = mb_substr($caseRef, 0, 95); // Ensure it fits in 100 chars

                $batch[] = [
                    'task_type' => 'consultation', // Must match ENUM in DB
                    'status' => 'completed',
                    'question' => "يرجى مراجعة وقائع وحكم هذه القضية ({$caseNumber}).",
                    'correct_answer' => "هذا الحكم هو مصدر قانوني أصلي معتمد من {$courtName}.",
                    'proposed_answer' => "هذا الحكم هو مصدر قانوني أصلي معتمد من {$courtName}.",
                    'case_text' => $caseText,
                    'case_reference' => $caseRef,
                    'domain' => 'law',
                    'source_file' => 'judgments(2).xlsx',
                    'created_at' => now(),
                    'updated_at' => now()
                ];

                $count++;

                if (count($batch) >= $batchSize) {
                    LegalTask::insert($batch);
                    $batch = [];
                    $this->info("Imported {$count} judgments...");
                }
            }

            if (!empty($batch)) {
                LegalTask::insert($batch);
            }

            $this->info("Import completed successfully! Total imported: {$count}");

        } catch (\Exception $e) {
            $this->error("Error: " . $e->getMessage());
        }
    }
}
