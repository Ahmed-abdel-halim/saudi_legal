<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\LegalArticle;
use Illuminate\Support\Facades\File;

$filePath = base_path('Saudi-law-mcp-main/data/seed/128-law-4d72d829-947b-45d5-b9b5-ae5800d6bac2.json');

if (!File::exists($filePath)) {
    die("File not found at: $filePath");
}

$content = File::get($filePath);
$data = json_decode($content, true);

if (!$data || !isset($data['provisions'])) {
    die("Invalid JSON structure");
}

$legislationTitle = $data['title'];
$legislationId = $data['id'];

echo "Importing: $legislationTitle ($legislationId)...\n";

$count = 0;
foreach ($data['provisions'] as $provision) {
    LegalArticle::updateOrCreate(
        [
            'legislation_id' => $legislationId,
            'article_title' => $provision['title'],
        ],
        [
            'legislation_title' => $legislationTitle,
            'content' => $provision['content'],
            'reference_id' => $provision['provision_ref'] ?? null,
        ]
    );
    $count++;
}

echo "Successfully imported $count articles for $legislationTitle.\n";
