<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        $now = now();
        $permissions = [
            'view_service_orders',
            'create_service_orders',
            'manage_service_orders',
        ];

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
            'Administrador' => $permissions,
            'Síndico' => $permissions,
            'Morador' => ['view_service_orders', 'create_service_orders'],
            'Porteiro' => ['view_service_orders'],
            'Secretaria' => ['view_service_orders', 'manage_service_orders'],
            'Conselho Fiscal' => ['view_service_orders'],
            'Agregado' => ['view_service_orders', 'create_service_orders'],
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
        $ids = DB::table('permissions')
            ->whereIn('name', ['view_service_orders', 'create_service_orders', 'manage_service_orders'])
            ->pluck('id');

        DB::table('role_has_permissions')->whereIn('permission_id', $ids)->delete();
        DB::table('permissions')->whereIn('id', $ids)->delete();

        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();
    }
};
