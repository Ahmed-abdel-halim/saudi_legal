<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\AiTask;
use App\Models\User;
use Carbon\Carbon;

echo "==============================================\n";
echo "TASKS UPLOADED AFTER CSV UPLOAD TO WORKBENCH\n";
echo "==============================================\n\n";

// Configuration: Set your filters here
$clientId = null; // Set to specific client ID or null for all clients
$afterDate = null; // Set to a date string like '2026-01-31' or null for all dates
$status = null; // Set to 'Pending', 'In_Progress', 'Completed', etc., or null for all statuses
$limit = null; // Set to a number to limit results, or null for all tasks

// Build the query
$query = AiTask::query()
    ->with(['assignments.expert', 'responses', 'consensus'])
    ->orderBy('created_at', 'desc');

// Apply filters
if ($clientId) {
    $query->where('client_id', $clientId);
}

if ($afterDate) {
    $query->where('created_at', '>=', Carbon::parse($afterDate));
}

if ($status) {
    $query->where('status', $status);
}

if ($limit) {
    $query->limit($limit);
}

// Get tasks
$tasks = $query->get();

// Display summary
echo "SUMMARY:\n";
echo "--------\n";
echo "Total Tasks Found: " . $tasks->count() . "\n";
echo "Filters Applied:\n";
echo "  - Client ID: " . ($clientId ?? 'All') . "\n";
echo "  - After Date: " . ($afterDate ?? 'All') . "\n";
echo "  - Status: " . ($status ?? 'All') . "\n";
echo "  - Limit: " . ($limit ?? 'None') . "\n";
echo "\n";

// Group tasks by status
$statusCounts = $tasks->groupBy('status')->map->count();
echo "Status Breakdown:\n";
foreach ($statusCounts as $statusName => $count) {
    echo "  - {$statusName}: {$count}\n";
}
echo "\n";

// Display detailed task information
echo "==============================================\n";
echo "DETAILED TASK INFORMATION\n";
echo "==============================================\n\n";

foreach ($tasks as $index => $task) {
    echo "Task #" . ($index + 1) . " (ID: {$task->id})\n";
    echo str_repeat("-", 50) . "\n";
    echo "Task Type: {$task->task_type}\n";
    echo "Status: {$task->status}\n";
    echo "Consensus Status: {$task->consensus_status}\n";
    echo "Client ID: {$task->client_id}\n";
    echo "Created At: {$task->created_at}\n";
    echo "Updated At: {$task->updated_at}\n";
    
    // Original data (truncated if too long)
    $originalData = $task->original_data;
    if (strlen($originalData) > 100) {
        $originalData = substr($originalData, 0, 100) . '...';
    }
    echo "Original Data: {$originalData}\n";
    
    // AI Suggestion (truncated if too long)
    if ($task->ai_suggestion) {
        $aiSuggestion = $task->ai_suggestion;
        if (strlen($aiSuggestion) > 100) {
            $aiSuggestion = substr($aiSuggestion, 0, 100) . '...';
        }
        echo "AI Suggestion: {$aiSuggestion}\n";
    }
    
    // Response tracking
    echo "Responses: {$task->current_responses}/{$task->required_responses}\n";
    
    // Gold standard info
    if ($task->is_gold_standard) {
        echo "Gold Standard: YES\n";
        if ($task->gold_answer) {
            $goldAnswer = is_array($task->gold_answer) ? json_encode($task->gold_answer) : $task->gold_answer;
            echo "Gold Answer: {$goldAnswer}\n";
        }
    }
    
    // Assignments
    $assignmentsCount = $task->assignments->count();
    echo "Assignments: {$assignmentsCount}\n";
    if ($assignmentsCount > 0) {
        foreach ($task->assignments as $assignment) {
            $expertName = $assignment->expert ? $assignment->expert->full_name : 'Unknown';
            echo "  - Expert: {$expertName} (ID: {$assignment->expert_id})\n";
            echo "    Assigned At: {$assignment->assigned_at}\n";
            echo "    Status: {$assignment->status}\n";
            if ($assignment->completed_at) {
                echo "    Completed At: {$assignment->completed_at}\n";
            }
        }
    }
    
    // Responses
    $responsesCount = $task->responses->count();
    echo "Expert Responses: {$responsesCount}\n";
    if ($responsesCount > 0) {
        foreach ($task->responses as $response) {
            echo "  - Expert ID: {$response->expert_id}\n";
            echo "    Answer: " . json_encode($response->answer) . "\n";
            echo "    Confidence: {$response->confidence}%\n";
            echo "    Submitted At: {$response->created_at}\n";
        }
    }
    
    // Consensus
    if ($task->consensus) {
        echo "Consensus:\n";
        echo "  - Type: {$task->consensus->consensus_type}\n";
        echo "  - Confidence: {$task->consensus->confidence_level}%\n";
        if ($task->consensus->final_answer) {
            echo "  - Final Answer: " . json_encode($task->consensus->final_answer) . "\n";
        }
        if ($task->consensus->conflict_notes) {
            echo "  - Notes: {$task->consensus->conflict_notes}\n";
        }
    }
    
    echo "\n";
}

echo "==============================================\n";
echo "END OF REPORT\n";
echo "==============================================\n";
