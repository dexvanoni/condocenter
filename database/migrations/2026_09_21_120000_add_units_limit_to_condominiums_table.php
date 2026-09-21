<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('condominiums', function (Blueprint $table) {
            $table->unsignedInteger('units_limit')
                ->nullable()
                ->after('enabled_modules')
                ->comment('Máximo de unidades que o síndico pode cadastrar; null = sem limite (legado)');
        });
    }

    public function down(): void
    {
        Schema::table('condominiums', function (Blueprint $table) {
            $table->dropColumn('units_limit');
        });
    }
};
