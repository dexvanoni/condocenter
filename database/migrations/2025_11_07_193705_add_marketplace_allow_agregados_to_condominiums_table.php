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
        Schema::table('condominiums', function (Blueprint $table) {
            $table->boolean('marketplace_allow_agregados')
                ->default(false)
                ->after('is_active');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('condominiums', function (Blueprint $table) {
            $table->dropColumn('marketplace_allow_agregados');
        });
    }
};
