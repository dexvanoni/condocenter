<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('condominiums', function (Blueprint $table) {
            $table->enum('payment_receiving_mode', ['manual', 'platform'])
                ->default('manual')
                ->after('financial_mode');
            $table->text('asaas_api_key')->nullable()->after('payment_receiving_mode');
            $table->boolean('asaas_sandbox')->default(true)->after('asaas_api_key');
            $table->string('asaas_webhook_email')->nullable()->after('asaas_sandbox');
            $table->text('asaas_webhook_token')->nullable()->after('asaas_webhook_email');
            $table->timestamp('asaas_setup_completed_at')->nullable()->after('asaas_webhook_token');
        });
    }

    public function down(): void
    {
        Schema::table('condominiums', function (Blueprint $table) {
            $table->dropColumn([
                'payment_receiving_mode',
                'asaas_api_key',
                'asaas_sandbox',
                'asaas_webhook_email',
                'asaas_webhook_token',
                'asaas_setup_completed_at',
            ]);
        });
    }
};
