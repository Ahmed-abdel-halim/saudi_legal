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
        Schema::create('task_assignments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('task_id')->constrained('ai_tasks_v2')->onDelete('cascade');
            $table->foreignId('expert_id')->constrained('users')->onDelete('cascade');
            $table->dateTime('assigned_at')->useCurrent();
            $table->dateTime('expires_at');
            $table->timestamps();

            // Enforce unique assignment per task-expert pair
            $table->unique(['task_id', 'expert_id']);
            
            // Allow indexing for quick lookups on active assignments
            $table->index(['expert_id', 'expires_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('task_assignments');
    }
};
