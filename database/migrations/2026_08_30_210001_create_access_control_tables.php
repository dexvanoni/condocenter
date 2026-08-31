<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('service_providers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('condominium_id')->constrained('condominiums')->cascadeOnDelete();
            $table->foreignId('unit_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('authorized_by')->constrained('users')->cascadeOnDelete();
            $table->enum('scope', ['unit', 'condominium'])->default('unit');
            $table->string('name');
            $table->string('document')->nullable();
            $table->string('phone')->nullable();
            $table->string('company')->nullable();
            $table->string('photo_path')->nullable();
            $table->date('contract_valid_until');
            $table->text('notes')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->index(['condominium_id', 'is_active']);
            $table->index(['condominium_id', 'scope']);
        });

        Schema::create('access_list_groups', function (Blueprint $table) {
            $table->id();
            $table->foreignId('condominium_id')->constrained('condominiums')->cascadeOnDelete();
            $table->foreignId('unit_id')->constrained()->cascadeOnDelete();
            $table->foreignId('authorized_by')->constrained('users')->cascadeOnDelete();
            $table->foreignId('notify_user_id')->constrained('users')->cascadeOnDelete();
            $table->string('title');
            $table->dateTime('scheduled_at');
            $table->dateTime('valid_until')->nullable();
            $table->dateTime('expires_at');
            $table->enum('status', ['active', 'completed', 'cancelled', 'expired'])->default('active');
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->index(['condominium_id', 'status', 'expires_at']);
        });

        Schema::create('access_list_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('access_list_group_id')->constrained()->cascadeOnDelete();
            $table->string('visitor_name');
            $table->enum('status', ['pending', 'entered', 'denied'])->default('pending');
            $table->foreignId('processed_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('processed_at')->nullable();
            $table->text('porteiro_notes')->nullable();
            $table->timestamps();

            $table->index(['access_list_group_id', 'status']);
        });

        Schema::create('access_authorizations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('condominium_id')->constrained('condominiums')->cascadeOnDelete();
            $table->foreignId('unit_id')->constrained()->cascadeOnDelete();
            $table->foreignId('authorized_by')->constrained('users')->cascadeOnDelete();
            $table->foreignId('notify_user_id')->constrained('users')->cascadeOnDelete();
            $table->string('visitor_name');
            $table->enum('authorization_type', ['allow', 'deny'])->default('allow');
            $table->dateTime('scheduled_at');
            $table->dateTime('valid_until')->nullable();
            $table->dateTime('expires_at');
            $table->enum('status', ['pending', 'entered', 'denied', 'expired', 'cancelled'])->default('pending');
            $table->foreignId('processed_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('processed_at')->nullable();
            $table->text('porteiro_notes')->nullable();
            $table->timestamps();

            $table->index(['condominium_id', 'status', 'expires_at']);
            $table->index(['condominium_id', 'scheduled_at']);
        });

        Schema::create('access_movements', function (Blueprint $table) {
            $table->id();
            $table->foreignId('condominium_id')->constrained('condominiums')->cascadeOnDelete();
            $table->foreignId('unit_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('notify_user_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('authorized_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('processed_by')->constrained('users')->cascadeOnDelete();
            $table->enum('source_type', ['authorization', 'list_item', 'service_provider']);
            $table->unsignedBigInteger('source_id');
            $table->enum('action', ['entered', 'denied']);
            $table->string('visitor_name');
            $table->string('reference_label')->nullable();
            $table->json('metadata')->nullable();
            $table->timestamp('occurred_at');
            $table->timestamps();

            $table->index(['condominium_id', 'occurred_at']);
            $table->index(['source_type', 'source_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('access_movements');
        Schema::dropIfExists('access_authorizations');
        Schema::dropIfExists('access_list_items');
        Schema::dropIfExists('access_list_groups');
        Schema::dropIfExists('service_providers');
    }
};
