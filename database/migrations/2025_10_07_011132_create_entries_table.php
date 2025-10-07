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
        Schema::create('entries', function (Blueprint $table) {
            $table->id();
            $table->foreignId('condominium_id')->constrained('condominiums')->onDelete('cascade');
            $table->foreignId('unit_id')->nullable()->constrained()->onDelete('set null');
            $table->foreignId('registered_by')->constrained('users')->onDelete('cascade'); // porteiro
            $table->enum('type', ['resident', 'visitor', 'service_provider', 'delivery'])->default('visitor');
            $table->string('visitor_name')->nullable();
            $table->string('visitor_document')->nullable();
            $table->string('visitor_phone')->nullable();
            $table->string('vehicle_plate')->nullable();
            $table->enum('entry_type', ['entry', 'exit'])->default('entry');
            $table->timestamp('entry_time')->nullable();
            $table->timestamp('exit_time')->nullable();
            $table->boolean('authorized')->default(false);
            $table->foreignId('authorized_by')->nullable()->constrained('users')->onDelete('set null');
            $table->text('notes')->nullable();
            $table->string('photo')->nullable(); // foto do visitante (opcional)
            $table->timestamps();
            
            $table->index('condominium_id');
            $table->index('unit_id');
            $table->index('entry_time');
            $table->index(['condominium_id', 'entry_type', 'entry_time']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('entries');
    }
};
