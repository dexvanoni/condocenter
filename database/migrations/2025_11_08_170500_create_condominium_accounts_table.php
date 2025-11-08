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
        Schema::create('condominium_accounts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('condominium_id')->constrained('condominiums')->onDelete('cascade');
            $table->enum('type', ['income', 'expense']);
            $table->string('source_type')->nullable();
            $table->unsignedBigInteger('source_id')->nullable();
            $table->string('description');
            $table->decimal('amount', 15, 2);
            $table->date('transaction_date');
            $table->enum('payment_method', ['cash', 'pix', 'bank_transfer', 'credit_card', 'debit_card', 'boleto', 'other'])->nullable();
            $table->unsignedTinyInteger('installments_total')->nullable();
            $table->unsignedTinyInteger('installment_number')->nullable();
            $table->string('document_path')->nullable();
            $table->string('captured_image_path')->nullable();
            $table->text('notes')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['condominium_id', 'type', 'transaction_date'], 'condominium_accounts_main_index');
            $table->index(['source_type', 'source_id'], 'condominium_accounts_source_index');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('condominium_accounts');
    }
};

