<?php

namespace App\Policies;

use App\Models\OccurrenceBookEntry;
use App\Models\User;

class OccurrenceBookEntryPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->isSindico() && $user->can('manage_occurrence_book');
    }

    public function view(User $user, OccurrenceBookEntry $entry): bool
    {
        if ($user->isSindico() && $user->can('manage_occurrence_book')) {
            return (int) $user->tenantCondominiumId() === (int) $entry->condominium_id;
        }

        return (int) $entry->user_id === (int) $user->id;
    }

    public function create(User $user): bool
    {
        if ($user->isAdmin() && !$user->isSindico()) {
            return false;
        }

        return $user->can('create_occurrence_book');
    }

    public function acknowledge(User $user, OccurrenceBookEntry $entry): bool
    {
        return $user->isSindico()
            && $user->can('manage_occurrence_book')
            && (int) $user->tenantCondominiumId() === (int) $entry->condominium_id
            && !$entry->isAcknowledged();
    }

    public function export(User $user): bool
    {
        return $user->isSindico() && $user->can('export_occurrence_book');
    }

    public function viewPublicBook(User $user): bool
    {
        if ($user->isAdmin() && !$user->isSindico()) {
            return false;
        }

        if (!$user->isMorador() && !$user->isAgregado()) {
            return false;
        }

        $condominiumId = $user->tenantCondominiumId();

        if (!$condominiumId) {
            return false;
        }

        return (bool) \App\Models\Condominium::query()
            ->whereKey($condominiumId)
            ->value('occurrence_book_public_enabled');
    }

    public function viewPublic(User $user, OccurrenceBookEntry $entry): bool
    {
        if (!$this->viewPublicBook($user)) {
            return false;
        }

        return (int) $user->tenantCondominiumId() === (int) $entry->condominium_id;
    }

    public function comment(User $user, OccurrenceBookEntry $entry): bool
    {
        return $user->isSindico()
            && $user->can('manage_occurrence_book')
            && (int) $user->tenantCondominiumId() === (int) $entry->condominium_id;
    }

    public function updateSettings(User $user): bool
    {
        return $user->isSindico() && $user->can('manage_occurrence_book');
    }
}
