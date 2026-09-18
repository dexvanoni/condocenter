<?php

namespace App\Http\Controllers\Concerns;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\JsonResponse;

trait GuardsTenantResource
{
    protected function belongsToTenant(User $user, Model $model, string $column = 'condominium_id'): bool
    {
        $tenantId = $user->tenantCondominiumId();

        if (!$tenantId) {
            return false;
        }

        return (int) $model->{$column} === (int) $tenantId;
    }

    protected function denyUnlessTenant(User $user, Model $model, string $column = 'condominium_id'): ?JsonResponse
    {
        if (!$this->belongsToTenant($user, $model, $column)) {
            return response()->json(['error' => 'Não autorizado'], 403);
        }

        return null;
    }
}
