<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('legal_articles', function (Blueprint $table) {
            $table->index('legislation_title');
            $table->index('article_title');
        });
    }

    public function down(): void
    {
        Schema::table('legal_articles', function (Blueprint $table) {
            $table->dropIndex(['legislation_title']);
            $table->dropIndex(['article_title']);
        });
    }
};
