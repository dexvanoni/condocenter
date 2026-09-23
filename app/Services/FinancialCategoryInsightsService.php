<?php

namespace App\Services;

use App\Models\CondominiumAccount;
use App\Support\ExpenseCategories;
use Carbon\Carbon;
use Illuminate\Support\Collection;

class FinancialCategoryInsightsService
{
    /**
     * @return array{
     *   categories: Collection,
     *   chart: list<array{category: string, label: string, total: float}>,
     *   insights: list<array<string, mixed>>,
     *   forecast: list<array<string, mixed>>,
     *   uncategorized_share: float,
     *   month_total: float,
     *   year_total: float
     * }
     */
    public function build(int $condominiumId, ?Carbon $reference = null): array
    {
        $reference ??= now();
        $monthStart = $reference->copy()->startOfMonth();
        $monthEnd = $reference->copy()->endOfMonth();
        $yearStart = $reference->copy()->startOfYear();

        $yearRows = $this->expenseTotalsByCategory($condominiumId, $yearStart, $reference->copy()->endOfDay());
        $monthRows = $this->expenseTotalsByCategory($condominiumId, $monthStart, $monthEnd);

        $prevMonthStart = $reference->copy()->subMonthNoOverflow()->startOfMonth();
        $prevMonthEnd = $reference->copy()->subMonthNoOverflow()->endOfMonth();
        $prevRows = $this->expenseTotalsByCategory($condominiumId, $prevMonthStart, $prevMonthEnd);

        $avg3 = $this->trailingAverageByCategory($condominiumId, $reference, 3);

        $yearTotal = (float) $yearRows->sum('total');
        $monthTotal = (float) $monthRows->sum('total');
        $uncategorized = (float) ($monthRows->get('__none')['total'] ?? 0);
        $uncategorizedShare = $monthTotal > 0 ? round(($uncategorized / $monthTotal) * 100, 1) : 0.0;

        $chart = $yearRows
            ->reject(fn ($row, $key) => $key === '__none' && (float) $row['total'] <= 0)
            ->sortByDesc('total')
            ->take(8)
            ->values()
            ->map(fn (array $row) => [
                'category' => $row['key'],
                'label' => $row['label'],
                'total_despesas' => round((float) $row['total'], 2),
            ]);

        $insights = [];
        $forecast = [];
        $remainingMonths = max(0, 12 - (int) $reference->month);

        foreach (ExpenseCategories::keys() as $key) {
            $current = (float) ($monthRows->get($key)['total'] ?? 0);
            $previous = (float) ($prevRows->get($key)['total'] ?? 0);
            $avg = (float) ($avg3[$key] ?? 0);
            $yearCat = (float) ($yearRows->get($key)['total'] ?? 0);

            if ($current <= 0 && $previous <= 0 && $avg <= 0) {
                continue;
            }

            $variation = $previous > 0
                ? (($current - $previous) / $previous) * 100
                : ($current > 0 ? 100.0 : 0.0);

            $level = $this->attentionLevel($variation, $current, $avg);
            $projectedYearEnd = $yearCat + ($avg * $remainingMonths);

            $insights[] = [
                'key' => $key,
                'label' => ExpenseCategories::label($key),
                'icon' => ExpenseCategories::icons()[$key] ?? 'bi-tag',
                'current' => round($current, 2),
                'previous' => round($previous, 2),
                'avg3' => round($avg, 2),
                'variation' => round($variation, 1),
                'level' => $level['level'],
                'message' => $level['message'],
                'share_month' => $monthTotal > 0 ? round(($current / $monthTotal) * 100, 1) : 0.0,
            ];

            if ($avg > 0 || $current > 0) {
                $forecast[] = [
                    'key' => $key,
                    'label' => ExpenseCategories::label($key),
                    'monthly_run_rate' => round(max($avg, $current), 2),
                    'projected_year_end' => round($projectedYearEnd, 2),
                    'year_to_date' => round($yearCat, 2),
                ];
            }
        }

        usort($insights, function (array $a, array $b) {
            $order = ['critical' => 0, 'attention' => 1, 'watch' => 2, 'positive' => 3, 'stable' => 4];

            return ($order[$a['level']] ?? 9) <=> ($order[$b['level']] ?? 9)
                ?: $b['current'] <=> $a['current'];
        });

        usort($forecast, fn (array $a, array $b) => $b['projected_year_end'] <=> $a['projected_year_end']);

        return [
            'categories' => $monthRows->values(),
            'chart' => $chart->all(),
            'insights' => array_slice($insights, 0, 8),
            'forecast' => array_slice($forecast, 0, 6),
            'uncategorized_share' => $uncategorizedShare,
            'month_total' => round($monthTotal, 2),
            'year_total' => round($yearTotal, 2),
        ];
    }

    /**
     * @return Collection<string, array{key: string, label: string, total: float}>
     */
    protected function expenseTotalsByCategory(int $condominiumId, Carbon $start, Carbon $end): Collection
    {
        $rows = CondominiumAccount::query()
            ->where('condominium_id', $condominiumId)
            ->expense()
            ->countsInBalance()
            ->whereBetween('transaction_date', [$start->toDateString(), $end->toDateString()])
            ->selectRaw('COALESCE(category, \'__none\') as category_key, SUM(amount) as total')
            ->groupBy('category_key')
            ->pluck('total', 'category_key');

        return $rows->map(function ($total, $key) {
            return [
                'key' => $key,
                'label' => $key === '__none' ? 'Não informada' : ExpenseCategories::label($key),
                'total' => (float) $total,
            ];
        });
    }

    /**
     * @return array<string, float>
     */
    protected function trailingAverageByCategory(int $condominiumId, Carbon $reference, int $months): array
    {
        $totals = [];
        $counts = [];

        for ($i = 1; $i <= $months; $i++) {
            $cursor = $reference->copy()->subMonthsNoOverflow($i);
            $rows = $this->expenseTotalsByCategory(
                $condominiumId,
                $cursor->copy()->startOfMonth(),
                $cursor->copy()->endOfMonth()
            );

            foreach ($rows as $key => $row) {
                if ($key === '__none') {
                    continue;
                }
                $totals[$key] = ($totals[$key] ?? 0) + (float) $row['total'];
                $counts[$key] = ($counts[$key] ?? 0) + 1;
            }
        }

        $averages = [];
        foreach ($totals as $key => $sum) {
            $averages[$key] = $sum / max(1, $counts[$key] ?? 1);
        }

        return $averages;
    }

    /**
     * @return array{level: string, message: string}
     */
    protected function attentionLevel(float $variation, float $current, float $avg): array
    {
        if ($current <= 0 && $avg <= 0) {
            return ['level' => 'stable', 'message' => 'Sem despesa nesta categoria no período.'];
        }

        if ($variation >= 30) {
            return [
                'level' => 'critical',
                'message' => 'Custo bem acima do mês anterior — revisar contratos, consumo ou horas extras.',
            ];
        }

        if ($variation >= 15) {
            return [
                'level' => 'attention',
                'message' => 'Alta relevante vs mês anterior — vale acompanhar de perto e buscar redução.',
            ];
        }

        if ($avg > 0 && $current > $avg * 1.2) {
            return [
                'level' => 'watch',
                'message' => 'Acima da média dos últimos 3 meses — tendência de pressão no orçamento.',
            ];
        }

        if ($variation <= -15) {
            return [
                'level' => 'positive',
                'message' => 'Queda vs mês anterior — bom sinal de controle de gastos nesta categoria.',
            ];
        }

        return [
            'level' => 'stable',
            'message' => 'Estável em relação ao mês anterior e à média recente.',
        ];
    }
}
