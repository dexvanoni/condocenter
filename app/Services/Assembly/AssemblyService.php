<?php

namespace App\Services\Assembly;

use App\Models\Assembly;
use App\Models\AssemblyItem;
use App\Models\User;
use Carbon\CarbonInterface;
use Illuminate\Support\Carbon;
use Illuminate\Database\DatabaseManager;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class AssemblyService
{
    public function __construct(
        private readonly DatabaseManager $db,
        private readonly AssemblyAttachmentService $attachmentService,
        private readonly AssemblyMinutesService $minutesService
    ) {
    }

    public function createAssembly(array $payload, User $creator): Assembly
    {
        return $this->db->transaction(function () use ($payload, $creator) {
            $itemsPayload = $payload['items'] ?? $this->convertAgendaToItems($payload['agenda'] ?? []);

            $votingOpensAt = $payload['voting_opens_at'] ?? $payload['scheduled_at'] ?? null;
            $votingClosesAt = $payload['voting_closes_at'] ?? null;
            $durationMinutes = $this->resolveDurationMinutes($votingOpensAt, $votingClosesAt, $payload['duration_minutes'] ?? null);

            $assembly = Assembly::create([
                'condominium_id' => $payload['condominium_id'] ?? $creator->tenantCondominiumId() ?? $creator->condominium_id,
                'created_by' => $creator->id,
                'title' => $payload['title'],
                'description' => $payload['description'] ?? null,
                'agenda' => $this->extractAgendaTitles($itemsPayload),
                'scheduled_at' => $votingOpensAt,
                'voting_opens_at' => $votingOpensAt,
                'voting_closes_at' => $votingClosesAt,
                'duration_minutes' => $durationMinutes,
                'status' => $payload['status'] ?? 'scheduled',
                'voting_type' => $payload['voting_type'] ?? 'open',
                'urgency' => $payload['urgency'] ?? 'normal',
                'allow_delegation' => (bool) ($payload['allow_delegation'] ?? false),
                'allow_comments' => (bool) ($payload['allow_comments'] ?? false),
                'results_visibility' => $payload['results_visibility'] ?? 'final_only',
            ]);

            $allowedRoles = Arr::wrap($payload['allowed_role_ids'] ?? []);
            if ($allowedRoles) {
                $assembly->syncAllowedRoles($allowedRoles);
            }

            $this->syncItems($assembly, $itemsPayload);
            $this->refreshAgenda($assembly);

            if (!empty($payload['attachments'])) {
                $this->attachmentService->storeMany($assembly, $payload['attachments'], $creator);
            }

            $assembly->statusLogs()->create([
                'changed_by' => $creator->id,
                'from_status' => null,
                'to_status' => $assembly->status,
                'context' => [
                    'created_at' => now()->toIso8601String(),
                    'urgency' => $assembly->urgency,
                ],
            ]);

            return $assembly->fresh(['items', 'attachments', 'allowedRoles']);
        });
    }

    public function updateAssembly(Assembly $assembly, array $payload, User $actor): Assembly
    {
        return $this->db->transaction(function () use ($assembly, $payload, $actor) {
            $updatable = Arr::only($payload, [
                'title',
                'description',
                'scheduled_at',
                'voting_opens_at',
                'voting_closes_at',
                'duration_minutes',
                'voting_type',
                'urgency',
                'allow_delegation',
                'allow_comments',
                'results_visibility',
                'status',
            ]);

            if (isset($updatable['status']) && $updatable['status'] !== $assembly->status) {
                $this->transitionStatus($assembly, $updatable['status'], $actor, Arr::get($payload, 'status_context', []));
                unset($updatable['status']);
            }

            if ($updatable) {
                if (isset($updatable['voting_opens_at'])) {
                    $updatable['scheduled_at'] = $updatable['voting_opens_at'];
                }

                if (isset($updatable['voting_opens_at'], $updatable['voting_closes_at'])) {
                    $updatable['duration_minutes'] = $this->resolveDurationMinutes(
                        $updatable['voting_opens_at'],
                        $updatable['voting_closes_at'],
                        $updatable['duration_minutes'] ?? $assembly->duration_minutes
                    );
                }

                $assembly->update($updatable);
            }

            if (array_key_exists('allowed_role_ids', $payload)) {
                $assembly->syncAllowedRoles(Arr::wrap($payload['allowed_role_ids']));
            }

            if (isset($payload['items'])) {
                $this->syncItems($assembly, $payload['items']);
                $this->refreshAgenda($assembly);
            }

            if (!empty($payload['attachments_to_add'])) {
                $this->attachmentService->storeMany($assembly, $payload['attachments_to_add'], $actor);
            }

            if (!empty($payload['attachments_to_remove'])) {
                foreach ($payload['attachments_to_remove'] as $attachment) {
                    $this->attachmentService->delete($attachment);
                }
            }

            return $assembly->fresh(['items', 'attachments', 'allowedRoles']);
        });
    }

    public function transitionStatus(Assembly $assembly, string $toStatus, User $actor, array $context = []): void
    {
        $validStatuses = ['scheduled', 'in_progress', 'completed', 'cancelled'];
        if (!in_array($toStatus, $validStatuses, true)) {
            throw ValidationException::withMessages([
                'status' => 'Status inválido para assembleia.',
            ]);
        }

        $fromStatus = $assembly->status;
        if ($fromStatus === $toStatus) {
            return;
        }

        $this->db->transaction(function () use ($assembly, $fromStatus, $toStatus, $actor, $context) {
            $timestamps = [];
            if ($toStatus === 'in_progress') {
                $timestamps['started_at'] = $context['started_at'] ?? now();
            }
            if ($toStatus === 'completed' || $toStatus === 'cancelled') {
                $endedAt = Arr::get($context, 'ended_at');
                if (!$endedAt instanceof CarbonInterface) {
                    $endedAt = $endedAt ? Carbon::parse($endedAt) : now();
                }
                $timestamps['ended_at'] = $endedAt;

                $requiresReason = $toStatus === 'cancelled'
                    || ($assembly->voting_closes_at && $endedAt->lt($assembly->voting_closes_at));

                if ($requiresReason && empty($context['reason'])) {
                    throw ValidationException::withMessages([
                        'reason' => 'Informe o motivo para encerrar a assembleia antes do horário previsto.',
                    ]);
                }
            }

            $assembly->update(array_merge(['status' => $toStatus], $timestamps));

            if ($toStatus === 'completed') {
                $assembly->items()
                    ->whereNotIn('status', ['cancelled', 'closed'])
                    ->update(['status' => 'closed']);
            }

            $assembly->refresh();

            if ($toStatus === 'completed') {
                $this->minutesService->generateAndPersistMinutes($assembly);
            }

            $assembly->statusLogs()->create([
                'changed_by' => $actor->id,
                'from_status' => $fromStatus,
                'to_status' => $toStatus,
                'context' => array_merge($context, [
                    'transitioned_at' => now()->toIso8601String(),
                ]),
            ]);
        });
    }

    public function reopenVoting(Assembly $assembly, array $payload, User $actor): Assembly
    {
        if (in_array($assembly->status, ['completed', 'cancelled'], true)) {
            throw ValidationException::withMessages([
                'assembly' => 'Não é possível reabrir assembleias concluídas ou canceladas.',
            ]);
        }

        $opensAt = $payload['voting_opens_at'];
        $closesAt = $payload['voting_closes_at'];

        return $this->db->transaction(function () use ($assembly, $opensAt, $closesAt, $actor) {
            $fromStatus = $assembly->status;

            $assembly->update([
                'voting_opens_at' => $opensAt,
                'voting_closes_at' => $closesAt,
                'scheduled_at' => $opensAt,
                'duration_minutes' => $this->resolveDurationMinutes($opensAt, $closesAt, $assembly->duration_minutes),
                'status' => 'scheduled',
                'ended_at' => null,
            ]);

            $assembly->items()
                ->where('status', 'closed')
                ->update(['status' => 'pending']);

            $assembly->statusLogs()->create([
                'changed_by' => $actor->id,
                'from_status' => $fromStatus,
                'to_status' => 'scheduled',
                'context' => [
                    'action' => 'reopen_voting',
                    'voting_opens_at' => $opensAt,
                    'voting_closes_at' => $closesAt,
                    'reopened_at' => now()->toIso8601String(),
                ],
            ]);

            return $assembly->fresh(['items', 'attachments', 'allowedRoles']);
        });
    }

    protected function resolveDurationMinutes($opensAt, $closesAt, ?int $fallback = null): int
    {
        if ($opensAt && $closesAt) {
            $opens = $opensAt instanceof \Carbon\CarbonInterface ? $opensAt : \Carbon\Carbon::parse($opensAt);
            $closes = $closesAt instanceof \Carbon\CarbonInterface ? $closesAt : \Carbon\Carbon::parse($closesAt);
            $minutes = $opens->diffInMinutes($closes);

            return max(15, min(1440, $minutes));
        }

        return $fallback ?? 120;
    }

    protected function syncItems(Assembly $assembly, array $items): void
    {
        if (empty($items)) {
            $assembly->items()->delete();
            return;
        }

        $existingIds = $assembly->items()->pluck('id')->all();
        $handledIds = [];
        $position = 0;

        foreach ($items as $itemData) {
            $itemId = $itemData['id'] ?? null;
            $payload = [
                'title' => Arr::get($itemData, 'title'),
                'description' => Arr::get($itemData, 'description'),
                'options' => Arr::get($itemData, 'options'),
                'position' => Arr::get($itemData, 'position', $position++),
                'status' => Arr::get($itemData, 'status', 'pending'),
            ];

            if ($itemId) {
                $item = $assembly->items()->withTrashed()->whereKey($itemId)->first();
                if ($item) {
                    $item->restore();
                    $item->update($payload);
                    $handledIds[] = $item->id;
                    continue;
                }
            }

            $item = $assembly->items()->create($payload);
            $handledIds[] = $item->id;
        }

        $idsToDelete = array_diff($existingIds, $handledIds);
        if (!empty($idsToDelete)) {
            $assembly->items()->whereIn('id', $idsToDelete)->delete();
        }
    }

    protected function extractAgendaTitles(array $items): array
    {
        return collect($items)
            ->pluck('title')
            ->filter()
            ->values()
            ->all();
    }

    protected function convertAgendaToItems(array $agenda): array
    {
        return collect($agenda)
            ->map(fn ($title, $index) => [
                'title' => is_array($title) ? Arr::get($title, 'title') : $title,
                'description' => is_array($title) ? Arr::get($title, 'description') : null,
                'position' => $index,
            ])
            ->filter(fn ($item) => filled($item['title']))
            ->values()
            ->all();
    }

    protected function refreshAgenda(Assembly $assembly): void
    {
        $titles = $assembly->items()
            ->orderBy('position')
            ->pluck('title')
            ->filter()
            ->values()
            ->all();

        $assembly->updateQuietly(['agenda' => $titles]);
    }
}

