<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\LegalJudgment;

echo "Total Judgments: " . LegalJudgment::count() . "\n";
echo "Judgments with 'غير محدد': " . LegalJudgment::where('case_number', 'غير محدد')->count() . "\n";
echo "Sample from non-'غير محدد':\n";
$samples = LegalJudgment::where('case_number', '!=', 'غير محدد')->whereNotNull('case_number')->limit(5)->get();
foreach ($samples as $s) {
    echo "ID: {$s->id} | Number: {$s->case_number}\n";
}

echo "\nSample from 'غير محدد' (first 2): \n";
$nullSamples = LegalJudgment::where('case_number', 'غير محدد')->limit(2)->get();
foreach ($nullSamples as $s) {
    echo "ID: {$s->id} | Metadata: " . json_encode($s->metadata) . "\n";
    echo "Text Snippet: " . mb_substr($s->case_text, 0, 100) . "...\n";
}
