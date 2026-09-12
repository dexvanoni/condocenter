<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('employees', function (Blueprint $table) {
            $table->id();
            $table->foreignId('condominium_id')->constrained('condominiums')->cascadeOnDelete();
            $table->string('name');
            $table->string('cpf', 14)->nullable();
            $table->string('rg', 20)->nullable();
            $table->string('position');
            $table->string('phone', 20)->nullable();
            $table->string('email')->nullable();
            $table->date('admission_date');
            $table->date('termination_date')->nullable();
            $table->decimal('base_salary', 15, 2)->default(0);
            $table->string('work_schedule')->nullable();
            $table->string('status', 20)->default('active');
            $table->text('notes')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['condominium_id', 'status']);
            $table->index(['condominium_id', 'admission_date']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('employees');
    }
};
