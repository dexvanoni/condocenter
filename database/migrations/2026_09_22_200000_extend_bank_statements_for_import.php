<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('bank_statements', function (Blueprint $table) {
            $table->foreignId('bank_account_id')
                ->nullable()
                ->after('condominium_id')
                ->constrained('bank_accounts')
                ->nullOnDelete();
            $table->string('format', 10)->nullable()->after('storage_path');
            $table->string('file_hash', 64)->nullable()->after('format');
            $table->decimal('opening_balance', 15, 2)->nullable()->after('period_end');
            $table->decimal('closing_balance', 15, 2)->nullable()->after('opening_balance');

            $table->index(['bank_account_id', 'file_hash']);
            $table->index(['bank_account_id', 'status']);
        });

        if (Schema::getConnection()->getDriverName() === 'mysql') {
            DB::statement("ALTER TABLE bank_statements MODIFY status ENUM('pending','processing','ready','reconciled','failed') NOT NULL DEFAULT 'pending'");
        }

        Schema::create('bank_statement_lines', function (Blueprint $table) {
            $table->id();
            $table->foreignId('bank_statement_id')->constrained('bank_statements')->cascadeOnDelete();
            $table->foreignId('bank_account_id')->constrained('bank_accounts')->cascadeOnDelete();
            $table->unsignedInteger('line_order')->default(0);
            $table->date('posted_at');
            $table->decimal('amount', 15, 2);
            $table->string('description')->nullable();
            $table->string('fitid')->nullable();
            $table->string('trn_type', 32)->nullable();
            $table->string('status', 32)->default('unmatched');
            $table->string('matched_source_type', 40)->nullable();
            $table->unsignedBigInteger('matched_source_id')->nullable();
            $table->string('suggested_source_type', 40)->nullable();
            $table->unsignedBigInteger('suggested_source_id')->nullable();
            $table->json('suggestion_meta')->nullable();
            $table->timestamps();

            $table->index(['bank_statement_id', 'status']);
            $table->index(['bank_account_id', 'posted_at']);
            $table->index(['matched_source_type', 'matched_source_id']);
            $table->unique(['bank_account_id', 'fitid']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('bank_statement_lines');

        if (Schema::getConnection()->getDriverName() === 'mysql') {
            DB::table('bank_statements')->where('status', 'ready')->update(['status' => 'processing']);
            DB::statement("ALTER TABLE bank_statements MODIFY status ENUM('pending','processing','reconciled','failed') NOT NULL DEFAULT 'pending'");
        }

        Schema::table('bank_statements', function (Blueprint $table) {
            $table->dropIndex(['bank_account_id', 'file_hash']);
            $table->dropIndex(['bank_account_id', 'status']);
            $table->dropConstrainedForeignId('bank_account_id');
            $table->dropColumn([
                'format',
                'file_hash',
                'opening_balance',
                'closing_balance',
            ]);
        });
    }
};
