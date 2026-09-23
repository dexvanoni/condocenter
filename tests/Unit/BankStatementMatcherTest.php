<?php

namespace Tests\Unit;

use App\Models\BankAccount;
use App\Models\BankStatement;
use App\Models\BankStatementLine;
use App\Models\Condominium;
use App\Models\CondominiumAccount;
use App\Models\User;
use App\Services\BankReconciliationService;
use App\Services\BankStatement\BankStatementMatcher;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class BankStatementMatcherTest extends TestCase
{
    use RefreshDatabase;

    private Condominium $condominium;

    private BankAccount $account;

    private User $user;

    protected function setUp(): void
    {
        parent::setUp();

        $this->condominium = Condominium::factory()->create([
            'financial_mode' => 'full',
        ]);
        $this->user = User::factory()->for($this->condominium)->create();
        $this->account = BankAccount::create([
            'condominium_id' => $this->condominium->id,
            'name' => 'Conta Principal',
            'institution' => 'Banco Teste',
            'active' => true,
            'is_primary' => true,
            'current_balance' => 1000,
            'type' => 'checking',
        ]);
    }

    public function test_auto_matches_exact_date_and_amount_when_unique(): void
    {
        $entry = CondominiumAccount::create([
            'condominium_id' => $this->condominium->id,
            'bank_account_id' => $this->account->id,
            'type' => 'expense',
            'status' => CondominiumAccount::STATUS_ACTIVE,
            'description' => 'Limpeza',
            'amount' => 100,
            'transaction_date' => '2026-09-10',
            'created_by' => $this->user->id,
        ]);

        $statement = $this->makeStatement([
            ['posted_at' => '2026-09-10', 'amount' => -100, 'description' => 'Pag limpeza', 'fitid' => 'F1'],
        ]);

        app(BankStatementMatcher::class)->match($statement->fresh('lines'));

        $line = $statement->lines()->first();
        $this->assertSame(BankStatementLine::STATUS_AUTO_MATCHED, $line->status);
        $this->assertSame('condominium_account', $line->matched_source_type);
        $this->assertSame($entry->id, $line->matched_source_id);
    }

    public function test_ambiguous_exact_match_becomes_suggestion(): void
    {
        CondominiumAccount::create([
            'condominium_id' => $this->condominium->id,
            'bank_account_id' => $this->account->id,
            'type' => 'expense',
            'status' => CondominiumAccount::STATUS_ACTIVE,
            'description' => 'Limpeza A',
            'amount' => 100,
            'transaction_date' => '2026-09-10',
            'created_by' => $this->user->id,
        ]);
        CondominiumAccount::create([
            'condominium_id' => $this->condominium->id,
            'bank_account_id' => $this->account->id,
            'type' => 'expense',
            'status' => CondominiumAccount::STATUS_ACTIVE,
            'description' => 'Limpeza B',
            'amount' => 100,
            'transaction_date' => '2026-09-10',
            'created_by' => $this->user->id,
        ]);

        $statement = $this->makeStatement([
            ['posted_at' => '2026-09-10', 'amount' => -100, 'description' => 'Pag', 'fitid' => 'F2'],
        ]);

        app(BankStatementMatcher::class)->match($statement->fresh('lines'));

        $line = $statement->lines()->first();
        $this->assertSame(BankStatementLine::STATUS_SUGGESTED, $line->status);
        $this->assertSame('ambiguous_exact', $line->suggestion_meta['reason'] ?? null);
    }

    public function test_fuzzy_near_date_becomes_suggestion(): void
    {
        CondominiumAccount::create([
            'condominium_id' => $this->condominium->id,
            'bank_account_id' => $this->account->id,
            'type' => 'income',
            'status' => CondominiumAccount::STATUS_ACTIVE,
            'source_type' => 'manual_income',
            'description' => 'PIX',
            'amount' => 250,
            'transaction_date' => '2026-09-10',
            'created_by' => $this->user->id,
        ]);

        $statement = $this->makeStatement([
            ['posted_at' => '2026-09-12', 'amount' => 250, 'description' => 'PIX recebido', 'fitid' => 'F3'],
        ]);

        app(BankStatementMatcher::class)->match($statement->fresh('lines'));

        $line = $statement->lines()->first();
        $this->assertSame(BankStatementLine::STATUS_SUGGESTED, $line->status);
    }

    public function test_reconcile_with_active_statement_only_closes_linked_entries(): void
    {
        $linked = CondominiumAccount::create([
            'condominium_id' => $this->condominium->id,
            'bank_account_id' => $this->account->id,
            'type' => 'expense',
            'status' => CondominiumAccount::STATUS_ACTIVE,
            'description' => 'Vinculado',
            'amount' => 80,
            'transaction_date' => '2026-09-10',
            'created_by' => $this->user->id,
        ]);
        $orphan = CondominiumAccount::create([
            'condominium_id' => $this->condominium->id,
            'bank_account_id' => $this->account->id,
            'type' => 'expense',
            'status' => CondominiumAccount::STATUS_ACTIVE,
            'description' => 'Sem extrato',
            'amount' => 50,
            'transaction_date' => '2026-09-11',
            'created_by' => $this->user->id,
        ]);

        $statement = $this->makeStatement([
            ['posted_at' => '2026-09-10', 'amount' => -80, 'description' => 'Pag', 'fitid' => 'F4'],
        ], closingBalance: 920);

        app(BankStatementMatcher::class)->match($statement->fresh('lines'));

        $service = app(BankReconciliationService::class);
        $service->reconcile(
            $this->user,
            $this->account->fresh(),
            Carbon::parse('2026-09-01')->startOfDay(),
            Carbon::parse('2026-09-30')->endOfDay(),
            null,
            true
        );

        $this->assertNotNull($linked->fresh()->reconciliation_id);
        $this->assertNull($orphan->fresh()->reconciliation_id);
        $this->assertSame(BankStatement::STATUS_RECONCILED, $statement->fresh()->status);
    }

    /**
     * @param  list<array{posted_at: string, amount: float, description: string, fitid: ?string}>  $lines
     */
    private function makeStatement(array $lines, ?float $closingBalance = null): BankStatement
    {
        $dates = collect($lines)->pluck('posted_at')->sort()->values();

        $statement = BankStatement::create([
            'condominium_id' => $this->condominium->id,
            'bank_account_id' => $this->account->id,
            'uploaded_by' => $this->user->id,
            'original_filename' => 'teste.ofx',
            'storage_path' => 'bank-statements/test.ofx',
            'format' => 'ofx',
            'file_hash' => hash('sha256', uniqid('', true)),
            'statement_date' => now()->toDateString(),
            'period_start' => $dates->first(),
            'period_end' => $dates->last(),
            'closing_balance' => $closingBalance,
            'status' => BankStatement::STATUS_READY,
            'total_transactions' => count($lines),
            'reconciled_transactions' => 0,
        ]);

        foreach ($lines as $index => $line) {
            BankStatementLine::create([
                'bank_statement_id' => $statement->id,
                'bank_account_id' => $this->account->id,
                'line_order' => $index,
                'posted_at' => $line['posted_at'],
                'amount' => $line['amount'],
                'description' => $line['description'],
                'fitid' => $line['fitid'] ?? null,
                'status' => BankStatementLine::STATUS_UNMATCHED,
            ]);
        }

        return $statement->fresh('lines');
    }
}
