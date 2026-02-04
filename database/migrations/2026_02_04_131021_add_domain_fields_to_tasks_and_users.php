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
        Schema::table('ai_tasks_v2', function (Blueprint $table) {
            $table->string('task_domain')->nullable()->index()->after('status');
            $table->json('allowed_roles')->nullable()->after('task_domain');
            $table->boolean('allow_all_roles')->default(false)->after('allowed_roles');
        });

        Schema::table('users', function (Blueprint $table) {
            $table->string('expert_domain')->nullable()->index()->after('role');
            $table->string('expert_specialization')->nullable()->after('expert_domain');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('ai_tasks_v2', function (Blueprint $table) {
            $table->dropColumn(['task_domain', 'allowed_roles', 'allow_all_roles']);
        });

        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['expert_domain', 'expert_specialization']);
        });
    }
};
