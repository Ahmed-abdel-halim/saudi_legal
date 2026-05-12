<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('legal_citations', function (Blueprint $table) {
            $table->id();

            $table->unsignedBigInteger('legal_record_id')->index();
            $table->foreign('legal_record_id')
                  ->references('id')->on('legal_records')
                  ->onDelete('cascade');

            // Citation info (extracted from qa_pairs[].legal_articles)
            $table->string('system_name', 255)->nullable();      // نظام المحاكم التجارية | اسم العقد
            $table->string('article_number', 100)->nullable();   // 19 | الثالثة

            /**
             * citation_source:
             *   'law'      → from an official Saudi law/system   → linked to legal_articles
             *   'contract' → from a contract/agreement clause    → no legal_articles link
             *   'other'    → unknown / mixed reference
             */
            $table->enum('citation_source', ['law', 'contract', 'other'])->default('law')->index();

            // Linked to legal_articles ONLY when citation_source = 'law'
            $table->unsignedBigInteger('legal_article_id')->nullable()->index();
            $table->foreign('legal_article_id')
                  ->references('id')->on('legal_articles')
                  ->onDelete('set null');

            $table->timestamps();

            $table->index(['legal_record_id', 'citation_source']);
            $table->index(['legal_record_id', 'system_name']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('legal_citations');
    }
};
