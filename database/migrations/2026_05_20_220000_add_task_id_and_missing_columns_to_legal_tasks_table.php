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
        Schema::table('legal_tasks', function (Blueprint $table) {
            if (!Schema::hasColumn('legal_tasks', 'task_id')) {
                $table->unsignedBigInteger('task_id')->nullable()->after('id')->comment('معرف مهمة الحوكمة الأساسية');
            }
            if (!Schema::hasColumn('legal_tasks', 'source_type')) {
                $table->string('source_type')->nullable()->after('task_id');
            }
            if (!Schema::hasColumn('legal_tasks', 'source_id')) {
                $table->unsignedBigInteger('source_id')->nullable()->after('source_type');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('legal_tasks', function (Blueprint $table) {
            $table->dropColumn(['task_id', 'source_type', 'source_id']);
        });
    }
};
