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

        if (Schema::hasColumn('pets', 'condominium_id')) {
            DB::statement('
                UPDATE pets p
                INNER JOIN units u ON u.id = p.unit_id
                SET p.condominium_id = u.condominium_id
                WHERE p.condominium_id IS NULL
            ');

            DB::statement('
                ALTER TABLE pets
                MODIFY condominium_id BIGINT UNSIGNED NOT NULL
            ');
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

