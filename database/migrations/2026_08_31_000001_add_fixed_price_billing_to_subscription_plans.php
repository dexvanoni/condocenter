<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('subscription_plans', function (Blueprint $table) {
            $table->decimal('fixed_price', 10, 2)->default(0)->after('user_price');
        });

        Schema::table('condominium_subscriptions', function (Blueprint $table) {
            $table->decimal('fixed_price', 10, 2)->default(0)->after('user_price');
        });

        DB::statement("ALTER TABLE subscription_plans MODIFY billing_metric ENUM('unit', 'user', 'fixed') NOT NULL DEFAULT 'unit'");
        DB::statement("ALTER TABLE condominium_subscriptions MODIFY billing_metric ENUM('unit', 'user', 'fixed') NOT NULL DEFAULT 'unit'");
    }

    public function down(): void
    {
        DB::table('subscription_plans')->where('billing_metric', 'fixed')->update(['billing_metric' => 'unit']);
        DB::table('condominium_subscriptions')->where('billing_metric', 'fixed')->update(['billing_metric' => 'unit']);

        DB::statement("ALTER TABLE subscription_plans MODIFY billing_metric ENUM('unit', 'user') NOT NULL DEFAULT 'unit'");
        DB::statement("ALTER TABLE condominium_subscriptions MODIFY billing_metric ENUM('unit', 'user') NOT NULL DEFAULT 'unit'");

        Schema::table('condominium_subscriptions', function (Blueprint $table) {
            $table->dropColumn('fixed_price');
        });

        Schema::table('subscription_plans', function (Blueprint $table) {
            $table->dropColumn('fixed_price');
        });
    }
};
