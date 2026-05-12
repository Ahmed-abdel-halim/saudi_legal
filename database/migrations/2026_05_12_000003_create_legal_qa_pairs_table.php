<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('legal_qa_pairs', function (Blueprint $table) {
            $table->id();

            $table->unsignedBigInteger('legal_record_id')->index();
            $table->foreign('legal_record_id')
                  ->references('id')->on('legal_records')
                  ->onDelete('cascade');

            // Q&A content
            $table->string('qa_id', 20)->nullable();        // Q-001, Q-002 ...
            $table->text('question');
            $table->longText('generated_answer');

            // human_review block
            $table->enum('review_status', ['Pending', 'Approved', 'Rejected', 'Modified'])
                  ->default('Pending');
            $table->unsignedBigInteger('reviewer_id')->nullable()->index();
            $table->foreign('reviewer_id')
                  ->references('id')->on('users')
                  ->onDelete('set null');
            $table->longText('corrected_answer')->nullable();
            $table->timestamp('reviewed_at')->nullable();

            $table->timestamps();

            $table->index('review_status');
            $table->index(['legal_record_id', 'qa_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('legal_qa_pairs');
    }
};
