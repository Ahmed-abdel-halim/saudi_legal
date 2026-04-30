<?php
require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\LegalArticle;

$log = "DB Debug Log - " . date('Y-m-d H:i:s') . "\n";
$log .= "Total Articles: " . LegalArticle::count() . "\n";
$log .= "Distinct Laws:\n";
$laws = LegalArticle::distinct()->pluck('legislation_title');
foreach ($laws as $law) {
    $log .= "- $law\n";
}

$log .= "\nSearching for Article 92:\n";
$arts = LegalArticle::where('article_title', 'LIKE', '%92%')
    ->orWhere('article_title', 'LIKE', '%تسع%')
    ->limit(10)
    ->get();

foreach ($arts as $a) {
    $log .= "- Law: {$a->legislation_title}, Title: {$a->article_title}\n";
}

file_put_contents(__DIR__ . '/storage/logs/db_debug.log', $log);
echo "Log written to storage/logs/db_debug.log\n";
