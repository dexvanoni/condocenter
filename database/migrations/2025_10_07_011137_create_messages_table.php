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
        Schema::create('messages', function (Blueprint $table) {
            $table->id();
            $table->foreignId('condominium_id')->constrained('condominiums')->onDelete('cascade');
            $table->foreignId('from_user_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('to_user_id')->nullable()->constrained('users')->onDelete('cascade'); // null = para todos
            $table->enum('type', ['announcement', 'sindico_message', 'marketplace_inquiry', 'panic_alert'])->default('announcement');
            $table->string('subject')->nullable();
            $table->text('message');
            $table->enum('priority', ['low', 'normal', 'high', 'urgent'])->default('normal');
            $table->boolean('is_read')->default(false);
            $table->timestamp('read_at')->nullable();
            $table->foreignId('related_item_id')->nullable(); // ID do item relacionado (marketplace, etc)
            $table->string('related_item_type')->nullable(); // tipo do item relacionado
            $table->timestamps();
            $table->softDeletes();
            
            $table->index('condominium_id');
            $table->index('from_user_id');
            $table->index('to_user_id');
            $table->index('type');
            $table->index(['condominium_id', 'type', 'created_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('messages');
    }
};
