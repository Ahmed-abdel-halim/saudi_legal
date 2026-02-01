<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\User;
use App\Models\AiTask;
use App\Models\AiResponse;

echo "==============================================\n";
echo "TESTING PREVIOUS TASK FUNCTIONALITY\n";
echo "==============================================\n\n";

$expertId = 11;
$expert = User::find($expertId);

if (!$expert) {
    echo "ERROR: Expert not found!\n";
    exit(1);
}

echo "Expert: {$expert->full_name} (ID: {$expertId})\n\n";

// Check if expert has any previous tasks
echo "--- Checking for Previous Tasks ---\n";
$hasPreviousTask = AiResponse::where('expert_id', $expert->id)->exists();
echo "Has Previous Tasks: " . ($hasPreviousTask ? 'YES' : 'NO') . "\n\n";

if ($hasPreviousTask) {
    // Get all tasks the expert has responded to
    $respondedTasks = AiTask::whereHas('responses', function ($q) use ($expert) {
            $q->where('expert_id', $expert->id);
        })
        ->with(['responses' => function ($q) use ($expert) {
            $q->where('expert_id', $expert->id);
        }])
        ->get()
        ->sortByDesc(function ($task) {
            return $task->responses->first()->created_at;
        });
    
    echo "Tasks Expert Has Responded To (ordered by response time):\n";
    echo "Total: {$respondedTasks->count()}\n\n";
    
    foreach ($respondedTasks as $index => $task) {
        $response = $task->responses->first();
        echo ($index + 1) . ". Task ID: {$task->id}\n";
        echo "   Original Data: {$task->original_data}\n";
        echo "   Response Action: {$response->action}\n";
        echo "   Response Time: {$response->created_at}\n";
        echo "   Reward: {$response->reward_amount} SAR\n";
        echo "\n";
    }
    
    // Test getting previous task from current task
    echo "--- Testing loadPrevious Logic ---\n";
    $currentTask = $respondedTasks->first(); // Most recent
    echo "Current Task: ID {$currentTask->id}\n";
    
    $currentResponse = AiResponse::where('task_id', $currentTask->id)
        ->where('expert_id', $expert->id)
        ->first();
    
    $previousTask = AiTask::whereHas('responses', function ($q) use ($expert) {
            $q->where('expert_id', $expert->id);
        })
        ->with(['responses' => function ($q) use ($expert) {
            $q->where('expert_id', $expert->id);
        }])
        ->where('id', '!=', $currentTask->id)
        ->whereHas('responses', function ($q) use ($expert, $currentResponse) {
            $q->where('expert_id', $expert->id)
              ->where('created_at', '<', $currentResponse->created_at);
        })
        ->get()
        ->sortByDesc(function ($task) {
            return $task->responses->first()->created_at;
        })
        ->first();
    
    if ($previousTask) {
        echo "Previous Task: ID {$previousTask->id}\n";
        echo "  Original Data: {$previousTask->original_data}\n";
        echo "  Response Time: {$previousTask->responses->first()->created_at}\n";
    } else {
        echo "No previous task found (this is the first one)\n";
    }
}

echo "\n==============================================\n";
echo "END OF TEST\n";
echo "==============================================\n";
