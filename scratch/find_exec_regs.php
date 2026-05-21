<?php
require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\LegalArticle;

$titles = LegalArticle::select('legislation_title')
    ->distinct()
    ->where('legislation_title', 'LIKE', '%اللائحة التنفيذية%')
    ->get();

echo "--- Exec Regulations in DB ---\n";
foreach ($titles as $t) {
    echo "- {$t->legislation_title}\n";
}
