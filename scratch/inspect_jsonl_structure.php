<?php
$jsonlFile = __DIR__ . '/../Radiif_Master_16-5-2026.jsonl';
$handle = fopen($jsonlFile, 'r');
for ($i = 0; $i < 5; $i++) {
    $line = fgets($handle);
    if ($line === false) break;
    echo "Line " . ($i + 1) . ":\n";
    $data = json_decode($line, true);
    print_r($data);
}
fclose($handle);
