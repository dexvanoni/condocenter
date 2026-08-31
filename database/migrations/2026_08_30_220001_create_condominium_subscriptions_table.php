<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('condominium_subscriptions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('condominium_id')->constrained('condominiums')->cascadeOnDelete();
            $table->foreignId('financial_responsible_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();

            $table->enum('billing_metric', ['unit', 'user'])->default('unit');
            $table->decimal('unit_price', 10, 2)->default(0);
            $table->decimal('user_price', 10, 2)->default(0);
            $table->enum('billing_cycle', ['monthly', 'quarterly', 'semiannual', 'annual'])->default('monthly');
            $table->unsignedInteger('trial_days')->default(0);

            $table->enum('payment_method', ['boleto', 'credit_card', 'pix_recurring', 'bank_deposit'])->default('boleto');

            $table->string('financial_cnpj', 18)->nullable();
            $table->string('financial_contact_name')->nullable();
            $table->string('financial_contact_email')->nullable();
            $table->string('financial_contact_phone', 30)->nullable();

            $table->date('contract_starts_at')->nullable();
            $table->date('contract_ends_at')->nullable();
            $table->timestamp('trial_starts_at')->nullable();
            $table->timestamp('trial_ends_at')->nullable();
            $table->date('extended_until')->nullable();

            $table->unsignedInteger('billable_quantity')->default(0);
            $table->decimal('recurring_amount', 10, 2)->default(0);

            $table->enum('status', [
                'draft',
                'trial',
                'active',
                'past_due',
                'suspended',
                'cancelled',
                'expired',
            ])->default('draft');

            $table->string('asaas_customer_id')->nullable();
            $table->string('asaas_subscription_id')->nullable();
            $table->text('admin_notes')->nullable();

            $table->timestamp('activated_at')->nullable();
            $table->timestamp('suspended_at')->nullable();
            $table->timestamp('cancelled_at')->nullable();

            $table->timestamps();

            $table->unique('condominium_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('condominium_subscriptions');
    }
};
