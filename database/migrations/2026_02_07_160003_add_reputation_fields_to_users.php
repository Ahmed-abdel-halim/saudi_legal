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
            // Reputation metrics
            $table->decimal('rating_average', 3, 2)->default(0)->after('role');
            $table->unsignedInteger('rating_count')->default(0)->after('rating_average');
            $table->decimal('completion_rate', 5, 2)->default(100)->after('rating_count');
            
            // Contract tracking metrics
            $table->unsignedInteger('total_contracts')->default(0)->after('completion_rate');
            $table->unsignedInteger('completed_contracts')->default(0)->after('total_contracts');
            $table->unsignedInteger('cancelled_contracts')->default(0)->after('completed_contracts');
            $table->unsignedInteger('disputed_contracts')->default(0)->after('cancelled_contracts');
            
            // Index for expert search/ranking
            $table->index(['rating_average', 'completion_rate']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropIndex(['rating_average', 'completion_rate']);
            
            $table->dropColumn([
                'rating_average',
                'rating_count',
                'completion_rate',
                'total_contracts',
                'completed_contracts',
                'cancelled_contracts',
                'disputed_contracts',
            ]);
        });
    }
};
