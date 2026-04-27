<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('legal_judgments', function (Blueprint $table) {
            $table->id();
            $table->string('case_number', 50)->nullable()->index();
            $table->string('court_name', 150)->nullable();
            $table->string('judgment_date', 50)->nullable();
            $table->longText('case_text');
            $table->string('law_system', 150)->nullable()->index();
            $table->string('source_file', 100)->nullable();
            $table->json('metadata')->nullable();
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('legal_judgments');
    }
};
