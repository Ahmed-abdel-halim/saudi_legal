<?php

require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;

$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

try {
    echo "Starting manual migration...\n";

    if (!Schema::hasColumn('ai_responses_v2', 'time_spent')) {
        Schema::table('ai_responses_v2', function (Blueprint $table) {
            $table->integer('time_spent')->nullable()->comment('Time spent in seconds');
        });
        echo "Added time_spent to ai_responses_v2\n";
    }

    if (!Schema::hasColumn('linguistic_tasks', 'time_spent')) {
        Schema::table('linguistic_tasks', function (Blueprint $table) {
            $table->integer('time_spent')->nullable()->comment('Time spent in seconds');
        });
        echo "Added time_spent to linguistic_tasks\n";
    }

    if (!Schema::hasColumn('legal_tasks', 'time_spent')) {
        Schema::table('legal_tasks', function (Blueprint $table) {
            $table->integer('time_spent')->nullable()->comment('Time spent in seconds');
        });
        echo "Added time_spent to legal_tasks\n";
    }

    if (!Schema::hasColumn('legal_qa_pairs', 'time_spent')) {
        Schema::table('legal_qa_pairs', function (Blueprint $table) {
            $table->integer('time_spent')->nullable()->comment('Time spent in seconds');
        });
        echo "Added time_spent to legal_qa_pairs\n";
    }

    echo "Migration completed successfully.\n";
} catch (\Exception $e) {
    echo "Migration failed: " . $e->getMessage() . "\n";
}
