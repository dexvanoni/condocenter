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
        Schema::create('agregado_permissions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->foreignId('granted_by')->constrained('users')->onDelete('cascade'); // quem deu a permissão
            $table->string('permission_key'); // chave da permissão (ex: view_spaces, view_marketplace, etc.)
            $table->boolean('is_granted')->default(true);
            $table->text('notes')->nullable(); // observações sobre a permissão
            $table->timestamps();
            
            $table->unique(['user_id', 'permission_key']);
            $table->index(['user_id', 'is_granted']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('agregado_permissions');
    }
};