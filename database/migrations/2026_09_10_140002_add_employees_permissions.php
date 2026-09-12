<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        $now = now();
        $permissions = [
            'manage_employees',
            'view_employees',
        ];

        foreach ($permissions as $name) {
            if (! DB::table('permissions')->where('name', $name)->where('guard_name', 'web')->exists()) {
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
            'Morador' => ['view_employees'],
            'Conselho Fiscal' => ['view_employees'],
            'Secretaria' => ['view_employees'],
        ];

        foreach ($rolePermissions as $roleName => $perms) {
            $roleId = DB::table('roles')->where('name', $roleName)->where('guard_name', 'web')->value('id');
            if (! $roleId) {
                continue;
            }

            foreach ($perms as $permName) {
                $permId = $permissionIds[$permName] ?? null;
                if (! $permId) {
                    continue;
                }

                if (! DB::table('role_has_permissions')->where('role_id', $roleId)->where('permission_id', $permId)->exists()) {
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
        $ids = DB::table('permissions')->whereIn('name', ['manage_employees', 'view_employees'])->pluck('id');
        DB::table('role_has_permissions')->whereIn('permission_id', $ids)->delete();
        DB::table('permissions')->whereIn('id', $ids)->delete();
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();
    }
};
