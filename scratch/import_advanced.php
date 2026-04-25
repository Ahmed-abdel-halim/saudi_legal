<?php

// Load Laravel Bootstrap
require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\LegalTask;

$csvFile = __DIR__ . '/../Radiif_Cleaned_Sample (1).csv';
$lawsDir = __DIR__ . '/../Saudi-law-mcp-main/Saudi-law-mcp-main/data/seed/';

if (!file_exists($csvFile)) {
    die("❌ Error: CSV file not found.\n");
}

echo "--- 1. Indexing 536 Saudi Laws ---\n";
$lawsMap = [];
$files = glob($lawsDir . "*.json");
foreach ($files as $file) {
    $data = json_decode(file_get_contents($file), true);
    if (isset($data['title'])) {
        // Store simple mapping: Title -> File Path
        $lawsMap[$data['title']] = $file;
        // Also store short names or keywords
        if (isset($data['short_name'])) {
            $lawsMap[$data['short_name']] = $file;
        }
    }
}
echo "Found " . count($lawsMap) . " law definitions.\n\n";

echo "--- 2. Importing Tasks & Linking to Laws ---\n";
$file = fopen($csvFile, 'r');
$header = fgetcsv($file); // Skip header

$count = 0;
while (($row = fgetcsv($file)) !== FALSE) {
    if (count($row) < 3) continue;

    $instruction = $row[0];
    $question = $row[1];
    $answer = $row[2];
    $caseNumber = $row[3] ?? '';
    $year = $row[4] ?? '';

    // Try to find which law this belongs to
    $matchedLawFile = null;
    $matchedLawTitle = "نظام سعودي";
    $articleNumber = "غير محدد";

    // Regex to find law name (e.g., نظام الإثبات)
    if (preg_match('/نظام\s+([^\s\.\)\(]+(?:\s+[^\s\.\)\(]+){0,3})/u', $instruction . " " . $answer, $matches)) {
        $potentialTitle = trim($matches[0]);
        // Search in our map
        foreach ($lawsMap as $title => $path) {
            if (mb_stripos($title, $potentialTitle) !== false || mb_stripos($potentialTitle, $title) !== false) {
                $matchedLawFile = $path;
                $matchedLawTitle = $title;
                break;
            }
        }
    }

    // Regex to find article number (e.g., المادة 29)
    if (preg_match('/المادة\s+(\d+)/u', $instruction . " " . $answer, $matches)) {
        $articleNumber = $matches[1];
    }

    // Fetch Article Text if law is matched
    $articleText = "يرجى البحث عن نص المادة في مستعرض المستندات.";
    if ($matchedLawFile) {
        $lawData = json_decode(file_get_contents($matchedLawFile), true);
        foreach ($lawData['provisions'] as $provision) {
            if (mb_stripos($provision['title'], $articleNumber) !== false) {
                $articleText = $provision['content'];
                break;
            }
        }
    }

    LegalTask::create([
        'task_type' => 'verification',
        'status' => 'pending',
        'question' => $question,
        'proposed_answer' => $answer,
        'law_article_text' => $articleText,
        'law_article_number' => $articleNumber,
        'law_system_name' => $matchedLawTitle,
        'case_reference' => "حكم رقم $caseNumber لعام $year",
        'expert_comment' => $instruction,
        'domain' => 'law',
        'source_file' => 'Radiif_Cleaned_Sample.csv'
    ]);
    
    $count++;
    if ($count % 50 == 0) echo "Processed $count rows...\n";
}

fclose($file);
echo "✅ Done! Imported $count tasks with automated law linking.\n";
