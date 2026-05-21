<?php
require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\LegalQaPair;
use App\Models\LegalCitation;
use App\Models\LegalArticle;

echo "--- CHECKING IMPORTED CITATIONS ---\n";

$qaPairs = LegalQaPair::where('qa_id', 'not like', '%GOLD%')
    ->limit(10)
    ->get();

foreach ($qaPairs as $qa) {
    echo "ID: {$qa->id} | Question: " . mb_substr($qa->question, 0, 50) . "...\n";
    $citations = LegalCitation::where('legal_qa_pair_id', $qa->id)->get();
    foreach ($citations as $cit) {
        echo "  - System Name: '{$cit->system_name}' | Article Number: '{$cit->article_number}' | Article ID: '{$cit->legal_article_id}'\n";
        if ($cit->legal_article_id) {
            $art = LegalArticle::find($cit->legal_article_id);
            echo "    [FOUND IN DB] Title: {$art->article_title} | Legislation: {$art->legislation_title}\n";
        } else {
            echo "    [NOT FOUND IN DB]\n";
        }
    }
    echo "--------------------------------------------------\n";
}
