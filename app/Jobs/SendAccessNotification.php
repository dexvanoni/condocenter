<?php

namespace App\Jobs;

use App\Models\AccessMovement;
use App\Models\Notification;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Log;

class SendAccessNotification implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(public AccessMovement $movement) {}

    public function handle(): void
    {
        $movement = $this->movement->fresh(['notifyUser', 'authorizedBy', 'unit.morador']);

        if (!$movement) {
            return;
        }

        $entered = $movement->action === AccessMovement::ACTION_ENTERED;
        $title = $entered ? 'Visitante entrou' : 'Acesso negado na portaria';
        $message = $this->buildMessage($movement, $entered);
        $type = $entered ? 'access_entered' : 'access_denied';

        foreach ($this->resolveRecipients($movement) as $recipient) {
            $this->storeNotification($movement, $recipient, $type, $title, $message, $entered);
        }
    }

    protected function resolveRecipients(AccessMovement $movement): Collection
    {
        $recipients = collect();

        if ($movement->notifyUser) {
            $recipients->push($movement->notifyUser);
        }

        if (
            $movement->authorizedBy
            && !$recipients->contains('id', $movement->authorized_by)
            && $this->isResidentMovementRecipient($movement->authorizedBy)
        ) {
            $recipients->push($movement->authorizedBy);
        }

        $unitMorador = $movement->unit?->morador;

        if (
            $unitMorador
            && !$recipients->contains('id', $unitMorador->id)
            && $this->isResidentMovementRecipient($unitMorador)
        ) {
            $recipients->push($unitMorador);
        }

        return $recipients->unique('id')->values();
    }

    /**
     * Alertas de movimentação na portaria são entre morador/agregado e portaria.
     * Síndico/admin só recebem quando são o morador notificado (liberação própria).
     */
    protected function isResidentMovementRecipient(User $user): bool
    {
        return !$user->isSindico() && !$user->isAdmin();
    }

    protected function storeNotification(
        AccessMovement $movement,
        User $recipient,
        string $type,
        string $title,
        string $message,
        bool $entered
    ): void {
        Notification::create([
            'condominium_id' => $movement->condominium_id,
            'user_id' => $recipient->id,
            'type' => $type,
            'title' => $title,
            'message' => $message,
            'data' => [
                'movement_id' => $movement->id,
                'visitor_name' => $movement->visitor_name,
                'action' => $movement->action,
                'source_type' => $movement->source_type,
                'unit' => $movement->unit?->full_identifier,
                'occurred_at' => $movement->occurred_at?->toIso8601String(),
            ],
            'channel' => 'database',
            'sent' => true,
            'sent_at' => now(),
        ]);
    }

    protected function buildMessage(AccessMovement $movement, bool $entered): string
    {
        $unit = $movement->unit?->full_identifier ?? 'unidade';
        $verb = $entered ? 'entrou' : 'teve o acesso negado';

        if ($movement->source_type === AccessMovement::SOURCE_SERVICE_PROVIDER) {
            return sprintf('O prestador %s %s no condomínio (%s).', $movement->visitor_name, $verb, $unit);
        }

        if ($movement->source_type === AccessMovement::SOURCE_LIST_ITEM) {
            $message = sprintf('%s %s — evento: %s (%s).', $movement->visitor_name, $verb, $movement->reference_label, $unit);
        } else {
            $message = sprintf('%s %s na portaria (%s).', $movement->visitor_name, $verb, $unit);
        }

        if ($movement->isEarlyEntry()) {
            $scheduledAt = $movement->metadata['scheduled_at'] ?? null;
            $scheduledLabel = $scheduledAt
                ? \Carbon\Carbon::parse($scheduledAt)->format('d/m/Y H:i')
                : 'horário liberado';

            $message .= sprintf(' Entrada antecipada (liberado para %s), confirmada pelo porteiro após autorização do morador.', $scheduledLabel);
        }

        return $message;
    }
}
