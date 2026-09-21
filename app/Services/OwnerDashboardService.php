<?php

namespace App\Services;

use App\Models\Charge;
use App\Models\Fine;
use App\Models\Unit;
use App\Models\User;
use Illuminate\Support\Collection;

class OwnerDashboardService
{
    public function __construct(
        private readonly UnitOccupancyService $occupancy,
    ) {
    }

    /**
     * @return array{
     *     units: Collection<int, array>,
     *     summary: array{units_count: int, open_owner_charges: int, overdue_owner_charges: int, owner_pending_amount: float, open_fines: int}
     * }
     */
    public function panorama(User $owner, int $condominiumId): array
    {
        $units = Unit::query()
            ->where('condominium_id', $condominiumId)
            ->where('owner_user_id', $owner->id)
            ->with(['morador'])
            ->orderBy('block')
            ->orderBy('number')
            ->get();

        if ($units->isEmpty()) {
            return [
                'units' => collect(),
                'summary' => [
                    'units_count' => 0,
                    'open_owner_charges' => 0,
                    'overdue_owner_charges' => 0,
                    'owner_pending_amount' => 0.0,
                    'open_fines' => 0,
                ],
            ];
        }

        $unitIds = $units->pluck('id')->map(fn ($id) => (int) $id)->all();

        $openCharges = Charge::query()
            ->whereIn('unit_id', $unitIds)
            ->whereNotIn('status', ['paid', 'cancelled'])
            ->get()
            ->groupBy('unit_id');

        $finesByUnit = $this->openFinesByUnit($condominiumId, $unitIds);

        $unitCards = $units->map(function (Unit $unit) use ($openCharges, $finesByUnit) {
            $charges = $openCharges->get($unit->id, collect());

            $ownerCharges = $charges->filter(
                fn (Charge $charge) => !$this->occupancy->isMoradorResponsibleCharge($charge)
            );

            $tenantCharges = $charges->filter(
                fn (Charge $charge) => $this->occupancy->isMoradorResponsibleCharge($charge)
            );

            $ownerPending = $ownerCharges->filter(
                fn (Charge $c) => $c->effectiveStatus() === 'pending'
            );
            $ownerOverdue = $ownerCharges->filter(
                fn (Charge $c) => $c->effectiveStatus() === 'overdue'
            );

            $fines = $finesByUnit->get($unit->id, collect());

            return [
                'unit' => $unit,
                'morador' => $unit->morador,
                'owner_charges_pending' => $ownerPending->values(),
                'owner_charges_overdue' => $ownerOverdue->values(),
                'tenant_charges_open' => $tenantCharges->values(),
                'fines' => $fines,
                'owner_open_count' => $ownerCharges->count(),
                'owner_pending_amount' => round(
                    (float) $ownerCharges->sum(fn (Charge $c) => $c->amount),
                    2
                ),
            ];
        });

        $allOwnerOpen = $unitCards->sum('owner_open_count');
        $allOverdue = $unitCards->sum(fn (array $row) => $row['owner_charges_overdue']->count());
        $allFines = $unitCards->sum(fn (array $row) => $row['fines']->count());
        $pendingAmount = round(
            (float) $unitCards->sum('owner_pending_amount'),
            2
        );

        return [
            'units' => $unitCards,
            'summary' => [
                'units_count' => $units->count(),
                'open_owner_charges' => $allOwnerOpen,
                'overdue_owner_charges' => $allOverdue,
                'owner_pending_amount' => $pendingAmount,
                'open_fines' => $allFines,
            ],
        ];
    }

    /**
     * @param  list<int>  $unitIds
     */
    private function openFinesByUnit(int $condominiumId, array $unitIds): Collection
    {
        $fines = Fine::query()
            ->issued()
            ->byCondominium($condominiumId)
            ->whereHas('recipients', fn ($q) => $q->whereIn('unit_id', $unitIds))
            ->with(['recipients' => fn ($q) => $q->whereIn('unit_id', $unitIds)])
            ->orderByDesc('applied_at')
            ->get();

        $grouped = collect();

        foreach ($fines as $fine) {
            foreach ($fine->recipients as $recipient) {
                if (!$recipient->unit_id) {
                    continue;
                }
                $unitId = (int) $recipient->unit_id;
                if (!isset($grouped[$unitId])) {
                    $grouped[$unitId] = collect();
                }
                $grouped[$unitId]->push($fine);
            }
        }

        return $grouped;
    }
}
