<?php

require __DIR__ . '/../vendor/autoload.php';

use PhpOffice\PhpSpreadsheet\IOFactory;

$file = __DIR__ . '/../Radiif_Smart_Golden_100.xlsx';

if (!file_exists($file)) {
    echo "File not found\n";
    exit(1);
}

try {
    $spreadsheet = IOFactory::load($file);
    $sheet = $spreadsheet->getActiveSheet();
    $rows = $sheet->toArray();
    
    echo "Total rows: " . count($rows) . "\n";
    echo "Headers:\n";
    print_r($rows[0]);
    echo "\nSample Row 1:\n";
    print_r($rows[1] ?? 'No row 1');
} catch (\Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
