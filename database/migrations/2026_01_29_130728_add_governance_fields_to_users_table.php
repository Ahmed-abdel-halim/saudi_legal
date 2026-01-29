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
        Schema::table('users', function (Blueprint $table) {
            $table->decimal('trust_score', 5, 2)->default(100.00)->after('email');
            $table->integer('gold_tasks_completed')->default(0)->after('trust_score');
            $table->integer('gold_tasks_failed')->default(0)->after('gold_tasks_completed');
            $table->boolean('is_banned')->default(false)->after('gold_tasks_failed');
            $table->boolean('trust_warning_issued')->default(false)->after('is_banned');
            $table->timestamp('banned_at')->nullable()->after('trust_warning_issued');
            $table->string('ban_reason')->nullable()->after('banned_at');
            
            $table->index('is_banned');
            $table->index('trust_score');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn([
                'trust_score',
                'gold_tasks_completed',
                'gold_tasks_failed',
                'is_banned',
                'trust_warning_issued',
                'banned_at',
                'ban_reason'
            ]);
        });
    }
};
