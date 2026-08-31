<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('rides', function (Blueprint $table) {
            $table->id();
            $table->foreignId('condominium_id')->constrained('condominiums')->cascadeOnDelete();
            $table->foreignId('driver_id')->constrained('users')->cascadeOnDelete();
            $table->string('destination');
            $table->dateTime('departure_at');
            $table->unsignedTinyInteger('seats_total');
            $table->unsignedTinyInteger('seats_available');
            $table->boolean('has_return')->default(false);
            $table->dateTime('return_at')->nullable();
            $table->boolean('is_free')->default(true);
            $table->decimal('price_per_seat', 10, 2)->nullable();
            $table->text('notes')->nullable();
            $table->enum('status', ['open', 'full', 'cancelled', 'completed'])->default('open');
            $table->foreignId('cancelled_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('cancelled_at')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['condominium_id', 'status', 'departure_at']);
            $table->index('driver_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('rides');
    }
};
