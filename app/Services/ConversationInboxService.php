<?php

namespace App\Services;

use App\Models\Conversation;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;

class ConversationInboxService
{
    public function __construct(
        private readonly SyndicConversationService $syndicConversationService,
        private readonly SyndicConversationStatsService $syndicStats,
        private readonly AnnouncementExpirationService $announcementExpiration,
    ) {
    }

    public function applyChannelScope(Builder $query, User $user, string $channel): void
    {
        if ($channel === Conversation::CHANNEL_PEER) {
            $query->where(function ($sub) {
                $sub->where('channel', Conversation::CHANNEL_PEER)
                    ->orWhere(function ($inner) {
                        $inner->whereNull('channel')->where('type', 'direct');
                    });
            });
        } elseif ($channel === Conversation::CHANNEL_SYNDIC) {
            $query->where('channel', Conversation::CHANNEL_SYNDIC);
            $this->syndicConversationService->applyResidentSyndicScope($query, $user);
        }
    }

    public function applyStatusScope(Builder $query, User $user, ?string $status): void
    {
        if (!$status || $status === 'all') {
            return;
        }

        if ($status === 'closed') {
            $query->where('is_closed', true);

            return;
        }

        if ($status === 'open') {
            $query->where('is_closed', false);

            return;
        }

        if ($status === 'announcement_active') {
            $now = now();
            $query->where('is_active', true)
                ->where('is_closed', false)
                ->where(function ($q) use ($now) {
                    $q->whereNull('expires_at')->orWhere('expires_at', '>', $now);
                });

            return;
        }

        // Filtros derivados exigem pós-processamento na coleção (última mensagem / timestamps).
    }

    /**
     * @return array<string, mixed>
     */
    public function serialize(Conversation $conversation, User $user): array
    {
        $conversation->loadMissing([
            'participants.user:id,name',
            'latestMessage.fromUser:id,name',
            'creator:id,name',
        ]);

        $inboxStatus = $this->resolveInboxStatus($conversation, $user);

        return [
            'id' => $conversation->id,
            'subject' => $conversation->subject,
            'type' => $conversation->type,
            'channel' => $conversation->channel,
            'priority' => $conversation->priority,
            'is_closed' => (bool) $conversation->is_closed,
            'is_active' => (bool) $conversation->is_active,
            'created_at' => $conversation->created_at?->toIso8601String(),
            'updated_at' => $conversation->updated_at?->toIso8601String(),
            'expires_at' => $conversation->expires_at?->toIso8601String(),
            'syndic_participant_profile' => $conversation->syndic_participant_profile,
            'syndic_profile_label' => $this->syndicConversationService->profileLabel(
                $conversation->syndic_participant_profile
            ),
            'participants' => $conversation->participants,
            'inbox_status' => $inboxStatus,
            'inbox_status_label' => $this->statusLabel($inboxStatus, $conversation, $user),
            'title' => $this->buildTitle($conversation, $user),
            'preview' => $this->buildPreview($conversation),
            'last_message_at' => $conversation->latestMessage?->created_at?->toIso8601String()
                ?? $conversation->updated_at?->toIso8601String(),
        ];
    }

    /**
     * @param  Collection<int, Conversation>  $conversations
     * @return Collection<int, array<string, mixed>>
     */
    public function serializeCollection(Collection $conversations, User $user, ?string $statusFilter = null): Collection
    {
        return $conversations
            ->map(fn (Conversation $c) => $this->serialize($c, $user))
            ->filter(function (array $row) use ($statusFilter) {
                if (!$statusFilter || in_array($statusFilter, ['all', 'open', 'closed', 'announcement_active'], true)) {
                    return true;
                }

                return $row['inbox_status'] === $statusFilter;
            })
            ->values();
    }

    public function resolveInboxStatus(Conversation $conversation, User $user): string
    {
        if ($conversation->is_closed || $this->announcementExpiration->isExpired($conversation)) {
            return 'closed';
        }

        if ($conversation->type === 'announcement') {
            return $conversation->is_active ? 'announcement_active' : 'announcement_inactive';
        }

        if ($conversation->isSyndicChannel()) {
            if ($user->isSindico()) {
                return $this->syndicStats->isPendingResponse($conversation)
                    ? 'awaiting_syndic'
                    : ($conversation->syndic_first_response_at ? 'responded' : 'open');
            }

            if (!$conversation->syndic_first_response_at) {
                return ($conversation->resident_first_message_at || $conversation->latestMessage)
                    ? 'awaiting_syndic'
                    : 'open';
            }

            $last = $conversation->latestMessage;
            if (!$last) {
                return 'responded';
            }

            return (int) $last->from_user_id === (int) $user->id ? 'awaiting_other' : 'awaiting_me';
        }

        $last = $conversation->latestMessage;
        if (!$last) {
            return 'open';
        }

        if ((int) $last->from_user_id === (int) $user->id) {
            return 'awaiting_other';
        }

        return 'awaiting_me';
    }

    protected function statusLabel(string $status, Conversation $conversation, User $user): string
    {
        return match ($status) {
            'closed' => 'Encerrada',
            'open' => 'Aberta',
            'awaiting_me' => 'Aguardando você',
            'awaiting_other' => 'Aguardando retorno',
            'awaiting_syndic' => $user->isSindico() ? 'Aguardando síndico' : 'Aguardando o síndico',
            'responded' => 'Respondida',
            'announcement_active' => 'Aviso ativo',
            'announcement_inactive' => 'Aviso inativo',
            default => ucfirst(str_replace('_', ' ', $status)),
        };
    }

    protected function buildTitle(Conversation $conversation, User $user): string
    {
        if ($conversation->subject) {
            return $conversation->subject;
        }

        if ($conversation->type === 'announcement') {
            return 'Aviso do condomínio';
        }

        if ($conversation->isSyndicChannel()) {
            $profile = $this->syndicConversationService->profileLabel($conversation->syndic_participant_profile);
            $base = 'Atendimento sigiloso';

            return $profile ? "{$base} — {$profile}" : $base;
        }

        $other = $conversation->participants
            ->map(fn ($p) => $p->user)
            ->first(fn ($u) => $u && (int) $u->id !== (int) $user->id);

        return $other?->name ?? 'Mensagem direta';
    }

    protected function buildPreview(Conversation $conversation): ?string
    {
        $message = $conversation->latestMessage?->message;

        if (!$message) {
            return null;
        }

        $plain = trim(preg_replace('/\s+/', ' ', strip_tags($message)) ?? '');

        return mb_strlen($plain) > 120 ? mb_substr($plain, 0, 117) . '…' : $plain;
    }
}
