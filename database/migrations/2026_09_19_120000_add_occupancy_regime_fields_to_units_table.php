<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('units', function (Blueprint $table) {
            $table->string('occupancy_regime', 32)->default('particular')->after('unit_model');
            $table->string('rental_period', 32)->nullable()->after('occupancy_regime');
            $table->string('public_property_kind', 32)->nullable()->after('rental_period');
            $table->foreignId('owner_user_id')->nullable()->after('public_property_kind')->constrained('users')->nullOnDelete();

            $table->index('occupancy_regime');
            $table->index('owner_user_id');
        });
    }

    public function down(): void
    {
        Schema::table('units', function (Blueprint $table) {
            $table->dropForeign(['owner_user_id']);
            $table->dropColumn([
                'occupancy_regime',
                'rental_period',
                'public_property_kind',
                'owner_user_id',
            ]);
        });
    }
};
