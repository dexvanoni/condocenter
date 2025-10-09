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
        Schema::create('recurring_reservations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('condominium_id')->constrained('condominiums')->onDelete('cascade');
            $table->foreignId('space_id')->constrained()->onDelete('cascade');
            $table->foreignId('created_by')->constrained('users')->onDelete('cascade');
            $table->string('title'); // Título que aparece no calendário
            $table->text('description')->nullable();
            $table->json('days_of_week'); // [1,3,5] para seg, qua, sex
            $table->time('start_time');
            $table->time('end_time');
            $table->date('start_date');
            $table->date('end_date');
            $table->enum('status', ['active', 'inactive', 'cancelled'])->default('active');
            $table->text('admin_notes')->nullable(); // Notas do administrador
            $table->timestamps();
            
            $table->index(['space_id', 'start_date', 'end_date']);
            $table->index(['status', 'start_date', 'end_date']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('recurring_reservations');
    }
};
