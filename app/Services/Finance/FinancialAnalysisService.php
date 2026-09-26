<?php

namespace App\Services\Finance;

use App\Models\Charge;
use App\Models\CondominiumAccount;
use App\Models\Unit;
use App\Services\FinancialCategoryInsightsService;
use App\Support\ExpenseCategories;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use InvalidArgumentException;

class FinancialAnalysisService
{
    public function __construct(
        private FinancialCategoryInsightsService $categoryInsights,
    ) {}

    /**
     * Monta snapshot agregado (sem PII) para a pergunta informada.
     *
     * @return array<string, mixed>
     */
    public function buildSnapshot(int $condominiumId, string $questionKey, ?Carbon $reference = null): array
    {
        $question = config("finance_ai.questions.{$questionKey}");

        if (! is_array($question)) {
            throw new InvalidArgumentException("Pergunta financeira inválida: {$questionKey}");
        }

        $reference ??= now();
        $sections = $question['sections'] ?? ['meta'];
        $periods = $question['periods'] ?? ['6m'];
        $focusCategories = $question['focus_categories'] ?? [];
        $primaryPeriod = $this->resolvePrimaryPeriod($periods, $reference);

        $snapshot = [
            'question_key' => $questionKey,
            'question_title' => $question['title'] ?? $questionKey,
        ];

        if (in_array('meta', $sections, true)) {
            $snapshot['condominio'] = $this->buildMeta($condominiumId, $primaryPeriod);
        }

        if (in_array('receitas', $sections, true)) {
            $snapshot['receitas'] = $this->buildRevenues($condominiumId, $primaryPeriod, $reference);
        }

        if (in_array('despesas', $sections, true) || in_array('expense_evolution', $sections, true)) {
            $snapshot['despesas'] = $this->buildExpenses(
                $condominiumId,
                $primaryPeriod,
                $reference,
                $focusCategories,
                in_array('expense_evolution', $sections, true)
            );
        }

        if (in_array('resultado', $sections, true)) {
            $receitas = $snapshot['receitas']['total'] ?? $this->sumAccounts($condominiumId, 'income', $primaryPeriod['start'], $primaryPeriod['end']);
            $despesas = $snapshot['despesas']['total'] ?? $this->sumAccounts($condominiumId, 'expense', $primaryPeriod['start'], $primaryPeriod['end']);
            $snapshot['resultado'] = $this->buildResult($receitas, $despesas, $condominiumId, $primaryPeriod, $reference);
        }

        if (in_array('inadimplencia', $sections, true)) {
            $snapshot['inadimplencia'] = $this->buildDefaults($condominiumId, $primaryPeriod, $reference);
        }

        if (in_array('category_insights', $sections, true)) {
            $insights = $this->categoryInsights->build($condominiumId, $reference);
            $snapshot['insights_categoria'] = [
                'uncategorized_share' => $insights['uncategorized_share'],
                'month_total' => $insights['month_total'],
                'year_total' => $insights['year_total'],
                'top_insights' => collect($insights['insights'])
                    ->when(
                        $focusCategories !== [],
                        fn (Collection $c) => $c->filter(fn (array $row) => in_array($row['key'], $focusCategories, true))
                    )
                    ->take(8)
                    ->map(fn (array $row) => [
                        'categoria' => $row['label'],
                        'atual' => $row['current'],
                        'anterior' => $row['previous'],
                        'variacao_percentual' => $row['variation'],
                        'nivel' => $row['level'],
                        'share_mes' => $row['share_month'],
                    ])
                    ->values()
                    ->all(),
            ];
        }

        return $snapshot;
    }

    public function indicatorsHash(array $snapshot): string
    {
        $payload = $snapshot;
        unset($payload['question_key'], $payload['question_title']);

        return hash('sha256', json_encode($payload, JSON_UNESCAPED_UNICODE | JSON_THROW_ON_ERROR));
    }

    /**
     * @param  list<string>  $periods
     * @return array{key: string, label: string, start: Carbon, end: Carbon}
     */
    protected function resolvePrimaryPeriod(array $periods, Carbon $reference): array
    {
        $preferred = ['6m', '3m', '12m', 'current_month'];
        $key = '6m';

        foreach ($preferred as $candidate) {
            if (in_array($candidate, $periods, true)) {
                $key = $candidate;
                break;
            }
        }

        if ($periods !== [] && ! in_array($key, $periods, true)) {
            $key = $periods[0];
        }

        return $this->periodWindow($key, $reference);
    }

    /**
     * @return array{key: string, label: string, start: Carbon, end: Carbon}
     */
    protected function periodWindow(string $key, Carbon $reference): array
    {
        $end = $reference->copy()->endOfDay();

        return match ($key) {
            'current_month' => [
                'key' => $key,
                'label' => 'mês atual',
                'start' => $reference->copy()->startOfMonth(),
                'end' => $end,
            ],
            '3m' => [
                'key' => $key,
                'label' => 'últimos 3 meses',
                'start' => $reference->copy()->subMonthsNoOverflow(2)->startOfMonth(),
                'end' => $end,
            ],
            '12m' => [
                'key' => $key,
                'label' => 'últimos 12 meses',
                'start' => $reference->copy()->subMonthsNoOverflow(11)->startOfMonth(),
                'end' => $end,
            ],
            default => [
                'key' => '6m',
                'label' => 'últimos 6 meses',
                'start' => $reference->copy()->subMonthsNoOverflow(5)->startOfMonth(),
                'end' => $end,
            ],
        };
    }

    /**
     * @param  array{key: string, label: string, start: Carbon, end: Carbon}  $period
     * @return array<string, mixed>
     */
    protected function buildMeta(int $condominiumId, array $period): array
    {
        return [
            'unidades' => Unit::query()->where('condominium_id', $condominiumId)->count(),
            'periodo_analise' => $period['label'],
            'periodo_inicio' => $period['start']->toDateString(),
            'periodo_fim' => $period['end']->toDateString(),
        ];
    }

    /**
     * @param  array{key: string, label: string, start: Carbon, end: Carbon}  $period
     * @return array<string, mixed>
     */
    protected function buildRevenues(int $condominiumId, array $period, Carbon $reference): array
    {
        $total = $this->sumAccounts($condominiumId, 'income', $period['start'], $period['end']);
        $fromCharges = $this->sumAccounts($condominiumId, 'income', $period['start'], $period['end'], 'charge');
        $manual = round($total - $fromCharges, 2);

        $prev = $this->previousPeriod($period);
        $prevTotal = $this->sumAccounts($condominiumId, 'income', $prev['start'], $prev['end']);

        return [
            'total' => $total,
            'taxa_condominial' => $fromCharges,
            'outras' => $manual,
            'evolucao_percentual' => $this->percentChange($prevTotal, $total),
            'serie_mensal' => $this->monthlySeries($condominiumId, 'income', $period['start'], $period['end']),
        ];
    }

    /**
     * @param  array{key: string, label: string, start: Carbon, end: Carbon}  $period
     * @param  list<string>  $focusCategories
     * @return array<string, mixed>
     */
    protected function buildExpenses(
        int $condominiumId,
        array $period,
        Carbon $reference,
        array $focusCategories,
        bool $includeEvolution
    ): array {
        $total = $this->sumAccounts($condominiumId, 'expense', $period['start'], $period['end']);
        $prev = $this->previousPeriod($period);
        $prevTotal = $this->sumAccounts($condominiumId, 'expense', $prev['start'], $prev['end']);

        $categories = $this->expenseCategoriesForPeriod($condominiumId, $period['start'], $period['end'], $total);

        if ($focusCategories !== []) {
            $categories = array_values(array_filter(
                $categories,
                fn (array $row) => in_array($row['categoria_key'], $focusCategories, true)
            ));
        }

        $payload = [
            'total' => $total,
            'evolucao_percentual' => $this->percentChange($prevTotal, $total),
            'categorias' => array_slice($categories, 0, 12),
            'serie_mensal' => $this->monthlySeries($condominiumId, 'expense', $period['start'], $period['end']),
        ];

        if ($includeEvolution) {
            $half = (int) max(1, floor($period['start']->diffInMonths($period['end']) / 2));
            $mid = $period['start']->copy()->addMonthsNoOverflow($half)->subDay()->endOfDay();
            $firstEnd = $mid->lessThan($period['start']) ? $period['start']->copy()->endOfDay() : $mid;
            $secondStart = $firstEnd->copy()->addDay()->startOfDay();

            $firstCats = $this->expenseTotalsMap($condominiumId, $period['start'], $firstEnd);
            $secondCats = $this->expenseTotalsMap($condominiumId, $secondStart, $period['end']);

            $evolution = [];
            $keys = collect($firstCats->keys())->merge($secondCats->keys())->unique();

            foreach ($keys as $key) {
                if ($key === '__none') {
                    continue;
                }
                $inicial = (float) ($firstCats[$key] ?? 0);
                $atual = (float) ($secondCats[$key] ?? 0);
                if ($inicial <= 0 && $atual <= 0) {
                    continue;
                }
                $evolution[] = [
                    'categoria' => ExpenseCategories::label($key),
                    'categoria_key' => $key,
                    'valor_inicial' => round($inicial, 2),
                    'valor_atual' => round($atual, 2),
                    'diferenca_absoluta' => round($atual - $inicial, 2),
                    'percentual_crescimento' => $this->percentChange($inicial, $atual),
                ];
            }

            usort($evolution, fn (array $a, array $b) => $b['percentual_crescimento'] <=> $a['percentual_crescimento']);
            $payload['evolucao_categorias'] = array_slice($evolution, 0, 10);
        }

        return $payload;
    }

    /**
     * @param  array{key: string, label: string, start: Carbon, end: Carbon}  $period
     * @return array<string, mixed>
     */
    protected function buildResult(float $receitas, float $despesas, int $condominiumId, array $period, Carbon $reference): array
    {
        $saldo = round($receitas - $despesas, 2);
        $margem = $receitas > 0 ? round(($saldo / $receitas) * 100, 2) : 0.0;

        $incomeSeries = $this->monthlySeries($condominiumId, 'income', $period['start'], $period['end']);
        $expenseSeries = $this->monthlySeries($condominiumId, 'expense', $period['start'], $period['end']);
        $byMonth = [];

        foreach ($incomeSeries as $row) {
            $byMonth[$row['mes']]['receitas'] = $row['total'];
            $byMonth[$row['mes']]['despesas'] = $byMonth[$row['mes']]['despesas'] ?? 0;
        }
        foreach ($expenseSeries as $row) {
            $byMonth[$row['mes']]['despesas'] = $row['total'];
            $byMonth[$row['mes']]['receitas'] = $byMonth[$row['mes']]['receitas'] ?? 0;
        }

        $serie = [];
        foreach ($byMonth as $mes => $vals) {
            $serie[] = [
                'mes' => $mes,
                'receitas' => round((float) $vals['receitas'], 2),
                'despesas' => round((float) $vals['despesas'], 2),
                'saldo' => round((float) $vals['receitas'] - (float) $vals['despesas'], 2),
            ];
        }

        usort($serie, fn (array $a, array $b) => strcmp($a['mes'], $b['mes']));

        return [
            'receitas' => round($receitas, 2),
            'despesas' => round($despesas, 2),
            'saldo' => $saldo,
            'margem_percentual' => $margem,
            'serie_mensal' => $serie,
        ];
    }

    /**
     * @param  array{key: string, label: string, start: Carbon, end: Carbon}  $period
     * @return array<string, mixed>
     */
    protected function buildDefaults(int $condominiumId, array $period, Carbon $reference): array
    {
        $unitsCount = Unit::query()->where('condominium_id', $condominiumId)->count();

        $openQuery = Charge::query()
            ->where('condominium_id', $condominiumId)
            ->whereIn('status', ['pending', 'overdue']);

        $valor = (float) (clone $openQuery)->sum('amount');
        $unidades = (int) (clone $openQuery)->distinct('unit_id')->count('unit_id');
        $percentual = $unitsCount > 0 ? round(($unidades / $unitsCount) * 100, 2) : 0.0;

        $prev = $this->previousPeriod($period);
        $prevValor = (float) Charge::query()
            ->where('condominium_id', $condominiumId)
            ->whereIn('status', ['pending', 'overdue'])
            ->whereBetween('due_date', [$prev['start']->toDateString(), $prev['end']->toDateString()])
            ->sum('amount');

        $periodOpen = (float) Charge::query()
            ->where('condominium_id', $condominiumId)
            ->whereIn('status', ['pending', 'overdue'])
            ->whereBetween('due_date', [$period['start']->toDateString(), $period['end']->toDateString()])
            ->sum('amount');

        return [
            'valor' => round($valor, 2),
            'unidades_inadimplentes' => $unidades,
            'percentual' => $percentual,
            'valor_periodo' => round($periodOpen, 2),
            'evolucao_percentual' => $this->percentChange($prevValor, $periodOpen),
        ];
    }

    /**
     * @return list<array{categoria: string, categoria_key: string, total: float, percentual_total: float}>
     */
    protected function expenseCategoriesForPeriod(int $condominiumId, Carbon $start, Carbon $end, float $total): array
    {
        $rows = $this->expenseTotalsMap($condominiumId, $start, $end);

        return $rows
            ->map(function (float $amount, string $key) use ($total) {
                return [
                    'categoria_key' => $key,
                    'categoria' => $key === '__none' ? 'Não informada' : ExpenseCategories::label($key),
                    'total' => round($amount, 2),
                    'percentual_total' => $total > 0 ? round(($amount / $total) * 100, 2) : 0.0,
                ];
            })
            ->sortByDesc('total')
            ->values()
            ->all();
    }

    /**
     * @return Collection<string, float>
     */
    protected function expenseTotalsMap(int $condominiumId, Carbon $start, Carbon $end): Collection
    {
        return CondominiumAccount::query()
            ->where('condominium_id', $condominiumId)
            ->expense()
            ->countsInBalance()
            ->whereBetween('transaction_date', [$start->toDateString(), $end->toDateString()])
            ->selectRaw('COALESCE(category, \'__none\') as category_key, SUM(amount) as total')
            ->groupBy('category_key')
            ->pluck('total', 'category_key')
            ->map(fn ($v) => (float) $v);
    }

    protected function sumAccounts(
        int $condominiumId,
        string $type,
        Carbon $start,
        Carbon $end,
        ?string $sourceType = null
    ): float {
        $query = CondominiumAccount::query()
            ->where('condominium_id', $condominiumId)
            ->where('type', $type)
            ->countsInBalance()
            ->whereBetween('transaction_date', [$start->toDateString(), $end->toDateString()]);

        if ($sourceType !== null) {
            $query->where('source_type', $sourceType);
        }

        return round((float) $query->sum('amount'), 2);
    }

    /**
     * @return list<array{mes: string, total: float}>
     */
    protected function monthlySeries(int $condominiumId, string $type, Carbon $start, Carbon $end): array
    {
        return $this->monthlySeriesPhp($condominiumId, $type, $start, $end);
    }

    /**
     * @return list<array{mes: string, total: float}>
     */
    protected function monthlySeriesPhp(int $condominiumId, string $type, Carbon $start, Carbon $end): array
    {
        $entries = CondominiumAccount::query()
            ->where('condominium_id', $condominiumId)
            ->where('type', $type)
            ->countsInBalance()
            ->whereBetween('transaction_date', [$start->toDateString(), $end->toDateString()])
            ->get(['transaction_date', 'amount']);

        $grouped = [];
        foreach ($entries as $entry) {
            $mes = Carbon::parse($entry->transaction_date)->format('Y-m');
            $grouped[$mes] = ($grouped[$mes] ?? 0) + (float) $entry->amount;
        }

        ksort($grouped);

        return collect($grouped)->map(fn (float $total, string $mes) => [
            'mes' => $mes,
            'total' => round($total, 2),
        ])->values()->all();
    }

    /**
     * @param  array{key: string, label: string, start: Carbon, end: Carbon}  $period
     * @return array{start: Carbon, end: Carbon}
     */
    protected function previousPeriod(array $period): array
    {
        $days = max(1, $period['start']->diffInDays($period['end']) + 1);

        return [
            'start' => $period['start']->copy()->subDays($days),
            'end' => $period['start']->copy()->subDay()->endOfDay(),
        ];
    }

    protected function percentChange(float $previous, float $current): float
    {
        if ($previous <= 0) {
            return $current > 0 ? 100.0 : 0.0;
        }

        return round((($current - $previous) / $previous) * 100, 2);
    }
}
