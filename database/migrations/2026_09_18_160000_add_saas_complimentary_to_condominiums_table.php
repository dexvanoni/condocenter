<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('condominiums', function (Blueprint $table) {
            $table->boolean('saas_complimentary')->default(false)->after('is_active');
            $table->text('saas_complimentary_notes')->nullable()->after('saas_complimentary');
        });
    }

    public function down(): void
    {
        Schema::table('condominiums', function (Blueprint $table) {
            $table->dropColumn(['saas_complimentary', 'saas_complimentary_notes']);
        });
    }
};
