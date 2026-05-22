<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

// ابحث في الـ JSONL عن القضية 1148 وجيب المواد المرتبطة بالسؤال
$jsonlPath = __DIR__ . '/Radiif_Master_16-5-2026.jsonl';
$targetCase = '1148';
$targetQuestion = 'متى تسري أحكام نظام المحاكم التجارية المتعلقة بوجوب تقديم طلب الاستئناف من محامٍ مرخص';

$handle = fopen($jsonlPath, 'r');
$found = false;

while (($line = fgets($handle)) !== false) {
    $data = json_decode(trim($line), true);
    if (!$data) continue;

    $caseNum = trim($data['metadata']['case_number'] ?? '');
    if ($caseNum !== $targetCase) continue;

    $found = true;
    echo "=== القضية: {$caseNum} ===\n\n";
    echo "عدد qa_pairs: " . count($data['qa_pairs'] ?? []) . "\n\n";

    foreach ($data['qa_pairs'] as $i => $pair) {
        echo "--- QA Pair #" . ($i+1) . " ---\n";
        echo "السؤال: " . ($pair['question'] ?? '') . "\n";
        echo "المواد:\n";
        foreach ($pair['legal_articles'] ?? [] as $art) {
            echo "  - " . $art . "\n";
        }
        echo "\n";
    }
    break;
}

fclose($handle);

if (!$found) {
    echo "القضية {$targetCase} غير موجودة في الـ JSONL\n";
}
