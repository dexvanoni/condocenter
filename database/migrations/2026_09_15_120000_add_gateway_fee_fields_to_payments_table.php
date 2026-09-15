<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('payments', function (Blueprint $table) {
            $table->decimal('gross_amount', 15, 2)->nullable()->after('amount_paid');
            $table->decimal('net_amount', 15, 2)->nullable()->after('gross_amount');
            $table->decimal('gateway_fee', 15, 2)->nullable()->after('net_amount');
            $table->unsignedTinyInteger('installment_count')->nullable()->after('gateway_fee');
            $table->string('asaas_billing_type', 32)->nullable()->after('installment_count');
        });
    }

    public function down(): void
    {
        Schema::table('payments', function (Blueprint $table) {
            $table->dropColumn([
                'gross_amount',
                'net_amount',
                'gateway_fee',
                'installment_count',
                'asaas_billing_type',
            ]);
        });
    }
};
