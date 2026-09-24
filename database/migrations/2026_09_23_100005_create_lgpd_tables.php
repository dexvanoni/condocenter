<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('terms', function (Blueprint $table) {
            $table->id();
            $table->string('type', 64); // terms_of_use | privacy_policy | media_consent
            $table->string('title');
            $table->boolean('is_required')->default(true);
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->unique('type');
        });

        Schema::create('term_versions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('term_id')->constrained('terms')->cascadeOnDelete();
            $table->string('version', 32);
            $table->string('title');
            $table->longText('content');
            $table->string('content_hash', 64)->nullable();
            $table->timestamp('published_at')->nullable();
            $table->boolean('is_active')->default(false);
            $table->foreignId('published_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->unique(['term_id', 'version']);
        });

        Schema::create('term_acceptances', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('term_id')->constrained('terms')->cascadeOnDelete();
            $table->foreignId('term_version_id')->constrained('term_versions')->cascadeOnDelete();
            $table->timestamp('accepted_at');
            $table->string('ip_address', 45)->nullable();
            $table->text('user_agent')->nullable();
            $table->string('content_hash', 64)->nullable();
            $table->timestamps();

            $table->index(['user_id', 'term_id']);
        });

        Schema::create('privacy_consents', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('term_version_id')->nullable()->constrained('term_versions')->nullOnDelete();
            $table->string('purpose', 64)->default('data_processing');
            $table->string('status', 32); // authorized|denied|revoked
            $table->timestamp('accepted_at')->nullable();
            $table->timestamp('revoked_at')->nullable();
            $table->string('ip_address', 45)->nullable();
            $table->text('user_agent')->nullable();
            $table->timestamps();

            $table->index(['user_id', 'purpose']);
        });

        Schema::create('media_consents', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('term_version_id')->nullable()->constrained('term_versions')->nullOnDelete();
            $table->string('purpose', 64)->default('image_use');
            $table->string('status', 32); // authorized|denied|revoked
            $table->timestamp('accepted_at')->nullable();
            $table->timestamp('revoked_at')->nullable();
            $table->string('ip_address', 45)->nullable();
            $table->text('user_agent')->nullable();
            $table->timestamps();

            $table->index(['user_id', 'purpose']);
        });

        Schema::create('privacy_requests', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->string('type', 32); // export|correction|deletion
            $table->string('status', 32)->default('pending'); // pending|in_progress|completed|rejected
            $table->text('details')->nullable();
            $table->text('admin_notes')->nullable();
            $table->timestamp('processed_at')->nullable();
            $table->foreignId('processed_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->index(['user_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('privacy_requests');
        Schema::dropIfExists('media_consents');
        Schema::dropIfExists('privacy_consents');
        Schema::dropIfExists('term_acceptances');
        Schema::dropIfExists('term_versions');
        Schema::dropIfExists('terms');
    }
};
