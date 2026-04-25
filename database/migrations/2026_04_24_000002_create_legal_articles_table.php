<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('legal_articles', function (Blueprint $table) {
            $table->id();
            $table->string('legislation_id')->index(); // sa-law-xxx
            $table->string('legislation_title'); // اسم النظام
            $table->string('article_title'); // المادة الأولى
            $table->text('content'); // نص المادة
            $table->string('reference_id')->nullable(); // art1
            
            // Full-text search index if supported by DB
            // $table->fullText('content'); 
            
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('legal_articles');
    }
};
