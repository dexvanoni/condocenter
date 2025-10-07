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
        Schema::create('notifications', function (Blueprint $table) {
            $table->id();
            $table->foreignId('condominium_id')->constrained('condominiums')->onDelete('cascade');
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->string('type'); // package_arrived, payment_overdue, reservation_approved, etc
            $table->string('title');
            $table->text('message');
            $table->json('data')->nullable(); // dados extras em JSON
            $table->boolean('is_read')->default(false);
            $table->timestamp('read_at')->nullable();
            $table->enum('channel', ['database', 'email', 'push', 'sms', 'whatsapp'])->default('database');
            $table->boolean('sent')->default(false);
            $table->timestamp('sent_at')->nullable();
            $table->timestamps();
            
            $table->index('condominium_id');
            $table->index('user_id');
            $table->index('type');
            $table->index('is_read');
            $table->index(['user_id', 'is_read', 'created_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('notifications');
    }
};
