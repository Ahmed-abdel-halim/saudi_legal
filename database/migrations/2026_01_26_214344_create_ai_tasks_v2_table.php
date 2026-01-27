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
        Schema::create('ai_tasks_v2', function (Blueprint $table) {
            $table->id();
            $table->string('task_type', 50)->default('text_correction'); // text_correction, data_validation, etc.
            $table->text('original_data');
            $table->text('ai_suggestion')->nullable();
            $table->string('status', 20)->default('pending'); // pending, completed, skipped
            $table->unsignedBigInteger('assigned_expert_id')->nullable();
            $table->timestamp('assigned_at')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->timestamps();
            
            // Indexes
            $table->index('status');
            $table->index('assigned_expert_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('ai_tasks_v2');
    }
};
