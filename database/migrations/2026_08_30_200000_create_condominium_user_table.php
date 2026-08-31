<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('condominium_user', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('condominium_id')->constrained('condominiums')->cascadeOnDelete();
            $table->timestamps();

            $table->unique(['user_id', 'condominium_id']);
        });

        $syndicRoleId = DB::table('roles')->where('name', 'Síndico')->value('id');

        if (!$syndicRoleId) {
            return;
        }

        $syndicUserIds = DB::table('model_has_roles')
            ->where('role_id', $syndicRoleId)
            ->where('model_type', 'App\\Models\\User')
            ->pluck('model_id');

        foreach ($syndicUserIds as $userId) {
            $condominiumId = DB::table('users')
                ->where('id', $userId)
                ->value('condominium_id');

            if (!$condominiumId) {
                continue;
            }

            DB::table('condominium_user')->insertOrIgnore([
                'user_id' => $userId,
                'condominium_id' => $condominiumId,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('condominium_user');
    }
};
