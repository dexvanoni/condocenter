<?php

namespace App\Http\Controllers\Concerns;

use App\Models\Condominium;
use App\Models\User;
use App\Services\ActiveCondominiumService;

trait ResolvesActiveCondominium
{
    protected function activeCondominiumService(): ActiveCondominiumService
    {
        return app(ActiveCondominiumService::class);
    }

    protected function activeCondominiumId(User $user): int
    {
        return \App\Support\TenantContext::requireId($user);
    }

    protected function activeCondominium(User $user): Condominium
    {
        return \App\Support\TenantContext::condominium($user)
            ?? abort(403, 'Selecione um condomínio para continuar.');
    }

    protected function ensureResourceBelongsToActiveCondominium(User $user, int $resourceCondominiumId): void
    {
        \App\Support\TenantContext::assertSame($resourceCondominiumId, $user);
    }
}
