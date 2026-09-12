<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::getConnection()->getDriverName() !== 'mysql') {
            return;
        }

        DB::statement("
            ALTER TABLE messages
            MODIFY COLUMN type ENUM(
                'announcement',
                'sindico_message',
                'syndic_channel_message',
                'direct_message',
                'marketplace_inquiry',
                'panic_alert'
            ) NOT NULL DEFAULT 'announcement'
        ");
    }

    public function down(): void
    {
        if (Schema::getConnection()->getDriverName() !== 'mysql') {
            return;
        }

        DB::table('messages')
            ->whereIn('type', ['syndic_channel_message', 'direct_message'])
            ->update(['type' => 'sindico_message']);

        DB::statement("
            ALTER TABLE messages
            MODIFY COLUMN type ENUM(
                'announcement',
                'sindico_message',
                'marketplace_inquiry',
                'panic_alert'
            ) NOT NULL DEFAULT 'announcement'
        ");
    }
};
