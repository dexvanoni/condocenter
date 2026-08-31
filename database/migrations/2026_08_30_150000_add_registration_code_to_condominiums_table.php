<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('condominiums', function (Blueprint $table) {
            $table->string('registration_code', 20)->nullable()->unique()->after('is_active');
        });

        $condominiums = DB::table('condominiums')->select('id', 'registration_code')->get();

        foreach ($condominiums as $condominium) {
            if ($condominium->registration_code) {
                continue;
            }

            do {
                $code = strtoupper(Str::random(8));
            } while (DB::table('condominiums')->where('registration_code', $code)->exists());

            DB::table('condominiums')
                ->where('id', $condominium->id)
                ->update(['registration_code' => $code]);
        }
    }

    public function down(): void
    {
        Schema::table('condominiums', function (Blueprint $table) {
            $table->dropColumn('registration_code');
        });
    }
};
