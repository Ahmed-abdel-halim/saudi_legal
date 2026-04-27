<?php

require __DIR__ . '/../vendor/autoload.php';

$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\LegalTask;
use App\Models\LegalArticle;

echo "--- Last Legal Task ---\n";
$task = LegalTask::latest()->first();
if ($task) {
    print_r($task->toArray());
} else {
    echo "No tasks found.\n";
}

echo "\n--- Sample Articles ---\n";
$articles = LegalArticle::limit(5)->get();
foreach ($articles as $art) {
    echo "ID: {$art->id} | System: {$art->legislation_title} | Title: {$art->article_title}\n";
}
