<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasColumn('bank_accounts', 'is_primary')) {
            Schema::table('bank_accounts', function (Blueprint $table) {
                $table->boolean('is_primary')->default(false)->after('active');
            });
        }

        if (!Schema::hasTable('bank_account_routing_rules')) {
            Schema::create('bank_account_routing_rules', function (Blueprint $table) {
                $table->id();
                $table->foreignId('condominium_id')->constrained('condominiums')->cascadeOnDelete();
                $table->string('source_key', 50);
                $table->foreignId('bank_account_id')->constrained('bank_accounts')->cascadeOnDelete();
                $table->timestamps();

                $table->unique(['condominium_id', 'source_key']);
            });
        }

        if (!Schema::hasColumn('transactions', 'bank_account_id')) {
            Schema::table('transactions', function (Blueprint $table) {
                $table->foreignId('bank_account_id')
                    ->nullable()
                    ->after('condominium_id')
                    ->constrained('bank_accounts')
                    ->nullOnDelete();
            });
        }

        if (!Schema::hasColumn('condominium_accounts', 'bank_account_id')) {
            Schema::table('condominium_accounts', function (Blueprint $table) {
                $table->foreignId('bank_account_id')
                    ->nullable()
                    ->after('condominium_id')
                    ->constrained('bank_accounts')
                    ->nullOnDelete();
            });
        }
    }

    public function down(): void
    {
        Schema::table('condominium_accounts', function (Blueprint $table) {
            $table->dropConstrainedForeignId('bank_account_id');
        });

        Schema::table('transactions', function (Blueprint $table) {
            $table->dropConstrainedForeignId('bank_account_id');
        });

        Schema::dropIfExists('bank_account_routing_rules');

        Schema::table('bank_accounts', function (Blueprint $table) {
            $table->dropColumn('is_primary');
        });
    }
};
