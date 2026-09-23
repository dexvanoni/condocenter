<?php

namespace App\Http\Controllers\Finance;

use App\Http\Controllers\Concerns\ResolvesActiveCondominium;
use App\Http\Controllers\Controller;
use App\Models\BankAccount;
use App\Models\BankStatement;
use App\Models\BankStatementLine;
use App\Services\BankReconciliationService;
use App\Services\BankStatement\BankStatementImportService;
use App\Services\BankStatement\BankStatementMatcher;
use App\Support\ExpenseCategories;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;

class BankStatementController extends Controller
{
    use ResolvesActiveCondominium;

    public function __construct(
        private readonly BankStatementImportService $importService,
        private readonly BankStatementMatcher $matcher,
        private readonly BankReconciliationService $reconciliationService,
    ) {
    }

    public function create(Request $request)
    {
        $user = Auth::user();
        $condominiumId = $this->activeCondominiumId($user);

        $accounts = BankAccount::where('condominium_id', $condominiumId)
            ->where('active', true)
            ->orderByDesc('is_primary')
            ->orderBy('name')
            ->get();

        $selectedAccountId = (int) $request->input('account_id');
        $selectedAccount = $accounts->firstWhere('id', $selectedAccountId);

        return view('finance.reconciliations.statements.upload', [
            'accounts' => $accounts,
            'selectedAccount' => $selectedAccount,
            'mappingSession' => session('statement_mapping'),
        ]);
    }

    public function store(Request $request)
    {
        $user = Auth::user();
        $condominiumId = $this->activeCondominiumId($user);

        $data = $request->validate([
            'account_id' => ['required', 'integer'],
            'file' => ['required', 'file', 'max:5120', 'mimes:csv,txt,ofx,qfx'],
        ], [
            'file.required' => 'Selecione o arquivo do extrato.',
            'file.mimes' => 'Envie um arquivo CSV ou OFX.',
            'file.max' => 'O arquivo deve ter no máximo 5 MB.',
        ]);

        $account = BankAccount::where('condominium_id', $condominiumId)
            ->where('id', $data['account_id'])
            ->firstOrFail();

        $result = $this->importService->import($user, $account, $request->file('file'));

        if ($result['needs_mapping']) {
            return redirect()
                ->route('bank-statements.create', ['account_id' => $account->id])
                ->with('statement_mapping', [
                    'temp_path' => $result['temp_path'],
                    'file_hash' => $result['file_hash'],
                    'original_filename' => $result['original_filename'],
                    'headers' => $result['headers'],
                    'account_id' => $account->id,
                ])
                ->with('info', 'Não reconhecemos o cabeçalho do CSV. Informe o mapeamento das colunas.');
        }

        return redirect()
            ->route('bank-statements.show', $result['statement'])
            ->with('success', 'Extrato importado. Revise os vínculos sugeridos.');
    }

    public function map(Request $request)
    {
        $user = Auth::user();
        $condominiumId = $this->activeCondominiumId($user);

        $data = $request->validate([
            'account_id' => ['required', 'integer'],
            'temp_path' => ['required', 'string'],
            'file_hash' => ['required', 'string'],
            'original_filename' => ['required', 'string'],
            'mapping.date' => ['required', 'integer', 'min:0'],
            'mapping.description' => ['required', 'integer', 'min:0'],
            'mapping.amount' => ['nullable', 'integer', 'min:0'],
            'mapping.debit' => ['nullable', 'integer', 'min:0'],
            'mapping.credit' => ['nullable', 'integer', 'min:0'],
        ]);

        $account = BankAccount::where('condominium_id', $condominiumId)
            ->where('id', $data['account_id'])
            ->firstOrFail();

        $statement = $this->importService->importFromStoredPath(
            $user,
            $account,
            $data['temp_path'],
            $data['original_filename'],
            $data['file_hash'],
            $data['mapping']
        );

        return redirect()
            ->route('bank-statements.show', $statement)
            ->with('success', 'Extrato importado com o mapeamento informado.');
    }

    public function show(BankStatement $statement)
    {
        $user = Auth::user();
        $this->ensureResourceBelongsToActiveCondominium($user, (int) $statement->condominium_id);

        $statement->load(['bankAccount', 'lines']);

        $candidates = $this->matcher->eligibleCandidates(
            (int) $statement->condominium_id,
            $statement->bankAccount
        );

        $linkedKeys = collect($statement->lines)
            ->filter(fn (BankStatementLine $line) => $line->isLinked())
            ->mapWithKeys(fn (BankStatementLine $line) => [
                $line->matched_source_type.':'.$line->matched_source_id => true,
            ]);

        $systemOnly = $candidates
            ->reject(fn (array $c) => isset($linkedKeys[$c['key']]))
            ->values();

        $autoMatched = $statement->lines->where('status', BankStatementLine::STATUS_AUTO_MATCHED)->values();
        $suggested = $statement->lines->where('status', BankStatementLine::STATUS_SUGGESTED)->values();
        $confirmed = $statement->lines->whereIn('status', [
            BankStatementLine::STATUS_CONFIRMED,
            BankStatementLine::STATUS_CREATED,
        ])->values();
        $bankOnly = $statement->lines->where('status', BankStatementLine::STATUS_UNMATCHED)->values();
        $ignored = $statement->lines->where('status', BankStatementLine::STATUS_IGNORED)->values();

        $projectedBalance = null;
        $balanceDifference = null;
        if ($statement->bankAccount && $statement->period_start && $statement->period_end) {
            $preview = $this->reconciliationService->preview(
                (int) $statement->condominium_id,
                $statement->bankAccount,
                $statement->period_start->copy()->startOfDay(),
                $statement->period_end->copy()->endOfDay(),
                true
            );
            $projectedBalance = ((float) ($statement->bankAccount->current_balance ?? 0)) + $preview['totals']['net'];
            if ($statement->closing_balance !== null) {
                $balanceDifference = round((float) $statement->closing_balance - $projectedBalance, 2);
            }
        }

        $candidateLabels = $candidates->mapWithKeys(fn (array $c) => [
            $c['key'] => sprintf(
                '%s — R$ %s em %s',
                $c['label'],
                number_format($c['amount'], 2, ',', '.'),
                $c['date']->format('d/m/Y')
            ),
        ]);

        return view('finance.reconciliations.statements.show', [
            'statement' => $statement,
            'autoMatched' => $autoMatched,
            'suggested' => $suggested,
            'confirmed' => $confirmed,
            'bankOnly' => $bankOnly,
            'ignored' => $ignored,
            'systemOnly' => $systemOnly,
            'candidates' => $candidates,
            'candidateLabels' => $candidateLabels,
            'projectedBalance' => $projectedBalance,
            'balanceDifference' => $balanceDifference,
        ]);
    }

    public function acceptSuggestion(BankStatementLine $line)
    {
        $this->authorizeLine($line);
        $this->matcher->acceptSuggestion($line->load('statement'));

        return back()->with('success', 'Sugestão confirmada.');
    }

    public function confirm(Request $request, BankStatementLine $line)
    {
        $this->authorizeLine($line);

        $data = $request->validate([
            'source_type' => ['required', Rule::in([
                BankStatementLine::SOURCE_TRANSACTION,
                BankStatementLine::SOURCE_CONDOMINIUM_ACCOUNT,
            ])],
            'source_id' => ['required', 'integer'],
        ]);

        $this->matcher->confirm($line->load(['statement', 'bankAccount']), $data['source_type'], (int) $data['source_id']);

        return back()->with('success', 'Vínculo confirmado.');
    }

    public function unlink(BankStatementLine $line)
    {
        $this->authorizeLine($line);
        $this->matcher->unlink($line->load('statement'));

        return back()->with('success', 'Vínculo desfeito. O extrato foi reavaliado.');
    }

    public function ignore(BankStatementLine $line)
    {
        $this->authorizeLine($line);
        $this->matcher->ignore($line->load('statement'));

        return back()->with('success', 'Linha ignorada nesta conciliação.');
    }

    public function createEntry(Request $request, BankStatementLine $line)
    {
        $user = Auth::user();
        $this->authorizeLine($line);

        if (! $user->can('manage_transactions')) {
            abort(403);
        }

        $data = $request->validate([
            'type' => ['required', Rule::in(['income', 'expense'])],
            'description' => ['nullable', 'string', 'max:255'],
            'payment_method' => ['nullable', Rule::in(['cash', 'pix', 'bank_transfer', 'credit_card', 'debit_card', 'boleto', 'other'])],
            'notes' => ['nullable', 'string'],
            'category' => [
                Rule::requiredIf(fn () => $request->input('type') === 'expense'),
                'nullable',
                Rule::in(ExpenseCategories::keys()),
            ],
        ]);

        $this->importService->createEntryFromLine(
            $user,
            $line->load('statement'),
            $data['type'],
            $data['description'] ?? null,
            $data['payment_method'] ?? null,
            $data['notes'] ?? null,
            $data['category'] ?? null,
        );

        return back()->with('success', 'Lançamento criado no caixa e vinculado à linha do extrato.');
    }

    public function rematch(BankStatement $statement)
    {
        $user = Auth::user();
        $this->ensureResourceBelongsToActiveCondominium($user, (int) $statement->condominium_id);

        $this->matcher->match($statement->load(['lines', 'bankAccount']));

        return back()->with('success', 'Correspondências recalculadas.');
    }

    protected function authorizeLine(BankStatementLine $line): void
    {
        $user = Auth::user();
        $line->loadMissing('statement');
        $this->ensureResourceBelongsToActiveCondominium($user, (int) $line->statement->condominium_id);
    }
}
