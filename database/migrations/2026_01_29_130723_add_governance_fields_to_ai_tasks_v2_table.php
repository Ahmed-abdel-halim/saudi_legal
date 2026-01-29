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
        Schema::table('ai_tasks_v2', function (Blueprint $table) {
            $table->boolean('is_gold_standard')->default(false)->after('status');
            $table->json('gold_answer')->nullable()->after('is_gold_standard');
            $table->integer('required_responses')->default(3)->after('gold_answer');
            $table->integer('current_responses')->default(0)->after('required_responses');
            $table->enum('consensus_status', ['pending', 'in_progress', 'consensus_reached', 'conflict'])
                ->default('pending')->after('current_responses');
            $table->foreignId('client_id')->nullable()->after('consensus_status')->constrained('users')->onDelete('set null');
            
            $table->index('is_gold_standard');
            $table->index('consensus_status');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('ai_tasks_v2', function (Blueprint $table) {
            $table->dropForeign(['client_id']);
            $table->dropColumn([
                'is_gold_standard',
                'gold_answer',
                'required_responses',
                'current_responses',
                'consensus_status',
                'client_id'
            ]);
        });
    }
};
