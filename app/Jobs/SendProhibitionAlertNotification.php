<?php

namespace App\Jobs;

use App\Models\AccessMovement;
use App\Models\Notification;
use App\Models\User;
use App\Services\OneSignalNotificationService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Log;

class SendProhibitionAlertNotification implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(public AccessMovement $movement) {}

    public function handle(): void
    {
        $movement = $this->movement->fresh(['notifyUser', 'authorizedBy', 'unit.morador']);

        if (!$movement || !$movement->isProhibitionAlert()) {
            return;
        }

        $title = 'ALERTA CRÍTICO';
        $message = $this->buildMessage($movement);

        foreach ($this->resolveRecipients($movement) as $recipient) {
            $this->storeNotification($movement, $recipient, $title, $message);
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
            && $this->isResidentRecipient($movement->authorizedBy)
        ) {
            $recipients->push($movement->authorizedBy);
        }

        $unitMorador = $movement->unit?->morador;

        if (
            $unitMorador
            && !$recipients->contains('id', $unitMorador->id)
            && $this->isResidentRecipient($unitMorador)
        ) {
            $recipients->push($unitMorador);
        }

        return $recipients->unique('id')->values();
    }

    protected function isResidentRecipient(User $user): bool
    {
        return !$user->isSindico() && !$user->isAdmin();
    }

    protected function storeNotification(
        AccessMovement $movement,
        User $recipient,
        string $title,
        string $message
    ): void {
        Notification::create([
            'condominium_id' => $movement->condominium_id,
            'user_id' => $recipient->id,
            'type' => 'access_prohibition_critical',
            'title' => $title,
            'message' => $message,
            'data' => [
                'movement_id' => $movement->id,
                'visitor_name' => $movement->visitor_name,
                'action' => $movement->action,
                'source_type' => $movement->source_type,
                'unit' => $movement->unit?->full_identifier,
                'occurred_at' => $movement->occurred_at?->toIso8601String(),
                'critical' => true,
            ],
            'channel' => 'database',
            'sent' => true,
            'sent_at' => now(),
        ]);

        $oneSignal = app(OneSignalNotificationService::class);

        if (!$oneSignal->isEnabled()) {
            return;
        }

        try {
            $oneSignal->sendToUsers(
                [$recipient->id],
                $message,
                $title,
                [
                    'type' => 'access_prohibition_critical',
                    'movement_id' => (string) $movement->id,
                    'critical' => '1',
                ]
            );
        } catch (\Throwable $e) {
            Log::warning('OneSignal prohibition alert failed: ' . $e->getMessage());
        }
    }

    protected function buildMessage(AccessMovement $movement): string
    {
        $unit = $movement->unit?->full_identifier ?? 'sua unidade';
        $prohibitedBy = $movement->metadata['prohibited_by'] ?? 'você';

        return sprintf(
            'Atenção! %s tentou entrar no condomínio (%s). Você registrou proibição para esta pessoa (cadastro de %s). A portaria bloqueou o acesso e enviou este alerta.',
            $movement->visitor_name,
            $unit,
            $prohibitedBy
        );
    }
}
