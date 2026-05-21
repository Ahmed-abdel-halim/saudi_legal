<?php
require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\LegalQaPair;

$qa = LegalQaPair::where('question', 'LIKE', '%شروط استحقاق أتعاب المحاماة%')->first();
if (!$qa) {
    echo "Question not found by LIKE search!\n";
    // Find all QA pairs in DB
    $qas = LegalQaPair::take(20)->get();
    foreach ($qas as $q) {
        echo "ID: {$q->id} | Question: {$q->question}\n";
    }
    exit;
}

echo "Found QA Pair ID: {$qa->id}\n";
echo "Question: {$qa->question}\n";
echo "Citations count: " . $qa->citations->count() . "\n";
foreach ($qa->citations as $c) {
    echo "- ID: {$c->id} | System Name: '{$c->system_name}' | Article Number: '{$c->article_number}' | Legal Article ID: '{$c->legal_article_id}'\n";
    if ($c->article) {
        echo "  [Real Article in DB] Title: {$c->article->article_title} | Legislation: {$c->article->legislation_title}\n";
    }
}
