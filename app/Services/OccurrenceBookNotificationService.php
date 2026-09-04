<?php

namespace App\Services;

use App\Models\Notification;
use App\Models\OccurrenceBookEntry;
use App\Models\User;
use Illuminate\Support\Collection;

class OccurrenceBookNotificationService
{
    public function __construct(
        private readonly WhatsAppNotificationService $whatsApp,
    ) {}

    public function notifySyndics(OccurrenceBookEntry $entry): void
    {
        $entry->loadMissing(['author', 'unit', 'condominium']);

        foreach ($this->syndicRecipients($entry->condominium_id) as $syndic) {
            Notification::create([
                'condominium_id' => $entry->condominium_id,
                'user_id' => $syndic->id,
                'type' => 'occurrence_book_new',
                'title' => 'Novo registro no Livro de Ocorrências',
                'message' => "{$entry->typeLabel()}: {$entry->title}",
                'data' => [
                    'occurrence_book_entry_id' => $entry->id,
                    'reference' => $entry->referenceCode(),
                    'author_name' => $entry->author?->name,
                    'unit' => $entry->unit?->full_identifier,
                ],
                'channel' => 'database',
                'sent' => true,
                'sent_at' => now(),
            ]);
        }

        if ($entry->notify_whatsapp) {
            $this->notifySyndicsViaWhatsApp($entry);
        }
    }

    public function notifyResidentAcknowledged(OccurrenceBookEntry $entry, User $syndic): void
    {
        $entry->loadMissing(['author', 'condominium']);

        if (!$entry->author) {
            return;
        }

        Notification::create([
            'condominium_id' => $entry->condominium_id,
            'user_id' => $entry->user_id,
            'type' => 'occurrence_book_acknowledged',
            'title' => 'Ciência registrada no Livro de Ocorrências',
            'message' => "O síndico registrou ciência do seu registro \"{$entry->title}\".",
            'data' => [
                'occurrence_book_entry_id' => $entry->id,
                'reference' => $entry->referenceCode(),
                'acknowledged_by' => $syndic->name,
                'acknowledgment_note' => $entry->acknowledgment_note,
            ],
            'channel' => 'database',
            'sent' => true,
            'sent_at' => now(),
        ]);

        $this->whatsApp->sendToUser(
            $entry->author,
            'Ciência registrada — Livro de Ocorrências',
            "Seu registro {$entry->referenceCode()} ({$entry->typeLabel()}) recebeu ciência do síndico.",
            'occurrence_book_acknowledged',
            $entry->condominium_id
        );
    }

    private function notifySyndicsViaWhatsApp(OccurrenceBookEntry $entry): void
    {
        $unitLabel = $entry->unit?->full_identifier ?? 'Sem unidade';
        $authorName = $entry->author?->name ?? 'Morador';
        $message = implode("\n", [
            "Registro: {$entry->referenceCode()}",
            "Tipo: {$entry->typeLabel()}",
            "Morador: {$authorName} ({$unitLabel})",
            "Assunto: {$entry->title}",
            '',
            \Illuminate\Support\Str::limit($entry->body, 500),
        ]);

        $sentAny = false;

        foreach ($this->syndicRecipients($entry->condominium_id) as $syndic) {
            $sent = $this->whatsApp->sendToUser(
                $syndic,
                'Livro de Ocorrências — novo registro',
                $message,
                'occurrence_book_new',
                $entry->condominium_id
            );

            $sentAny = $sentAny || $sent;
        }

        if ($sentAny) {
            $entry->update(['whatsapp_notified_at' => now()]);
        }
    }

    private function syndicRecipients(int $condominiumId): Collection
    {
        return User::query()
            ->where('condominium_id', $condominiumId)
            ->where('is_active', true)
            ->whereHas('roles', fn ($query) => $query->where('name', 'Síndico'))
            ->get();
    }
}
