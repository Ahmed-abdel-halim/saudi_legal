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
        Schema::create('legal_tasks', function (Blueprint $table) {
            $table->id();
            
            // نوع المهمة: تحقق (verification) أو استشارة (consultation)
            $table->enum('task_type', ['verification', 'consultation'])->default('verification');
            
            // بيانات الخبير والحالة
            $table->unsignedBigInteger('expert_id')->nullable();
            $table->enum('status', ['pending', 'in_progress', 'completed', 'skipped'])->default('pending');
            $table->timestamp('assigned_at')->nullable();
            $table->timestamp('completed_at')->nullable();
            
            // المحتوى القانوني
            $table->text('question')->nullable()->comment('السؤال القانوني');
            $table->text('proposed_answer')->nullable()->comment('إجابة الذكاء الاصطناعي');
            $table->text('correct_answer')->nullable()->comment('إجابة المحامي (في حال التعديل)');
            
            // المراجع القانونية (للظهور في مستعرض المستندات)
            $table->text('law_article_text')->nullable()->comment('نص المادة القانونية المستشهد بها');
            $table->string('law_article_number', 50)->nullable()->comment('رقم المادة');
            $table->string('law_system_name', 255)->nullable()->comment('اسم النظام (مثل: نظام الإثبات)');
            
            // السوابق القضائية (من ملف 5000 قضية)
            $table->string('case_reference', 100)->nullable()->comment('رقم القضية أو المرجع');
            $table->text('case_text')->nullable()->comment('نص الحكم القضائي');
            
            // التقييم (RLHF)
            $table->boolean('is_correct')->nullable()->comment('هل إجابة الـ AI صحيحة؟');
            $table->text('expert_comment')->nullable()->comment('ملاحظات المحامي');
            
            // بيانات إضافية
            $table->string('domain', 100)->default('محاماة');
            $table->string('source_file')->nullable()->comment('الملف المصدري (GitHub/JSONL)');
            $table->integer('row_number')->nullable();
            
            $table->timestamps();
            $table->softDeletes();

            // Indexes
            $table->index('task_type');
            $table->index('status');
            $table->index('expert_id');
            $table->index('law_system_name');
            $table->index('case_reference');
            
            // Foreign keys
            $table->foreign('expert_id')->references('id')->on('users')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('legal_tasks');
    }
};
