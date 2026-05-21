<?php
require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\LegalCitation;
use App\Models\LegalQaPair;

// Let's get 5 citations that don't have a linked article and inspect their columns
$citations = LegalCitation::whereNull('legal_article_id')
    ->limit(10)
    ->get();

echo "Citations without linked articles:\n";
foreach ($citations as $index => $c) {
    echo "Citation #{$index}:\n";
    echo "  ID: {$c->id}\n";
    echo "  System Name: '{$c->system_name}'\n";
    echo "  Article Number: '{$c->article_number}'\n";
    echo "  Raw Citation: '{$c->raw_citation}'\n";
    echo "--------------------\n";
}
