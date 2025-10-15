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
        Schema::table('users', function (Blueprint $table) {
            $table->text('fcm_token')->nullable()->after('remember_token');
            $table->boolean('fcm_enabled')->default(true)->after('fcm_token');
            $table->json('fcm_topics')->nullable()->after('fcm_enabled');
            $table->timestamp('fcm_token_updated_at')->nullable()->after('fcm_topics');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['fcm_token', 'fcm_enabled', 'fcm_topics', 'fcm_token_updated_at']);
        });
    }
};
