<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('legal_records', function (Blueprint $table) {
            $table->id();

            // Unique record identifier e.g. RD-LGL-2024-00001
            $table->string('record_id', 50)->unique()->index();

            // Metadata
            $table->string('domain', 50)->default('Legal');           // Legal / Medical / Engineering
            $table->string('sub_domain', 100)->nullable();            // Commercial Law / Labor Law ...
            $table->char('language', 5)->default('ar');
            $table->date('upload_date')->nullable();
            $table->json('tags')->nullable();

            // Context / Source
            $table->string('source_type', 50)->default('Court_Judgment');  // Court_Judgment / Report / Code
            $table->string('source_reference', 300)->nullable()->index();  // رقم القضية
            $table->string('court_type', 150)->nullable();                 // المحكمة التجارية
            $table->longText('full_text');                                  // نص الحكم (مجرد من HTML)

            // Summary (optional)
            $table->text('case_summary')->nullable();

            $table->timestamps();

            $table->index('domain');
            $table->index('sub_domain');
            $table->index('upload_date');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('legal_records');
    }
};
