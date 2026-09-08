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
        if (!Schema::hasTable('beta_access_requests')) {
            Schema::create('beta_access_requests', function (Blueprint $table) {
                $table->id();
                $table->string('name');
                $table->string('email')->index();
                $table->string('phone')->nullable();
                $table->string('company')->nullable();
                $table->string('organization_type')->nullable(); // law_firm, legaltech, enterprise, developer, individual
                $table->text('use_case')->nullable();
                $table->string('expected_volume')->nullable(); // <10k, 10k-100k, 100k+
                $table->string('tech_stack')->nullable(); // python, php, nodejs, etc.
                $table->text('notes')->nullable();
                $table->string('status')->default('pending'); // pending, approved, waitlist, rejected
                $table->string('api_key_sandbox')->nullable();
                $table->timestamps();
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('beta_access_requests');
    }
};
