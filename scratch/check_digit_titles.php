<?php
require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\DB;

$digitTitlesCount = DB::table('legal_articles')
    ->where('article_title', 'REGEXP', '[0-9]')
    ->count();

echo "Number of articles with digits in title: {$digitTitlesCount}\n";

if ($digitTitlesCount > 0) {
    $samples = DB::table('legal_articles')
        ->where('article_title', 'REGEXP', '[0-9]')
        ->limit(10)
        ->get();
    foreach ($samples as $s) {
        echo "- ID: {$s->id} | Title: '{$s->article_title}' | Legislation: '{$s->legislation_title}'\n";
    }
}
