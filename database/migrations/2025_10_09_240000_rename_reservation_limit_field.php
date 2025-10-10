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
        Schema::table('spaces', function (Blueprint $table) {
            // Renomear o campo para ser mais claro que é por usuário
            $table->renameColumn('max_reservations_per_month_per_unit', 'max_reservations_per_month_per_user');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('spaces', function (Blueprint $table) {
            $table->renameColumn('max_reservations_per_month_per_user', 'max_reservations_per_month_per_unit');
        });
    }
};
