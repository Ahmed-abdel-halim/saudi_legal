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
        Schema::create('workflow_tasks', function (Blueprint $table) {
            $table->uuid('task_id')->primary();
            $table->string('task_type', 50)->index();
            $table->integer('status_code')->default(2)->index(); // 1 = GREEN, 2 = YELLOW, 3 = RED
            $table->decimal('confidence_score', 4, 2)->default(0.00);
            $table->uuid('hospital_id')->index();
            $table->uuid('insurance_id')->index();
            $table->json('payload');
            $table->json('original_payload')->nullable();
            $table->json('audit_trail')->nullable();
            
            // Human-in-the-Loop tracking (auditor doctor)
            $table->unsignedBigInteger('assigned_doctor_id')->nullable()->index();
            $table->string('doctor_response', 20)->nullable(); // e.g. Approve, Deny, Query
            $table->text('doctor_comment')->nullable();
            $table->decimal('reward_amount', 8, 2)->default(0.00);
            $table->timestamp('doctor_completed_at')->nullable();
            
            $table->timestamps();

            // Foreign keys
            $table->foreign('assigned_doctor_id')->references('id')->on('users')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('workflow_tasks');
    }
};
