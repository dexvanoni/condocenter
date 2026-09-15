<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('access_authorizations', function (Blueprint $table) {
            $table->string('visitor_preset_key', 50)->nullable()->after('visitor_name');
            $table->string('access_pin_hash')->nullable()->after('porteiro_notes');
            $table->string('qr_token', 64)->nullable()->unique()->after('access_pin_hash');
        });
    }

    public function down(): void
    {
        Schema::table('access_authorizations', function (Blueprint $table) {
            $table->dropColumn(['visitor_preset_key', 'access_pin_hash', 'qr_token']);
        });
    }
};
