<?php
require __DIR__ . '/../vendor/autoload.php';

$jsonlFile = __DIR__ . '/../Radiif_Master_16-5-2026.jsonl';

$handle = fopen($jsonlFile, 'r');
$count = 0;

while (($line = fgets($handle)) !== false) {
    $line = trim($line);
    if (empty($line)) continue;

    $data = json_decode($line, true);
    if (!$data) continue;

    $meta = $data['metadata'] ?? [];
    $caseNum = trim($meta['case_number'] ?? '');
    
    $qaPairs = $data['qa_pairs'] ?? [];
    foreach ($qaPairs as $qa) {
        $count++;
        echo "Question #{$count} (Case: {$caseNum}): " . mb_substr($qa['question'], 0, 50) . "...\n";
        echo "  - Raw Legal Articles: " . json_encode($qa['legal_articles'], JSON_UNESCAPED_UNICODE) . "\n";
        if ($count >= 15) break 2;
    }
}
fclose($handle);
