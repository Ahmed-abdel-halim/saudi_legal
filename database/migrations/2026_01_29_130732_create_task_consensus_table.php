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
        Schema::create('task_consensus', function (Blueprint $table) {
            $table->id();
            $table->foreignId('task_id')->constrained('ai_tasks_v2')->onDelete('cascade');
            $table->json('expert_answers'); // Array of {expert_id, answer, confidence}
            $table->json('final_answer')->nullable();
            $table->decimal('confidence_level', 5, 2)->nullable(); // 0-100
            $table->enum('consensus_type', ['perfect_match', 'majority_vote', 'conflict'])->nullable();
            $table->text('conflict_notes')->nullable();
            $table->foreignId('resolved_by')->nullable()->constrained('users')->onDelete('set null'); // Super admin
            $table->timestamp('resolved_at')->nullable();
            $table->timestamps();
            
            $table->index('task_id');
            $table->index('consensus_type');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('task_consensus');
    }
};
