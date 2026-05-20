<?php

require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

$normalCitations = \App\Models\LegalCitation::whereHas('qaPair', function($q) {
    $q->where('qa_id', '!=', 'Q-GOLD');
})->get();

$total = $normalCitations->count();
$matched = $normalCitations->whereNotNull('legal_article_id')->count();
$unmatched = $total - $matched;

echo "Total normal citations imported: {$total}\n";
echo "Successfully matched to a Law Article: {$matched} (" . ($total > 0 ? round($matched / $total * 100, 2) : 0) . "%)\n";
echo "Unmatched (NULL legal_article_id): {$unmatched} (" . ($total > 0 ? round($unmatched / $total * 100, 2) : 0) . "%)\n";

echo "\n--- Samples of Unmatched Citations ---\n";
$samples = $normalCitations->whereNull('legal_article_id')->take(15);
foreach ($samples as $s) {
    echo "- System: '{$s->system_name}', Article: '{$s->article_number}' (Original text: " . $s->system_name . ($s->article_number ? " المادة " . $s->article_number : "") . ")\n";
}
