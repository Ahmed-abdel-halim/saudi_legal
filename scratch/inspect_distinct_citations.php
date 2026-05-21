<?php
require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\LegalCitation;

$citations = LegalCitation::select('system_name')->distinct()->take(100)->get();
echo "--- Distinct Citation System Names in DB ---\n";
foreach ($citations as $c) {
    echo "- '{$c->system_name}'\n";
}
