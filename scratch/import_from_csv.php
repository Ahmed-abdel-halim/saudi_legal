<?php

// Load Laravel Bootstrap
require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\LegalTask;

$csvFile = __DIR__ . '/../Radiif_Cleaned_Sample (1).csv';

if (!file_exists($csvFile)) {
    die("❌ Error: File not found at $csvFile\n");
}

echo "--- Importing Legal Tasks from CSV ---\n";

$file = fopen($csvFile, 'r');
$header = fgetcsv($file); // Skip header

$count = 0;
while (($row = fgetcsv($file)) !== FALSE) {
    if (count($row) < 3) continue;

    // Mapping: 0:instruction, 1:question, 2:answer, 3:case_number, 4:year
    $instruction = $row[0];
    $question = $row[1];
    $answer = $row[2];
    $caseNumber = $row[3] ?? '';
    $year = $row[4] ?? '';

    // Extracting System Name from instruction if possible (e.g., "نظام الإثبات")
    preg_match('/نظام\s+([^\s\.]+)/u', $instruction, $matches);
    $systemName = isset($matches[0]) ? $matches[0] : 'الأنظمة السعودية';

    LegalTask::create([
        'task_type' => 'verification',
        'status' => 'pending',
        'question' => $question,
        'proposed_answer' => $answer,
        'law_system_name' => $systemName,
        'law_article_number' => 'مراجعة المادة ذات الصلة',
        'case_reference' => "حكم رقم $caseNumber لعام $year",
        'expert_comment' => "السياق المستهدف: " . $instruction,
        'domain' => 'law',
        'source_file' => 'Radiif_Cleaned_Sample.csv'
    ]);
    
    $count++;
    if ($count % 10 == 0) echo "Imported $count rows...\n";
}

fclose($file);

echo "✅ Successfully imported $count legal tasks.\n";
echo "You can now go to /dashboard/expert/legal-workbench to start reviewing!\n";
