<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('condominium_landing_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('landing_page_id')->constrained('condominium_landing_pages')->cascadeOnDelete();
            $table->string('type', 32);
            $table->string('title');
            $table->string('subtitle')->nullable();
            $table->text('content')->nullable();
            $table->string('image_path')->nullable();
            $table->json('images')->nullable();
            $table->timestamp('event_starts_at')->nullable();
            $table->timestamp('event_ends_at')->nullable();
            $table->boolean('is_popup')->default(false);
            $table->timestamp('popup_starts_at')->nullable();
            $table->timestamp('popup_ends_at')->nullable();
            $table->boolean('is_featured')->default(false);
            $table->boolean('is_published')->default(true);
            $table->unsignedInteger('sort_order')->default(0);
            $table->json('metadata')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['landing_page_id', 'type', 'is_published'], 'landing_items_page_type_pub_idx');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('condominium_landing_items');
    }
};
