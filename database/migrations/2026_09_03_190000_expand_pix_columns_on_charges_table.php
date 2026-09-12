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

        DB::statement('ALTER TABLE charges MODIFY pix_code TEXT NULL');
        DB::statement('ALTER TABLE charges MODIFY pix_qrcode MEDIUMTEXT NULL');
    }

    public function down(): void
    {
        if (Schema::getConnection()->getDriverName() !== 'mysql') {
            return;
        }

        DB::statement('ALTER TABLE charges MODIFY pix_code VARCHAR(255) NULL');
        DB::statement('ALTER TABLE charges MODIFY pix_qrcode VARCHAR(255) NULL');
    }
};
