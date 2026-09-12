<?php

namespace App\Services;

use App\Models\Charge;
use App\Models\Fine;
use App\Models\FineRecipient;
use App\Models\Notification;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\DatabaseManager;
use Illuminate\Support\Collection;
use Illuminate\Validation\ValidationException;

class FineService
{
    public function __construct(
        private readonly DatabaseManager $database,
        private readonly ChargeSettlementService $chargeSettlementService,
        private readonly ChargeDueDateService $chargeDueDateService,
    ) {
    }

    public function issue(User $issuer, array $data): Fine
    {
        return $this->database->transaction(function () use ($issuer, $data) {
            $condominiumId = (int) $issuer->tenantCondominiumId();
            $userIds = collect($data['user_ids'])->unique()->values();

            $infractors = User::query()
                ->with(['moradorVinculado', 'unit', 'roles'])
                ->whereIn('id', $userIds)
                ->byCondominium($condominiumId)
                ->where('is_active', true)
                ->get()
                ->keyBy('id');

            if ($infractors->count() !== $userIds->count()) {
                throw ValidationException::withMessages([
                    'user_ids' => 'Um ou mais moradores selecionados são inválidos para este condomínio.',
                ]);
            }

            $fine = Fine::create([
                'condominium_id' => $condominiumId,
                'reference' => $this->generateReference($condominiumId),
                'motivo' => $data['motivo'],
                'enquadramento' => $data['enquadramento'],
                'amount' => $data['amount'],
                'due_date' => $data['due_date'],
                'applied_at' => now(),
                'applied_by' => $issuer->id,
                'status' => 'issued',
                'notes' => $data['notes'] ?? null,
            ]);

            foreach ($userIds as $userId) {
                $infractor = $infractors->get($userId);
                $unitId = $this->resolveUnitId($infractor);

                if (!$unitId) {
                    throw ValidationException::withMessages([
                        'user_ids' => "O usuário {$infractor->name} não possui unidade vinculada.",
                    ]);
                }

                $notifiedUser = $this->resolveNotificationRecipient($infractor);

                $charge = Charge::create([
                    'condominium_id' => $condominiumId,
                    'unit_id' => $unitId,
                    'title' => 'Multa — ' . $data['enquadramento'],
                    'description' => $this->buildChargeDescription($fine, $infractor),
                    'amount' => $data['amount'],
                    'due_date' => $data['due_date'],
                    'type' => 'extra',
                    'status' => 'pending',
                    'generated_by' => 'fine',
                    'metadata' => [
                        'fine_id' => $fine->id,
                        'fine_reference' => $fine->reference,
                        'infractor_user_id' => $infractor->id,
                        'enquadramento' => $data['enquadramento'],
                    ],
                ]);

                FineRecipient::create([
                    'fine_id' => $fine->id,
                    'user_id' => $infractor->id,
                    'unit_id' => $unitId,
                    'notified_user_id' => $notifiedUser->id,
                    'charge_id' => $charge->id,
                ]);

                $this->notifyFineIssued($fine, $infractor, $notifiedUser, $charge);
            }

            return $fine->load([
                'recipients.user',
                'recipients.unit',
                'recipients.notifiedUser',
                'recipients.charge',
                'appliedBy',
            ]);
        });
    }

    /**
     * Altera o vencimento da multa e propaga para as cobranças pendentes/em atraso.
     */
    public function updateDueDate(Fine $fine, User $updatedBy, string $newDueDate): Fine
    {
        if ($fine->isCancelled()) {
            throw ValidationException::withMessages([
                'due_date' => 'Não é possível alterar o vencimento de uma multa cancelada.',
            ]);
        }

        $date = Carbon::parse($newDueDate)->startOfDay();

        return $this->database->transaction(function () use ($fine, $date) {
            $fine->load('recipients.charge', 'recipients.notifiedUser');

            $updatable = $fine->recipients->filter(
                fn (FineRecipient $recipient) => $recipient->charge
                    && in_array($recipient->charge->status, ['pending', 'overdue'], true)
            );

            if ($updatable->isEmpty()) {
                throw ValidationException::withMessages([
                    'due_date' => 'Nenhuma cobrança desta multa está pendente. Cobranças pagas ou canceladas não podem ter o vencimento alterado.',
                ]);
            }

            $fine->update(['due_date' => $date->toDateString()]);

            foreach ($updatable as $recipient) {
                $this->chargeDueDateService->updateDueDate($recipient->charge, $date);
                $this->notifyDueDateChanged($fine, $recipient, $date);
            }

            return $fine->fresh([
                'recipients.user',
                'recipients.unit',
                'recipients.notifiedUser',
                'recipients.charge',
                'appliedBy',
            ]);
        });
    }

    public function cancel(Fine $fine, User $cancelledBy, string $reason): Fine
    {
        if ($fine->isCancelled()) {
            throw ValidationException::withMessages([
                'fine' => 'Esta multa já foi cancelada.',
            ]);
        }

        return $this->database->transaction(function () use ($fine, $cancelledBy, $reason) {
            $fine->load('recipients.charge');

            foreach ($fine->recipients as $recipient) {
                $charge = $recipient->charge;
                if ($charge && $charge->status !== 'cancelled' && $charge->status !== 'paid') {
                    $this->chargeSettlementService->cancelCharge(
                        $charge,
                        'Cancelamento da multa ' . $fine->reference . ': ' . $reason,
                        $cancelledBy->id
                    );
                }
            }

            $fine->update([
                'status' => 'cancelled',
                'cancelled_at' => now(),
                'cancelled_by' => $cancelledBy->id,
                'cancellation_reason' => $reason,
            ]);

            return $fine->fresh([
                'recipients.user',
                'recipients.unit',
                'recipients.notifiedUser',
                'recipients.charge',
                'appliedBy',
                'cancelledBy',
            ]);
        });
    }

    public function resolveNotificationRecipient(User $infractor): User
    {
        if ($infractor->isAgregado() && $infractor->morador_vinculado_id) {
            $responsible = $infractor->moradorVinculado;

            if ($responsible && $responsible->is_active) {
                return $responsible;
            }
        }

        return $infractor;
    }

    public function resolveUnitId(User $user): ?int
    {
        if ($user->unit_id) {
            return (int) $user->unit_id;
        }

        if ($user->isAgregado() && $user->moradorVinculado?->unit_id) {
            return (int) $user->moradorVinculado->unit_id;
        }

        return null;
    }

    public function eligibleInfractors(int $condominiumId): Collection
    {
        return User::query()
            ->with(['unit', 'moradorVinculado.unit', 'roles'])
            ->active()
            ->byCondominium($condominiumId)
            ->whereHas('roles', fn ($query) => $query->whereIn('name', ['Morador', 'Agregado']))
            ->orderBy('name')
            ->get();
    }

    public function searchEligibleInfractors(int $condominiumId, string $term, int $limit = 15): Collection
    {
        $term = trim($term);

        if (strlen($term) < 2) {
            return collect();
        }

        return User::query()
            ->with(['unit', 'moradorVinculado.unit', 'roles'])
            ->active()
            ->byCondominium($condominiumId)
            ->whereHas('roles', fn ($query) => $query->whereIn('name', ['Morador', 'Agregado']))
            ->where(function ($query) use ($term) {
                $query->where('name', 'like', "%{$term}%")
                    ->orWhere('cpf', 'like', "%{$term}%")
                    ->orWhere('email', 'like', "%{$term}%")
                    ->orWhereHas('unit', function ($unitQuery) use ($term) {
                        $unitQuery->where('number', 'like', "%{$term}%")
                            ->orWhere('block', 'like', "%{$term}%");
                    })
                    ->orWhereHas('moradorVinculado.unit', function ($unitQuery) use ($term) {
                        $unitQuery->where('number', 'like', "%{$term}%")
                            ->orWhere('block', 'like', "%{$term}%");
                    });
            })
            ->orderBy('name')
            ->limit($limit)
            ->get()
            ->map(fn (User $user) => $this->formatInfractorForPicker($user));
    }

    /**
     * @param  list<int|string>  $userIds
     */
    public function infractorsByIds(int $condominiumId, array $userIds): Collection
    {
        if ($userIds === []) {
            return collect();
        }

        return User::query()
            ->with(['unit', 'moradorVinculado.unit', 'roles'])
            ->active()
            ->byCondominium($condominiumId)
            ->whereIn('id', $userIds)
            ->whereHas('roles', fn ($query) => $query->whereIn('name', ['Morador', 'Agregado']))
            ->get()
            ->map(fn (User $user) => $this->formatInfractorForPicker($user));
    }

    public function formatInfractorForPicker(User $user): array
    {
        $isAgregado = $user->isAgregado();
        $responsible = $isAgregado ? $user->moradorVinculado : $user;
        $unitLabel = $user->unit?->full_identifier
            ?? $user->moradorVinculado?->unit?->full_identifier
            ?? '—';

        return [
            'id' => $user->id,
            'name' => $user->name,
            'cpf' => $user->cpf,
            'email' => $user->email,
            'role' => $isAgregado ? 'Agregado' : 'Morador',
            'role_class' => $isAgregado ? 'info' : 'primary',
            'unit' => $unitLabel,
            'notified_name' => ($isAgregado && $responsible) ? $responsible->name : null,
        ];
    }

    protected function generateReference(int $condominiumId): string
    {
        $year = now()->format('Y');
        $last = Fine::withTrashed()
            ->where('condominium_id', $condominiumId)
            ->where('reference', 'like', "MULTA-{$year}-%")
            ->orderByDesc('id')
            ->value('reference');

        $sequence = 1;
        if ($last && preg_match('/MULTA-\d{4}-(\d+)/', $last, $matches)) {
            $sequence = (int) $matches[1] + 1;
        }

        return sprintf('MULTA-%s-%04d', $year, $sequence);
    }

    protected function buildChargeDescription(Fine $fine, User $infractor): string
    {
        $role = $infractor->isAgregado() ? 'Agregado' : 'Morador';

        return implode("\n", array_filter([
            "Referência: {$fine->reference}",
            "Enquadramento: {$fine->enquadramento}",
            "Infrator: {$infractor->name} ({$role})",
            "Motivo: {$fine->motivo}",
        ]));
    }

    protected function notifyDueDateChanged(Fine $fine, FineRecipient $recipient, Carbon $newDueDate): void
    {
        $notifiedUser = $recipient->notifiedUser;

        if (! $notifiedUser) {
            return;
        }

        $amount = number_format((float) $fine->amount, 2, ',', '.');

        Notification::create([
            'condominium_id' => $fine->condominium_id,
            'user_id' => $notifiedUser->id,
            'type' => 'fine_due_date_updated',
            'title' => 'Vencimento alterado — ' . $fine->reference,
            'message' => "O vencimento da multa {$fine->reference} (R$ {$amount}) foi alterado para {$newDueDate->format('d/m/Y')}.",
            'data' => [
                'fine_id' => $fine->id,
                'fine_reference' => $fine->reference,
                'charge_id' => $recipient->charge_id,
                'due_date' => $newDueDate->format('Y-m-d'),
            ],
            'channel' => 'database',
            'sent' => true,
            'sent_at' => now(),
        ]);
    }

    protected function notifyFineIssued(Fine $fine, User $infractor, User $notifiedUser, Charge $charge): void
    {
        $isAgregadoFine = $infractor->isAgregado() && $notifiedUser->id !== $infractor->id;
        $amount = number_format((float) $fine->amount, 2, ',', '.');
        $dueDate = $fine->due_date->format('d/m/Y');

        if ($isAgregadoFine) {
            $message = "Foi aplicada uma multa ao agregado {$infractor->name} ({$fine->enquadramento}). "
                . "Valor: R$ {$amount}. Vencimento: {$dueDate}. Motivo: {$fine->motivo}";
        } else {
            $message = "Foi aplicada uma multa em seu nome ({$fine->enquadramento}). "
                . "Valor: R$ {$amount}. Vencimento: {$dueDate}. Motivo: {$fine->motivo}";
        }

        Notification::create([
            'condominium_id' => $fine->condominium_id,
            'user_id' => $notifiedUser->id,
            'type' => 'fine_issued',
            'title' => 'Multa aplicada — ' . $fine->reference,
            'message' => $message,
            'data' => [
                'fine_id' => $fine->id,
                'fine_reference' => $fine->reference,
                'charge_id' => $charge->id,
                'infractor_user_id' => $infractor->id,
                'infractor_name' => $infractor->name,
                'enquadramento' => $fine->enquadramento,
                'amount' => (float) $fine->amount,
                'due_date' => $fine->due_date->format('Y-m-d'),
            ],
            'channel' => 'database',
            'sent' => true,
            'sent_at' => now(),
        ]);
    }
}
