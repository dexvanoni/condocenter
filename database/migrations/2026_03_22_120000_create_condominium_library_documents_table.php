<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('condominium_library_documents', function (Blueprint $table) {
            $table->id();
            $table->foreignId('condominium_id')->constrained('condominiums')->cascadeOnDelete();
            $table->foreignId('internal_regulation_id')->nullable()->constrained('internal_regulations')->nullOnDelete();
            $table->string('title');
            $table->text('description')->nullable();
            $table->string('source', 20)->default('upload'); // upload | regulation
            $table->longText('content')->nullable();
            $table->string('file_path')->nullable();
            $table->string('file_mime', 120)->nullable();
            $table->unsignedBigInteger('file_size')->nullable();
            $table->longText('search_text')->nullable();
            $table->unsignedInteger('sort_order')->default(0);
            $table->boolean('is_active')->default(true);
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['condominium_id', 'is_active', 'sort_order']);
            $table->unique(['condominium_id', 'internal_regulation_id'], 'condo_library_regulation_unique');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('condominium_library_documents');
    }
};
