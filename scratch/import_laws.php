<?php

// Load Laravel Bootstrap
require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\LegalTask;

echo "--- Saudi Law Importer (SQLite -> MySQL) ---\n";

try {
    $sqlite = new PDO('sqlite:' . __DIR__ . '/../database.db');
    
    // Attempt to find the correct tables
    $tables = $sqlite->query("SELECT name FROM sqlite_master WHERE type='table'")->fetchAll(PDO::FETCH_COLUMN);
    
    echo "Tables in SQLite: " . implode(', ', $tables) . "\n";

    // Assuming standard schema from Saudi-law-mcp: 'legislation' and 'articles'
    // Let's try to fetch laws and their articles
    $query = "
        SELECT 
            l.name_ar as system_name,
            a.article_number,
            a.content_ar as article_text
        FROM articles a
        JOIN legislation l ON a.legislation_id = l.id
        LIMIT 50
    ";
    
    $stmt = $sqlite->query($query);
    if (!$stmt) {
        // Fallback if schema is different
        echo "⚠️ Default schema not found, trying alternative query...\n";
        $query = "SELECT * FROM articles LIMIT 50";
        $stmt = $sqlite->query($query);
    }

    $count = 0;
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        // Create a task for each article to be refined by lawyers
        LegalTask::create([
            'task_type' => 'verification',
            'status' => 'pending',
            'question' => "يرجى مراجعة وتدقيق نص المادة رقم " . ($row['article_number'] ?? '---') . " من " . ($row['system_name'] ?? 'النظام'),
            'proposed_answer' => $row['article_text'] ?? 'لا يوجد نص',
            'law_article_text' => $row['article_text'] ?? '',
            'law_article_number' => $row['article_number'] ?? '',
            'law_system_name' => $row['system_name'] ?? 'نظام سعودي',
            'domain' => 'law',
            'source_file' => 'database.db'
        ]);
        $count++;
    }

    echo "✅ Successfully imported $count legal articles into legal_tasks table.\n";
    echo "You can now go to the dashboard and start refining!\n";

} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
}
