<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('condominium_subscriptions', function (Blueprint $table) {
            $table->timestamp('past_due_at')->nullable()->after('activated_at');
        });
    }

    public function down(): void
    {
        Schema::table('condominium_subscriptions', function (Blueprint $table) {
            $table->dropColumn('past_due_at');
        });
    }
};
