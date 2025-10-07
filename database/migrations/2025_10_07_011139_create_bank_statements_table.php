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
        Schema::create('bank_statements', function (Blueprint $table) {
            $table->id();
            $table->foreignId('condominium_id')->constrained('condominiums')->onDelete('cascade');
            $table->foreignId('uploaded_by')->constrained('users')->onDelete('cascade');
            $table->string('original_filename');
            $table->string('storage_path');
            $table->date('statement_date');
            $table->date('period_start')->nullable();
            $table->date('period_end')->nullable();
            $table->enum('status', ['pending', 'processing', 'reconciled', 'failed'])->default('pending');
            $table->integer('total_transactions')->default(0);
            $table->integer('reconciled_transactions')->default(0);
            $table->json('unmatched_items')->nullable(); // itens não conciliados em JSON
            $table->text('notes')->nullable();
            $table->timestamps();
            $table->softDeletes();
            
            $table->index('condominium_id');
            $table->index('statement_date');
            $table->index('status');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('bank_statements');
    }
};
