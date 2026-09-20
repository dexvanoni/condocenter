<?php

namespace App\Services;

use App\Http\Controllers\Api\SyndicConversationController;
use App\Models\Conversation;
use App\Models\ConversationParticipant;
use App\Models\Unit;
use App\Models\User;

class SyndicConversationService
{
    public const PROFILE_PROPRIETARIO = 'proprietario';

    public const PROFILE_MORADOR = 'morador';

    public function __construct(
        private readonly UnitOccupancyService $unitOccupancyService,
    ) {
    }

    public function participantProfile(User $user): ?string
    {
        $tenantId = $user->tenantCondominiumId();
        $ownsRental = $tenantId !== null
            && $this->unitOccupancyService->ownedRentalUnits($user, $tenantId)->isNotEmpty();
        $livesInRental = $this->unitOccupancyService->userLivesInRentalUnit($user);

        if ($ownsRental && $livesInRental && $user->hasMultipleRoles()) {
            $active = $user->getActiveRoleName();

            if ($active === 'Proprietário') {
                return self::PROFILE_PROPRIETARIO;
            }

            if ($active === 'Morador') {
                return self::PROFILE_MORADOR;
            }
        }

        if ($ownsRental && $user->isProprietario()) {
            return self::PROFILE_PROPRIETARIO;
        }

        if ($livesInRental && $user->isMorador()) {
            return self::PROFILE_MORADOR;
        }

        return null;
    }

    public function profileLabel(?string $profile): ?string
    {
        return match ($profile) {
            self::PROFILE_PROPRIETARIO => 'Proprietário',
            self::PROFILE_MORADOR => 'Morador (inquilino)',
            default => null,
        };
    }

    public function findOpenConversation(User $user): ?Conversation
    {
        $profile = $this->participantProfile($user);

        $query = Conversation::query()
            ->where('condominium_id', $user->tenantCondominiumId())
            ->where('channel', Conversation::CHANNEL_SYNDIC)
            ->where('is_closed', false)
            ->whereHas('participants', function ($participantQuery) use ($user) {
                $participantQuery->where('user_id', $user->id)->where('role', 'owner');
            });

        if ($profile !== null) {
            $query->where('syndic_participant_profile', $profile);
        } else {
            $query->whereNull('syndic_participant_profile');
        }

        return $query->latest('updated_at')->first();
    }

    public function createConversation(User $user, ?string $subject = null, string $priority = 'normal'): Conversation
    {
        $profile = $this->participantProfile($user);

        $conversation = Conversation::create([
            'condominium_id' => $user->tenantCondominiumId(),
            'created_by' => $user->id,
            'subject' => $subject,
            'type' => 'direct',
            'channel' => Conversation::CHANNEL_SYNDIC,
            'syndic_participant_profile' => $profile,
            'priority' => $priority,
            'is_active' => true,
        ]);

        ConversationParticipant::create([
            'conversation_id' => $conversation->id,
            'user_id' => $user->id,
            'role' => 'owner',
            'joined_at' => now(),
        ]);

        SyndicConversationController::attachSyndicParticipants($conversation);

        return $conversation;
    }

    public function findConversationForResidentOnUnit(User $resident, Unit $unit): ?Conversation
    {
        $profile = $this->unitOccupancyService->syndicProfileForRentalUnit($resident, $unit);

        $query = Conversation::query()
            ->where('condominium_id', $unit->condominium_id)
            ->where('channel', Conversation::CHANNEL_SYNDIC)
            ->whereHas('participants', function ($participantQuery) use ($resident) {
                $participantQuery->where('user_id', $resident->id)->where('role', 'owner');
            });

        if ($profile !== null) {
            $query->where('syndic_participant_profile', $profile);
        } else {
            $query->whereNull('syndic_participant_profile');
        }

        return $query->latest('updated_at')->first();
    }

    public function applyResidentSyndicScope($query, User $user): void
    {
        if ($user->isSindico() || $user->isAdmin()) {
            return;
        }

        $profile = $this->participantProfile($user);

        if ($profile !== null) {
            $query->where('syndic_participant_profile', $profile);
        } else {
            $query->whereNull('syndic_participant_profile');
        }
    }
}
