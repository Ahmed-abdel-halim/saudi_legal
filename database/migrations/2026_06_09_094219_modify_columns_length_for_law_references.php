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
        Schema::table('legal_citations', function (Blueprint $table) {
            $table->text('article_number')->nullable()->change();
        });

        Schema::table('legal_tasks', function (Blueprint $table) {
            $table->text('correct_law_system')->nullable()->comment('النظام القانوني المقترح للتصحيح')->change();
            $table->text('correct_law_article')->nullable()->comment('المادة القانونية المقترحة للتصحيح')->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('legal_citations', function (Blueprint $table) {
            $table->string('article_number', 100)->nullable()->change();
        });

        Schema::table('legal_tasks', function (Blueprint $table) {
            $table->string('correct_law_system')->nullable()->comment('النظام القانوني المقترح للتصحيح')->change();
            $table->string('correct_law_article')->nullable()->comment('المادة القانونية المقترحة للتصحيح')->change();
        });
    }
};
