<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('condominium_subscription_documents', function (Blueprint $table) {
            $table->id();
            $table->foreignId('condominium_subscription_id');
            $table->foreign('condominium_subscription_id', 'cs_docs_subscription_fk')
                ->references('id')
                ->on('condominium_subscriptions')
                ->cascadeOnDelete();
            $table->foreignId('uploaded_by')->nullable()->constrained('users')->nullOnDelete();
            $table->string('title');
            $table->string('file_path');
            $table->string('original_name');
            $table->string('mime_type', 120)->nullable();
            $table->unsignedBigInteger('file_size')->default(0);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('condominium_subscription_documents');
    }
};
