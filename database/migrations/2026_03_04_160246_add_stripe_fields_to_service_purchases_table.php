<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('service_purchases', function (Blueprint $table) {
            $table->string('stripe_session_id')->nullable()->unique()->after('status');
            $table->string('stripe_payment_intent_id')->nullable()->after('stripe_session_id');
            $table->string('payment_status')->nullable()->default('unpaid')->after('stripe_payment_intent_id');
            $table->timestamp('paid_at')->nullable()->after('payment_status');
        });
    }

    public function down(): void
    {
        Schema::table('service_purchases', function (Blueprint $table) {
            $table->dropColumn(['stripe_session_id', 'stripe_payment_intent_id', 'payment_status', 'paid_at']);
        });
    }
};
