<?php
require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;

try {
    if (!Schema::hasColumn('legal_tasks', 'tags')) {
        Schema::table('legal_tasks', function (Blueprint $table) {
            $table->json('tags')->nullable()->after('expert_comment');
        });
        echo "Column 'tags' added successfully to 'legal_tasks'.";
    } else {
        echo "Column 'tags' already exists.";
    }
} catch (\Exception $e) {
    echo "Error: " . $e->getMessage();
}
