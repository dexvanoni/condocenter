<?php

namespace App\Services;

use App\Models\Conversation;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;

class ConversationAuthorization
{
    public static function canAccess(User $user, Conversation $conversation): bool
    {
        if ($conversation->condominium_id !== $user->tenantCondominiumId()) {
            return false;
        }

        if ($conversation->type === 'announcement') {
            return true;
        }

        if ($conversation->isSyndicChannel()) {
            if ($user->isAdmin() && !$user->isSindico()) {
                return false;
            }

            if ($user->isSindico()) {
                return true;
            }

            return $conversation->participants()->where('user_id', $user->id)->exists();
        }

        if ($user->isSindico() || $user->isAdmin()) {
            return true;
        }

        return $conversation->participants()->where('user_id', $user->id)->exists();
    }

    public static function canManageSyndicChannel(User $user): bool
    {
        return $user->isSindico();
    }

    public static function canAddParticipant(User $user, Conversation $conversation): bool
    {
        if (!$conversation->isSyndicChannel()) {
            if ($user->isSindico() || $user->isAdmin()) {
                return true;
            }

            return $conversation->participants()
                ->where('user_id', $user->id)
                ->where('role', 'owner')
                ->exists();
        }

        return $user->isSindico();
    }

    public static function applyVisibilityScope(Builder $query, User $user): Builder
    {
        $query->where('condominium_id', $user->tenantCondominiumId());

        if ($user->isAdmin() && !$user->isSindico()) {
            $query->where(function (Builder $sub) {
                $sub->whereNull('channel')
                    ->orWhere('channel', '!=', Conversation::CHANNEL_SYNDIC);
            });
        } elseif (!$user->isSindico() && !$user->isAdmin()) {
            $query->where(function (Builder $sub) use ($user) {
                $sub->whereHas('participants', fn (Builder $p) => $p->where('user_id', $user->id))
                    ->orWhere('type', 'announcement');
            });
        }

        return $query;
    }

    public static function denyIfUnauthorized(User $user, Conversation $conversation): ?array
    {
        if (!self::canAccess($user, $conversation)) {
            return ['error' => 'Não autorizado', 'status' => 403];
        }

        return null;
    }
}
