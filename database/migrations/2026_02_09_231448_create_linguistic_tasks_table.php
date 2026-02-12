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
        Schema::create('linguistic_tasks', function (Blueprint $table) {
            $table->id();
            
            // Task type: linguistic (original) or sentiment (new)
            $table->enum('task_type', ['linguistic', 'sentiment'])->default('linguistic');
            
            // Common fields for all task types
            $table->unsignedBigInteger('expert_id')->nullable();
            $table->enum('status', ['pending', 'in_progress', 'completed', 'skipped'])->default('pending');
            $table->timestamp('assigned_at')->nullable();
            $table->timestamp('completed_at')->nullable();
            
            // Linguistic task fields (original system)
            $table->text('sentence')->nullable();
            $table->text('correct_sentence')->nullable();
            $table->json('errors')->nullable();
            
            // Sentiment analysis fields (new system)
            $table->text('comment_text')->nullable();
            $table->string('proposed_classification', 50)->nullable()
                ->comment('إيجابي, سلبي, محايد');
            $table->string('correct_classification', 50)->nullable()
                ->comment('Expert\'s classification');
            $table->boolean('is_correct')->nullable()
                ->comment('True if expert agrees with proposed classification');
            
            // Domain for expert specialization matching
            $table->string('domain', 100)->nullable()
                ->comment('طب, هندسة, محاماة, تعليم, تقنية, أعمال');
            
            // Metadata
            $table->string('csv_file')->nullable()->comment('Source CSV filename');
            $table->integer('row_number')->nullable()->comment('Row number in CSV');
            
            $table->timestamps();
            
            // Indexes for performance
            $table->index('task_type');
            $table->index('status');
            $table->index('domain');
            $table->index('expert_id');
            $table->index(['task_type', 'domain', 'status']);
            $table->index(['expert_id', 'status']);
            
            // Foreign keys
            $table->foreign('expert_id')->references('id')->on('users')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('linguistic_tasks');
    }
};
