<?php

namespace App\Services;

use App\Models\Charge;
use App\Models\Fine;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Schema;

class OrganizationCondominiumInsightsService
{
    public const HEALTH_HEALTHY = 'healthy';

    public const HEALTH_ATTENTION = 'attention';

    public const HEALTH_CRITICAL = 'critical';

    public const HEALTH_EMPTY = 'empty';

    /**
     * Indicadores por condomínio da administradora.
     *
     * Saúde financeira = adimplência: unidades sem cobrança efetivamente em atraso
     * sobre o total de unidades. ≥ 90% saudável, ≥ 70% atenção, abaixo crítica.
     *
     * @param  Collection<int, \App\Models\Condominium>  $condominiums
     * @return Collection<int, array{
     *     users: int,
     *     fines_count: int,
     *     fines_amount: float,
     *     overdue_units: int,
     *     overdue_amount: float,
     *     compliance_rate: ?float,
     *     health: string,
     *     health_label: string
     * }>
     */
    public function forCondominiums(Collection $condominiums): Collection
    {
        if ($condominiums->isEmpty()) {
            return collect();
        }

        $ids = $condominiums->pluck('id')->map(fn ($id) => (int) $id)->all();
        $fines = $this->issuedFinesByCondominium($ids);
        $overdue = $this->overdueChargesByCondominium($ids);

        return $condominiums->mapWithKeys(function ($condominium) use ($fines, $overdue) {
            $id = (int) $condominium->id;
            $fine = $fines->get($id);
            $debt = $overdue->get($id);
            $units = (int) ($condominium->units_count ?? 0);
            $overdueUnits = (int) ($debt->overdue_units ?? 0);
            $health = $this->classify($units, $overdueUnits);

            return [$id => [
                'users' => (int) ($condominium->users_count ?? 0),
                'fines_count' => (int) ($fine->fines_count ?? 0),
                'fines_amount' => (float) ($fine->fines_amount ?? 0),
                'overdue_units' => $overdueUnits,
                'overdue_amount' => (float) ($debt->overdue_amount ?? 0),
                'compliance_rate' => $health['rate'],
                'health' => $health['key'],
                'health_label' => $health['label'],
            ]];
        });
    }

    /**
     * @return array{rate: ?float, key: string, label: string}
     */
    public function classify(int $units, int $overdueUnits): array
    {
        if ($units <= 0) {
            return [
                'rate' => null,
                'key' => self::HEALTH_EMPTY,
                'label' => 'Sem unidades',
            ];
        }

        $overdueUnits = min(max($overdueUnits, 0), $units);
        $rate = round((($units - $overdueUnits) / $units) * 100, 1);

        if ($rate >= 90) {
            return ['rate' => $rate, 'key' => self::HEALTH_HEALTHY, 'label' => 'Saudável'];
        }

        if ($rate >= 70) {
            return ['rate' => $rate, 'key' => self::HEALTH_ATTENTION, 'label' => 'Atenção'];
        }

        return ['rate' => $rate, 'key' => self::HEALTH_CRITICAL, 'label' => 'Crítica'];
    }

    /**
     * @param  list<int>  $ids
     */
    protected function issuedFinesByCondominium(array $ids): Collection
    {
        if ($ids === [] || ! Schema::hasTable('fines')) {
            return collect();
        }

        return Fine::query()
            ->whereIn('condominium_id', $ids)
            ->where('status', 'issued')
            ->selectRaw('condominium_id, COUNT(*) as fines_count, SUM(amount) as fines_amount')
            ->groupBy('condominium_id')
            ->get()
            ->keyBy(fn ($row) => (int) $row->condominium_id);
    }

    /**
     * @param  list<int>  $ids
     */
    protected function overdueChargesByCondominium(array $ids): Collection
    {
        if ($ids === [] || ! Schema::hasTable('charges')) {
            return collect();
        }

        return Charge::query()
            ->whereIn('condominium_id', $ids)
            ->effectivelyOverdue()
            ->selectRaw('condominium_id, COUNT(DISTINCT unit_id) as overdue_units, SUM(amount) as overdue_amount')
            ->groupBy('condominium_id')
            ->get()
            ->keyBy(fn ($row) => (int) $row->condominium_id);
    }
}
