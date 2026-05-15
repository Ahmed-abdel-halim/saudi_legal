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
            $table->integer('time_spent')->nullable()->comment('Time spent in seconds');
        });

        Schema::table('linguistic_tasks', function (Blueprint $table) {
            $table->integer('time_spent')->nullable()->comment('Time spent in seconds');
        });

        Schema::table('legal_tasks', function (Blueprint $table) {
            $table->integer('time_spent')->nullable()->comment('Time spent in seconds');
        });

        Schema::table('legal_qa_pairs', function (Blueprint $table) {
            $table->integer('time_spent')->nullable()->comment('Time spent in seconds');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('ai_responses_v2', function (Blueprint $table) {
            $table->dropColumn('time_spent');
        });

        Schema::table('linguistic_tasks', function (Blueprint $table) {
            $table->dropColumn('time_spent');
        });

        Schema::table('legal_tasks', function (Blueprint $table) {
            $table->dropColumn('time_spent');
        });

        Schema::table('legal_qa_pairs', function (Blueprint $table) {
            $table->dropColumn('time_spent');
        });
    }
};
