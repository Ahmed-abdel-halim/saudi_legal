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
        Schema::table('public_legal_answers', function (Blueprint $table) {
            // تتبع وقت الترجمة — null = لم تُترجَم بعد، قيمة = تاريخ ترجمة Gemini
            $table->timestamp('translated_at')->nullable()->after('counterpart_slug');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('public_legal_answers', function (Blueprint $table) {
            $table->dropColumn('translated_at');
        });
    }
};
