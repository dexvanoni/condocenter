<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('monthly_closings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('condominium_id')->constrained('condominiums')->cascadeOnDelete();
            $table->date('reference_month');
            $table->string('status', 20)->default('in_progress');
            $table->timestamp('completed_at')->nullable();
            $table->foreignId('completed_by')->nullable()->constrained('users')->nullOnDelete();
            $table->text('closing_notes')->nullable();
            $table->timestamps();

            $table->unique(['condominium_id', 'reference_month']);
            $table->index(['condominium_id', 'status']);
        });

        Schema::create('monthly_closing_step_confirmations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('monthly_closing_id')->constrained('monthly_closings')->cascadeOnDelete();
            $table->string('step_key', 50);
            $table->text('notes')->nullable();
            $table->foreignId('confirmed_by')->constrained('users')->cascadeOnDelete();
            $table->timestamp('confirmed_at');
            $table->timestamps();

            $table->unique(['monthly_closing_id', 'step_key'], 'mc_step_confirmations_closing_step_unique');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('monthly_closing_step_confirmations');
        Schema::dropIfExists('monthly_closings');
    }
};
