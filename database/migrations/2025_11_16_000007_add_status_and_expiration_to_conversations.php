<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('conversations', function (Blueprint $table) {
            $table->boolean('is_active')->default(true)->after('priority');
            $table->timestamp('expires_at')->nullable()->after('is_active');
            $table->index(['is_active', 'expires_at']);
        });
    }

    public function down(): void
    {
        Schema::table('conversations', function (Blueprint $table) {
            $table->dropIndex(['is_active', 'expires_at']);
            $table->dropColumn(['is_active', 'expires_at']);
        });
    }
};


