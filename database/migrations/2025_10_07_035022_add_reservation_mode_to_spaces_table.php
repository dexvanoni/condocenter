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
            // Modo de reserva: full_day (dia inteiro) ou hourly (por horário)
            $table->enum('reservation_mode', ['full_day', 'hourly'])->default('full_day')->after('type');
            
            // Duração mínima de reserva em horas (para modo hourly)
            $table->integer('min_hours_per_reservation')->default(1)->after('max_hours_per_reservation');
            
            // Intervalo entre reservas em minutos (para limpeza, preparação, etc)
            $table->integer('interval_between_reservations')->default(0)->after('min_hours_per_reservation');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('spaces', function (Blueprint $table) {
            $table->dropColumn(['reservation_mode', 'min_hours_per_reservation', 'interval_between_reservations']);
        });
    }
};
