<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('condominiums', function (Blueprint $table) {
            $table->boolean('whatsapp_enabled')->default(false)->after('registration_code');
            $table->string('evolution_api_url', 500)->nullable()->after('whatsapp_enabled');
            $table->text('evolution_api_key')->nullable()->after('evolution_api_url');
            $table->string('evolution_instance', 120)->nullable()->after('evolution_api_key');
            $table->json('whatsapp_notify_groups')->nullable()->after('evolution_instance');
        });
    }

    public function down(): void
    {
        Schema::table('condominiums', function (Blueprint $table) {
            $table->dropColumn([
                'whatsapp_enabled',
                'evolution_api_url',
                'evolution_api_key',
                'evolution_instance',
                'whatsapp_notify_groups',
            ]);
        });
    }
};
