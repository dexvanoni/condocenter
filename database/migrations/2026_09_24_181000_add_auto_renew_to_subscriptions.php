<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('organization_subscriptions', function (Blueprint $table) {
            $table->boolean('auto_renew')->default(false)->after('contract_ends_at');
        });

        Schema::table('condominium_subscriptions', function (Blueprint $table) {
            $table->boolean('auto_renew')->default(false)->after('contract_ends_at');
        });
    }

    public function down(): void
    {
        Schema::table('organization_subscriptions', function (Blueprint $table) {
            $table->dropColumn('auto_renew');
        });

        Schema::table('condominium_subscriptions', function (Blueprint $table) {
            $table->dropColumn('auto_renew');
        });
    }
};
