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
        Schema::create('ai_responses_v2', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('task_id');
            $table->unsignedBigInteger('expert_id');
            $table->text('corrected_data');
            $table->text('correction_notes')->nullable();
            $table->string('confidence_level', 20)->default('medium'); // low, medium, high, certain
            $table->string('action', 20); // approved, rejected, corrected
            $table->decimal('reward_amount', 8, 2)->default(5.00);
            $table->timestamps();
            
            // Indexes
            $table->index('task_id');
            $table->index('expert_id');
            $table->index('created_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('ai_responses_v2');
    }
};
