<?php

require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\LegalJudgment;

$judgments = LegalJudgment::where('case_text', 'LIKE', '%المادة%')->limit(3)->get();

foreach ($judgments as $j) {
    echo "ID: {$j->id}\n";
    echo "Law System: {$j->law_system}\n";
    echo "Text Snippet: " . mb_substr($j->case_text, 0, 300) . "...\n";
    echo "-----------------------------------\n";
}
