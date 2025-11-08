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
        Schema::create('assembly_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('assembly_id')
                ->constrained('assemblies')
                ->cascadeOnDelete();
            $table->string('title');
            $table->text('description')->nullable();
            $table->json('options')->nullable();
            $table->integer('position')->default(0);
            $table->enum('status', ['pending', 'open', 'closed', 'cancelled'])
                ->default('pending');
            $table->timestamp('opens_at')->nullable();
            $table->timestamp('closes_at')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['assembly_id', 'status']);
            $table->index('position');
        });

        Schema::create('assembly_votes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('assembly_id')
                ->constrained('assemblies')
                ->cascadeOnDelete();
            $table->foreignId('assembly_item_id')
                ->constrained('assembly_items')
                ->cascadeOnDelete();
            $table->foreignId('voter_id')
                ->constrained('users')
                ->cascadeOnDelete();
            $table->foreignId('unit_id')
                ->nullable()
                ->constrained('units')
                ->nullOnDelete();
            $table->string('choice');
            $table->text('encrypted_choice')->nullable();
            $table->text('comment')->nullable();
            $table->timestamp('submitted_at')->nullable();
            $table->timestamps();

            $table->unique(['assembly_item_id', 'voter_id'], 'assembly_item_voter_unique');
            $table->index(['assembly_id', 'assembly_item_id']);
        });

        Schema::create('assembly_attachments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('assembly_id')
                ->constrained('assemblies')
                ->cascadeOnDelete();
            $table->foreignId('uploaded_by')
                ->constrained('users')
                ->cascadeOnDelete();
            $table->string('collection')->default('documents');
            $table->string('disk')->default('public');
            $table->string('path');
            $table->string('original_name');
            $table->string('mime_type')->nullable();
            $table->unsignedBigInteger('size')->default(0);
            $table->timestamps();

            $table->index(['assembly_id', 'collection']);
        });

        Schema::create('assembly_status_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('assembly_id')
                ->constrained('assemblies')
                ->cascadeOnDelete();
            $table->foreignId('changed_by')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();
            $table->enum('from_status', ['scheduled', 'in_progress', 'completed', 'cancelled'])
                ->nullable();
            $table->enum('to_status', ['scheduled', 'in_progress', 'completed', 'cancelled']);
            $table->json('context')->nullable();
            $table->timestamps();

            $table->index(['assembly_id', 'created_at']);
        });

        Schema::create('assembly_allowed_roles', function (Blueprint $table) {
            $table->foreignId('assembly_id')
                ->constrained('assemblies')
                ->cascadeOnDelete();
            $table->foreignId('role_id')
                ->constrained('roles')
                ->cascadeOnDelete();

            $table->primary(['assembly_id', 'role_id'], 'assembly_allowed_roles_pk');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('assembly_allowed_roles');
        Schema::dropIfExists('assembly_status_logs');
        Schema::dropIfExists('assembly_attachments');
        Schema::dropIfExists('assembly_votes');
        Schema::dropIfExists('assembly_items');
    }
};

