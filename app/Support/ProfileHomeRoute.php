<?php

namespace App\Support;

class ProfileHomeRoute
{
    public static function routeNameForRole(string $roleName): string
    {
        return match ($roleName) {
            'Administrador' => 'platform.dashboard',
            default => 'dashboard',
        };
    }

    public static function urlForRole(string $roleName): string
    {
        return route(self::routeNameForRole($roleName));
    }
}
