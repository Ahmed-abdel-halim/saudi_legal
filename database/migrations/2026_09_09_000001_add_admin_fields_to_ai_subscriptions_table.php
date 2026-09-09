<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('ai_subscriptions', function (Blueprint $table) {
            $table->boolean('is_unlimited')->default(false)->after('queries_used');
            $table->string('granted_by')->nullable()->after('is_unlimited'); // admin name
            $table->text('notes')->nullable()->after('granted_by'); // admin notes
        });
    }

    public function down(): void
    {
        Schema::table('ai_subscriptions', function (Blueprint $table) {
            $table->dropColumn(['is_unlimited', 'granted_by', 'notes']);
        });
    }
};
