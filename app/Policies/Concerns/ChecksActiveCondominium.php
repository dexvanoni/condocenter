<?php

namespace App\Policies\Concerns;

use App\Models\User;
use App\Services\ActiveCondominiumService;

trait ChecksActiveCondominium
{
    protected function belongsToActiveCondominium(User $user, int $condominiumId): bool
    {
        $activeId = app(ActiveCondominiumService::class)->getActiveCondominiumId($user);

        return $activeId !== null && (int) $condominiumId === (int) $activeId;
    }
}
