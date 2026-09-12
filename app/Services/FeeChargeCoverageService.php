<?php

namespace App\Services;

use App\Models\Charge;
use App\Models\Fee;
use App\Models\FeeUnitConfiguration;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;

class FeeChargeCoverageService
{
    /**
     * Audita cobertura de cobranças por unidade para taxas mensais ativas na competência informada.
     */
    public function audit(int $condominiumId, Carbon $referenceMonth): array
    {
        $referenceMonth = $referenceMonth->copy()->startOfMonth();
        $competencePeriod = $referenceMonth->format('Y-m');
        $competenceLabel = $referenceMonth->translatedFormat('F \d\e Y');
        $checkDate = $referenceMonth->copy()->endOfMonth();

        $fees = Fee::byCondominium($condominiumId)
            ->active()
            ->where('recurrence', 'monthly')
            ->get()
            ->filter(fn (Fee $fee) => ! $fee->isInvalidated() && $fee->isActiveForDate($checkDate));

        $missingUnits = [];
        $feeSummaries = [];
        $totalExpected = 0;
        $totalCovered = 0;

        foreach ($fees as $fee) {
            $feeAudit = $this->auditFee($fee, $competencePeriod, $checkDate);
            $feeSummaries[] = $feeAudit['summary'];
            $missingUnits = array_merge($missingUnits, $feeAudit['missing_units']);
            $totalExpected += $feeAudit['summary']['expected_count'];
            $totalCovered += $feeAudit['summary']['covered_count'];
        }

        return [
            'competence_period' => $competencePeriod,
            'competence_label' => $competenceLabel,
            'fees' => $feeSummaries,
            'missing_units' => $missingUnits,
            'total_expected' => $totalExpected,
            'total_covered' => $totalCovered,
            'total_missing' => count($missingUnits),
            'is_complete' => count($missingUnits) === 0,
        ];
    }

    /**
     * Compara unidades configuradas na taxa com cobranças existentes na competência.
     *
     * @return array{summary: array<string, mixed>, missing_units: array<int, array<string, mixed>>}
     */
    public function auditFee(Fee $fee, string $competencePeriod, Carbon $checkDate): array
    {
        $configurations = $this->expectedConfigurations($fee, $checkDate);

        $chargedUnitIds = Charge::query()
            ->where('fee_id', $fee->id)
            ->where('status', '!=', 'cancelled')
            ->get()
            ->filter(fn (Charge $charge) => $charge->competencePeriod() === $competencePeriod)
            ->pluck('unit_id')
            ->unique()
            ->flip();

        $missingUnits = [];

        foreach ($configurations as $configuration) {
            if ($chargedUnitIds->has($configuration->unit_id)) {
                continue;
            }

            $amount = (float) ($configuration->custom_amount ?? $fee->amount);

            $missingUnits[] = [
                'fee_id' => $fee->id,
                'fee_name' => $fee->name,
                'unit_id' => $configuration->unit_id,
                'unit_label' => $configuration->unit->full_identifier,
                'morador' => $configuration->unit->morador?->name,
                'expected_amount' => $amount,
                'expected_amount_label' => $this->money($amount),
                'competence_period' => $competencePeriod,
                'competence_label' => $this->competenceLabel($competencePeriod),
            ];
        }

        $expectedCount = $configurations->count();
        $missingCount = count($missingUnits);

        return [
            'summary' => [
                'fee_id' => $fee->id,
                'fee_name' => $fee->name,
                'expected_count' => $expectedCount,
                'covered_count' => max(0, $expectedCount - $missingCount),
                'missing_count' => $missingCount,
            ],
            'missing_units' => $missingUnits,
        ];
    }

    /**
     * Resumo de cobertura para a tela de detalhes da taxa (última competência gerada).
     */
    public function summarizeForFee(Fee $fee): array
    {
        $fee->loadCount('configurations');

        $charges = Charge::query()
            ->where('fee_id', $fee->id)
            ->where('status', '!=', 'cancelled')
            ->get();

        $competencePeriods = $charges
            ->map(fn (Charge $charge) => $charge->competencePeriod())
            ->filter()
            ->unique()
            ->sort()
            ->values();

        $latestCompetence = $competencePeriods->last();
        $latestCompetenceLabel = $this->competenceLabel($latestCompetence);

        if (! $latestCompetence) {
            return [
                'latest_competence' => null,
                'latest_competence_label' => null,
                'configured_units' => (int) $fee->configurations_count,
                'charged_units' => 0,
                'missing_units' => (int) $fee->configurations_count,
                'missing_unit_details' => [],
            ];
        }

        $checkDate = preg_match('/^\d{4}-\d{2}$/', (string) $latestCompetence)
            ? Carbon::createFromFormat('Y-m', $latestCompetence)->endOfMonth()
            : now();

        $audit = $this->auditFee($fee, $latestCompetence, $checkDate);

        return [
            'latest_competence' => $latestCompetence,
            'latest_competence_label' => $latestCompetenceLabel,
            'configured_units' => $audit['summary']['expected_count'],
            'charged_units' => $audit['summary']['covered_count'],
            'missing_units' => $audit['summary']['missing_count'],
            'missing_unit_details' => $audit['missing_units'],
        ];
    }

    private function expectedConfigurations(Fee $fee, Carbon $checkDate): Collection
    {
        return $fee->configurations()
            ->with(['unit.morador'])
            ->whereNull('deleted_at')
            ->get()
            ->filter(function (FeeUnitConfiguration $configuration) use ($checkDate) {
                if (! $configuration->unit?->is_active) {
                    return false;
                }

                return $configuration->isActiveForDate($checkDate);
            });
    }

    private function competenceLabel(?string $competencePeriod): ?string
    {
        if (! $competencePeriod) {
            return null;
        }

        if (preg_match('/^\d{4}-\d{2}$/', $competencePeriod)) {
            return Carbon::createFromFormat('Y-m', $competencePeriod)->translatedFormat('M/Y');
        }

        return $competencePeriod;
    }

    private function money(float $value): string
    {
        return 'R$ '.number_format($value, 2, ',', '.');
    }
}
