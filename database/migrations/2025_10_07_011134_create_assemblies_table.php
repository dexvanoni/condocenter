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
        Schema::create('assemblies', function (Blueprint $table) {
            $table->id();
            $table->foreignId('condominium_id')->constrained('condominiums')->onDelete('cascade');
            $table->foreignId('created_by')->constrained('users')->onDelete('cascade');
            $table->string('title');
            $table->text('description')->nullable();
            $table->json('agenda')->nullable(); // pauta em JSON
            $table->timestamp('scheduled_at');
            $table->timestamp('started_at')->nullable();
            $table->timestamp('ended_at')->nullable();
            $table->integer('duration_minutes')->default(120);
            $table->enum('status', ['scheduled', 'in_progress', 'completed', 'cancelled'])->default('scheduled');
            $table->enum('voting_type', ['open', 'secret'])->default('open');
            $table->boolean('allow_delegation')->default(false);
            $table->text('minutes')->nullable(); // ata da assembleia
            $table->string('minutes_pdf')->nullable(); // PDF da ata
            $table->timestamps();
            $table->softDeletes();
            
            $table->index('condominium_id');
            $table->index('scheduled_at');
            $table->index('status');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('assemblies');
    }
};
