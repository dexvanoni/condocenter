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
        Schema::create('spaces', function (Blueprint $table) {
            $table->id();
            $table->foreignId('condominium_id')->constrained('condominiums')->onDelete('cascade');
            $table->string('name'); // ex: Churrasqueira 1, Salão de festas
            $table->text('description')->nullable();
            $table->enum('type', ['party_hall', 'bbq', 'pool', 'sports_court', 'gym', 'meeting_room', 'other'])->default('other');
            $table->integer('capacity')->nullable(); // capacidade de pessoas
            $table->decimal('price_per_hour', 10, 2)->default(0); // preço por hora de uso
            $table->boolean('requires_approval')->default(false); // requer aprovação do síndico
            $table->integer('max_hours_per_reservation')->default(4);
            $table->integer('max_reservations_per_month_per_unit')->default(1);
            $table->time('available_from')->default('08:00:00');
            $table->time('available_until')->default('22:00:00');
            $table->boolean('is_active')->default(true);
            $table->text('rules')->nullable(); // regras de uso
            $table->timestamps();
            $table->softDeletes();
            
            $table->index('condominium_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('spaces');
    }
};
