<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->enum('registration_status', ['approved', 'pending', 'rejected'])
                ->default('approved')
                ->after('is_active');
        });

        DB::table('users')
            ->where('is_active', false)
            ->where('senha_temporaria', false)
            ->update(['registration_status' => 'pending']);
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('registration_status');
        });
    }
};
