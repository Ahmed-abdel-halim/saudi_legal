<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('legal_tasks', function (Blueprint $table) {
            $table->string('source_type')->nullable()->after('id')->index(); // 'judgment' or 'client_question'
            $table->unsignedBigInteger('source_id')->nullable()->after('source_type')->index();
        });
    }

    public function down()
    {
        Schema::table('legal_tasks', function (Blueprint $table) {
            $table->dropColumn(['source_type', 'source_id']);
        });
    }
};
