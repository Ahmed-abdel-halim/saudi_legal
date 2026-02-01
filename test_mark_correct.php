<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\User;
use App\Models\AiTask;
use App\Models\AiResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

echo "==============================================\n";
echo "TESTING MARK AS CORRECT ACTION\n";
echo "==============================================\n\n";

// Simulate being logged in as Expert 11
$expert = User::find(11);
if (!$expert) {
    echo "ERROR: Expert 11 not found!\n";
    exit(1);
}

echo "Expert: {$expert->full_name} (ID: {$expert->id})\n\n";

// Get Task 19
$task = AiTask::find(19);
if (!$task) {
    echo "ERROR: Task 19 not found!\n";
    exit(1);
}

echo "Task: ID {$task->id}, Status: {$task->status}\n";
echo "Original Data: {$task->original_data}\n\n";

echo "--- Simulating 'mark_correct' action ---\n";

try {
    DB::beginTransaction();
    
    // Create response record for "Accepted" action
    echo "Creating AiResponse record...\n";
    $response = AiResponse::create([
        'task_id'          => $task->id,
        'expert_id'        => $expert->id,
        'corrected_data'   => $task->ai_suggestion ?? '',
        'correction_notes' => null,
        'confidence_level' => 10,
        'action'           => 'accepted',
        'reward_amount'    => 7.0, // Example reward
    ]);
    
    echo "✓ Response created: ID {$response->id}\n";
    
    // Update task
    echo "Updating task...\n";
    $task->update([
        'completed_at' => Carbon::now(),
    ]);
    
    echo "✓ Task updated\n";
    
    // Trigger Governance Event
    echo "Triggering ExpertAnswerSubmitted event...\n";
    event(new \App\Events\ExpertAnswerSubmitted($response));
    
    echo "✓ Event triggered\n";
    
    DB::commit();
    
    echo "\n✓✓✓ SUCCESS! Action completed without errors.\n";
    
} catch (\Exception $e) {
    DB::rollBack();
    echo "\n✗✗✗ ERROR CAUGHT:\n";
    echo "Message: " . $e->getMessage() . "\n";
    echo "File: " . $e->getFile() . " (Line " . $e->getLine() . ")\n";
    echo "\nStack Trace:\n";
    echo $e->getTraceAsString() . "\n";
}

echo "\n==============================================\n";
echo "END OF TEST\n";
echo "==============================================\n";
