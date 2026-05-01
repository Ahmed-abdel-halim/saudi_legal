<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\LegalJudgment;
use App\Models\LegalTask;

$search = '4570092541';
echo "Searching for: $search\n";

$j = LegalJudgment::where('case_number', 'LIKE', "%$search%")->first();
if ($j) {
    echo "Found in LegalJudgment! ID: " . $j->id . "\n";
    echo "Text length: " . strlen($j->case_text) . "\n";
} else {
    echo "NOT found in LegalJudgment.\n";
}

$t = LegalTask::where('case_reference', 'LIKE', "%$search%")->whereNotNull('case_text')->first();
if ($t) {
    echo "Found in LegalTask! ID: " . $t->id . "\n";
} else {
    echo "NOT found in LegalTask.\n";
}
