<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('conversation_recipients', function (Blueprint $table) {
            $table->id();
            $table->foreignId('conversation_id')->constrained('conversations')->onDelete('cascade');
            $table->enum('target_type', ['all', 'role', 'user']);
            $table->string('target_value')->nullable(); // role name ou user_id (string para flexibilidade)
            $table->timestamps();

            $table->index(['conversation_id', 'target_type']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('conversation_recipients');
    }
};


