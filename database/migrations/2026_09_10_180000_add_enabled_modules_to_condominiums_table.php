<?php

use App\Support\CondominiumModules;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('condominiums', function (Blueprint $table) {
            $table->json('enabled_modules')->nullable()->after('restrict_defaulters');
        });

        $all = json_encode(CondominiumModules::keys());

        DB::table('condominiums')->whereNull('enabled_modules')->update([
            'enabled_modules' => $all,
        ]);
    }

    public function down(): void
    {
        Schema::table('condominiums', function (Blueprint $table) {
            $table->dropColumn('enabled_modules');
        });
    }
};
