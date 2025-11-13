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
        Schema::create('bank_account_reconciliations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('condominium_id')->constrained('condominiums')->cascadeOnDelete();
            $table->foreignId('bank_account_id')->constrained()->cascadeOnDelete();
            $table->date('start_date');
            $table->date('end_date');
            $table->decimal('total_income', 15, 2);
            $table->decimal('total_expense', 15, 2);
            $table->decimal('net_amount', 15, 2);
            $table->decimal('previous_balance', 15, 2);
            $table->decimal('resulting_balance', 15, 2);
            $table->dateTime('previous_balance_updated_at')->nullable();
            $table->foreignId('bank_account_balance_id')
                ->nullable()
                ->constrained('bank_account_balances')
                ->nullOnDelete();
            $table->foreignId('created_by')->constrained('users')->cascadeOnDelete();
            $table->timestamps();
        });

        Schema::create('bank_account_reconciliation_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('reconciliation_id')
                ->constrained('bank_account_reconciliations')
                ->cascadeOnDelete();
            $table->string('source_type', 60);
            $table->unsignedBigInteger('source_id')->nullable();
            $table->enum('direction', ['income', 'expense']);
            $table->date('reference_date');
            $table->decimal('amount', 15, 2);
            $table->string('label', 120)->nullable();
            $table->timestamps();
        });

        Schema::table('transactions', function (Blueprint $table) {
            $table->foreignId('reconciliation_id')
                ->nullable()
                ->after('notes')
                ->constrained('bank_account_reconciliations')
                ->nullOnDelete();
        });

        Schema::table('condominium_accounts', function (Blueprint $table) {
            $table->foreignId('reconciliation_id')
                ->nullable()
                ->after('created_by')
                ->constrained('bank_account_reconciliations')
                ->nullOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('condominium_accounts', function (Blueprint $table) {
            $table->dropConstrainedForeignId('reconciliation_id');
        });

        Schema::table('transactions', function (Blueprint $table) {
            $table->dropConstrainedForeignId('reconciliation_id');
        });

        Schema::dropIfExists('bank_account_reconciliation_items');
        Schema::dropIfExists('bank_account_reconciliations');
    }
};

