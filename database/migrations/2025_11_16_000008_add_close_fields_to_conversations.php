<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('conversations', function (Blueprint $table) {
            $table->boolean('is_closed')->default(false)->after('is_active');
            $table->timestamp('closed_at')->nullable()->after('is_closed');
            $table->foreignId('closed_by')->nullable()->constrained('users')->nullOnDelete()->after('closed_at');
            $table->index(['is_closed', 'closed_at']);
        });
    }

    public function down(): void
    {
        Schema::table('conversations', function (Blueprint $table) {
            $table->dropIndex(['is_closed', 'closed_at']);
            $table->dropConstrainedForeignId('closed_by');
            $table->dropColumn(['is_closed', 'closed_at']);
        });
    }
};


