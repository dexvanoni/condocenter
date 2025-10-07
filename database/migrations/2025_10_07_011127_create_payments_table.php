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
        Schema::create('payments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('charge_id')->constrained()->onDelete('cascade');
            $table->foreignId('user_id')->nullable()->constrained()->onDelete('set null'); // quem pagou
            $table->decimal('amount_paid', 15, 2);
            $table->date('payment_date');
            $table->enum('payment_method', ['cash', 'pix', 'bank_transfer', 'credit_card', 'debit_card', 'boleto', 'other']);
            $table->string('asaas_payment_id')->nullable(); // ID do pagamento no Asaas
            $table->string('transaction_id')->nullable(); // ID da transação bancária
            $table->text('notes')->nullable();
            $table->timestamps();
            $table->softDeletes();
            
            $table->index('charge_id');
            $table->index('payment_date');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('payments');
    }
};
