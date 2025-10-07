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
        Schema::create('units', function (Blueprint $table) {
            $table->id();
            $table->foreignId('condominium_id')->constrained('condominiums')->onDelete('cascade');
            $table->string('number');
            $table->string('block')->nullable();
            $table->enum('type', ['residential', 'commercial'])->default('residential');
            $table->decimal('ideal_fraction', 8, 4)->default(1.0000); // fração ideal
            $table->decimal('area', 10, 2)->nullable(); // área em m²
            $table->integer('floor')->nullable();
            $table->text('notes')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            $table->softDeletes();
            
            $table->unique(['condominium_id', 'number', 'block']);
            $table->index('condominium_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('units');
    }
};
