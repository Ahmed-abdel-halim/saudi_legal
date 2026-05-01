<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\LegalJudgment;
use App\Models\LegalTask;

echo "LegalJudgment count: " . LegalJudgment::count() . "\n";
echo "LegalTask (judgment) count: " . LegalTask::where('source_type', 'judgment')->count() . "\n";
