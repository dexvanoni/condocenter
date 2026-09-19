<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('conversations', function (Blueprint $table) {
            $table->string('syndic_participant_profile', 32)->nullable()->after('channel');
            $table->index(['channel', 'syndic_participant_profile']);
        });
    }

    public function down(): void
    {
        Schema::table('conversations', function (Blueprint $table) {
            $table->dropIndex(['channel', 'syndic_participant_profile']);
            $table->dropColumn('syndic_participant_profile');
        });
    }
};
