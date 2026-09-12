<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('pets', function (Blueprint $table) {
            if (!Schema::hasColumn('pets', 'condominium_id')) {
                $table->foreignId('condominium_id')
                    ->nullable()
                    ->after('id')
                    ->constrained()
                    ->cascadeOnDelete();
            }
        });

        if (Schema::hasColumn('pets', 'condominium_id') && Schema::hasTable('units')) {
            $pets = DB::table('pets')
                ->whereNull('condominium_id')
                ->whereNotNull('unit_id')
                ->get(['id', 'unit_id']);

            foreach ($pets as $pet) {
                $condominiumId = DB::table('units')
                    ->where('id', $pet->unit_id)
                    ->value('condominium_id');

                if ($condominiumId) {
                    DB::table('pets')
                        ->where('id', $pet->id)
                        ->update(['condominium_id' => $condominiumId]);
                }
            }

            if (Schema::getConnection()->getDriverName() === 'mysql') {
                DB::statement('
                    ALTER TABLE pets
                    MODIFY condominium_id BIGINT UNSIGNED NOT NULL
                ');
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasColumn('pets', 'condominium_id')) {
            Schema::table('pets', function (Blueprint $table) {
                $table->dropForeign(['condominium_id']);
                $table->dropColumn('condominium_id');
            });
        }
    }
};

