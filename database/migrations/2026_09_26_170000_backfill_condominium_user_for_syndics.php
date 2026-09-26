<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        $syndicRoleId = DB::table('roles')->where('name', 'Síndico')->value('id');

        if (!$syndicRoleId) {
            return;
        }

        $syndicUserIds = DB::table('model_has_roles')
            ->where('role_id', $syndicRoleId)
            ->where('model_type', 'App\\Models\\User')
            ->pluck('model_id');

        $now = now();

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
                'created_at' => $now,
                'updated_at' => $now,
            ]);
        }
    }

    public function down(): void
    {
        // Dados de vínculo não são removidos no rollback (pivot pode ter sido criado manualmente).
    }
};
