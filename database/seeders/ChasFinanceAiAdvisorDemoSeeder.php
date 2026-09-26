<?php

namespace Database\Seeders;

use App\Models\BankAccount;
use App\Models\Charge;
use App\Models\Condominium;
use App\Models\CondominiumAccount;
use App\Models\Fee;
use App\Models\Fine;
use App\Models\FineRecipient;
use App\Models\Payment;
use App\Models\Unit;
use App\Models\User;
use App\Services\ChargeSettlementService;
use App\Support\UnitModels;
use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

/**
 * Dados financeiros ricos só no condomínio CHAS, para testar o Consultor de IA.
 *
 * Marcadores:
 * - condominium_accounts.source_type = seed_chas_finance_ai
 * - notes / metadata.seed = seed_chas_finance_ai
 * - e-mails dos moradores demo: seed.chas.ai.*@example.test
 *
 * Popular:
 *   php artisan db:seed --class=ChasFinanceAiAdvisorDemoSeeder
 *
 * Remover tudo deste seed:
 *   php artisan db:seed --class=ChasFinanceAiAdvisorDemoPurgeSeeder
 */
class ChasFinanceAiAdvisorDemoSeeder extends Seeder
{
    public const SOURCE_TYPE = 'seed_chas_finance_ai';

    public const NOTES_MARKER = '[SEED_CHAS_FINANCE_AI] Remover com ChasFinanceAiAdvisorDemoPurgeSeeder';

    public const EMAIL_PREFIX = 'seed.chas.ai.';

    public const CONDOMINIUM_NAME = 'CHAS';

    public function run(): void
    {
        $condominium = $this->resolveChas();

        if (! $condominium) {
            $this->command?->error('Condomínio CHAS não encontrado.');

            return;
        }

        $syndic = $this->resolveSyndic($condominium->id);

        if (! $syndic) {
            $this->command?->error('Nenhum síndico encontrado para o CHAS. Abortando.');

            return;
        }

        $this->command?->info("Condomínio: {$condominium->name} (id={$condominium->id})");
        $this->command?->info("Síndico: {$syndic->email} (id={$syndic->id})");

        $purged = self::purgeForCondominium($condominium->id);
        if ($purged > 0) {
            $this->command?->warn("Removidos {$purged} registro(s) demo anterior(es) antes de recriar.");
        }

        $now = Carbon::now();

        DB::transaction(function () use ($condominium, $syndic, $now) {
            $bank = $this->ensureBankAccount($condominium->id);
            $units = $this->ensureUnitsAndResidents($condominium->id);
            $fees = $this->createFees($condominium->id, $bank->id, $now);
            $this->createMonthlyChargesAndPayments($condominium, $units, $fees, $syndic, $now);
            $this->createExpensesAndManualIncomes($condominium->id, $bank->id, $syndic->id, $now);
            $this->createFines($condominium, $units, $syndic, $now);
        });

        $this->command?->info('Seed CHAS (Consultor IA) concluído.');
        $this->command?->comment('Para apagar: php artisan db:seed --class=ChasFinanceAiAdvisorDemoPurgeSeeder');
    }

    public static function purgeForCondominium(?int $condominiumId = null): int
    {
        $condoIds = $condominiumId
            ? collect([$condominiumId])
            : Condominium::query()
                ->where('name', self::CONDOMINIUM_NAME)
                ->pluck('id');

        if ($condoIds->isEmpty()) {
            return 0;
        }

        $removed = 0;

        foreach ($condoIds as $id) {
            $removed += self::purgeOneCondominium((int) $id);
        }

        return $removed;
    }

    protected static function purgeOneCondominium(int $condominiumId): int
    {
        $removed = 0;

        $seedChargeIds = Charge::withTrashed()
            ->where('condominium_id', $condominiumId)
            ->where(function ($q) {
                $q->where('metadata->seed', self::SOURCE_TYPE)
                    ->orWhere('description', 'like', '%'.self::NOTES_MARKER.'%');
            })
            ->pluck('id');

        $fineIds = Fine::withTrashed()
            ->where('condominium_id', $condominiumId)
            ->where('notes', self::NOTES_MARKER)
            ->pluck('id');

        $fineChargeIds = FineRecipient::query()
            ->whereIn('fine_id', $fineIds)
            ->whereNotNull('charge_id')
            ->pluck('charge_id');

        $allChargeIds = $seedChargeIds->merge($fineChargeIds)->unique()->values();

        $paymentIds = Payment::withTrashed()
            ->where(function ($q) use ($allChargeIds) {
                $q->where('notes', self::NOTES_MARKER);
                if ($allChargeIds->isNotEmpty()) {
                    $q->orWhereIn('charge_id', $allChargeIds);
                }
            })
            ->pluck('id');

        // Contas do caixa (marcadas + vinculadas a cobranças/pagamentos do seed)
        $accountQuery = CondominiumAccount::withTrashed()
            ->where('condominium_id', $condominiumId)
            ->where(function ($q) use ($allChargeIds, $paymentIds) {
                $q->where('source_type', self::SOURCE_TYPE)
                    ->orWhere('notes', self::NOTES_MARKER);

                if ($allChargeIds->isNotEmpty()) {
                    $q->orWhere(function ($inner) use ($allChargeIds) {
                        $inner->where('source_type', 'charge')
                            ->whereIn('source_id', $allChargeIds);
                    });
                }

                if ($paymentIds->isNotEmpty()) {
                    $q->orWhere(function ($inner) use ($paymentIds) {
                        $inner->where('source_type', 'asaas_gateway_fee')
                            ->whereIn('source_id', $paymentIds);
                    });
                }
            });

        $removed += (int) $accountQuery->forceDelete();

        if ($paymentIds->isNotEmpty()) {
            $removed += (int) Payment::withTrashed()->whereIn('id', $paymentIds)->forceDelete();
        }

        if ($fineIds->isNotEmpty()) {
            $removed += (int) FineRecipient::query()->whereIn('fine_id', $fineIds)->delete();
            $removed += (int) Fine::withTrashed()->whereIn('id', $fineIds)->forceDelete();
        }

        if ($allChargeIds->isNotEmpty()) {
            $removed += (int) Charge::withTrashed()->whereIn('id', $allChargeIds)->forceDelete();
        }

        $removed += (int) Fee::withTrashed()
            ->where('condominium_id', $condominiumId)
            ->where('metadata->seed', self::SOURCE_TYPE)
            ->forceDelete();

        $seedUserIds = User::withTrashed()
            ->where('condominium_id', $condominiumId)
            ->where('email', 'like', self::EMAIL_PREFIX.'%@example.test')
            ->pluck('id');

        $seedUnitIds = Unit::withTrashed()
            ->where('condominium_id', $condominiumId)
            ->where('notes', self::NOTES_MARKER)
            ->pluck('id');

        if ($seedUserIds->isNotEmpty()) {
            DB::table('model_has_roles')->whereIn('model_id', $seedUserIds)->delete();
            $removed += (int) User::withTrashed()->whereIn('id', $seedUserIds)->forceDelete();
        }

        if ($seedUnitIds->isNotEmpty()) {
            $removed += (int) Unit::withTrashed()->whereIn('id', $seedUnitIds)->forceDelete();
        }

        $removed += (int) BankAccount::withTrashed()
            ->where('condominium_id', $condominiumId)
            ->where('notes', self::NOTES_MARKER)
            ->forceDelete();

        return $removed;
    }

    protected function resolveChas(): ?Condominium
    {
        return Condominium::query()
            ->whereRaw('UPPER(name) = ?', [self::CONDOMINIUM_NAME])
            ->orderBy('id')
            ->first()
            ?? Condominium::query()->find(1);
    }

    protected function resolveSyndic(int $condominiumId): ?User
    {
        return User::query()
            ->where('condominium_id', $condominiumId)
            ->whereHas('roles', fn ($q) => $q->where('name', 'Síndico'))
            ->first()
            ?? User::query()
                ->whereHas('managedCondominiums', fn ($q) => $q->where('condominiums.id', $condominiumId))
                ->whereHas('roles', fn ($q) => $q->where('name', 'Síndico'))
                ->first();
    }

    protected function ensureBankAccount(int $condominiumId): BankAccount
    {
        $existing = BankAccount::query()
            ->where('condominium_id', $condominiumId)
            ->where('notes', self::NOTES_MARKER)
            ->first();

        if ($existing) {
            return $existing;
        }

        return BankAccount::create([
            'condominium_id' => $condominiumId,
            'name' => 'Conta Principal CHAS (seed IA)',
            'institution' => 'Banco Seed',
            'holder_name' => 'Condomínio CHAS',
            'document_number' => '00.000.000/0001-00',
            'bank_name' => 'Banco Seed',
            'agency' => '0001',
            'account' => '12345-6',
            'type' => 'checking',
            'pix_key' => 'seed-chas-finance-ai@example.test',
            'active' => true,
            'is_primary' => true,
            'current_balance' => 0,
            'notes' => self::NOTES_MARKER,
        ]);
    }

    /**
     * @return list<array{unit: Unit, user: User}>
     */
    protected function ensureUnitsAndResidents(int $condominiumId): array
    {
        $specs = [
            ['block' => 'A', 'number' => '101'],
            ['block' => 'A', 'number' => '102'],
            ['block' => 'A', 'number' => '103'],
            ['block' => 'A', 'number' => '104'],
            ['block' => 'B', 'number' => '201'],
            ['block' => 'B', 'number' => '202'],
            ['block' => 'B', 'number' => '203'],
            ['block' => 'B', 'number' => '204'],
        ];

        $rows = [];

        foreach ($specs as $i => $spec) {
            $idx = $i + 1;

            $unit = Unit::create([
                'condominium_id' => $condominiumId,
                'number' => $spec['number'],
                'block' => $spec['block'],
                'type' => 'residential',
                'unit_model' => UnitModels::APARTAMENTO,
                'ideal_fraction' => 1.0000,
                'area' => 75 + ($i * 5),
                'floor' => (int) substr($spec['number'], 0, 1),
                'is_active' => true,
                'notes' => self::NOTES_MARKER,
            ]);

            $user = User::create([
                'condominium_id' => $condominiumId,
                'unit_id' => $unit->id,
                'name' => "Morador Seed CHAS {$idx}",
                'email' => self::EMAIL_PREFIX."u{$idx}@example.test",
                'password' => Hash::make('password'),
                'phone' => '(11) 9'.str_pad((string) (80000000 + $idx), 8, '0', STR_PAD_LEFT),
                'cpf' => sprintf('900.%03d.%03d-%02d', $idx, $idx * 11, $idx),
                'is_active' => true,
                'registration_status' => 'approved',
            ]);
            $user->assignRole('Morador');

            $unit->update(['owner_user_id' => $user->id]);

            $rows[] = ['unit' => $unit->fresh(), 'user' => $user];
        }

        return $rows;
    }

    /**
     * @return array{condo: Fee, reserve: Fee}
     */
    protected function createFees(int $condominiumId, int $bankAccountId, Carbon $now): array
    {
        $condo = Fee::create([
            'condominium_id' => $condominiumId,
            'bank_account_id' => $bankAccountId,
            'name' => 'Taxa Condominial CHAS (seed IA)',
            'description' => self::NOTES_MARKER,
            'amount' => 650.00,
            'recurrence' => 'monthly',
            'due_day' => 10,
            'billing_type' => 'condominium_fee',
            'auto_generate_charges' => false,
            'active' => true,
            'starts_at' => $now->copy()->subMonthsNoOverflow(8)->startOfMonth(),
            'metadata' => ['seed' => self::SOURCE_TYPE],
        ]);

        $reserve = Fee::create([
            'condominium_id' => $condominiumId,
            'bank_account_id' => $bankAccountId,
            'name' => 'Fundo de Reserva CHAS (seed IA)',
            'description' => self::NOTES_MARKER,
            'amount' => 80.00,
            'recurrence' => 'monthly',
            'due_day' => 10,
            'billing_type' => 'extra',
            'auto_generate_charges' => false,
            'active' => true,
            'starts_at' => $now->copy()->subMonthsNoOverflow(8)->startOfMonth(),
            'metadata' => ['seed' => self::SOURCE_TYPE],
        ]);

        return ['condo' => $condo, 'reserve' => $reserve];
    }

    /**
     * @param  list<array{unit: Unit, user: User}>  $units
     * @param  array{condo: Fee, reserve: Fee}  $fees
     */
    protected function createMonthlyChargesAndPayments(
        Condominium $condominium,
        array $units,
        array $fees,
        User $syndic,
        Carbon $now
    ): void {
        $settlement = app(ChargeSettlementService::class);
        $unitCount = count($units);

        for ($m = 5; $m >= 0; $m--) {
            $month = $now->copy()->subMonthsNoOverflow($m)->startOfMonth();
            $dueDate = $month->copy()->day(10);
            $label = $month->translatedFormat('M/Y');

            foreach ($units as $i => $row) {
                $unit = $row['unit'];
                $user = $row['user'];

                // Padrão de inadimplência: unidades 7 e 8 (índices 6 e 7) atrasam no mês atual e anterior
                $isChronicDefaulter = $i >= ($unitCount - 2);
                $isCurrentOrPrev = $m <= 1;

                $condoStatus = 'paid';
                if ($isChronicDefaulter && $isCurrentOrPrev) {
                    $condoStatus = $m === 0 ? 'pending' : 'overdue';
                } elseif ($i === 3 && $m === 0) {
                    $condoStatus = 'pending'; // 1 pendente recente
                }

                $condoCharge = Charge::create([
                    'condominium_id' => $condominium->id,
                    'unit_id' => $unit->id,
                    'fee_id' => $fees['condo']->id,
                    'title' => "Taxa condominial {$label} — {$unit->block}/{$unit->number}",
                    'description' => self::NOTES_MARKER,
                    'amount' => 650.00,
                    'due_date' => $dueDate->toDateString(),
                    'status' => $condoStatus === 'paid' ? 'pending' : $condoStatus,
                    'type' => 'regular',
                    'generated_by' => 'fee',
                    'metadata' => [
                        'seed' => self::SOURCE_TYPE,
                        'kind' => 'condo_fee',
                    ],
                ]);

                if ($condoStatus === 'paid') {
                    $paidAt = $dueDate->copy()->subDays(($i % 4) + 1)->setTime(10, 0);
                    $settlement->markAsPaid(
                        $condoCharge,
                        $paidAt,
                        $i % 2 === 0 ? 'pix' : 'boleto',
                        self::NOTES_MARKER,
                        $syndic->id
                    );
                }

                // Fundo de reserva: pagos nos meses mais antigos; pendente no atual para metade das unidades
                $reserveStatus = 'paid';
                if ($m === 0 && $i % 2 === 1) {
                    $reserveStatus = 'pending';
                }

                $reserveCharge = Charge::create([
                    'condominium_id' => $condominium->id,
                    'unit_id' => $unit->id,
                    'fee_id' => $fees['reserve']->id,
                    'title' => "Fundo de reserva {$label} — {$unit->block}/{$unit->number}",
                    'description' => self::NOTES_MARKER,
                    'amount' => 80.00,
                    'due_date' => $dueDate->toDateString(),
                    'status' => $reserveStatus === 'paid' ? 'pending' : $reserveStatus,
                    'type' => 'regular',
                    'generated_by' => 'fee',
                    'metadata' => [
                        'seed' => self::SOURCE_TYPE,
                        'kind' => 'reserve_fee',
                    ],
                ]);

                if ($reserveStatus === 'paid') {
                    $paidAt = $dueDate->copy()->addDays(1)->setTime(14, 0);
                    $settlement->markAsPaid(
                        $reserveCharge,
                        $paidAt,
                        'pix',
                        self::NOTES_MARKER,
                        $user->id
                    );
                }
            }
        }
    }

    protected function createExpensesAndManualIncomes(
        int $condominiumId,
        int $bankAccountId,
        int $creatorId,
        Carbon $now
    ): void {
        $day = static fn (Carbon $month, int $d): string => $month
            ->copy()
            ->day(min($d, $month->daysInMonth))
            ->toDateString();

        $rows = [];

        for ($m = 5; $m >= 0; $m--) {
            $month = $now->copy()->subMonthsNoOverflow($m)->startOfMonth();
            $factor = 1 + (($m === 0) ? 0.18 : 0); // pico leve no mês atual (energia/manutenção)

            $rows = array_merge($rows, [
                ['type' => 'expense', 'description' => 'Folha porteiros e zeladoria', 'amount' => 11800 * $factor, 'date' => $day($month, 5), 'category' => 'pessoal', 'subcategory' => 'salary', 'payment_method' => 'bank_transfer'],
                ['type' => 'expense', 'description' => 'Encargos patronais (INSS/FGTS)', 'amount' => 3900 * $factor, 'date' => $day($month, 10), 'category' => 'encargos', 'payment_method' => 'boleto'],
                ['type' => 'expense', 'description' => 'Conta de energia elétrica', 'amount' => ($m === 0 ? 2750 : 1600 + ($m * 40)), 'date' => $day($month, 8), 'category' => 'energia'],
                ['type' => 'expense', 'description' => 'Água e esgoto', 'amount' => ($m === 0 ? 890 : 1200 - ($m * 30)), 'date' => $day($month, 12), 'category' => 'agua'],
                ['type' => 'expense', 'description' => 'Manutenção predial', 'amount' => ($m === 0 ? 3100 : 1050), 'date' => $day($month, 15), 'category' => 'manutencao'],
                ['type' => 'expense', 'description' => 'Material de limpeza', 'amount' => 580 + ($m * 20), 'date' => $day($month, 18), 'category' => 'limpeza'],
                ['type' => 'expense', 'description' => 'Internet / telefonia', 'amount' => 299.90, 'date' => $day($month, 7), 'category' => 'comunicacao'],
                ['type' => 'expense', 'description' => 'Segurança / monitoramento', 'amount' => 1850, 'date' => $day($month, 6), 'category' => 'seguranca', 'payment_method' => 'boleto'],
                ['type' => 'income', 'description' => 'Aluguel salão de festas', 'amount' => 350 + (($m % 2) * 150), 'date' => $day($month, 20), 'category' => null, 'source_type' => 'manual_income', 'payment_method' => 'pix'],
            ]);
        }

        // Extras pontuais
        $m0 = $now->copy()->startOfMonth();
        $m1 = $now->copy()->subMonthsNoOverflow(1)->startOfMonth();
        $rows[] = ['type' => 'expense', 'description' => 'Seguro predial (parcela)', 'amount' => 1450, 'date' => $day($m0, 3), 'category' => 'seguros', 'payment_method' => 'boleto'];
        $rows[] = ['type' => 'expense', 'description' => 'Seguro predial (parcela)', 'amount' => 1450, 'date' => $day($m1, 3), 'category' => 'seguros', 'payment_method' => 'boleto'];
        $rows[] = ['type' => 'expense', 'description' => 'Tarifa bancária', 'amount' => 89.90, 'date' => $day($m0, 22), 'category' => 'taxas_bancarias', 'payment_method' => 'bank_transfer'];
        $rows[] = ['type' => 'expense', 'description' => 'Despesa avulsa sem categoria', 'amount' => 320, 'date' => $day($m0, 25), 'category' => null];
        $rows[] = ['type' => 'income', 'description' => 'Doação / receita eventual', 'amount' => 500, 'date' => $day($m1, 28), 'category' => null, 'source_type' => 'manual_income', 'payment_method' => 'pix'];

        foreach ($rows as $row) {
            CondominiumAccount::create([
                'condominium_id' => $condominiumId,
                'bank_account_id' => $bankAccountId,
                'type' => $row['type'],
                'status' => CondominiumAccount::STATUS_ACTIVE,
                'source_type' => $row['source_type'] ?? self::SOURCE_TYPE,
                'source_id' => null,
                'description' => '[CHAS seed IA] '.$row['description'],
                'amount' => round((float) $row['amount'], 2),
                'transaction_date' => $row['date'],
                'payment_method' => $row['payment_method'] ?? 'pix',
                'category' => $row['category'] ?? null,
                'subcategory' => $row['subcategory'] ?? null,
                'notes' => self::NOTES_MARKER,
                'created_by' => $creatorId,
            ]);
        }
    }

    /**
     * @param  list<array{unit: Unit, user: User}>  $units
     */
    protected function createFines(Condominium $condominium, array $units, User $syndic, Carbon $now): void
    {
        $settlement = app(ChargeSettlementService::class);

        $scenarios = [
            [
                'idx' => 0,
                'motivo' => 'Barulho excessivo após 22h em área comum.',
                'enquadramento' => 'Perturbação do sossego',
                'amount' => 250.00,
                'due' => $now->copy()->subDays(20),
                'paid' => true,
            ],
            [
                'idx' => 2,
                'motivo' => 'Estacionamento irregular em vaga de visitantes.',
                'enquadramento' => 'Uso indevido de área comum',
                'amount' => 180.00,
                'due' => $now->copy()->addDays(5),
                'paid' => false,
            ],
            [
                'idx' => 5,
                'motivo' => 'Descarte incorreto de entulho na área de coleta.',
                'enquadramento' => 'Infração de limpeza',
                'amount' => 320.00,
                'due' => $now->copy()->subDays(5),
                'paid' => false,
                'overdue' => true,
            ],
        ];

        foreach ($scenarios as $n => $scenario) {
            $row = $units[$scenario['idx']];
            $unit = $row['unit'];
            $user = $row['user'];
            $reference = 'SEED-CHAS-'.str_pad((string) ($n + 1), 3, '0', STR_PAD_LEFT);

            $fine = Fine::create([
                'condominium_id' => $condominium->id,
                'reference' => $reference,
                'motivo' => $scenario['motivo'],
                'enquadramento' => $scenario['enquadramento'],
                'amount' => $scenario['amount'],
                'due_date' => $scenario['due']->toDateString(),
                'applied_at' => $scenario['due']->copy()->subDays(7),
                'applied_by' => $syndic->id,
                'status' => 'issued',
                'notes' => self::NOTES_MARKER,
            ]);

            $status = $scenario['paid'] ? 'pending' : (($scenario['overdue'] ?? false) ? 'overdue' : 'pending');

            $charge = Charge::create([
                'condominium_id' => $condominium->id,
                'unit_id' => $unit->id,
                'title' => 'Multa — '.$scenario['enquadramento'],
                'description' => self::NOTES_MARKER."\nReferência: {$reference}\nMotivo: {$scenario['motivo']}",
                'amount' => $scenario['amount'],
                'due_date' => $scenario['due']->toDateString(),
                'type' => 'extra',
                'status' => $status,
                'generated_by' => 'fine',
                'metadata' => [
                    'seed' => self::SOURCE_TYPE,
                    'fine_id' => $fine->id,
                    'fine_reference' => $reference,
                    'infractor_user_id' => $user->id,
                    'enquadramento' => $scenario['enquadramento'],
                ],
            ]);

            FineRecipient::create([
                'fine_id' => $fine->id,
                'user_id' => $user->id,
                'unit_id' => $unit->id,
                'notified_user_id' => $user->id,
                'charge_id' => $charge->id,
            ]);

            if ($scenario['paid']) {
                $settlement->markAsPaid(
                    $charge,
                    $scenario['due']->copy()->subDays(2)->setTime(11, 30),
                    'pix',
                    self::NOTES_MARKER,
                    $syndic->id
                );
            }
        }
    }
}
