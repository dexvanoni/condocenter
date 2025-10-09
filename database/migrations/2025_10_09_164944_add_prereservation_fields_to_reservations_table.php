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
        Schema::table('reservations', function (Blueprint $table) {
            // Status da pré-reserva
            $table->enum('prereservation_status', ['pending_payment', 'paid', 'expired', 'cancelled'])
                  ->nullable()
                  ->comment('Status da pré-reserva: pending_payment, paid, expired, cancelled')
                  ->after('status');
            
            // Data limite para pagamento
            $table->timestamp('payment_deadline')
                  ->nullable()
                  ->comment('Data limite para pagamento da pré-reserva')
                  ->after('prereservation_status');
            
            // Data do pagamento
            $table->timestamp('payment_completed_at')
                  ->nullable()
                  ->comment('Data em que o pagamento foi realizado')
                  ->after('payment_deadline');
            
            // Referência do pagamento
            $table->string('payment_reference')
                  ->nullable()
                  ->comment('Referência do pagamento (PIX, boleto, etc)')
                  ->after('payment_completed_at');
            
            // Valor da pré-reserva
            $table->decimal('prereservation_amount', 10, 2)
                  ->nullable()
                  ->comment('Valor a ser pago para confirmar a pré-reserva')
                  ->after('payment_reference');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('reservations', function (Blueprint $table) {
            $table->dropColumn([
                'prereservation_status',
                'payment_deadline',
                'payment_completed_at',
                'payment_reference',
                'prereservation_amount'
            ]);
        });
    }
};