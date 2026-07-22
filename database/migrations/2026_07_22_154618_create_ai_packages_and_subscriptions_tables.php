<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // ─── AI Packages (باقات المساعد القانوني الذكي) ────────────────────────
        Schema::create('ai_packages', function (Blueprint $table) {
            $table->id();
            $table->string('name');                               // اسم الباقة
            $table->text('description')->nullable();             // وصف الباقة
            $table->decimal('price', 10, 2)->default(0.00);      // السعر بالريال
            $table->string('billing_period')->default('monthly'); // monthly | yearly | lifetime
            $table->integer('query_limit')->default(20);         // عدد الاستعلامات (-1 = unlimited)
            $table->boolean('is_unlimited')->default(false);     // بلا حدود؟
            $table->json('features')->nullable();                 // مميزات الباقة (JSON)
            $table->string('badge_text')->nullable();            // نص الشارة (مثل: الأكثر طلباً)
            $table->boolean('is_popular')->default(false);       // هل مميزة؟
            $table->boolean('is_active')->default(true);         // هل مفعلة؟
            $table->boolean('is_free')->default(false);          // هل مجانية؟
            $table->string('stripe_price_id')->nullable();       // معرف السعر على Stripe
            $table->string('color_scheme')->default('emerald');  // لون الكارت: emerald | indigo | gold
            $table->integer('sort_order')->default(0);           // ترتيب العرض
            $table->timestamps();
        });

        // ─── AI Subscriptions (اشتراكات المستخدمين) ────────────────────────────
        Schema::create('ai_subscriptions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('ai_package_id')->constrained()->cascadeOnDelete();
            $table->string('status')->default('pending'); // pending | active | cancelled | expired
            $table->string('stripe_session_id')->nullable();
            $table->string('stripe_payment_intent_id')->nullable();
            $table->string('stripe_subscription_id')->nullable();
            $table->decimal('amount_paid', 10, 2)->default(0.00);
            $table->string('currency')->default('SAR');
            $table->timestamp('starts_at')->nullable();
            $table->timestamp('ends_at')->nullable();
            $table->integer('queries_used')->default(0);         // عدد الاستعلامات المستخدمة
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ai_subscriptions');
        Schema::dropIfExists('ai_packages');
    }
};
