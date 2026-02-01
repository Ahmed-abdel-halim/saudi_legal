<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\User;
use App\Models\AiTask;
use App\Services\TaskAssignmentService;
use Illuminate\Support\Facades\DB;

// Login as Muhamed (ID 11) or first expert
$expert = User::find(11) ?? User::where('role', 'expert')->first();

echo "Debugging for Expert: {$expert->name} (ID: {$expert->id})\n";
echo "Active: " . ($expert->is_active ? 'YES' : 'NO') . "\n";
echo "For Hire: " . ($expert->is_active_for_hire ? 'YES' : 'NO') . "\n";

$service = new TaskAssignmentService();

// 1. Check direct query for candidate tasks
echo "\n--- Candidate Tasks Query ---\n";
$query = AiTask::whereIn('status', ['Pending', 'In_Progress'])
    ->whereColumn('current_responses', '<', 'required_responses')
    ->whereDoesntHave('assignments', function ($q) use ($expert) {
        $q->where('expert_id', $expert->id);
    });

echo "SQL: " . $query->toSql() . "\n";
echo "Bindings: " . json_encode($query->getBindings()) . "\n";

$candidates = $query->take(3)->get();
echo "Found Candidates: " . $candidates->count() . "\n";

foreach ($candidates as $task) {
    echo " - Task #{$task->id}: Status={$task->status}, Resp={$task->current_responses}/{$task->required_responses}\n";
}

// 2. Check existing active assignment
echo "\n--- Existing Active Assignment ---\n";
$existing = \App\Models\TaskAssignment::where('expert_id', $expert->id)
            ->active()
            ->whereHas('task', function ($q) {
                $q->whereIn('status', ['Pending', 'In_Progress']);
            })
            ->with('task')
            ->first();

if ($existing) {
    echo "Found Existing Active Task: #{$existing->task->id}\n";
} else {
    echo "No existing active assignment.\n";
}

// 3. Test assignNextTask
echo "\n--- Testing assignNextTask() ---\n";
try {
    $result = $service->assignNextTask($expert);
    if ($result) {
        echo "SUCCESS: Assigned Task #{$result->id}\n";
    } else {
        echo "FAILED: assignNextTask returned null.\n";
    }
} catch (\Exception $e) {
    echo "ERROR: " . $e->getMessage() . "\n";
}
