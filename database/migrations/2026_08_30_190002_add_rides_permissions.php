<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        $now = now();
        $permissions = ['view_rides', 'create_rides', 'book_rides', 'manage_rides'];

        foreach ($permissions as $name) {
            $exists = DB::table('permissions')->where('name', $name)->where('guard_name', 'web')->exists();
            if (!$exists) {
                DB::table('permissions')->insert([
                    'name' => $name,
                    'guard_name' => 'web',
                    'created_at' => $now,
                    'updated_at' => $now,
                ]);
            }
        }

        $permissionIds = DB::table('permissions')
            ->whereIn('name', $permissions)
            ->where('guard_name', 'web')
            ->pluck('id', 'name');

        $rolePermissions = [
            'Administrador' => ['view_rides', 'create_rides', 'book_rides', 'manage_rides'],
            'Síndico' => ['view_rides', 'create_rides', 'book_rides', 'manage_rides'],
            'Morador' => ['view_rides', 'create_rides', 'book_rides'],
            'Porteiro' => ['view_rides', 'create_rides', 'book_rides'],
            'Secretaria' => ['view_rides', 'create_rides', 'book_rides'],
            'Conselho Fiscal' => ['view_rides', 'create_rides', 'book_rides'],
            'Agregado' => ['view_rides'],
        ];

        foreach ($rolePermissions as $roleName => $perms) {
            $roleId = DB::table('roles')->where('name', $roleName)->where('guard_name', 'web')->value('id');
            if (!$roleId) {
                continue;
            }

            foreach ($perms as $permName) {
                $permId = $permissionIds[$permName] ?? null;
                if (!$permId) {
                    continue;
                }

                $exists = DB::table('role_has_permissions')
                    ->where('role_id', $roleId)
                    ->where('permission_id', $permId)
                    ->exists();

                if (!$exists) {
                    DB::table('role_has_permissions')->insert([
                        'permission_id' => $permId,
                        'role_id' => $roleId,
                    ]);
                }
            }
        }

        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();
    }

    public function down(): void
    {
        $names = ['view_rides', 'create_rides', 'book_rides', 'manage_rides'];
        $ids = DB::table('permissions')->whereIn('name', $names)->pluck('id');
        DB::table('role_has_permissions')->whereIn('permission_id', $ids)->delete();
        DB::table('permissions')->whereIn('id', $ids)->delete();
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();
    }
};
