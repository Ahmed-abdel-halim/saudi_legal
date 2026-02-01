<?php

use App\Models\User;
use App\Models\AiTask;
use App\Models\AiResponse;
use App\Services\TaskAssignmentService;
use Illuminate\Support\Facades\Config;

// Set queue to sync to ensure immediate execution of listeners
Config::set('queue.default', 'sync');

echo "Starting QA & Governance Flow Test...\n";

// Cleanup previous test data
User::where('email', 'like', 'expert_test_%')->delete();
User::where('email', 'client_test@test.com')->delete();
AiTask::where('original_data', 'Test Data 123')->delete();

// 1. Create Client
echo "Creating Client...\n";
$client = User::create([
    'name' => "Client Test",
    'email' => "client_test@test.com",
    'password' => bcrypt('password'),
    'role' => 'client',
    'is_active' => true,
    'trust_score' => 100,
    'company_id' => 1, // Added just in case
]);
echo "Created Client Object. ID: " . ($client->id ?? 'NULL') . "\n";

// 2. Create 3 Experts
$experts = [];
for ($i=1; $i<=3; $i++) {
    $experts[] = User::create([
        'name' => "Expert Test $i",
        'email' => "expert_test_$i@test.com",
        'password' => bcrypt('password'), // password
        'role' => 'expert',
        'is_active' => true,
        'is_active_for_hire' => true,
        'trust_score' => 100,
        'company_id' => 1 
    ]);
}
echo "Created 3 experts.\n";

// 3. Create Task (Gold Standard)
$task = AiTask::create([
    'task_type' => 'translation',
    'original_data' => 'Test Data 123',
    'status' => 'Pending',
    'required_responses' => 3,
    'current_responses' => 0,
    'client_id' => $client->id, 
    'is_gold_standard' => true,
    'gold_answer' => json_encode('Correct Answer'), // Storing as JSON
]);
echo "Created Gold Standard Task ID: {$task->id}\n";

// 4. Assign Task
$assignmentService = new App\Services\TaskAssignmentService();
foreach ($experts as $expert) {
    try {
        $assignmentService->assignTaskToExpert($task->id, $expert->id);
        echo "Assigned to {$expert->name}\n";
    } catch (\Exception $e) {
        echo "Assignment Error for {$expert->name}: " . $e->getMessage() . "\n";
    }
}

$task->refresh();
echo "Task Status after assignment: {$task->status}\n";

// 5. Submit Answers 
// Expert 1: Correct
// Expert 2: Correct
// Expert 3: Incorrect

$answers = ['Correct Answer', 'Correct Answer', 'Wrong Answer'];

foreach ($experts as $index => $expert) {
    echo "Expert {$expert->name} submitting: {$answers[$index]}\n";
    
    // Determine confidence
    $conf = 90;
    
    // Note: AiResponse casts 'corrected_data'? No, it's string usually.
    // Ensure we send what we expect. 
    // In ValidateGoldStandard we compare `$response->corrected_data == json_encode($goldAnswer)` 
    // logic in ValidateGoldStandard:
    // $isCorrect = $response->corrected_data == json_encode($goldAnswer) || ...
    // My gold_answer in task is `json_encode('Correct Answer')` which is `""Correct Answer""` (double quoted string in DB)
    // If I submit `'Correct Answer'` string in response w/o json_encoding,
    // ValidateGoldStandard: `json_encode('Correct Answer')` (from task) == `'Correct Answer'` (from response) -> FALSE
    
    // Let's align:
    // Task gold_answer = "Correct Answer" (simple string)
    // Response corrected_data = "Correct Answer"
    
    // I should update task to simple string for this test to be clear, OR handle JSON matching carefully.
    // The listener checks: $isCorrect = $response->corrected_data == json_encode($goldAnswer) ||  $response->corrected_data == $goldAnswer
    
    // Let's use simple string for everything for simplicity in this test.
    
    $response = AiResponse::create([
        'task_id' => $task->id,
        'expert_id' => $expert->id,
        'corrected_data' => json_encode($answers[$index]), // Encoding to match typical JSON storage
        'confidence_level' => $conf,
        'correction_notes' => 'Test note'
    ]);
}

// 6. Verify Results
$task->refresh();
echo "\n--- Verification Results ---\n";
echo "Final Task Status: " . $task->status . " (Expected: Consensus_Reached)\n";
echo "Consensus Status: " . $task->consensus_status . " (Expected: majority_vote)\n"; // 2 vs 1

if ($task->consensus) {
    echo "Consensus Record Found. Type: " . $task->consensus->consensus_type . "\n";
    echo "Confidence: " . $task->consensus->confidence_level . "\n";
} else {
    echo "CRITICAL: No Consensus Record Found!\n";
}

// Check Trust Scores
$experts[0]->refresh();
$experts[2]->refresh();
echo "Expert 1 Trust Score: " . $experts[0]->trust_score . " (Expected: 100 or unchanged/incremented)\n";
echo "Expert 3 Trust Score: " . $experts[2]->trust_score . " (Expected: 90)\n"; // -10 penalty

// Check Logs
$logs = \App\Models\GovernanceLog::where('task_id', $task->id)->get();
echo "Governance Logs Count: " . $logs->count() . "\n";
foreach($logs as $log) {
    echo "Log: {$log->event_type} - Expert: {$log->expert_id}\n";
}
