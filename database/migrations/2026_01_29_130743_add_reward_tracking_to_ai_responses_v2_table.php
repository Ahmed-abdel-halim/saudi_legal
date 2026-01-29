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
            // Add unique constraint to prevent duplicate responses
            $table->unique(['task_id', 'expert_id'], 'unique_expert_task_response');
            
            // Add reward tracking
            $table->enum('reward_status', ['pending', 'partial', 'full', 'denied'])
                ->default('pending')->after('reward_amount');
            $table->decimal('final_reward_amount', 10, 2)->nullable()->after('reward_status');
            
            $table->index('reward_status');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('ai_responses_v2', function (Blueprint $table) {
            $table->dropUnique('unique_expert_task_response');
            $table->dropColumn(['reward_status', 'final_reward_amount']);
        });
    }
};
