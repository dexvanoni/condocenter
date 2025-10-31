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
        Schema::create('internal_regulation_history', function (Blueprint $table) {
            $table->id();
            $table->foreignId('internal_regulation_id')->constrained()->onDelete('cascade');
            $table->foreignId('condominium_id')->constrained('condominiums')->onDelete('cascade');
            $table->text('content')->comment('Conteúdo anterior');
            $table->text('changes_summary')->nullable()->comment('Resumo das alterações realizadas');
            $table->date('assembly_date')->nullable()->comment('Data da assembleia de aprovação');
            $table->string('assembly_details')->nullable()->comment('Detalhes da assembleia');
            $table->integer('version')->comment('Versão do regimento');
            $table->foreignId('updated_by')->nullable()->constrained('users')->onDelete('set null');
            $table->timestamp('changed_at');
            $table->timestamps();
            
            $table->index('internal_regulation_id');
            $table->index('condominium_id');
            $table->index('version');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('internal_regulation_history');
    }
};
