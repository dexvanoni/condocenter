<?php

namespace App\Services;

use App\Models\Notification;
use App\Models\Unit;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Collection;

class LeaseContractService
{
    public const SUSPENSION_REASON_LEASE_EXPIRED = 'lease_expired';

    /** @var list<int> */
    public const OWNER_ALERT_DAYS = [30, 15, 7, 3, 1];

    public function __construct(
        private readonly UnitOccupancyService $unitOccupancyService,
    ) {
    }

    public function tenantHasActiveLease(User $user): bool
    {
        $unit = $this->resolveRentalUnitForTenant($user);

        if (!$unit) {
            return true;
        }

        return $this->leaseIsActive($unit);
    }

    public function leaseIsActive(Unit $unit): bool
    {
        if (!$this->unitOccupancyService->isRental($unit)) {
            return true;
        }

        if (!$unit->lease_contract_ends_at) {
            return true;
        }

        return $unit->lease_contract_ends_at->endOfDay()->isFuture()
            || $unit->lease_contract_ends_at->isToday();
    }

    public function leaseIsExpired(Unit $unit): bool
    {
        if (!$this->unitOccupancyService->isRental($unit) || !$unit->lease_contract_ends_at) {
            return false;
        }

        return $unit->lease_contract_ends_at->endOfDay()->isPast()
            && !$unit->lease_contract_ends_at->isToday();
    }

    public function daysUntilLeaseEnds(Unit $unit): ?int
    {
        if (!$unit->lease_contract_ends_at) {
            return null;
        }

        return (int) now()->startOfDay()->diffInDays($unit->lease_contract_ends_at, false);
    }

    public function resolveRentalUnitForTenant(User $user): ?Unit
    {
        if ($user->isMorador() && $user->unit_id) {
            $unit = Unit::query()->find($user->unit_id);

            return ($unit && $this->unitOccupancyService->isRental($unit)) ? $unit : null;
        }

        if ($user->isAgregado() && $user->moradorVinculado?->unit_id) {
            $unit = Unit::query()->find($user->moradorVinculado->unit_id);

            return ($unit && $this->unitOccupancyService->isRental($unit)) ? $unit : null;
        }

        return null;
    }

    public function suspendTenantAccessForUnit(Unit $unit): void
    {
        $morador = $unit->morador()->first();

        if (!$morador) {
            return;
        }

        $this->suspendUser($morador);

        User::query()
            ->where('morador_vinculado_id', $morador->id)
            ->where('is_active', true)
            ->each(fn (User $agregado) => $this->suspendUser($agregado));
    }

    public function restoreTenantAccessForUnit(Unit $unit): void
    {
        $morador = $unit->morador()->first();

        if (!$morador) {
            return;
        }

        $this->restoreUserIfLeaseSuspended($morador);

        User::query()
            ->where('morador_vinculado_id', $morador->id)
            ->where('access_suspended_reason', self::SUSPENSION_REASON_LEASE_EXPIRED)
            ->each(fn (User $agregado) => $this->restoreUserIfLeaseSuspended($agregado));
    }

    public function processExpiredLeases(): int
    {
        $processed = 0;

        Unit::query()
            ->where('occupancy_regime', 'aluguel')
            ->whereNotNull('lease_contract_ends_at')
            ->whereDate('lease_contract_ends_at', '<', now()->toDateString())
            ->whereHas('users', function ($query) {
                $query->where('is_active', true)
                    ->whereHas('roles', fn ($role) => $role->where('name', 'Morador'));
            })
            ->with(['owner', 'morador'])
            ->each(function (Unit $unit) use (&$processed) {
                $this->suspendTenantAccessForUnit($unit);
                $this->notifyOwnerLeaseExpired($unit);
                $processed++;
            });

        return $processed;
    }

    public function notifyOwnersOfUpcomingExpirations(): int
    {
        $sent = 0;

        foreach (self::OWNER_ALERT_DAYS as $days) {
            $targetDate = now()->addDays($days)->toDateString();

            Unit::query()
                ->where('occupancy_regime', 'aluguel')
                ->whereDate('lease_contract_ends_at', $targetDate)
                ->whereNotNull('owner_user_id')
                ->with(['owner', 'morador'])
                ->each(function (Unit $unit) use ($days, &$sent) {
                    if ($this->notifyOwnerLeaseExpiring($unit, $days)) {
                        $sent++;
                    }
                });
        }

        return $sent;
    }

    /**
     * @return Collection<int, array{unit: Unit, days: int|null, expired: bool, morador: ?User}>
     */
    public function ownerLeaseAlerts(User $owner, int $condominiumId): Collection
    {
        return Unit::query()
            ->where('condominium_id', $condominiumId)
            ->where('owner_user_id', $owner->id)
            ->where('occupancy_regime', 'aluguel')
            ->whereNotNull('lease_contract_ends_at')
            ->with('morador')
            ->get()
            ->map(function (Unit $unit) {
                $days = $this->daysUntilLeaseEnds($unit);

                return [
                    'unit' => $unit,
                    'days' => $days,
                    'expired' => $this->leaseIsExpired($unit),
                    'morador' => $unit->morador,
                ];
            })
            ->filter(fn (array $row) => $row['expired'] || ($row['days'] !== null && $row['days'] <= 30));
    }

    protected function suspendUser(User $user): void
    {
        if (!$user->is_active && $user->access_suspended_reason === self::SUSPENSION_REASON_LEASE_EXPIRED) {
            return;
        }

        $user->update([
            'is_active' => false,
            'access_suspended_reason' => self::SUSPENSION_REASON_LEASE_EXPIRED,
        ]);
    }

    protected function restoreUserIfLeaseSuspended(User $user): void
    {
        if ($user->access_suspended_reason !== self::SUSPENSION_REASON_LEASE_EXPIRED) {
            return;
        }

        $user->update([
            'is_active' => true,
            'access_suspended_reason' => null,
        ]);
    }

    protected function notifyOwnerLeaseExpired(Unit $unit): void
    {
        $owner = $unit->owner;

        if (!$owner) {
            return;
        }

        $this->createOwnerNotification(
            $unit,
            $owner,
            'lease_expired',
            'Contrato de locação encerrado — ' . $unit->full_identifier,
            'O contrato de locação da unidade ' . $unit->full_identifier . ' terminou em '
            . $unit->lease_contract_ends_at->format('d/m/Y')
            . '. O acesso do inquilino e dos agregados foi suspenso. Renove a data do contrato na unidade para restabelecer o acesso, se necessário.'
        );
    }

    protected function notifyOwnerLeaseExpiring(Unit $unit, int $days): bool
    {
        $owner = $unit->owner;

        if (!$owner) {
            return false;
        }

        $type = 'lease_expiring_' . $days;

        $alreadySent = Notification::query()
            ->where('user_id', $owner->id)
            ->where('type', $type)
            ->where('data->unit_id', $unit->id)
            ->where('created_at', '>=', now()->subDay())
            ->exists();

        if ($alreadySent) {
            return false;
        }

        $moradorName = $unit->morador?->name ?? 'inquilino';

        $this->createOwnerNotification(
            $unit,
            $owner,
            $type,
            "Contrato de locação vence em {$days} dia(s) — {$unit->full_identifier}",
            "O contrato do inquilino {$moradorName} na unidade {$unit->full_identifier} vence em "
            . $unit->lease_contract_ends_at->format('d/m/Y')
            . ". Após essa data, o acesso do inquilino e dos agregados será suspenso automaticamente. Atualize a validade na ficha da unidade se o contrato for renovado."
        );

        return true;
    }

    protected function createOwnerNotification(
        Unit $unit,
        User $owner,
        string $type,
        string $title,
        string $message,
    ): void {
        Notification::create([
            'condominium_id' => $unit->condominium_id,
            'user_id' => $owner->id,
            'type' => $type,
            'title' => $title,
            'message' => $message,
            'data' => [
                'unit_id' => $unit->id,
                'unit_identifier' => $unit->full_identifier,
                'lease_contract_ends_at' => $unit->lease_contract_ends_at?->format('Y-m-d'),
            ],
            'channel' => 'database',
            'sent' => true,
            'sent_at' => now(),
        ]);
    }
}
