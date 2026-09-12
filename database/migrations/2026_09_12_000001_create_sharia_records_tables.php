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
        // 1. Sharia Master Records (Polymorphic JSON Schema aligned)
        Schema::create('sharia_records', function (Blueprint $table) {
            $table->id();
            $table->string('record_id')->unique()->index(); // e.g. SHARIA_FATWA_001
            $table->string('domain')->default('Islamic Sharia & Jurisprudence');
            $table->string('sub_domain')->nullable()->index(); // e.g. عبادات, فقه المعاملات, فقه الأسرة
            $table->string('source_authority')->index(); // e.g. اللجنة الدائمة, ابن باز, ابن عثيمين
            $table->string('verification_status')->default('VERIFIED')->index(); // VERIFIED, PENDING, REJECTED
            $table->string('title')->nullable();
            $table->longText('full_text')->nullable();
            $table->text('summary')->nullable();
            $table->json('core_principles')->nullable();
            $table->json('tags')->nullable();
            $table->string('source_url', 1000)->nullable();
            $table->timestamps();
        });

        // 2. Sharia QA Pairs
        Schema::create('sharia_qa_pairs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('sharia_record_id')->constrained('sharia_records')->onDelete('cascade');
            $table->string('qa_id')->nullable()->index();
            $table->text('question');
            $table->longText('generated_answer')->nullable();
            $table->longText('corrected_answer')->nullable();
            $table->string('review_status')->default('Pending')->index(); // Pending, Approved, Modified, Rejected
            $table->string('traffic_light')->default('green')->index(); // green (🟢), yellow (🟡), red (🔴)
            $table->unsignedBigInteger('reviewer_id')->nullable()->index();
            $table->timestamp('reviewed_at')->nullable();
            $table->integer('time_spent')->nullable(); // in seconds
            $table->boolean('has_custom_citations')->default(false);
            $table->timestamps();
        });

        // 3. Sharia Citations (Quran verses, Hadiths, Scholar references)
        Schema::create('sharia_citations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('sharia_record_id')->nullable()->constrained('sharia_records')->onDelete('cascade');
            $table->foreignId('sharia_qa_pair_id')->nullable()->constrained('sharia_qa_pairs')->onDelete('cascade');
            $table->string('citation_type')->index(); // quran, hadith, scholar
            $table->string('surah_name')->nullable()->index();
            $table->integer('ayah_number')->nullable();
            $table->text('quran_text')->nullable();
            $table->text('hadith_text')->nullable();
            $table->string('hadith_source')->nullable(); // e.g. صحيح البخاري, صحيح مسلم
            $table->string('hadith_number')->nullable();
            $table->string('hadith_grade')->nullable(); // e.g. صحيح, متفق عليه, حسن
            $table->string('scholar_name')->nullable()->index(); // e.g. الإمام ابن باز
            $table->text('scholar_quote')->nullable();
            $table->timestamps();
        });

        // 4. Sharia Core References (Foundational Quran & Hadith repository)
        Schema::create('sharia_core_references', function (Blueprint $table) {
            $table->id();
            $table->string('ref_type')->index(); // quran_surah, hadith_collection, fiqh_encyclopedia
            $table->string('title')->index();
            $table->string('author')->nullable();
            $table->longText('content');
            $table->json('metadata')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('sharia_citations');
        Schema::dropIfExists('sharia_qa_pairs');
        Schema::dropIfExists('sharia_records');
        Schema::dropIfExists('sharia_core_references');
    }
};
