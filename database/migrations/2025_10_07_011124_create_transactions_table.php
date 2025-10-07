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
        Schema::create('transactions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('condominium_id')->constrained('condominiums')->onDelete('cascade');
            $table->foreignId('unit_id')->nullable()->constrained()->onDelete('set null');
            $table->foreignId('user_id')->constrained()->onDelete('cascade'); // quem lançou
            $table->enum('type', ['income', 'expense']); // receita ou despesa
            $table->string('category'); // categoria principal
            $table->string('subcategory')->nullable();
            $table->text('description');
            $table->decimal('amount', 15, 2);
            $table->date('transaction_date');
            $table->date('due_date')->nullable();
            $table->date('paid_date')->nullable();
            $table->enum('status', ['pending', 'paid', 'overdue', 'cancelled'])->default('pending');
            $table->enum('payment_method', ['cash', 'pix', 'bank_transfer', 'credit_card', 'debit_card', 'check', 'boleto', 'other'])->nullable();
            $table->string('store_location')->nullable(); // local da compra
            $table->boolean('is_recurring')->default(false);
            $table->string('recurrence_period')->nullable(); // monthly, yearly
            $table->foreignId('parent_transaction_id')->nullable()->constrained('transactions')->onDelete('set null'); // para lançamentos recorrentes
            $table->text('tags')->nullable(); // JSON para tags
            $table->text('notes')->nullable();
            $table->timestamps();
            $table->softDeletes();
            
            $table->index('condominium_id');
            $table->index('transaction_date');
            $table->index('status');
            $table->index(['condominium_id', 'type', 'status']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('transactions');
    }
};
