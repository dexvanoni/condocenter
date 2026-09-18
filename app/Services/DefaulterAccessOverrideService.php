<?php

namespace App\Services;

use App\Models\DefaulterAccessOverride;
use App\Models\User;

class DefaulterAccessOverrideService
{
    public const MAX_DAYS = 30;

    public function getActiveOverride(User $user): ?DefaulterAccessOverride
    {
        return DefaulterAccessOverride::query()
            ->where('user_id', $user->id)
            ->active()
            ->with('grantedBy')
            ->orderByDesc('expires_at')
            ->first();
    }

    public function grant(User $target, User $grantedBy, int $days, ?string $notes = null): DefaulterAccessOverride
    {
        $this->revokeActive($target);

        return DefaulterAccessOverride::create([
            'user_id' => $target->id,
            'condominium_id' => $target->condominium_id,
            'granted_by' => $grantedBy->id,
            'days' => $days,
            'expires_at' => now()->addDays($days),
            'notes' => $notes,
        ]);
    }

    public function revokeActive(User $user): int
    {
        return DefaulterAccessOverride::query()
            ->where('user_id', $user->id)
            ->active()
            ->update(['expires_at' => now()]);
    }

    public function canGrantTo(User $target, DefaulterRestrictionService $restrictionService): bool
    {
        if ($target->isAdmin() || $target->isSindico()) {
            return false;
        }

        if (!$target->unit_id) {
            return false;
        }

        $condominium = $target->activeCondominium() ?? $target->condominium;

        if (!$restrictionService->isEnabled($condominium)) {
            return false;
        }

        return $restrictionService->hasOverdueCharges($target);
    }
}
