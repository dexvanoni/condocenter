<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('subscription_plans', function (Blueprint $table) {
            $table->string('audience', 32)->default('condominium')->after('slug');
            $table->unsignedInteger('max_condominiums')->nullable()->after('trial_days');
            $table->unsignedInteger('max_units')->nullable()->after('max_condominiums');
            $table->unsignedInteger('max_users')->nullable()->after('max_units');
            $table->json('modules')->nullable()->after('max_users');
        });

        DB::table('subscription_plans')
            ->whereNull('audience')
            ->orWhere('audience', '')
            ->update(['audience' => 'condominium']);
    }

    public function down(): void
    {
        Schema::table('subscription_plans', function (Blueprint $table) {
            $table->dropColumn([
                'audience',
                'max_condominiums',
                'max_units',
                'max_users',
                'modules',
            ]);
        });
    }
};
