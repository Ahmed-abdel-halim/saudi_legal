<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('public_legal_answers', function (Blueprint $table) {
            $table->id();
            $table->string('locale', 5)->default('ar');           // اللغة: ar أو en
            $table->string('slug')->unique();                      // رابط صديق لجوجل
            $table->text('question');                              // السؤال (عربي أو إنجليزي)
            $table->longText('answer');                            // الإجابة الموثقة بالمراجع
            $table->json('citations')->nullable();                 // أرقام المواد، الأنظمة، المراجع
            $table->unsignedBigInteger('views_count')->default(0);// عداد الزيارات من جوجل
            $table->string('source_type')->default('ai_chat');    // مصدر البيانات: ai_chat, manual
            $table->unsignedBigInteger('source_id')->nullable();  // معرف المحادثة الأصلية
            $table->string('counterpart_slug')->nullable();        // slug النسخة المقابلة (ar<->en)
            $table->timestamps();

            // فهرسة للأداء مع حجم 50,000 سجل
            $table->index('locale');
            $table->index('views_count');
            $table->index(['locale', 'slug']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('public_legal_answers');
    }
};
