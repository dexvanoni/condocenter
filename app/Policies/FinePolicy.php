<?php

namespace App\Policies;

use App\Models\Fine;
use App\Models\User;
use App\Policies\Concerns\ChecksActiveCondominium;

class FinePolicy
{
    use ChecksActiveCondominium;

    public function viewAny(User $user): bool
    {
        return $user->can('manage_fines') || $user->can('view_fines');
    }

    public function view(User $user, Fine $fine): bool
    {
        if (!$this->belongsToActiveCondominium($user, (int) $fine->condominium_id)) {
            return false;
        }

        if ($user->can('manage_fines') || ($user->can('view_fines') && $user->hasRole('Conselho Fiscal'))) {
            return true;
        }

        if (!$user->can('view_fines')) {
            return false;
        }

        return $fine->recipients()
            ->where(function ($query) use ($user) {
                $query->where('notified_user_id', $user->id)
                    ->orWhere('user_id', $user->id);
            })
            ->exists();
    }

    public function create(User $user): bool
    {
        return $user->can('manage_fines');
    }

    public function cancel(User $user, Fine $fine): bool
    {
        return $user->can('manage_fines')
            && $this->belongsToActiveCondominium($user, (int) $fine->condominium_id);
    }

    public function export(User $user, Fine $fine): bool
    {
        return $this->view($user, $fine);
    }
}
