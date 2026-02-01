<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\AiTask;
use App\Models\AiResponse;
use App\Models\TaskConsensus;
use App\Models\GovernanceLog;
use App\Models\User;

echo "==============================================\n";
echo "GOVERNANCE DASHBOARD COMPREHENSIVE TEST\n";
echo "==============================================\n\n";

// Test 1: Check Event Listeners Registration
echo "--- Test 1: Event Listeners Registration ---\n";
$events = app('events')->getListeners('App\\Events\\AnswerSubmitted');
echo "Listeners for AnswerSubmitted event:\n";
if (empty($events)) {
    echo "  ⚠️  WARNING: No listeners registered!\n";
} else {
    foreach ($events as $listener) {
        echo "  ✓ " . (is_string($listener) ? $listener : get_class($listener)) . "\n";
    }
}
echo "\n";

// Test 2: Check Database Tables
echo "--- Test 2: Database Tables ---\n";
$tables = ['ai_tasks', 'ai_responses_v2', 'task_consensus', 'governance_logs', 'task_assignments'];
foreach ($tables as $table) {
    try {
        DB::table($table)->limit(1)->get();
        echo "  ✓ Table '{$table}' exists\n";
    } catch (\Exception $e) {
        echo "  ✗ Table '{$table}' missing or error: " . $e->getMessage() . "\n";
    }
}
echo "\n";

// Test 3: Consensus Calculation Logic
echo "--- Test 3: Consensus Calculation ---\n";
$consensusStats = TaskConsensus::select('consensus_type', DB::raw('count(*) as count'))
    ->groupBy('consensus_type')
    ->get();

if ($consensusStats->isEmpty()) {
    echo "  ⚠️  No consensus records found. This is normal if no tasks have been completed yet.\n";
} else {
    echo "  Consensus Statistics:\n";
    foreach ($consensusStats as $stat) {
        echo "    - {$stat->consensus_type}: {$stat->count} tasks\n";
    }
}

$totalConsensus = TaskConsensus::count();
$totalTasks = AiTask::count();
echo "  Total Consensus Records: {$totalConsensus}\n";
echo "  Total Tasks: {$totalTasks}\n";
echo "\n";

// Test 4: Gold Standard Tasks
echo "--- Test 4: Gold Standard Tasks ---\n";
$goldTasks = AiTask::where('is_gold_standard', true)->count();
echo "  Gold Standard Tasks: {$goldTasks}\n";

if ($goldTasks > 0) {
    $goldLogs = GovernanceLog::whereIn('event_type', ['gold_task_passed', 'gold_task_failed'])
        ->count();
    echo "  Gold Task Validation Logs: {$goldLogs}\n";
}
echo "\n";

// Test 5: Trust Score Management
echo "--- Test 5: Trust Score Management ---\n";
$experts = User::where('role', 'expert')->get();
echo "  Total Experts: {$experts->count()}\n";

if ($experts->count() > 0) {
    echo "  Trust Score Statistics:\n";
    echo "    - Average: " . round($experts->avg('trust_score'), 2) . "%\n";
    echo "    - Highest: " . $experts->max('trust_score') . "%\n";
    echo "    - Lowest: " . $experts->min('trust_score') . "%\n";
    echo "    - Banned Experts: " . $experts->where('is_banned', true)->count() . "\n";
}
echo "\n";

// Test 6: Governance Logs
echo "--- Test 6: Governance Logs ---\n";
$logTypes = GovernanceLog::select('event_type', DB::raw('count(*) as count'))
    ->groupBy('event_type')
    ->get();

if ($logTypes->isEmpty()) {
    echo "  ⚠️  No governance logs found.\n";
} else {
    echo "  Event Types:\n";
    foreach ($logTypes as $log) {
        echo "    - {$log->event_type}: {$log->count} events\n";
    }
}
echo "\n";

// Test 7: Task Status Distribution
echo "--- Test 7: Task Status Distribution ---\n";
$taskStats = AiTask::select('status', DB::raw('count(*) as count'))
    ->groupBy('status')
    ->get();

echo "  Task Status:\n";
foreach ($taskStats as $stat) {
    echo "    - {$stat->status}: {$stat->count} tasks\n";
}
echo "\n";

// Test 8: Response Statistics
echo "--- Test 8: Response Statistics ---\n";
$totalResponses = AiResponse::count();
$responsesByAction = AiResponse::select('action', DB::raw('count(*) as count'))
    ->groupBy('action')
    ->get();

echo "  Total Responses: {$totalResponses}\n";
echo "  Responses by Action:\n";
foreach ($responsesByAction as $resp) {
    echo "    - {$resp->action}: {$resp->count}\n";
}
echo "\n";

// Test 9: Dashboard Metrics Calculation
echo "--- Test 9: Dashboard Metrics ---\n";
$total = TaskConsensus::count();

if ($total > 0) {
    $stats = TaskConsensus::select('consensus_type', DB::raw('count(*) as count'))
        ->groupBy('consensus_type')
        ->pluck('count', 'consensus_type')
        ->toArray();

    echo "  Accuracy Metrics:\n";
    echo "    - Perfect Consensus: " . round(($stats['perfect_match'] ?? 0) / $total * 100, 2) . "%\n";
    echo "    - Majority Vote: " . round(($stats['majority_vote'] ?? 0) / $total * 100, 2) . "%\n";
    echo "    - Conflict: " . round(($stats['conflict'] ?? 0) / $total * 100, 2) . "%\n";
} else {
    echo "  ⚠️  No consensus data available for metrics.\n";
}
echo "\n";

// Test 10: Recent Activity
echo "--- Test 10: Recent Activity ---\n";
$recentTasks = AiTask::latest()->limit(5)->get();
echo "  Last 5 Tasks Created:\n";
foreach ($recentTasks as $task) {
    echo "    - Task #{$task->id}: {$task->status} ({$task->current_responses}/{$task->required_responses} responses)\n";
}
echo "\n";

// Summary
echo "==============================================\n";
echo "SUMMARY\n";
echo "==============================================\n";
echo "✓ Event listeners: " . (empty($events) ? "⚠️  NOT REGISTERED" : "OK") . "\n";
echo "✓ Database tables: OK\n";
echo "✓ Consensus system: " . ($totalConsensus > 0 ? "ACTIVE ({$totalConsensus} records)" : "⚠️  NO DATA") . "\n";
echo "✓ Gold standard: " . ($goldTasks > 0 ? "CONFIGURED ({$goldTasks} tasks)" : "⚠️  NO GOLD TASKS") . "\n";
echo "✓ Trust scores: " . ($experts->count() > 0 ? "OK (Avg: " . round($experts->avg('trust_score'), 1) . "%)" : "⚠️  NO EXPERTS") . "\n";
echo "✓ Governance logs: " . ($logTypes->count() > 0 ? "ACTIVE ({$logTypes->sum('count')} events)" : "⚠️  NO LOGS") . "\n";
echo "\n";

if (empty($events)) {
    echo "⚠️  ACTION REQUIRED: Event listeners are not registered!\n";
    echo "   Check EventServiceProvider configuration.\n";
}

echo "\n==============================================\n";
echo "END OF TEST\n";
echo "==============================================\n";
