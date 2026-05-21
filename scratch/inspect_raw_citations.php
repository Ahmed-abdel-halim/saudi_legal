<?php
require __DIR__ . '/../vendor/autoload.php';

$jsonlFile = __DIR__ . '/../Radiif_Master_16-5-2026.jsonl';
$handle = fopen($jsonlFile, 'r');

$citations = [];
while (($line = fgets($handle)) !== false) {
    $data = json_decode($line, true);
    if (isset($data['qa_pairs'])) {
        foreach ($data['qa_pairs'] as $pair) {
            if (isset($pair['legal_articles'])) {
                foreach ($pair['legal_articles'] as $art) {
                    if (is_string($art)) {
                        $citations[$art] = ($citations[$art] ?? 0) + 1;
                    }
                }
            }
        }
    }
}
fclose($handle);

arsort($citations);
echo "Total distinct citations: " . count($citations) . "\n";
echo "Top 100 citations:\n";
$i = 0;
foreach ($citations as $cit => $count) {
    echo "- [{$count} times] '{$cit}'\n";
    if (++$i >= 100) break;
}
