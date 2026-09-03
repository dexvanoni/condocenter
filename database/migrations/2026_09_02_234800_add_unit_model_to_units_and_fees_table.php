<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('units', function (Blueprint $table) {
            $table->enum('unit_model', ['casa', 'apartamento', 'kitnet', 'quarto', 'flat'])
                ->default('apartamento')
                ->after('type');
            $table->index('unit_model');
        });

        Schema::table('fees', function (Blueprint $table) {
            $table->json('unit_models')->nullable()->after('billing_type');
        });
    }

    public function down(): void
    {
        Schema::table('fees', function (Blueprint $table) {
            $table->dropColumn('unit_models');
        });

        Schema::table('units', function (Blueprint $table) {
            $table->dropIndex(['unit_model']);
            $table->dropColumn('unit_model');
        });
    }
};
