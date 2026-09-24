<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('organization_subscriptions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('organization_id')->constrained('organizations')->cascadeOnDelete();
            $table->foreignId('subscription_plan_id')->nullable()->constrained('subscription_plans')->nullOnDelete();
            $table->foreignId('financial_responsible_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();

            $table->string('billing_metric', 16)->default('fixed');
            $table->decimal('unit_price', 10, 2)->default(0);
            $table->decimal('user_price', 10, 2)->default(0);
            $table->decimal('fixed_price', 10, 2)->default(0);
            $table->string('billing_cycle', 16)->default('monthly');
            $table->unsignedInteger('trial_days')->default(0);
            $table->string('payment_method', 32)->default('boleto');

            $table->string('financial_cnpj', 18)->nullable();
            $table->string('financial_contact_name')->nullable();
            $table->string('financial_contact_email')->nullable();
            $table->string('financial_contact_phone', 30)->nullable();

            $table->date('contract_starts_at')->nullable();
            $table->date('contract_ends_at')->nullable();
            $table->timestamp('trial_starts_at')->nullable();
            $table->timestamp('trial_ends_at')->nullable();
            $table->date('extended_until')->nullable();

            $table->unsignedInteger('max_condominiums')->nullable();
            $table->unsignedInteger('max_units')->nullable();
            $table->unsignedInteger('max_users')->nullable();
            $table->unsignedInteger('billable_quantity')->default(0);
            $table->decimal('recurring_amount', 10, 2)->default(0);

            $table->string('status', 32)->default('draft');
            $table->string('asaas_customer_id')->nullable();
            $table->string('asaas_subscription_id')->nullable();
            $table->text('admin_notes')->nullable();

            $table->timestamp('activated_at')->nullable();
            $table->timestamp('past_due_at')->nullable();
            $table->timestamp('suspended_at')->nullable();
            $table->timestamp('cancelled_at')->nullable();
            $table->timestamps();

            $table->unique('organization_id');
            $table->index('status');
            $table->index('asaas_customer_id');
            $table->index('asaas_subscription_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('organization_subscriptions');
    }
};
