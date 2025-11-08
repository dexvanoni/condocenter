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
        Schema::create('fees', function (Blueprint $table) {
            $table->id();
            $table->foreignId('condominium_id')->constrained('condominiums')->onDelete('cascade');
            $table->foreignId('bank_account_id')->nullable()->constrained('bank_accounts')->nullOnDelete();
            $table->string('name');
            $table->text('description')->nullable();
            $table->decimal('amount', 15, 2);
            $table->enum('recurrence', ['monthly', 'quarterly', 'yearly', 'one_time', 'custom'])->default('monthly');
            $table->unsignedTinyInteger('due_day')->nullable();
            $table->integer('due_offset_days')->default(0);
            $table->enum('billing_type', ['condominium_fee', 'fine', 'extra', 'reservation'])->default('condominium_fee');
            $table->boolean('auto_generate_charges')->default(true);
            $table->boolean('active')->default(true);
            $table->date('starts_at')->nullable();
            $table->date('ends_at')->nullable();
            $table->json('custom_schedule')->nullable();
            $table->json('metadata')->nullable();
            $table->timestamp('last_generated_at')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['condominium_id', 'active']);
            $table->index('recurrence');
            $table->index('billing_type');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('fees');
    }
};

