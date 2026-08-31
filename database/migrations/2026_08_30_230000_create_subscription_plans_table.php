<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('subscription_plans', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('slug')->unique();
            $table->text('description')->nullable();
            $table->enum('billing_metric', ['unit', 'user'])->default('unit');
            $table->decimal('unit_price', 10, 2)->default(0);
            $table->decimal('user_price', 10, 2)->default(0);
            $table->enum('billing_cycle', ['monthly', 'quarterly', 'semiannual', 'annual'])->default('monthly');
            $table->unsignedInteger('trial_days')->default(0);
            $table->enum('payment_method', ['boleto', 'credit_card', 'pix_recurring', 'bank_deposit'])->default('boleto');
            $table->boolean('is_active')->default(true);
            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamps();
        });

        Schema::table('condominium_subscriptions', function (Blueprint $table) {
            $table->foreignId('subscription_plan_id')->nullable()->after('condominium_id');
            $table->foreign('subscription_plan_id', 'cs_plan_fk')
                ->references('id')
                ->on('subscription_plans')
                ->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('condominium_subscriptions', function (Blueprint $table) {
            $table->dropForeign('cs_plan_fk');
            $table->dropColumn('subscription_plan_id');
        });

        Schema::dropIfExists('subscription_plans');
    }
};
