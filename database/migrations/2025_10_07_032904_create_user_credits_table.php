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
        Schema::create('user_credits', function (Blueprint $table) {
            $table->id();
            $table->foreignId('condominium_id')->constrained('condominiums')->onDelete('cascade');
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->decimal('amount', 10, 2);
            $table->enum('type', ['refund', 'bonus', 'manual'])->default('refund');
            $table->string('description');
            $table->foreignId('reservation_id')->nullable()->constrained()->onDelete('set null');
            $table->foreignId('charge_id')->nullable()->constrained()->onDelete('set null');
            $table->enum('status', ['available', 'used', 'expired'])->default('available');
            $table->foreignId('used_in_reservation_id')->nullable()->constrained('reservations')->onDelete('set null');
            $table->timestamp('used_at')->nullable();
            $table->timestamp('expires_at')->nullable();
            $table->timestamps();
            $table->softDeletes();
            
            $table->index(['user_id', 'status']);
            $table->index(['condominium_id', 'status']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('user_credits');
    }
};
