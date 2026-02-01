<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\User;
use App\Models\AiTask;
use App\Models\AiResponse;
use App\Services\TaskAssignmentService;
use Carbon\Carbon;

echo "==============================================\n";
echo "SIMULATING WORKBENCH CONTROLLER LOGIC\n";
echo "==============================================\n\n";

// Simulate the controller logic
$expertId = 11;
$expert = User::find($expertId);

if (!$expert) {
    echo "ERROR: Expert not found!\n";
    exit(1);
}

echo "Expert: {$expert->full_name} (ID: {$expertId})\n\n";

// Simulate the index() method logic
$taskId = null; // No task_id query parameter

echo "--- Step 1: Load current task ---\n";
if ($taskId) {
    echo "Loading specific task ID: {$taskId}\n";
    $currentTask = AiTask::where('id', $taskId)->first();
} else {
    echo "No task_id provided, calling assignNextTask()...\n";
    $assignmentService = new TaskAssignmentService();
    $currentTask = $assignmentService->assignNextTask($expert);
}

if ($currentTask) {
    echo "✓ Current Task Found:\n";
    echo "  ID: {$currentTask->id}\n";
    echo "  Type: {$currentTask->task_type}\n";
    echo "  Status: {$currentTask->status}\n";
    echo "  Original Data: '{$currentTask->original_data}'\n";
    echo "  Original Data Length: " . strlen($currentTask->original_data) . " characters\n";
    echo "  Original Data is Empty: " . (empty($currentTask->original_data) ? 'YES' : 'NO') . "\n";
    echo "  Original Data is Null: " . (is_null($currentTask->original_data) ? 'YES' : 'NO') . "\n";
} else {
    echo "✗ Current Task is NULL\n";
}

echo "\n--- Step 2: Check for previous tasks ---\n";
$hasPreviousTask = AiTask::whereIn('status', ['completed', 'skipped'])->exists();
echo "Has Previous Task: " . ($hasPreviousTask ? 'YES' : 'NO') . "\n";

echo "\n--- Step 3: Get stats ---\n";
$tasksToday = AiResponse::where('expert_id', $expert->id)
    ->whereDate('created_at', Carbon::today())
    ->count();
echo "Tasks Today: {$tasksToday}\n";

$earningsToday = AiResponse::where('expert_id', $expert->id)
    ->whereDate('created_at', Carbon::today())
    ->sum('reward_amount') ?? 0;
echo "Earnings Today: {$earningsToday}\n";

echo "\n--- Step 4: View data ---\n";
echo "Variables that would be passed to view:\n";
echo "  - currentTask: " . ($currentTask ? "Task #{$currentTask->id}" : "NULL") . "\n";
echo "  - hasPreviousTask: " . ($hasPreviousTask ? 'true' : 'false') . "\n";
echo "  - tasks_today: {$tasksToday}\n";
echo "  - earnings_today: {$earningsToday}\n";

echo "\n--- Step 5: Blade condition check ---\n";
echo "In the Blade view, the condition is: @if (\$currentTask)\n";
echo "Result: " . ($currentTask ? "TRUE - Will show task" : "FALSE - Will show empty state") . "\n";

if ($currentTask) {
    echo "\nTask data that would be displayed:\n";
    echo "  - Task ID badge: TASK #{$currentTask->id}\n";
    echo "  - Question text: " . nl2br(htmlspecialchars($currentTask->original_data)) . "\n";
}

echo "\n==============================================\n";
echo "END OF SIMULATION\n";
echo "==============================================\n";
