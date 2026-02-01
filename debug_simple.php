<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\AiTask;
use Illuminate\Support\Facades\DB;

// 1. Check raw DB records
$raw = DB::table('ai_tasks_v2')->select('id', 'status', 'current_responses', 'required_responses')->limit(5)->get();
echo "Raw DB Count: " . $raw->count() . "\n";
foreach($raw as $r) {
    echo "ID: $r->id | Sts: $r->status | Cur: $r->current_responses | Req: $r->required_responses\n";
}

// 2. Check Eloquent
$eloquent = AiTask::limit(5)->get(); // removed take(5)
echo "Eloquent Count: " . $eloquent->count() . "\n";
