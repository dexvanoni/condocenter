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
        Schema::create('packages', function (Blueprint $table) {
            $table->id();
            $table->foreignId('condominium_id')->constrained('condominiums')->onDelete('cascade');
            $table->foreignId('unit_id')->constrained()->onDelete('cascade');
            $table->foreignId('registered_by')->constrained('users')->onDelete('cascade'); // porteiro
            $table->string('sender')->nullable(); // remetente
            $table->string('tracking_code')->nullable();
            $table->text('description')->nullable();
            $table->timestamp('received_at');
            $table->timestamp('collected_at')->nullable();
            $table->foreignId('collected_by')->nullable()->constrained('users')->onDelete('set null'); // morador que retirou
            $table->enum('status', ['pending', 'collected'])->default('pending');
            $table->text('notes')->nullable();
            $table->boolean('notification_sent')->default(false);
            $table->timestamps();
            $table->softDeletes();
            
            $table->index('condominium_id');
            $table->index('unit_id');
            $table->index('status');
            $table->index('received_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('packages');
    }
};
