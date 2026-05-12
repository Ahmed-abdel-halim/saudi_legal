<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\LegalCitation;
use App\Models\LegalRecord;
use App\Models\LegalQaPair;

echo "=== Legal Records Import Status ===\n\n";
echo "Records    : " . LegalRecord::count() . "\n";
echo "Citations  : " . LegalCitation::count() . "\n";
echo "QA Pairs   : " . LegalQaPair::count() . "\n\n";

echo "--- Citations Breakdown ---\n";
echo "  law       : " . LegalCitation::where('citation_source', 'law')->count() . "\n";
echo "  contract  : " . LegalCitation::where('citation_source', 'contract')->count() . "\n";
echo "  religious : " . LegalCitation::where('citation_source', 'religious')->count() . "\n";
echo "  other     : " . LegalCitation::where('citation_source', 'other')->count() . "\n\n";

$linked = LegalCitation::whereNotNull('legal_article_id')->count();
$total  = LegalCitation::count();
echo "--- legal_articles Linking ---\n";
echo "  Linked   : {$linked} / {$total}\n\n";

echo "--- 'other' citations (sample) ---\n";
LegalCitation::where('citation_source', 'other')
    ->limit(10)
    ->get(['system_name', 'article_number'])
    ->each(function ($c) {
        echo "  [{$c->article_number}] {$c->system_name}\n";
    });

echo "\n--- Sub-domains distribution ---\n";
LegalRecord::selectRaw('sub_domain, count(*) as cnt')
    ->groupBy('sub_domain')
    ->orderByDesc('cnt')
    ->get()
    ->each(fn($r) => print("  {$r->sub_domain}: {$r->cnt}\n"));
