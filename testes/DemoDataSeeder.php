<?php

/**
 * Seeder de dados de demonstração isolado dentro da pasta `testes`.
 *
 * IMPORTANTE:
 * - Este arquivo não está registrado no ciclo padrão de seeders do Laravel.
 *   A intenção é permitir a revisão cuidadosa antes de qualquer execução.
 * - Todos os métodos são pequenos e altamente comentados para facilitar
 *   adaptações e garantir rastreabilidade durante as apresentações.
 *
 * Uso sugerido (após aprovação do responsável pelo projeto):
 *   php artisan tinker
 *   >>> (new \Testes\DemoDataSeeder())->run();
 *
 * O seeder grava um manifesto com todos os IDs gerados em
 * `storage/app/testes/demo_seed_manifest.json`, possibilitando rollback.
 */

namespace Testes;

use Carbon\Carbon;
use Faker\Factory as FakerFactory;
use Faker\Generator as FakerGenerator;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class DemoDataSeeder
{
    /**
     * Tag padrão aplicada em textos para facilitar filtros no sistema.
     */
    private const TAG = '[DEMO]';

    /**
     * Caminho do manifesto com os IDs inseridos (armazenado no disco "local").
     */
    public const MANIFEST_PATH = 'testes/demo_seed_manifest.json';

    private FakerGenerator $faker;

    /**
     * Estrutura que armazena os IDs inseridos separados por tabela.
     *
     * @var array<string, array<int>>
     */
    private array $manifest = [];

    /**
     * Índices auxiliares para garantir relacionamentos consistentes.
     */
    private array $condominiums = [];
    private array $unitsByCondominium = [];
    private array $usersByCondominium = [];
    private array $spacesByCondominium = [];
    private array $feesByCondominium = [];
    private array $bankAccountsByCondominium = [];
    private array $assembliesByCondominium = [];
    private array $reservations = [];
    private array $charges = [];

    public function __construct()
    {
        // Faker configurado com locale pt_BR para gerar dados verossímeis.
        $this->faker = FakerFactory::create('pt_BR');
    }

    /**
     * Executa toda a orquestração de dados falsos para demonstração.
     *
     * O fluxo está organizado em blocos bem definidos para facilitar leitura,
     * manutenção e eventuais extensões futuras.
     */
    public function run(): void
    {
        $this->garantirAmbienteSeguro();
        $this->garantirManifestoNaoExistente();

        DB::transaction(function (): void {
            $this->seedCondominiums();
            $this->seedUnits();
            $this->seedUsers();
            $this->seedSpaces();
            $this->seedRecurringReservations();
            $this->seedReservations();
            $this->seedBankAccounts();
            $this->seedFees();
            $this->seedCharges();
            $this->seedPayments();
            $this->seedPaymentCancellations();
            $this->seedTransactions();
            $this->seedReceipts();
            $this->seedPackages();
            $this->seedEntries();
            $this->seedMarketplaceItems();
            $this->seedMessages();
            $this->seedNotifications();
            $this->seedPanicAlerts();
            $this->seedPets();
            $this->seedAssemblies();
            $this->seedAssemblyDetails();
            $this->seedVotes();
            $this->seedBankStatements();
            $this->seedBankAccountBalances();
            $this->seedCondominiumAccounts();
            $this->seedInternalRegulations();
            $this->seedInternalRegulationHistory();
            $this->seedUserCredits();
            $this->seedUserActivityLogs();
            $this->seedProfileSelections();
            $this->seedAgregadoPermissions();
        });

        $this->salvarManifesto();
    }

    /**
     * Impede execução acidental em produção ou em ambientes não previstos.
     */
    private function garantirAmbienteSeguro(): void
    {
        if (App::environment('production')) {
            throw new \RuntimeException('Seeder de demonstração bloqueado em ambiente de produção.');
        }
    }

    /**
     * Evita duplicidade de registros verificando se já existe arquivo de manifesto.
     */
    private function garantirManifestoNaoExistente(): void
    {
        if (Storage::disk('local')->exists(self::MANIFEST_PATH)) {
            throw new \RuntimeException('Manifesto existente encontrado. Execute primeiro o clear para remover dados DEMO.');
        }

        Storage::disk('local')->makeDirectory('testes');
    }

    /**
     * Registra informação no manifesto em memória.
     */
    private function registrar(string $tabela, int $id): void
    {
        $this->manifest[$tabela][] = $id;
    }

    /**
     * Persistência final do manifesto em disco.
     */
    private function salvarManifesto(): void
    {
        Storage::disk('local')->put(
            self::MANIFEST_PATH,
            json_encode($this->manifest, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE)
        );
    }

    /**
     * Cria um carimbo consistente para campos created_at/updated_at.
     */
    private function timestamps(): array
    {
        $now = Carbon::now();

        return [
            'created_at' => $now,
            'updated_at' => $now,
        ];
    }

    /**
     * Inserção auxiliar com retorno do ID gerado.
     */
    private function inserir(string $tabela, array $dados): int
    {
        $id = DB::table($tabela)->insertGetId($dados);
        $this->registrar($tabela, $id);

        return $id;
    }

    private function seedCondominiums(): void
    {
        for ($i = 1; $i <= 12; $i++) {
            $dados = [
                'name' => sprintf('%s Condomínio %02d', self::TAG, $i),
                'cnpj' => sprintf('11.%03d.%03d/%04d-%02d', $i, $i + 10, $i + 100, $i),
                'address' => $this->faker->streetAddress(),
                'city' => $this->faker->city(),
                'state' => $this->faker->stateAbbr(),
                'zip_code' => $this->faker->numerify('#####-###'),
                'phone' => $this->faker->cellphoneNumber(),
                'email' => sprintf('demo-condo-%02d@condocenter.test', $i),
                'description' => 'Condomínio fictício para apresentações',
                'is_active' => true,
                'marketplace_allow_agregados' => $this->faker->boolean(),
            ] + $this->timestamps();

            $id = $this->inserir('condominiums', $dados);
            $this->condominiums[] = $id;
        }
    }

    private function seedUnits(): void
    {
        foreach ($this->condominiums as $condominiumId) {
            for ($i = 1; $i <= 15; $i++) {
                $dados = [
                    'condominium_id' => $condominiumId,
                    'default_payment_channel' => $this->faker->randomElement(['system', 'payroll']),
                    'number' => sprintf('%03d', $i),
                    'block' => $this->faker->randomElement(['A', 'B', 'C', null]),
                    'type' => $this->faker->randomElement(['residential', 'commercial']),
                    'ideal_fraction' => 1.0000,
                    'area' => $this->faker->randomFloat(2, 45, 180),
                    'floor' => $this->faker->numberBetween(1, 12),
                    'notes' => 'Unidade criada para cenários de teste',
                    'situacao' => $this->faker->randomElement(['habitado', 'fechado', 'indisponivel', 'em_obra']),
                    'cep' => $this->faker->numerify('#####-###'),
                    'logradouro' => $this->faker->streetName(),
                    'numero' => (string) $this->faker->numberBetween(1, 999),
                    'complemento' => $this->faker->randomElement(['Bloco 1', 'Entrada B', null]),
                    'bairro' => $this->faker->citySuffix(),
                    'cidade' => $this->faker->city(),
                    'estado' => $this->faker->stateAbbr(),
                    'num_quartos' => $this->faker->numberBetween(1, 4),
                    'num_banheiros' => $this->faker->numberBetween(1, 3),
                    'foto' => null,
                    'possui_dividas' => $this->faker->boolean(),
                    'is_active' => true,
                ] + $this->timestamps();

                $id = $this->inserir('units', $dados);
                $this->unitsByCondominium[$condominiumId][] = $id;
            }
        }
    }

    private function seedUsers(): void
    {
        $senhaPadrao = Hash::make('Demo@2025');

        $temCanalPadraoUsuario = Schema::hasColumn('users', 'default_payment_channel');

        foreach ($this->condominiums as $condominiumId) {
            $units = $this->unitsByCondominium[$condominiumId] ?? [];

            for ($i = 1; $i <= 20; $i++) {
                $unitId = $this->faker->randomElement($units);
                $dados = [
                    'condominium_id' => $condominiumId,
                    'unit_id' => $unitId,
                    'name' => $this->faker->name(),
                    'email' => sprintf('morador+%d_%d@condocenter.test', $condominiumId, $i),
                    'phone' => $this->faker->cellphoneNumber(),
                    'cpf' => sprintf('111.%03d.%03d-%02d', $condominiumId, $i + 100, $i),
                    'photo' => null,
                    'qr_code' => Str::uuid(),
                    'is_active' => true,
                    'password' => $senhaPadrao,
                    'remember_token' => Str::random(10),
                    'telefone_residencial' => $this->faker->phoneNumber(),
                    'telefone_celular' => $this->faker->cellphoneNumber(),
                    'telefone_comercial' => $this->faker->phoneNumber(),
                    'cnh' => sprintf('CNH%08d', $i * $condominiumId),
                    'data_nascimento' => $this->faker->date(),
                    'data_entrada' => Carbon::now()->subMonths($this->faker->numberBetween(1, 12)),
                    'data_saida' => null,
                    'necessita_cuidados_especiais' => false,
                    'descricao_cuidados_especiais' => null,
                    'local_trabalho' => $this->faker->company(),
                    'contato_comercial' => $this->faker->email(),
                    'morador_vinculado_id' => null,
                    'senha_temporaria' => false,
                    'possui_dividas' => $this->faker->boolean(),
                    'email_verified_at' => Carbon::now(),
                    'fcm_token' => Str::random(32),
                    'fcm_enabled' => true,
                    'fcm_topics' => json_encode(['demo']),
                    'fcm_token_updated_at' => Carbon::now()->subDays($this->faker->numberBetween(1, 15)),
                ] + $this->timestamps();

                if ($temCanalPadraoUsuario) {
                    $dados['default_payment_channel'] = $this->faker->randomElement(['system', 'payroll']);
                }

                $id = $this->inserir('users', $dados);
                $this->usersByCondominium[$condominiumId][] = $id;
            }
        }
    }

    private function seedSpaces(): void
    {
        $tipos = ['party_hall', 'bbq', 'pool', 'sports_court', 'gym', 'meeting_room', 'other'];

        foreach ($this->condominiums as $condominiumId) {
            for ($i = 1; $i <= 12; $i++) {
                $dados = [
                    'condominium_id' => $condominiumId,
                    'name' => sprintf('%s Espaço %02d', self::TAG, $i),
                    'description' => 'Espaço configurado para agendamento de reservas',
                    'type' => $this->faker->randomElement($tipos),
                    'reservation_mode' => $this->faker->randomElement(['full_day', 'hourly']),
                    'capacity' => $this->faker->numberBetween(10, 120),
                    'price_per_hour' => $this->faker->randomFloat(2, 0, 150),
                    'requires_approval' => $this->faker->boolean(),
                    'approval_type' => $this->faker->randomElement(['automatic', 'manual', 'prereservation']),
                    'prereservation_payment_hours' => $this->faker->randomElement([24, 48, 72]),
                    'prereservation_auto_cancel' => $this->faker->boolean(),
                    'prereservation_instructions' => 'Pagamento via PIX no app.',
                    'max_hours_per_reservation' => $this->faker->numberBetween(2, 8),
                    'min_hours_per_reservation' => 1,
                    'interval_between_reservations' => $this->faker->numberBetween(0, 60),
                    'max_reservations_per_month_per_unit' => 3,
                    'available_from' => '08:00:00',
                    'available_until' => '22:00:00',
                    'is_active' => true,
                    'rules' => 'Manter o espaço limpo após o uso.',
                    'photo_path' => null,
                ] + $this->timestamps();

                $id = $this->inserir('spaces', $dados);
                $this->spacesByCondominium[$condominiumId][] = $id;
            }
        }
    }

    private function seedRecurringReservations(): void
    {
        foreach ($this->condominiums as $condominiumId) {
            $spaces = $this->spacesByCondominium[$condominiumId] ?? [];
            $users = $this->usersByCondominium[$condominiumId] ?? [];

            for ($i = 0; $i < 10; $i++) {
                $spaceId = $this->faker->randomElement($spaces);
                $userId = $this->faker->randomElement($users);
                $startDate = Carbon::now()->subWeeks(4);
                $endDate = Carbon::now()->addWeeks(8);

                $dados = [
                    'condominium_id' => $condominiumId,
                    'space_id' => $spaceId,
                    'created_by' => $userId,
                    'title' => sprintf('%s Série Recorrente %02d', self::TAG, $i + 1),
                    'description' => 'Série de reservas recursivas para fins de demonstração.',
                    'days_of_week' => json_encode([1, 3, 5]),
                    'start_time' => '18:00:00',
                    'end_time' => '20:00:00',
                    'start_date' => $startDate->toDateString(),
                    'end_date' => $endDate->toDateString(),
                    'status' => 'active',
                    'admin_notes' => 'Criado automaticamente pelo seeder DEMO.',
                ] + $this->timestamps();

                $this->inserir('recurring_reservations', $dados);
            }
        }
    }

    private function seedReservations(): void
    {
        foreach ($this->condominiums as $condominiumId) {
            $spaces = $this->spacesByCondominium[$condominiumId] ?? [];
            $units = $this->unitsByCondominium[$condominiumId] ?? [];
            $users = $this->usersByCondominium[$condominiumId] ?? [];

            for ($i = 0; $i < 20; $i++) {
                $spaceId = $this->faker->randomElement($spaces);
                $unitId = $this->faker->randomElement($units);
                $userId = $this->faker->randomElement($users);
                $date = Carbon::now()->addDays($i);

                $dados = [
                    'space_id' => $spaceId,
                    'unit_id' => $unitId,
                    'user_id' => $userId,
                    'reservation_date' => $date->toDateString(),
                    'start_time' => '18:00:00',
                    'end_time' => '22:00:00',
                    'status' => $this->faker->randomElement(['pending', 'approved', 'completed']),
                    'prereservation_status' => $this->faker->randomElement(['pending_payment', 'paid', 'cancelled']),
                    'payment_deadline' => Carbon::now()->addDays(2),
                    'payment_completed_at' => Carbon::now()->addDay(),
                    'payment_reference' => Str::uuid(),
                    'prereservation_amount' => $this->faker->randomFloat(2, 100, 500),
                    'approved_by' => $this->faker->randomElement($users),
                    'approved_at' => Carbon::now()->subDays(1),
                    'cancelled_by' => null,
                    'cancelled_at' => null,
                    'cancellation_reason' => null,
                    'notes' => 'Reserva criada para cenário de demonstração.',
                    'rejection_reason' => null,
                    'recurring_reservation_id' => null,
                    'admin_action' => 'created',
                    'admin_reason' => 'Processo automatizado para demo.',
                    'admin_action_by' => $this->faker->randomElement($users),
                    'admin_action_at' => Carbon::now()->subHours(2),
                ] + $this->timestamps();

                $id = $this->inserir('reservations', $dados);
                $this->reservations[] = $id;
            }
        }
    }

    private function seedBankAccounts(): void
    {
        foreach ($this->condominiums as $condominiumId) {
            for ($i = 0; $i < 4; $i++) {
                $dados = [
                    'condominium_id' => $condominiumId,
                    'name' => sprintf('%s Conta %02d', self::TAG, $i + 1),
                    'institution' => $this->faker->company(),
                    'holder_name' => $this->faker->company(),
                    'document_number' => sprintf('22.%03d.%03d-%02d', $condominiumId, $i + 200, $i + 1),
                    'bank_name' => $this->faker->company(),
                    'agency' => (string) $this->faker->numberBetween(1000, 9999),
                    'account' => (string) $this->faker->numberBetween(100000, 999999),
                    'type' => $this->faker->randomElement(['checking', 'savings', 'payment']),
                    'pix_key' => $this->faker->email(),
                    'current_balance' => $this->faker->randomFloat(2, 0, 50000),
                    'balance_updated_at' => Carbon::now()->subDays($this->faker->numberBetween(1, 10)),
                    'active' => true,
                    'notes' => 'Conta utilizada para testes de conciliação.',
                ] + $this->timestamps();

                $id = $this->inserir('bank_accounts', $dados);
                $this->bankAccountsByCondominium[$condominiumId][] = $id;
            }
        }
    }

    private function seedFees(): void
    {
        foreach ($this->condominiums as $condominiumId) {
            $bankAccounts = $this->bankAccountsByCondominium[$condominiumId] ?? [];

            for ($i = 0; $i < 10; $i++) {
                $dados = [
                    'condominium_id' => $condominiumId,
                    'bank_account_id' => $this->faker->randomElement($bankAccounts),
                    'name' => sprintf('%s Taxa %02d', self::TAG, $i + 1),
                    'description' => 'Taxa mensal gerada para apresentação.',
                    'amount' => $this->faker->randomFloat(2, 150, 450),
                    'recurrence' => $this->faker->randomElement(['monthly', 'quarterly', 'yearly']),
                    'due_day' => $this->faker->numberBetween(1, 25),
                    'due_offset_days' => $this->faker->numberBetween(0, 10),
                    'billing_type' => $this->faker->randomElement(['condominium_fee', 'fine', 'extra']),
                    'auto_generate_charges' => true,
                    'active' => true,
                    'starts_at' => Carbon::now()->subMonths(2),
                    'ends_at' => null,
                    'custom_schedule' => null,
                    'metadata' => json_encode(['demo' => true]),
                    'last_generated_at' => Carbon::now()->subDays(5),
                ] + $this->timestamps();

                $id = $this->inserir('fees', $dados);
                $this->feesByCondominium[$condominiumId][] = $id;
            }
        }
    }

    private function seedCharges(): void
    {
        foreach ($this->condominiums as $condominiumId) {
            $units = $this->unitsByCondominium[$condominiumId] ?? [];
            $fees = $this->feesByCondominium[$condominiumId] ?? [];

            for ($i = 0; $i < 20; $i++) {
                $dueDate = Carbon::now()->addDays($i);
                $dados = [
                    'condominium_id' => $condominiumId,
                    'unit_id' => $this->faker->randomElement($units),
                    'fee_id' => $this->faker->randomElement($fees),
                    'title' => sprintf('%s Cobrança %02d', self::TAG, $i + 1),
                    'description' => 'Cobrança fictícia para simular painéis financeiros.',
                    'amount' => $this->faker->randomFloat(2, 150, 800),
                    'due_date' => $dueDate->toDateString(),
                    'recurrence_period' => 'monthly',
                    'fine_percentage' => 2.00,
                    'interest_rate' => 1.00,
                    'status' => $this->faker->randomElement(['pending', 'paid', 'overdue']),
                    'generated_by' => $this->faker->randomElement(['manual', 'fee']),
                    'type' => $this->faker->randomElement(['regular', 'extra']),
                    'asaas_payment_id' => Str::uuid(),
                    'boleto_url' => 'https://condocenter.test/boleto-demo',
                    'pix_code' => Str::random(32),
                    'pix_qrcode' => null,
                    'metadata' => json_encode(['demo' => true]),
                    'first_reminder_sent_at' => Carbon::now()->subDays(1),
                    'second_reminder_sent_at' => null,
                    'paid_at' => Carbon::now()->subDays(1),
                ] + $this->timestamps();

                $id = $this->inserir('charges', $dados);
                $this->charges[] = $id;
            }
        }
    }

    private function seedPayments(): void
    {
        $usuarios = $this->flattenArray($this->usersByCondominium);

        if (empty($usuarios)) {
            return;
        }

        foreach ($this->charges as $chargeId) {
            $userId = $this->faker->randomElement($usuarios);
            $dados = [
                'charge_id' => $chargeId,
                'user_id' => $userId,
                'amount_paid' => $this->faker->randomFloat(2, 150, 800),
                'payment_date' => Carbon::now()->subDays($this->faker->numberBetween(1, 10)),
                'payment_method' => $this->faker->randomElement(['cash', 'pix', 'bank_transfer', 'credit_card', 'payroll']),
                'asaas_payment_id' => Str::uuid(),
                'transaction_id' => Str::uuid(),
                'notes' => 'Pagamento inserido automaticamente para demonstrar histórico.',
            ] + $this->timestamps();

            $this->inserir('payments', $dados);
        }
    }

    private function seedPaymentCancellations(): void
    {
        $usuarios = $this->flattenArray($this->usersByCondominium);

        if (empty($usuarios) || empty($this->charges)) {
            return;
        }

        for ($i = 0; $i < 10; $i++) {
            $chargeId = $this->faker->randomElement($this->charges);
            $userId = $this->faker->randomElement($usuarios);

            $dados = [
                'charge_id' => $chargeId,
                'cancelled_by' => $userId,
                'reason' => 'Cancelamento demonstrativo para testes de relatórios.',
            ] + $this->timestamps();

            $this->inserir('payment_cancellations', $dados);
        }
    }

    private function seedTransactions(): void
    {
        foreach ($this->condominiums as $condominiumId) {
            $users = $this->usersByCondominium[$condominiumId] ?? [];
            $units = $this->unitsByCondominium[$condominiumId] ?? [];

            for ($i = 0; $i < 15; $i++) {
                $dados = [
                    'condominium_id' => $condominiumId,
                    'unit_id' => $this->faker->randomElement($units),
                    'user_id' => $this->faker->randomElement($users),
                    'type' => $this->faker->randomElement(['income', 'expense']),
                    'category' => $this->faker->randomElement(['condominium_fee', 'maintenance', 'event']),
                    'subcategory' => $this->faker->randomElement(['geral', 'torre_a', 'torre_b']),
                    'description' => 'Lançamento financeiro gerado para fins de demonstração.',
                    'amount' => $this->faker->randomFloat(2, 100, 3000),
                    'transaction_date' => Carbon::now()->subDays($this->faker->numberBetween(1, 40)),
                    'due_date' => Carbon::now()->addDays($this->faker->numberBetween(1, 20)),
                    'paid_date' => Carbon::now()->addDays($this->faker->numberBetween(1, 25)),
                    'status' => $this->faker->randomElement(['pending', 'paid', 'overdue']),
                    'payment_method' => $this->faker->randomElement(['cash', 'pix', 'bank_transfer', 'credit_card']),
                    'store_location' => $this->faker->city(),
                    'is_recurring' => $this->faker->boolean(),
                    'recurrence_period' => $this->faker->randomElement(['monthly', 'yearly']),
                    'parent_transaction_id' => null,
                    'tags' => json_encode(['demo']),
                    'notes' => 'Lançamento criado automaticamente.',
                ] + $this->timestamps();

                $this->inserir('transactions', $dados);
            }
        }
    }

    private function seedReceipts(): void
    {
        $transactionIds = DB::table('transactions')->pluck('id')->toArray();

        if (empty($transactionIds)) {
            return;
        }

        for ($i = 0, $max = 20; $i < $max; $i++) {
            $dados = [
                'transaction_id' => $this->faker->randomElement($transactionIds),
                'original_filename' => sprintf('comprovante-demo-%02d.pdf', $i + 1),
                'storage_path' => sprintf('demo/recibos/%s.pdf', Str::uuid()),
                'mime_type' => 'application/pdf',
                'file_size' => $this->faker->numberBetween(100000, 500000),
                'description' => 'Comprovante inserido para fins de apresentação.',
            ] + $this->timestamps();

            $this->inserir('receipts', $dados);
        }
    }

    private function seedPackages(): void
    {
        foreach ($this->condominiums as $condominiumId) {
            $units = $this->unitsByCondominium[$condominiumId] ?? [];
            $users = $this->usersByCondominium[$condominiumId] ?? [];

            for ($i = 0; $i < 15; $i++) {
                $dados = [
                    'condominium_id' => $condominiumId,
                    'unit_id' => $this->faker->randomElement($units),
                    'registered_by' => $this->faker->randomElement($users),
                    'type' => $this->faker->randomElement(['leve', 'pesado', 'caixa_grande', 'fragil']),
                    'sender' => $this->faker->company(),
                    'tracking_code' => strtoupper(Str::random(10)),
                    'description' => 'Entrega simulada para testes de portaria.',
                    'received_at' => Carbon::now()->subDays($this->faker->numberBetween(1, 5)),
                    'collected_at' => Carbon::now(),
                    'collected_by' => $this->faker->randomElement($users),
                    'status' => $this->faker->randomElement(['pending', 'collected']),
                    'notes' => 'Pacote criado automaticamente.',
                    'notification_sent' => true,
                ] + $this->timestamps();

                $this->inserir('packages', $dados);
            }
        }
    }

    private function seedEntries(): void
    {
        foreach ($this->condominiums as $condominiumId) {
            $units = $this->unitsByCondominium[$condominiumId] ?? [];
            $users = $this->usersByCondominium[$condominiumId] ?? [];

            for ($i = 0; $i < 15; $i++) {
                $dados = [
                    'condominium_id' => $condominiumId,
                    'unit_id' => $this->faker->randomElement($units),
                    'registered_by' => $this->faker->randomElement($users),
                    'type' => $this->faker->randomElement(['resident', 'visitor', 'service_provider', 'delivery']),
                    'visitor_name' => $this->faker->name(),
                    'visitor_document' => sprintf('RG%08d', $i),
                    'visitor_phone' => $this->faker->cellphoneNumber(),
                    'vehicle_plate' => strtoupper($this->faker->bothify('???-####')),
                    'entry_type' => $this->faker->randomElement(['entry', 'exit']),
                    'entry_time' => Carbon::now()->subHours($this->faker->numberBetween(1, 24)),
                    'exit_time' => Carbon::now(),
                    'authorized' => true,
                    'authorized_by' => $this->faker->randomElement($users),
                    'notes' => 'Registro criado automaticamente.',
                    'photo' => null,
                ] + $this->timestamps();

                $this->inserir('entries', $dados);
            }
        }
    }

    private function seedMarketplaceItems(): void
    {
        $categorias = ['products', 'services', 'jobs', 'real_estate', 'vehicles', 'other'];
        $condicoes = ['new', 'used', 'refurbished', 'not_applicable'];

        foreach ($this->condominiums as $condominiumId) {
            $users = $this->usersByCondominium[$condominiumId] ?? [];
            $units = $this->unitsByCondominium[$condominiumId] ?? [];

            for ($i = 0; $i < 12; $i++) {
                $dados = [
                    'condominium_id' => $condominiumId,
                    'seller_id' => $this->faker->randomElement($users),
                    'unit_id' => $this->faker->randomElement($units),
                    'title' => sprintf('%s Item Marketplace %02d', self::TAG, $i + 1),
                    'description' => 'Item de marketplace fictício para demonstração.',
                    'price' => $this->faker->randomFloat(2, 50, 1200),
                    'category' => $this->faker->randomElement($categorias),
                    'condition' => $this->faker->randomElement($condicoes),
                    'whatsapp' => $this->faker->cellphoneNumber(),
                    'images' => json_encode([
                        'https://picsum.photos/seed/' . Str::random(8) . '/600/400',
                    ]),
                    'status' => $this->faker->randomElement(['active', 'sold']),
                    'views' => $this->faker->numberBetween(5, 200),
                ] + $this->timestamps();

                $this->inserir('marketplace_items', $dados);
            }
        }
    }

    private function seedMessages(): void
    {
        foreach ($this->condominiums as $condominiumId) {
            $users = $this->usersByCondominium[$condominiumId] ?? [];

            for ($i = 0; $i < 12; $i++) {
                $from = $this->faker->randomElement($users);
                $to = $this->faker->randomElement($users);

                $dados = [
                    'condominium_id' => $condominiumId,
                    'from_user_id' => $from,
                    'to_user_id' => $to,
                    'type' => $this->faker->randomElement(['announcement', 'sindico_message', 'marketplace_inquiry']),
                    'subject' => sprintf('%s Comunicado %02d', self::TAG, $i + 1),
                    'message' => 'Conteúdo de mensagem para demonstração do módulo de comunicação.',
                    'priority' => $this->faker->randomElement(['low', 'normal', 'high', 'urgent']),
                    'is_read' => $this->faker->boolean(),
                    'read_at' => Carbon::now(),
                    'related_item_id' => null,
                    'related_item_type' => null,
                ] + $this->timestamps();

                $this->inserir('messages', $dados);
            }
        }
    }

    private function seedNotifications(): void
    {
        $tipos = ['package_arrived', 'payment_overdue', 'reservation_approved', 'alert_panic', 'custom'];

        foreach ($this->condominiums as $condominiumId) {
            $users = $this->usersByCondominium[$condominiumId] ?? [];

            for ($i = 0; $i < 15; $i++) {
                $dados = [
                    'condominium_id' => $condominiumId,
                    'user_id' => $this->faker->randomElement($users),
                    'type' => $this->faker->randomElement($tipos),
                    'title' => sprintf('%s Notificação %02d', self::TAG, $i + 1),
                    'message' => 'Notificação criada para simular o histórico do usuário.',
                    'data' => json_encode(['demo' => true]),
                    'is_read' => $this->faker->boolean(),
                    'read_at' => Carbon::now(),
                    'channel' => $this->faker->randomElement(['database', 'email', 'push']),
                    'sent' => true,
                    'sent_at' => Carbon::now()->subHours(2),
                ] + $this->timestamps();

                $this->inserir('notifications', $dados);
            }
        }
    }

    private function seedPanicAlerts(): void
    {
        foreach ($this->condominiums as $condominiumId) {
            $users = $this->usersByCondominium[$condominiumId] ?? [];

            for ($i = 0; $i < 12; $i++) {
                $dados = [
                    'condominium_id' => $condominiumId,
                    'user_id' => $this->faker->randomElement($users),
                    'alert_type' => $this->faker->randomElement(['panic', 'emergency']),
                    'title' => sprintf('%s Alerta %02d', self::TAG, $i + 1),
                    'description' => 'Alerta de pânico fictício para apresentar dashboards.',
                    'location' => $this->faker->address(),
                    'severity' => $this->faker->randomElement(['low', 'medium', 'high', 'critical']),
                    'status' => $this->faker->randomElement(['active', 'resolved']),
                    'resolved_by' => $this->faker->randomElement($users),
                    'resolved_at' => Carbon::now(),
                    'metadata' => json_encode(['geo' => [-23.5, -46.6]]),
                ] + $this->timestamps();

                $this->inserir('panic_alerts', $dados);
            }
        }
    }

    private function seedPets(): void
    {
        foreach ($this->condominiums as $condominiumId) {
            $units = $this->unitsByCondominium[$condominiumId] ?? [];
            $users = $this->usersByCondominium[$condominiumId] ?? [];

            for ($i = 0; $i < 12; $i++) {
                $dados = [
                    'condominium_id' => $condominiumId,
                    'unit_id' => $this->faker->randomElement($units),
                    'owner_id' => $this->faker->randomElement($users),
                    'name' => $this->faker->firstName(),
                    'type' => $this->faker->randomElement(['dog', 'cat', 'bird', 'other']),
                    'breed' => $this->faker->word(),
                    'color' => $this->faker->safeColorName(),
                    'birth_date' => $this->faker->date(),
                    'size' => $this->faker->randomElement(['small', 'medium', 'large']),
                    'photo' => null,
                    'qr_code' => Str::uuid(),
                    'observations' => 'Pet cadastrado para testes do módulo.',
                    'is_active' => true,
                ] + $this->timestamps();

                $this->inserir('pets', $dados);
            }
        }
    }

    private function seedAssemblies(): void
    {
        foreach ($this->condominiums as $condominiumId) {
            $users = $this->usersByCondominium[$condominiumId] ?? [];

            for ($i = 0; $i < 10; $i++) {
                $dados = [
                    'condominium_id' => $condominiumId,
                    'created_by' => $this->faker->randomElement($users),
                    'title' => sprintf('%s Assembleia %02d', self::TAG, $i + 1),
                    'description' => 'Assembleia fictícia para demonstrar fluxo de votação.',
                    'agenda' => json_encode(['Pauta 1', 'Pauta 2', 'Pauta 3']),
                    'scheduled_at' => Carbon::now()->addDays($i),
                    'started_at' => Carbon::now()->addDays($i)->addHours(1),
                    'ended_at' => Carbon::now()->addDays($i)->addHours(2),
                    'duration_minutes' => 120,
                    'status' => $this->faker->randomElement(['scheduled', 'in_progress', 'completed']),
                    'voting_type' => $this->faker->randomElement(['open', 'secret']),
                    'allow_delegation' => $this->faker->boolean(),
                    'minutes' => 'Ata de assembleia criada para testes.',
                    'minutes_pdf' => 'demo/atas/' . Str::uuid() . '.pdf',
                ] + $this->timestamps();

                $id = $this->inserir('assemblies', $dados);
                $this->assembliesByCondominium[$condominiumId][] = $id;
            }
        }
    }

    private function seedAssemblyDetails(): void
    {
        foreach ($this->assembliesByCondominium as $condominiumId => $assemblyIds) {
            $users = $this->usersByCondominium[$condominiumId] ?? [];

            foreach ($assemblyIds as $assemblyId) {
                $itemIds = [];

                for ($i = 0; $i < 5; $i++) {
                    $dadosItem = [
                        'assembly_id' => $assemblyId,
                        'title' => sprintf('Pauta %02d', $i + 1),
                        'description' => 'Ponto de votação criado para demonstrar resultados.',
                        'options' => json_encode(['Sim', 'Não', 'Abstenção']),
                        'position' => $i,
                        'status' => $this->faker->randomElement(['pending', 'open', 'closed']),
                        'opens_at' => Carbon::now()->addMinutes($i * 10),
                        'closes_at' => Carbon::now()->addMinutes(($i + 1) * 10),
                    ] + $this->timestamps();

                    $itemId = $this->inserir('assembly_items', $dadosItem);
                    $itemIds[] = $itemId;
                }

                foreach ($itemIds as $itemId) {
                    for ($j = 0; $j < 10; $j++) {
                        $userId = $this->faker->randomElement($users);
                        $unitId = $this->faker->randomElement($this->unitsByCondominium[$condominiumId] ?? []);

                        $dadosVoto = [
                            'assembly_id' => $assemblyId,
                            'assembly_item_id' => $itemId,
                            'voter_id' => $userId,
                            'unit_id' => $unitId,
                            'choice' => $this->faker->randomElement(['Sim', 'Não', 'Abstenção']),
                            'encrypted_choice' => Str::random(40),
                            'comment' => 'Voto registrado no cenário de demonstração.',
                            'submitted_at' => Carbon::now(),
                        ] + $this->timestamps();

                        // Usamos insertIgnore manual para evitar colisão da unique key.
                        try {
                            $this->inserir('assembly_votes', $dadosVoto);
                        } catch (\Throwable $exception) {
                            // Ignora colisões ocasionais de unique constraint em demonstrações.
                        }
                    }
                }

                for ($k = 0; $k < 3; $k++) {
                    $dadosAnexo = [
                        'assembly_id' => $assemblyId,
                        'uploaded_by' => $this->faker->randomElement($users),
                        'collection' => 'documents',
                        'disk' => 'public',
                        'path' => 'assemblies/' . Str::uuid() . '.pdf',
                        'original_name' => sprintf('documento-assembleia-%02d.pdf', $k + 1),
                        'mime_type' => 'application/pdf',
                        'size' => $this->faker->numberBetween(50000, 200000),
                    ] + $this->timestamps();

                    $this->inserir('assembly_attachments', $dadosAnexo);
                }

                for ($l = 0; $l < 4; $l++) {
                    $dadosLog = [
                        'assembly_id' => $assemblyId,
                        'changed_by' => $this->faker->randomElement($users),
                        'from_status' => $this->faker->randomElement(['scheduled', 'in_progress', 'completed', null]),
                        'to_status' => $this->faker->randomElement(['scheduled', 'in_progress', 'completed']),
                        'context' => json_encode(['demo' => true]),
                    ] + $this->timestamps();

                    $this->inserir('assembly_status_logs', $dadosLog);
                }
            }
        }
    }

    private function seedVotes(): void
    {
        foreach ($this->assembliesByCondominium as $condominiumId => $assemblyIds) {
            $users = $this->usersByCondominium[$condominiumId] ?? [];
            $units = $this->unitsByCondominium[$condominiumId] ?? [];

            foreach ($assemblyIds as $assemblyId) {
                for ($i = 0; $i < 10; $i++) {
                    $dados = [
                        'assembly_id' => $assemblyId,
                        'user_id' => $this->faker->randomElement($users),
                        'unit_id' => $this->faker->randomElement($units),
                        'agenda_item' => 'Pauta Geral',
                        'vote' => $this->faker->randomElement(['yes', 'no', 'abstain']),
                        'encrypted_vote' => Str::random(32),
                        'delegated_from' => null,
                    ] + $this->timestamps();

                    try {
                        $this->inserir('votes', $dados);
                    } catch (\Throwable $exception) {
                        // Em caso de colisão na unique constraint, apenas ignoramos.
                    }
                }
            }
        }
    }

    private function seedBankStatements(): void
    {
        foreach ($this->condominiums as $condominiumId) {
            $users = $this->usersByCondominium[$condominiumId] ?? [];

            for ($i = 0; $i < 10; $i++) {
                $dados = [
                    'condominium_id' => $condominiumId,
                    'uploaded_by' => $this->faker->randomElement($users),
                    'original_filename' => sprintf('extrato-demo-%02d.csv', $i + 1),
                    'storage_path' => 'demo/extratos/' . Str::uuid() . '.csv',
                    'statement_date' => Carbon::now()->subMonths($i),
                    'period_start' => Carbon::now()->subMonths($i)->startOfMonth(),
                    'period_end' => Carbon::now()->subMonths($i)->endOfMonth(),
                    'status' => $this->faker->randomElement(['pending', 'processing', 'reconciled']),
                    'total_transactions' => $this->faker->numberBetween(10, 100),
                    'reconciled_transactions' => $this->faker->numberBetween(5, 80),
                    'unmatched_items' => json_encode([]),
                    'notes' => 'Extrato bancário criado para simulações.',
                ] + $this->timestamps();

                $this->inserir('bank_statements', $dados);
            }
        }
    }

    private function seedBankAccountBalances(): void
    {
        $accounts = $this->flattenArray($this->bankAccountsByCondominium);

        if (empty($accounts)) {
            return;
        }

        foreach ($accounts as $accountId) {
            for ($i = 0; $i < 10; $i++) {
                $dados = [
                    'bank_account_id' => $accountId,
                    'balance' => $this->faker->randomFloat(2, 0, 50000),
                    'recorded_at' => Carbon::now()->subDays($i),
                    'reference' => sprintf('Fechamento diário %02d', $i + 1),
                    'notes' => 'Saldo registrado para fins demonstrativos.',
                ] + $this->timestamps();

                $this->inserir('bank_account_balances', $dados);
            }
        }
    }

    private function seedCondominiumAccounts(): void
    {
        foreach ($this->condominiums as $condominiumId) {
            $users = $this->usersByCondominium[$condominiumId] ?? [];

            for ($i = 0; $i < 12; $i++) {
                $dados = [
                    'condominium_id' => $condominiumId,
                    'type' => $this->faker->randomElement(['income', 'expense']),
                    'source_type' => 'demo_source',
                    'source_id' => null,
                    'description' => 'Movimentação financeira para quadro de contas.',
                    'amount' => $this->faker->randomFloat(2, 100, 3000),
                    'transaction_date' => Carbon::now()->subDays($i),
                    'payment_method' => $this->faker->randomElement(['cash', 'pix', 'bank_transfer']),
                    'installments_total' => null,
                    'installment_number' => null,
                    'document_path' => null,
                    'captured_image_path' => null,
                    'notes' => 'Registro inserido pelo seeder DEMO.',
                    'created_by' => $this->faker->randomElement($users),
                ] + $this->timestamps();

                $this->inserir('condominium_accounts', $dados);
            }
        }
    }

    private function seedInternalRegulations(): void
    {
        foreach ($this->condominiums as $condominiumId) {
            $users = $this->usersByCondominium[$condominiumId] ?? [];

            for ($i = 0; $i < 10; $i++) {
                $dados = [
                    'condominium_id' => $condominiumId,
                    'content' => 'Conteúdo do regimento interno para apresentações.',
                    'assembly_date' => Carbon::now()->subMonths($i),
                    'assembly_details' => 'Assembleia extraordinária de demonstração.',
                    'version' => $i + 1,
                    'is_active' => true,
                    'updated_by' => $this->faker->randomElement($users),
                ] + $this->timestamps();

                $this->inserir('internal_regulations', $dados);
            }
        }
    }

    private function seedInternalRegulationHistory(): void
    {
        foreach ($this->condominiums as $condominiumId) {
            $users = $this->usersByCondominium[$condominiumId] ?? [];

            for ($i = 0; $i < 10; $i++) {
                $internalRegulationId = DB::table('internal_regulations')
                    ->where('condominium_id', $condominiumId)
                    ->inRandomOrder()
                    ->value('id');

                $dados = [
                    'internal_regulation_id' => $internalRegulationId,
                    'condominium_id' => $condominiumId,
                    'content' => 'Histórico anterior do regimento para conferência.',
                    'changes_summary' => 'Resumo das mudanças realizadas nesta versão demo.',
                    'assembly_date' => Carbon::now()->subMonths($i + 1),
                    'assembly_details' => 'Ata registrada no painel.',
                    'version' => $i + 1,
                    'updated_by' => $this->faker->randomElement($users),
                    'changed_at' => Carbon::now()->subMonths($i + 1),
                ] + $this->timestamps();

                $this->inserir('internal_regulation_history', $dados);
            }
        }
    }

    private function seedUserCredits(): void
    {
        foreach ($this->condominiums as $condominiumId) {
            $users = $this->usersByCondominium[$condominiumId] ?? [];
            $charges = DB::table('charges')->where('condominium_id', $condominiumId)->pluck('id')->toArray();
            $reservas = DB::table('reservations')
                ->join('spaces', 'reservations.space_id', '=', 'spaces.id')
                ->where('spaces.condominium_id', $condominiumId)
                ->pluck('reservations.id')
                ->toArray();

            for ($i = 0; $i < 10; $i++) {
                $dados = [
                    'condominium_id' => $condominiumId,
                    'user_id' => $this->faker->randomElement($users),
                    'amount' => $this->faker->randomFloat(2, 50, 500),
                    'type' => $this->faker->randomElement(['refund', 'bonus', 'manual']),
                    'description' => 'Crédito adicionado para compensar cobrança demo.',
                    'reservation_id' => $this->faker->randomElement($reservas),
                    'charge_id' => $this->faker->randomElement($charges),
                    'status' => $this->faker->randomElement(['available', 'used', 'expired']),
                    'used_in_reservation_id' => $this->faker->randomElement($reservas),
                    'used_at' => Carbon::now()->subDays($this->faker->numberBetween(1, 5)),
                    'expires_at' => Carbon::now()->addMonths(1),
                ] + $this->timestamps();

                $this->inserir('user_credits', $dados);
            }
        }
    }

    private function seedUserActivityLogs(): void
    {
        $acoes = ['create', 'update', 'delete', 'view'];
        $modulos = ['reservations', 'packages', 'notifications', 'marketplace', 'finance'];

        foreach ($this->condominiums as $condominiumId) {
            $users = $this->usersByCondominium[$condominiumId] ?? [];

            for ($i = 0; $i < 12; $i++) {
                $dados = [
                    'user_id' => $this->faker->randomElement($users),
                    'condominium_id' => $condominiumId,
                    'action' => $this->faker->randomElement($acoes),
                    'module' => $this->faker->randomElement($modulos),
                    'description' => 'Log de atividade gerado pelo seeder de demonstração.',
                    'metadata' => json_encode(['demo' => true]),
                    'ip_address' => $this->faker->ipv4(),
                    'user_agent' => $this->faker->userAgent(),
                ] + $this->timestamps();

                $this->inserir('user_activity_logs', $dados);
            }
        }
    }

    private function seedProfileSelections(): void
    {
        $perfis = ['morador', 'sindico', 'porteiro', 'admin'];

        foreach ($this->condominiums as $condominiumId) {
            $users = $this->usersByCondominium[$condominiumId] ?? [];

            for ($i = 0; $i < 10; $i++) {
                $dados = [
                    'user_id' => $this->faker->randomElement($users),
                    'role_name' => $this->faker->randomElement($perfis),
                    'selected_at' => Carbon::now()->subDays($i),
                    'ip_address' => $this->faker->ipv4(),
                ] + $this->timestamps();

                $this->inserir('profile_selections', $dados);
            }
        }
    }

    private function seedAgregadoPermissions(): void
    {
        $permissoes = ['view_spaces', 'create_reservations', 'view_marketplace', 'manage_packages'];
        $permissoesNivel = ['view', 'edit', 'full'];

        foreach ($this->condominiums as $condominiumId) {
            $users = $this->usersByCondominium[$condominiumId] ?? [];

            for ($i = 0; $i < 10; $i++) {
                $userId = $this->faker->randomElement($users);
                $grantedBy = $this->faker->randomElement($users);

                $dados = [
                    'user_id' => $userId,
                    'granted_by' => $grantedBy,
                    'permission_key' => $this->faker->randomElement($permissoes),
                    'permission_level' => $this->faker->randomElement($permissoesNivel),
                    'is_granted' => true,
                    'notes' => 'Permissão criada para testes de agregados.',
                ] + $this->timestamps();

                try {
                    $this->inserir('agregado_permissions', $dados);
                } catch (\Throwable $exception) {
                    // Em caso de conflito na unique key, simplesmente ignoramos.
                }
            }
        }
    }

    /**
     * Converte arrays aninhados em lista plana de IDs.
     *
     * @param array<array<int>> $dados
     * @return array<int>
     */
    private function flattenArray(array $dados): array
    {
        if (empty($dados)) {
            return [];
        }

        $filtrados = array_filter(array_values($dados), static fn ($item) => !empty($item));

        if (empty($filtrados)) {
            return [];
        }

        return array_values(array_merge(...$filtrados));
    }
}

