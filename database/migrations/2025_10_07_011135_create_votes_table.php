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
        Schema::create('votes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('assembly_id')->constrained()->onDelete('cascade');
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->foreignId('unit_id')->constrained()->onDelete('cascade');
            $table->string('agenda_item'); // item da pauta que está sendo votado
            $table->enum('vote', ['yes', 'no', 'abstain']); // sim, não, abstenção
            $table->text('encrypted_vote')->nullable(); // para votação secreta
            $table->foreignId('delegated_from')->nullable()->constrained('users')->onDelete('set null'); // se foi voto delegado
            $table->timestamps();
            
            $table->index('assembly_id');
            $table->index('user_id');
            $table->unique(['assembly_id', 'user_id', 'agenda_item']); // um voto por item por usuário
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('votes');
    }
};
