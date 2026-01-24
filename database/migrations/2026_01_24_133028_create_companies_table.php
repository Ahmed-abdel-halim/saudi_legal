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
        Schema::create('companies', function (Blueprint $table) {
            $table->id('company_id');
            $table->string('name');
            $table->string('company_logo')->nullable();
            $table->boolean('is_verified_provider')->default(false);
            $table->string('cr_number')->nullable();
            $table->string('industry')->nullable();
            $table->string('size')->nullable(); // stored as string in legacy (e.g. '1-10')
            $table->boolean('is_requester')->default(false);
            $table->boolean('is_supplier')->default(false);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('companies');
    }
};
