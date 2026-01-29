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
        Schema::create('governance_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('expert_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('task_id')->nullable()->constrained('ai_tasks_v2')->onDelete('set null');
            $table->enum('event_type', [
                'gold_task_passed',
                'gold_task_failed',
                'trust_score_warning',
                'trust_score_updated',
                'expert_banned',
                'expert_unbanned',
                'consensus_conflict'
            ]);
            $table->json('event_data'); // Flexible storage for event details
            $table->decimal('trust_score_before', 5, 2)->nullable();
            $table->decimal('trust_score_after', 5, 2)->nullable();
            $table->timestamps();
            
            $table->index('expert_id');
            $table->index('event_type');
            $table->index('created_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('governance_logs');
    }
};
