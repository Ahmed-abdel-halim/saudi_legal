<?php

require __DIR__ . '/../vendor/autoload.php';

$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\LegalTask;
use App\Models\AiTask;
use App\Models\TaskAssignment;
use App\Models\TaskConsensus;
use App\Models\GovernanceLog;
use Illuminate\Support\Facades\DB;

$tables = [
    'legal_tasks',
    'ai_tasks',
    'task_assignments',
    'task_consensus',
    'governance_logs'
];

echo "--- Governance System Full Reset ---\n";

foreach ($tables as $table) {
    try {
        $count = DB::table($table)->count();
        echo "Table [$table]: Found $count records.\n";
    } catch (\Exception $e) {
        echo "Table [$table]: Does not exist or error counting.\n";
    }
}

echo "\nStarting full reset...\n";

try {
    DB::statement('SET FOREIGN_KEY_CHECKS=0;');
    
    foreach ($tables as $table) {
        try {
            DB::table($table)->truncate();
            echo "Truncated [$table] successfully.\n";
        } catch (\Exception $e) {
            echo "Failed to truncate [$table]: " . $e->getMessage() . "\n";
        }
    }
    
    DB::statement('SET FOREIGN_KEY_CHECKS=1;');
    echo "\nReset Completed successfully.\n";
    echo "Expert Workbench and Governance Dashboard should now be empty.\n";

} catch (\Exception $e) {
    echo "Fatal Error: " . $e->getMessage() . "\n";
}
