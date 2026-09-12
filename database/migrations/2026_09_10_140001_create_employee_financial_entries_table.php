<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('employee_financial_entries', function (Blueprint $table) {
            $table->id();
            $table->foreignId('condominium_id')->constrained('condominiums')->cascadeOnDelete();
            $table->foreignId('employee_id')->constrained('employees')->cascadeOnDelete();
            $table->string('type', 30);
            $table->string('status', 20)->default('active');
            $table->date('reference_date');
            $table->date('competence_month');
            $table->decimal('amount', 15, 2);
            $table->decimal('hours', 8, 2)->nullable();
            $table->decimal('hourly_rate', 15, 2)->nullable();
            $table->date('vacation_start')->nullable();
            $table->date('vacation_end')->nullable();
            $table->string('description')->nullable();
            $table->json('tax_breakdown')->nullable();
            $table->foreignId('condominium_account_id')->nullable()->constrained('condominium_accounts')->nullOnDelete();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('cancelled_at')->nullable();
            $table->foreignId('cancelled_by')->nullable()->constrained('users')->nullOnDelete();
            $table->text('cancellation_reason')->nullable();
            $table->timestamps();

            $table->index(['condominium_id', 'competence_month']);
            $table->index(['employee_id', 'reference_date']);
            $table->index(['condominium_id', 'type', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('employee_financial_entries');
    }
};
