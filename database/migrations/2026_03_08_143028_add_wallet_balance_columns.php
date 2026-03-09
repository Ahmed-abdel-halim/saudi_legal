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
            $table->decimal('wallet_balance', 10, 2)->default(0.00)->after('completion_rate');
        });

        Schema::table('companies', function (Blueprint $table) {
            $table->decimal('wallet_balance', 10, 2)->default(0.00)->after('rating');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('wallet_balance');
        });

        Schema::table('companies', function (Blueprint $table) {
            $table->dropColumn('wallet_balance');
        });
    }
};
