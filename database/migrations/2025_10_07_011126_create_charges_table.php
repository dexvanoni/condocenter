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
        Schema::create('charges', function (Blueprint $table) {
            $table->id();
            $table->foreignId('condominium_id')->constrained('condominiums')->onDelete('cascade');
            $table->foreignId('unit_id')->nullable()->constrained()->onDelete('cascade');
            $table->string('title'); // ex: Taxa Condominial Outubro 2025
            $table->text('description')->nullable();
            $table->decimal('amount', 15, 2);
            $table->date('due_date');
            $table->decimal('fine_percentage', 5, 2)->default(2.00); // multa %
            $table->decimal('interest_rate', 5, 2)->default(1.00); // juros % ao mês
            $table->enum('status', ['pending', 'paid', 'overdue', 'cancelled'])->default('pending');
            $table->enum('type', ['regular', 'extra'])->default('regular'); // taxa normal ou extra
            $table->string('asaas_payment_id')->nullable()->unique(); // ID do pagamento no Asaas
            $table->string('boleto_url')->nullable();
            $table->string('pix_code')->nullable();
            $table->string('pix_qrcode')->nullable();
            $table->timestamps();
            $table->softDeletes();
            
            $table->index('condominium_id');
            $table->index('unit_id');
            $table->index('due_date');
            $table->index('status');
            $table->index(['condominium_id', 'status']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('charges');
    }
};
