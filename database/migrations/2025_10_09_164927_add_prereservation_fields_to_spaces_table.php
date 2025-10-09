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
            // Tipo de aprovação
            $table->enum('approval_type', ['automatic', 'manual', 'prereservation'])
                  ->default('automatic')
                  ->after('requires_approval');
            
            // Configurações de pré-reserva
            $table->integer('prereservation_payment_hours')
                  ->nullable()
                  ->comment('Horas para pagamento da pré-reserva (24, 48, 72)')
                  ->after('approval_type');
            
            $table->boolean('prereservation_auto_cancel')
                  ->default(true)
                  ->comment('Se deve cancelar automaticamente se não pagar')
                  ->after('prereservation_payment_hours');
            
            $table->text('prereservation_instructions')
                  ->nullable()
                  ->comment('Instruções para pagamento da pré-reserva')
                  ->after('prereservation_auto_cancel');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('spaces', function (Blueprint $table) {
            $table->dropColumn([
                'approval_type',
                'prereservation_payment_hours',
                'prereservation_auto_cancel',
                'prereservation_instructions'
            ]);
        });
    }
};