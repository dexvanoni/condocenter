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
        Schema::table('pets', function (Blueprint $table) {
            // Adicionar qr_code (condominium_id já existe)
            if (!Schema::hasColumn('pets', 'qr_code')) {
                $table->string('qr_code')->unique()->after('photo');
                $table->index('qr_code');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('pets', function (Blueprint $table) {
            if (Schema::hasColumn('pets', 'qr_code')) {
                $table->dropIndex(['qr_code']);
                $table->dropColumn('qr_code');
            }
        });
    }
};
