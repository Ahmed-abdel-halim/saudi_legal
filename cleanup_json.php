<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\AiTask;
use App\Models\ClientQuestion;

$count = AiTask::where('original_data', 'LIKE', '{%')->count();
AiTask::where('original_data', 'LIKE', '{%')->delete();
// Also delete from ClientQuestion if they match
ClientQuestion::where('question', 'LIKE', '{%')->delete();

echo "Deleted $count corrupted tasks.\n";
