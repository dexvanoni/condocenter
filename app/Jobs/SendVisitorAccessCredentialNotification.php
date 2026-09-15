<?php

namespace App\Jobs;

use App\Models\AccessAuthorization;
use App\Models\Notification;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Collection;

class SendVisitorAccessCredentialNotification implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(
        public AccessAuthorization $authorization,
        public string $accessPin
    ) {}

    public function handle(): void
    {
        $authorization = $this->authorization->fresh(['unit', 'notifyUser', 'authorizedBy']);

        if (!$authorization || !$authorization->hasDigitalPass()) {
            return;
        }

        $title = 'Liberação de visitante criada';
        $message = $this->buildMessage($authorization);

        foreach ($this->resolveRecipients($authorization) as $recipient) {
            Notification::create([
                'condominium_id' => $authorization->condominium_id,
                'user_id' => $recipient->id,
                'type' => 'access_visitor_credential',
                'title' => $title,
                'message' => $message,
                'data' => [
                    'authorization_id' => $authorization->id,
                    'visitor_name' => $authorization->visitor_name,
                    'unit' => $authorization->unit?->full_identifier,
                    'access_pin' => $this->accessPin,
                    'valid_until' => $authorization->valid_until?->toIso8601String(),
                    'has_qr_code' => true,
                ],
                'channel' => 'database',
                'sent' => true,
                'sent_at' => now(),
            ]);
        }
    }

    protected function resolveRecipients(AccessAuthorization $authorization): Collection
    {
        $recipients = collect();

        if ($authorization->notifyUser) {
            $recipients->push($authorization->notifyUser);
        }

        if (
            $authorization->authorizedBy
            && !$recipients->contains('id', $authorization->authorized_by)
            && $this->isResidentRecipient($authorization->authorizedBy)
        ) {
            $recipients->push($authorization->authorizedBy);
        }

        $unitMorador = $authorization->unit?->morador;

        if (
            $unitMorador
            && !$recipients->contains('id', $unitMorador->id)
            && $this->isResidentRecipient($unitMorador)
        ) {
            $recipients->push($unitMorador);
        }

        return $recipients
            ->filter(fn (User $recipient) => $recipient->canReceiveWhatsApp())
            ->unique('id')
            ->values();
    }

    protected function isResidentRecipient(User $user): bool
    {
        return !$user->isSindico() && !$user->isAdmin();
    }

    protected function buildMessage(AccessAuthorization $authorization): string
    {
        $unit = $authorization->unit?->full_identifier ?? 'unidade';
        $validUntil = $authorization->valid_until?->format('d/m/Y H:i') ?? '—';

        return sprintf(
            'Liberação criada para %s (%s). Senha na portaria: %s. Válida até %s. Baixe o PDF com QR Code no app e envie ao visitante. O visitante pode entrar e sair quantas vezes quiser até expirar.',
            $authorization->visitor_name,
            $unit,
            $this->accessPin,
            $validUntil
        );
    }
}
