<?php

require __DIR__ . '/../vendor/autoload.php';

use PhpOffice\PhpSpreadsheet\IOFactory;

$xlsxFile = __DIR__ . '/../Radiif_Smart_Golden_100.xlsx';
$jsonlFile = __DIR__ . '/../Radiif_Master_16-5-2026.jsonl';

function normalizeArabic(string $text): string
{
    $text = preg_replace('/[أإآ]/u', 'ا', $text);
    $text = str_replace(['ة','ى'], ['ه','ي'], $text);
    return mb_strtolower(trim($text));
}

function findColumnIndex(array $headers, array $keywords): ?int
{
    foreach ($headers as $index => $header) {
        foreach ($keywords as $kw) {
            if (str_contains($header, $kw)) {
                return $index;
            }
        }
    }
    return null;
}

echo "Loading Excel...\n";
$spreadsheet = IOFactory::load($xlsxFile);
$sheet = $spreadsheet->getActiveSheet();
$xlsxRows = $sheet->toArray();
$headers = array_map(fn($v) => trim(mb_strtolower((string)$v)), $xlsxRows[0]);
array_shift($xlsxRows);

$colMap = [
    'question' => findColumnIndex($headers, ['سؤال', 'question', 'السؤال', 'النص', 'الطلب']),
    'proposed_answer' => findColumnIndex($headers, ['خاطئ', 'غلط', 'مقترح', 'الذكاء', 'incorrect', 'proposed', 'wrong', 'الخطأ']),
    'correct_answer' => findColumnIndex($headers, ['صحيح', 'معدل', 'الذهبي', 'correct', 'gold', 'right', 'النموذجية']),
    'case_reference' => findColumnIndex($headers, ['مرجع', 'رقم القضية', 'reference']),
];

$goldRecords = [];
$lookup = [];

foreach ($xlsxRows as $row) {
    $q = $colMap['question'] !== null ? trim((string)($row[$colMap['question']] ?? '')) : '';
    if (empty($q)) continue;

    $caseRef = $colMap['case_reference'] !== null ? trim((string)($row[$colMap['case_reference']] ?? '')) : '';
    $normQ = normalizeArabic($q);

    $goldRecords[] = [
        'question' => $q,
        'norm_q' => $normQ,
        'case_reference' => $caseRef,
        'matched' => false
    ];

    if ($caseRef) {
        $lookup[trim($caseRef)][$normQ] = count($goldRecords) - 1;
    }
}

echo "Total Excel questions: " . count($goldRecords) . "\n";

echo "Scanning JSONL...\n";
$start = microtime(true);
$handle = fopen($jsonlFile, 'r');
$matchedCount = 0;
$lineCount = 0;

while (($line = fgets($handle)) !== false) {
    $lineCount++;
    $line = trim($line);
    if (empty($line)) continue;

    // Check if the line contains any of our case numbers before decoding to save CPU
    // This is a fast pre-filter!
    $hasCase = false;
    foreach ($lookup as $caseRef => $qs) {
        if (str_contains($line, $caseRef)) {
            $hasCase = true;
            break;
        }
    }
    if (!$hasCase) continue;

    $data = json_decode($line, true);
    if (!$data) continue;

    $meta = $data['metadata'] ?? [];
    $caseNum = trim($meta['case_number'] ?? '');

    if (isset($lookup[$caseNum])) {
        $qaPairs = $data['qa_pairs'] ?? [];
        foreach ($qaPairs as $qa) {
            $jsonQ = trim($qa['question'] ?? '');
            $normJsonQ = normalizeArabic($jsonQ);
            
            // Try exact or contains matching
            foreach ($lookup[$caseNum] as $normGoldQ => $idx) {
                if ($normGoldQ === $normJsonQ || str_contains($normJsonQ, $normGoldQ) || str_contains($normGoldQ, $normJsonQ)) {
                    if (!$goldRecords[$idx]['matched']) {
                        $goldRecords[$idx]['matched'] = true;
                        $matchedCount++;
                    }
                }
            }
        }
    }
}
fclose($handle);
$end = microtime(true);

echo "Scan complete in " . round($end - $start, 2) . " seconds.\n";
echo "Matched: {$matchedCount} / " . count($goldRecords) . "\n";

echo "Unmatched samples:\n";
$unmatchedCount = 0;
foreach ($goldRecords as $gr) {
    if (!$gr['matched']) {
        echo "- Case: {$gr['case_reference']} | Question: " . mb_substr($gr['question'], 0, 50) . "...\n";
        $unmatchedCount++;
        if ($unmatchedCount >= 10) break;
    }
}
