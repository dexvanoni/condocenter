<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('organization_subscriptions', function (Blueprint $table) {
            $table->dropForeign(['organization_id']);
            $table->dropUnique(['organization_id']);
            $table->index('organization_id');
            $table->foreign('organization_id')->references('id')->on('organizations')->cascadeOnDelete();
        });

        Schema::table('condominium_subscriptions', function (Blueprint $table) {
            $table->dropForeign(['condominium_id']);
            $table->dropUnique(['condominium_id']);
            $table->index('condominium_id');
            $table->foreign('condominium_id')->references('id')->on('condominiums')->cascadeOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('organization_subscriptions', function (Blueprint $table) {
            $table->dropForeign(['organization_id']);
            $table->dropIndex(['organization_id']);
            $table->unique('organization_id');
            $table->foreign('organization_id')->references('id')->on('organizations')->cascadeOnDelete();
        });

        Schema::table('condominium_subscriptions', function (Blueprint $table) {
            $table->dropForeign(['condominium_id']);
            $table->dropIndex(['condominium_id']);
            $table->unique('condominium_id');
            $table->foreign('condominium_id')->references('id')->on('condominiums')->cascadeOnDelete();
        });
    }
};
