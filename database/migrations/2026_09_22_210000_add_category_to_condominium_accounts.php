<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('condominium_accounts', function (Blueprint $table) {
            $table->string('category', 40)->nullable()->after('type');
            $table->string('subcategory', 80)->nullable()->after('category');
            $table->index(['condominium_id', 'type', 'category'], 'condominium_accounts_cat_idx');
        });
    }

    public function down(): void
    {
        Schema::table('condominium_accounts', function (Blueprint $table) {
            $table->dropIndex('condominium_accounts_cat_idx');
            $table->dropColumn(['category', 'subcategory']);
        });
    }
};
