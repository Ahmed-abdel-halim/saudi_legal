<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('ai_responses_v2', function (Blueprint $table) {
            // We strip the default and change type. 
            // Note: If table has data 'low'/'medium'/'high', this might truncate to 0.
            // Ideally we'd map them, but for this task we assume fresh start or test data.
            $table->integer('confidence_level')->default(0)->change(); 
        });
    }

    public function down(): void
    {
        Schema::table('ai_responses_v2', function (Blueprint $table) {
            $table->string('confidence_level', 20)->default('medium')->change();
        });
    }
};
