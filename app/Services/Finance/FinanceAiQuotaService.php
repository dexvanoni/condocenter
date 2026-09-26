<?php

namespace App\Services\Finance;

use App\Models\AiFinancialConsultation;
use App\Models\Condominium;
use App\Models\Organization;
use Carbon\Carbon;
use Illuminate\Support\Collection;

class FinanceAiQuotaService
{
    /**
     * @return array{
     *   organization: Organization|null,
     *   limit: int|null,
     *   used: int,
     *   remaining: int|null,
     *   shared: bool,
     *   period_label: string,
     *   allowed: bool,
     *   message: string|null
     * }
     */
    public function quotaForCondominium(int $condominiumId, ?Carbon $reference = null): array
    {
        $reference ??= now();
        $condo = Condominium::query()->with('organization')->find($condominiumId);
        $organization = $condo?->organization;

        if (! $organization) {
            return [
                'organization' => null,
                'limit' => null,
                'used' => 0,
                'remaining' => null,
                'shared' => false,
                'period_label' => $reference->translatedFormat('F/Y'),
                'allowed' => false,
                'message' => 'Organização do condomínio não encontrada. Contate o suporte SindCON.',
            ];
        }

        return $this->quotaForOrganization($organization, $reference);
    }

    /**
     * @return array{
     *   organization: Organization,
     *   limit: int|null,
     *   used: int,
     *   remaining: int|null,
     *   shared: bool,
     *   period_label: string,
     *   allowed: bool,
     *   message: string|null
     * }
     */
    public function quotaForOrganization(Organization $organization, ?Carbon $reference = null): array
    {
        $reference ??= now();
        $limit = $organization->llm_monthly_limit;
        $shared = $organization->isManagementCompany();
        $used = $this->usedThisMonth($organization, $reference);
        $periodLabel = $reference->copy()->locale('pt_BR')->translatedFormat('F/Y');

        if ($limit === null) {
            return [
                'organization' => $organization,
                'limit' => null,
                'used' => $used,
                'remaining' => null,
                'shared' => $shared,
                'period_label' => $periodLabel,
                'allowed' => false,
                'message' => $shared
                    ? 'O limite mensal de consultas do Consultor ainda não foi configurado para esta administradora. Contate a plataforma SindCON.'
                    : 'O limite mensal de consultas do Consultor ainda não foi configurado para este condomínio. Contate a plataforma SindCON.',
            ];
        }

        $limit = (int) $limit;
        $remaining = max(0, $limit - $used);
        $allowed = $used < $limit;

        return [
            'organization' => $organization,
            'limit' => $limit,
            'used' => $used,
            'remaining' => $remaining,
            'shared' => $shared,
            'period_label' => $periodLabel,
            'allowed' => $allowed,
            'message' => $allowed
                ? null
                : ($shared
                    ? "Limite mensal de consultas do Consultor atingido para a administradora ({$used}/{$limit} em {$periodLabel})."
                    : "Limite mensal de consultas do Consultor atingido para este condomínio ({$used}/{$limit} em {$periodLabel})."),
        ];
    }

    public function usedThisMonth(Organization $organization, ?Carbon $reference = null): int
    {
        $reference ??= now();
        $condoIds = $this->scopedCondominiumIds($organization);

        if ($condoIds->isEmpty()) {
            return 0;
        }

        return AiFinancialConsultation::query()
            ->whereIn('condominium_id', $condoIds->all())
            ->where('status', 'success')
            ->whereBetween('created_at', [
                $reference->copy()->startOfMonth(),
                $reference->copy()->endOfMonth(),
            ])
            ->count();
    }

    /**
     * @return Collection<int, int>
     */
    public function scopedCondominiumIds(Organization $organization): Collection
    {
        return $organization->condominiums()->pluck('id')->map(fn ($id) => (int) $id)->values();
    }
}
