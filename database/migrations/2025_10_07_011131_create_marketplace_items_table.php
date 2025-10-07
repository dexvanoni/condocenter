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
        Schema::create('marketplace_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('condominium_id')->constrained('condominiums')->onDelete('cascade');
            $table->foreignId('seller_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('unit_id')->nullable()->constrained()->onDelete('set null');
            $table->string('title');
            $table->text('description');
            $table->decimal('price', 15, 2);
            $table->enum('category', ['products', 'services', 'jobs', 'real_estate', 'vehicles', 'other'])->default('products');
            $table->enum('condition', ['new', 'used', 'refurbished', 'not_applicable'])->default('not_applicable');
            $table->json('images')->nullable(); // até 3 imagens
            $table->enum('status', ['active', 'sold', 'inactive'])->default('active');
            $table->integer('views')->default(0);
            $table->timestamps();
            $table->softDeletes();
            
            $table->index('condominium_id');
            $table->index('seller_id');
            $table->index('category');
            $table->index('status');
            $table->index(['condominium_id', 'status', 'category']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('marketplace_items');
    }
};
