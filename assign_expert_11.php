<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\User;
use App\Models\AiTask;
use App\Services\TaskAssignmentService;

// Force find Expert 11
$expert = User::find(11);
if (!$expert) {
    echo "Expert 11 not found!\n";
    exit;
}

echo "Expert: {$expert->name} ({$expert->id})\n";

// Candidates Check
$candidates = AiTask::whereIn('status', ['Pending', 'In_Progress'])
    ->whereColumn('current_responses', '<', 'required_responses')
    ->whereDoesntHave('assignments', function ($q) use ($expert) {
        $q->where('expert_id', $expert->id);
    })->count();
echo "Candidates found: $candidates\n";

if ($candidates > 0) {
    $service = new TaskAssignmentService();
    try {
        $task = $service->assignNextTask($expert);
        if ($task) {
            echo "SUCCESS: Assigned Task {$task->id}\n";
        } else {
            echo "FAILED: Returned null\n";
        }
    } catch (\Exception $e) {
        echo "ERROR: " . $e->getMessage() . "\n";
    }
} else {
    echo "No candidates avaliable.\n";
}
