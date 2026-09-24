<?php

namespace App\Support;

use App\Models\Condominium;
use App\Models\User;
use App\Services\ActiveCondominiumService;

class TenantContext
{
    public static function id(?User $user = null): ?int
    {
        $user = $user ?? auth()->user();

        return $user?->getActiveCondominiumId();
    }

    public static function requireId(?User $user = null): int
    {
        $id = self::id($user);

        if (!$id) {
            abort(403, 'Selecione um condomínio para continuar.');
        }

        return $id;
    }

    public static function condominium(?User $user = null): ?Condominium
    {
        $user = $user ?? auth()->user();

        if (!$user) {
            return null;
        }

        return app(ActiveCondominiumService::class)->getActiveCondominium($user);
    }

    public static function organizationId(?User $user = null): ?int
    {
        $user = $user ?? auth()->user();

        if (!$user) {
            return null;
        }

        return app(ActiveCondominiumService::class)->getActiveOrganizationId($user);
    }

    public static function assertSame(int $condominiumId, ?User $user = null): void
    {
        if (self::id($user) !== $condominiumId) {
            abort(403, 'Recurso não pertence ao condomínio selecionado.');
        }
    }

    public static function assertOrganization(int $organizationId, ?User $user = null): void
    {
        $user = $user ?? auth()->user();

        if (!$user || !app(ActiveCondominiumService::class)->userCanAccessOrganization($user, $organizationId)) {
            abort(404);
        }
    }

    public static function userCanAccess(int $condominiumId, ?User $user = null): bool
    {
        $user = $user ?? auth()->user();

        if (!$user) {
            return false;
        }

        return app(ActiveCondominiumService::class)->userCanAccessCondominium($user, $condominiumId);
    }
}
