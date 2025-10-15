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
        Schema::create('panic_alerts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('condominium_id')->constrained('condominiums')->onDelete('cascade');
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->string('alert_type')->default('panic'); // panic, emergency, etc.
            $table->string('title');
            $table->text('description');
            $table->string('location')->nullable(); // Localização específica
            $table->string('severity')->default('high'); // low, medium, high, critical
            $table->enum('status', ['active', 'resolved'])->default('active');
            $table->foreignId('resolved_by')->nullable()->constrained('users')->onDelete('set null');
            $table->timestamp('resolved_at')->nullable();
            $table->json('metadata')->nullable(); // Dados extras como coordenadas, fotos, etc.
            $table->timestamps();

            $table->index(['condominium_id', 'status']);
            $table->index(['status', 'created_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('panic_alerts');
    }
};