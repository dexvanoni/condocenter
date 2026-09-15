<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Migration aditiva e reversível. Não modifica registros existentes.
     */
    public function up(): void
    {
        Schema::table('packages', function (Blueprint $table) {
            $table->string('picked_up_by_name')->nullable()->after('collected_by');
            $table->timestamp('pickup_verified_at')->nullable()->after('picked_up_by_name');
        });
    }

    public function down(): void
    {
        Schema::table('packages', function (Blueprint $table) {
            $table->dropColumn(['picked_up_by_name', 'pickup_verified_at']);
        });
    }
};
