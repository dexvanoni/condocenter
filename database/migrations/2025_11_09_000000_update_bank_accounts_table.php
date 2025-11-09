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
        Schema::table('bank_accounts', function (Blueprint $table) {
            $table->string('institution')->nullable()->after('name');
            $table->string('holder_name')->nullable()->after('institution');
            $table->string('document_number')->nullable()->after('holder_name');
            $table->decimal('current_balance', 15, 2)->default(0)->after('pix_key');
            $table->timestamp('balance_updated_at')->nullable()->after('current_balance');
        });

        Schema::create('bank_account_balances', function (Blueprint $table) {
            $table->id();
            $table->foreignId('bank_account_id')->constrained()->onDelete('cascade');
            $table->decimal('balance', 15, 2);
            $table->date('recorded_at');
            $table->string('reference')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->index(['bank_account_id', 'recorded_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('bank_account_balances');

        Schema::table('bank_accounts', function (Blueprint $table) {
            $table->dropColumn([
                'institution',
                'holder_name',
                'document_number',
                'current_balance',
                'balance_updated_at',
            ]);
        });
    }
};

