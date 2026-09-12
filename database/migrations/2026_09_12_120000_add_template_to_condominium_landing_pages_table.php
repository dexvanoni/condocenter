<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('condominium_landing_pages', function (Blueprint $table) {
            $table->string('template', 32)->default('classic')->after('slug');
        });
    }

    public function down(): void
    {
        Schema::table('condominium_landing_pages', function (Blueprint $table) {
            $table->dropColumn('template');
        });
    }
};
