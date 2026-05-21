<?php
require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\LegalArticle;

// Find all distinct legislation titles containing "المحاكم" or "التجارية" or "لائحة"
$titles = LegalArticle::select('legislation_title')
    ->distinct()
    ->where(function($q) {
        $q->where('legislation_title', 'LIKE', '%تجار%')
          ->orWhere('legislation_title', 'LIKE', '%محاكم%');
    })
    ->get();

echo "--- Commercial Legislations in DB ---\n";
foreach ($titles as $t) {
    echo "- {$t->legislation_title}\n";
}
