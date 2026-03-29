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
        Schema::create('categories', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('slug')->unique();
            $table->text('description')->nullable();
            $table->timestamps();
        });

        Schema::create('tags', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('slug')->unique();
            $table->timestamps();
        });

        Schema::create('category_ai_task', function (Blueprint $table) {
            $table->id();
            $table->foreignId('category_id')->constrained()->cascadeOnDelete();
            $table->foreignId('ai_task_id')->constrained('ai_tasks_v2')->cascadeOnDelete();
            $table->timestamps();

            $table->unique(['category_id', 'ai_task_id']);
        });

        Schema::create('ai_task_tag', function (Blueprint $table) {
            $table->id();
            $table->foreignId('ai_task_id')->constrained('ai_tasks_v2')->cascadeOnDelete();
            $table->foreignId('tag_id')->constrained()->cascadeOnDelete();
            $table->timestamps();

            $table->unique(['ai_task_id', 'tag_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('ai_task_tag');
        Schema::dropIfExists('category_ai_task');
        Schema::dropIfExists('tags');
        Schema::dropIfExists('categories');
    }
};
