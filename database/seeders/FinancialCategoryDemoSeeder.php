<?php

namespace Database\Seeders;

use App\Models\BankAccount;
use App\Models\Condominium;
use App\Models\CondominiumAccount;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

/**
 * Demo temporário: despesas categorizadas para o dashboard do síndico
 * (gráficos, alertas MoM e previsão anual).
 *
 * Marcador: source_type = seed_category_demo (fácil de apagar).
 *
 * Popular:
 *   php artisan db:seed --class=FinancialCategoryDemoSeeder
 *
 * Remover só estes lançamentos:
 *   php artisan db:seed --class=FinancialCategoryDemoPurgeSeeder
 */
class FinancialCategoryDemoSeeder extends Seeder
{
    public const SOURCE_TYPE = 'seed_category_demo';

    public const NOTES_MARKER = '[SEED_CATEGORY_DEMO] Remover com FinancialCategoryDemoPurgeSeeder';

    public function run(): void
    {
        $condominium = $this->resolveCondominium();

        if (! $condominium) {
            $this->command?->error('Nenhum condomínio encontrado. Rode o DemoDataSeeder antes ou informe um condomínio ativo.');

            return;
        }

        $creator = User::query()
            ->where('condominium_id', $condominium->id)
            ->whereHas('roles', fn ($q) => $q->where('name', 'Síndico'))
            ->first()
            ?? User::query()->where('condominium_id', $condominium->id)->first();

        $bankAccountId = BankAccount::query()
            ->where('condominium_id', $condominium->id)
            ->where('active', true)
            ->orderByDesc('is_primary')
            ->value('id');

        $this->command?->info("Condomínio: {$condominium->name} (id={$condominium->id})");

        $removed = $this->purgeExisting($condominium->id);
        if ($removed > 0) {
            $this->command?->warn("Removidos {$removed} lançamento(s) demo anterior(es) antes de recriar.");
        }

        $rows = $this->buildRows(Carbon::now());
        $created = 0;

        DB::transaction(function () use ($rows, $condominium, $creator, $bankAccountId, &$created) {
            foreach ($rows as $row) {
                CondominiumAccount::create([
                    'condominium_id' => $condominium->id,
                    'bank_account_id' => $bankAccountId,
                    'type' => 'expense',
                    'status' => CondominiumAccount::STATUS_ACTIVE,
                    'source_type' => self::SOURCE_TYPE,
                    'source_id' => null,
                    'description' => $row['description'],
                    'amount' => $row['amount'],
                    'transaction_date' => $row['date'],
                    'payment_method' => $row['payment_method'] ?? 'pix',
                    'category' => $row['category'],
                    'subcategory' => $row['subcategory'] ?? null,
                    'notes' => self::NOTES_MARKER,
                    'created_by' => $creator?->id,
                ]);
                $created++;
            }
        });

        $this->command?->info("Criados {$created} lançamentos demo em condominium_accounts.");
        $this->command?->info('Abra o Dashboard do Síndico (modo financeiro completo) para ver categorias, alertas e previsão.');
        $this->command?->comment('Para apagar depois: php artisan db:seed --class=FinancialCategoryDemoPurgeSeeder');
    }

    public static function purgeForCondominium(?int $condominiumId = null): int
    {
        $query = CondominiumAccount::withTrashed()
            ->where('source_type', self::SOURCE_TYPE);

        if ($condominiumId) {
            $query->where('condominium_id', $condominiumId);
        }

        $ids = $query->pluck('id');
        if ($ids->isEmpty()) {
            return 0;
        }

        return CondominiumAccount::withTrashed()
            ->whereIn('id', $ids)
            ->forceDelete();
    }

    protected function purgeExisting(int $condominiumId): int
    {
        return self::purgeForCondominium($condominiumId);
    }

    protected function resolveCondominium(): ?Condominium
    {
        $byEmail = User::query()
            ->where('email', 'sindico@vistaverde.com')
            ->value('condominium_id');

        if ($byEmail) {
            return Condominium::query()->find($byEmail);
        }

        return Condominium::query()
            ->where('is_active', true)
            ->where(function ($q) {
                $q->where('financial_mode', 'full')
                    ->orWhereNull('financial_mode');
            })
            ->orderBy('id')
            ->first()
            ?? Condominium::query()->orderBy('id')->first();
    }

    /**
     * Cenário didático (valores em R$):
     * - energia: alta forte no mês atual (alerta crítico)
     * - pessoal / encargos: base estável com leve pressão
     * - água: queda (sinal positivo)
     * - manutenção: acima da média 3m
     * - sem categoria: 1 lançamento para o aviso de %
     *
     * @return list<array{description: string, amount: float, date: string, category: ?string, subcategory?: string, payment_method?: string}>
     */
    protected function buildRows(Carbon $reference): array
    {
        $m0 = $reference->copy()->startOfMonth();
        $m1 = $reference->copy()->subMonthsNoOverflow(1)->startOfMonth();
        $m2 = $reference->copy()->subMonthsNoOverflow(2)->startOfMonth();
        $m3 = $reference->copy()->subMonthsNoOverflow(3)->startOfMonth();

        $day = static fn (Carbon $month, int $d): string => $month->copy()->day(min($d, $month->daysInMonth))->toDateString();

        return [
            // Energia — spike no mês atual
            ['description' => '[Demo] Conta de energia — pico sazonal', 'amount' => 2850.00, 'date' => $day($m0, 8), 'category' => 'energia'],
            ['description' => '[Demo] Conta de energia', 'amount' => 1680.00, 'date' => $day($m1, 8), 'category' => 'energia'],
            ['description' => '[Demo] Conta de energia', 'amount' => 1590.00, 'date' => $day($m2, 8), 'category' => 'energia'],
            ['description' => '[Demo] Conta de energia', 'amount' => 1520.00, 'date' => $day($m3, 8), 'category' => 'energia'],

            // Pessoal — estável / leve alta
            ['description' => '[Demo] Folha porteiros e zeladoria', 'amount' => 12400.00, 'date' => $day($m0, 5), 'category' => 'pessoal', 'subcategory' => 'salary', 'payment_method' => 'bank_transfer'],
            ['description' => '[Demo] Folha porteiros e zeladoria', 'amount' => 11800.00, 'date' => $day($m1, 5), 'category' => 'pessoal', 'subcategory' => 'salary', 'payment_method' => 'bank_transfer'],
            ['description' => '[Demo] Folha porteiros e zeladoria', 'amount' => 11800.00, 'date' => $day($m2, 5), 'category' => 'pessoal', 'subcategory' => 'salary', 'payment_method' => 'bank_transfer'],
            ['description' => '[Demo] Folha porteiros e zeladoria', 'amount' => 11500.00, 'date' => $day($m3, 5), 'category' => 'pessoal', 'subcategory' => 'salary', 'payment_method' => 'bank_transfer'],
            ['description' => '[Demo] Horas extras portaria', 'amount' => 980.00, 'date' => $day($m0, 28), 'category' => 'pessoal', 'subcategory' => 'overtime'],

            // Encargos / impostos
            ['description' => '[Demo] Encargos patronais (INSS/FGTS)', 'amount' => 4100.00, 'date' => $day($m0, 10), 'category' => 'encargos', 'payment_method' => 'boleto'],
            ['description' => '[Demo] Encargos patronais (INSS/FGTS)', 'amount' => 3900.00, 'date' => $day($m1, 10), 'category' => 'encargos', 'payment_method' => 'boleto'],
            ['description' => '[Demo] Encargos patronais (INSS/FGTS)', 'amount' => 3850.00, 'date' => $day($m2, 10), 'category' => 'encargos', 'payment_method' => 'boleto'],
            ['description' => '[Demo] Encargos patronais (INSS/FGTS)', 'amount' => 3800.00, 'date' => $day($m3, 10), 'category' => 'encargos', 'payment_method' => 'boleto'],

            // Água — queda (positivo)
            ['description' => '[Demo] Conta de água e esgoto', 'amount' => 920.00, 'date' => $day($m0, 12), 'category' => 'agua'],
            ['description' => '[Demo] Conta de água e esgoto', 'amount' => 1280.00, 'date' => $day($m1, 12), 'category' => 'agua'],
            ['description' => '[Demo] Conta de água e esgoto', 'amount' => 1310.00, 'date' => $day($m2, 12), 'category' => 'agua'],
            ['description' => '[Demo] Conta de água e esgoto', 'amount' => 1250.00, 'date' => $day($m3, 12), 'category' => 'agua'],

            // Manutenção — acima da média
            ['description' => '[Demo] Reparo elevador / manutenção corretiva', 'amount' => 3200.00, 'date' => $day($m0, 15), 'category' => 'manutencao'],
            ['description' => '[Demo] Manutenção preventiva', 'amount' => 1100.00, 'date' => $day($m1, 15), 'category' => 'manutencao'],
            ['description' => '[Demo] Manutenção preventiva', 'amount' => 1050.00, 'date' => $day($m2, 15), 'category' => 'manutencao'],
            ['description' => '[Demo] Manutenção preventiva', 'amount' => 980.00, 'date' => $day($m3, 15), 'category' => 'manutencao'],

            // Limpeza, seguros, tarifas
            ['description' => '[Demo] Material de limpeza', 'amount' => 640.00, 'date' => $day($m0, 18), 'category' => 'limpeza'],
            ['description' => '[Demo] Material de limpeza', 'amount' => 580.00, 'date' => $day($m1, 18), 'category' => 'limpeza'],
            ['description' => '[Demo] Seguro predial (parcela)', 'amount' => 1450.00, 'date' => $day($m0, 3), 'category' => 'seguros', 'payment_method' => 'boleto'],
            ['description' => '[Demo] Seguro predial (parcela)', 'amount' => 1450.00, 'date' => $day($m1, 3), 'category' => 'seguros', 'payment_method' => 'boleto'],
            ['description' => '[Demo] Tarifa bancária / TED', 'amount' => 89.90, 'date' => $day($m0, 20), 'category' => 'taxas_bancarias', 'payment_method' => 'bank_transfer'],
            ['description' => '[Demo] Internet condomínio', 'amount' => 299.90, 'date' => $day($m0, 7), 'category' => 'comunicacao'],
            ['description' => '[Demo] Internet condomínio', 'amount' => 299.90, 'date' => $day($m1, 7), 'category' => 'comunicacao'],

            // Sem categoria — gera aviso de % no dashboard
            ['description' => '[Demo] Despesa avulsa sem categoria (para demonstrar aviso)', 'amount' => 350.00, 'date' => $day($m0, 22), 'category' => null],
        ];
    }
}
