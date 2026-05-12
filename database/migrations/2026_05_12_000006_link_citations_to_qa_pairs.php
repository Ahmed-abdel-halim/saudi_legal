<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('legal_citations', function (Blueprint $blueprint) {
            $blueprint->unsignedBigInteger('legal_qa_pair_id')->nullable()->after('legal_record_id');
            $blueprint->foreign('legal_qa_pair_id')->references('id')->on('legal_qa_pairs')->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::table('legal_citations', function (Blueprint $blueprint) {
            $blueprint->dropForeign(['legal_qa_pair_id']);
            $blueprint->dropColumn('legal_qa_pair_id');
        });
    }
};
