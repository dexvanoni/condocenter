<?php

namespace App\Services\Assembly;

use App\Models\Assembly;
use App\Models\Notification;
use App\Models\User;

class AssemblyNotificationService
{
    public function notifyEligibleUsers(Assembly $assembly, string $event): void
    {
        $assembly->loadMissing('allowedRoles');

        $query = User::query()
            ->where('condominium_id', $assembly->condominium_id)
            ->where('is_active', true);

        $roleNames = $assembly->allowedRoles->pluck('name')->filter()->unique()->values();

        if ($roleNames->isNotEmpty()) {
            $query->whereHas('roles', fn ($roles) => $roles->whereIn('name', $roleNames));
        } else {
            $query->whereHas('roles', fn ($roles) => $roles->whereIn('name', [
                'Morador',
                'Síndico',
            ]));
        }

        $opensAt = $assembly->voting_opens_at ?? $assembly->scheduled_at;
        $closesAt = $assembly->voting_closes_at;

        [$title, $message, $type] = match ($event) {
            'created' => [
                'Nova assembleia convocada',
                sprintf(
                    'A assembleia "%s" foi convocada. Votação de %s até %s.',
                    $assembly->title,
                    $opensAt?->format('d/m/Y H:i') ?? 'em breve',
                    $closesAt?->format('d/m/Y H:i') ?? 'data a definir'
                ),
                'assembly_created',
            ],
            'reopened' => [
                'Votação reaberta',
                sprintf(
                    'A votação da assembleia "%s" foi reaberta até %s.',
                    $assembly->title,
                    $closesAt?->format('d/m/Y H:i') ?? 'nova data'
                ),
                'assembly_reopened',
            ],
            default => [
                'Atualização de assembleia',
                sprintf('A assembleia "%s" foi atualizada.', $assembly->title),
                'assembly_updated',
            ],
        };

        foreach ($query->get(['id']) as $recipient) {
            Notification::create([
                'condominium_id' => $assembly->condominium_id,
                'user_id' => $recipient->id,
                'type' => $type,
                'title' => $title,
                'message' => $message,
                'data' => [
                    'assembly_id' => $assembly->id,
                    'event' => $event,
                    'voting_opens_at' => $opensAt?->toIso8601String(),
                    'voting_closes_at' => $closesAt?->toIso8601String(),
                ],
                'channel' => 'database',
                'sent' => true,
                'sent_at' => now(),
            ]);
        }
    }
}
