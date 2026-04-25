<?php

// Load Laravel Bootstrap
require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\LegalArticle;
use Illuminate\Support\Facades\DB;

$lawsDir = __DIR__ . '/../Saudi-law-mcp-main/Saudi-law-mcp-main/data/seed/';

if (!is_dir($lawsDir)) {
    die("❌ Error: Laws directory not found.\n");
}

echo "--- 🚀 Starting Full Legal Indexing --- \n";

$files = glob($lawsDir . "*.json");
$totalFiles = count($files);
echo "Processing $totalFiles legislation files...\n";

DB::beginTransaction();
try {
    $totalArticles = 0;
    foreach ($files as $index => $file) {
        $data = json_decode(file_get_contents($file), true);
        
        $legId = $data['id'] ?? 'unknown';
        $legTitle = $data['title'] ?? 'بدون عنوان';
        
        if (isset($data['provisions'])) {
            foreach ($data['provisions'] as $provision) {
                LegalArticle::create([
                    'legislation_id' => $legId,
                    'legislation_title' => $legTitle,
                    'article_title' => $provision['title'] ?? 'مادة',
                    'content' => $provision['content'] ?? '',
                    'reference_id' => $provision['provision_ref'] ?? null
                ]);
                $totalArticles++;
            }
        }
        
        if (($index + 1) % 50 == 0) {
            echo "Processed " . ($index + 1) . "/$totalFiles files... ($totalArticles articles indexed)\n";
        }
    }
    DB::commit();
    echo "\n✅ SUCCESS! Indexed $totalArticles articles from $totalFiles systems.\n";
} catch (Exception $e) {
    DB::rollBack();
    echo "❌ Error during indexing: " . $e->getMessage() . "\n";
}
