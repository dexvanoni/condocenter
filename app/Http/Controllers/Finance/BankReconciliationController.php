<?php

namespace App\Http\Controllers\Finance;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Concerns\ResolvesActiveCondominium;
use App\Models\BankAccount;
use App\Models\BankAccountReconciliation;
use App\Services\BankReconciliationService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class BankReconciliationController extends Controller
{
    use ResolvesActiveCondominium;

    public function __construct(
        private readonly BankReconciliationService $service,
    ) {
    }

    public function index(Request $request)
    {
        $user = Auth::user();
        $condominiumId = $this->activeCondominiumId($user);

        $accounts = BankAccount::where('condominium_id', $condominiumId)
            ->orderBy('name')
            ->get();

        $filters = [
            'account_id' => $request->input('account_id'),
            'start_date' => $request->input('start_date'),
            'end_date' => $request->input('end_date'),
        ];
        $pendingOnly = $request->boolean('pending_only');

        $preview = null;
        $selectedAccount = null;
        $latestReconciliation = null;
        $suggestedStartDate = null;
        $pendingForAccount = null;
        $accountPeriodDefaults = [];

        foreach ($accounts as $account) {
            $accountPeriodDefaults[$account->id] = $this->defaultPeriodForAccount(
                $condominiumId,
                $account
            );
        }

        if ($filters['account_id']) {
            $selectedAccount = $accounts->firstWhere('id', (int) $filters['account_id']);
        }

        if ($selectedAccount) {
            $defaults = $accountPeriodDefaults[$selectedAccount->id] ?? $this->defaultPeriodForAccount(
                $condominiumId,
                $selectedAccount
            );

            $latestReconciliation = BankAccountReconciliation::where('bank_account_id', $selectedAccount->id)
                ->where('condominium_id', $condominiumId)
                ->latest('created_at')
                ->first();

            $suggestedStartDate = $defaults['start_date'];

            if (!$filters['start_date']) {
                $filters['start_date'] = $defaults['start_date'];
            }

            if (!$filters['end_date']) {
                $filters['end_date'] = $defaults['end_date'];
            }

            $this->normalizePeriodFilters($filters);

            $pendingForAccount = $this->service->preview(
                $condominiumId,
                $selectedAccount,
                null,
                null
            )['totals'];
        }

        if ($selectedAccount && $pendingOnly) {
            $preview = $this->service->preview($condominiumId, $selectedAccount, null, null);

            if (($preview['totals']['count_entries'] ?? 0) > 0) {
                [$periodStart, $periodEnd] = $this->service->resolvePeriodFromPreview($preview);
                $filters['start_date'] = $periodStart->format('Y-m-d');
                $filters['end_date'] = $periodEnd->format('Y-m-d');
            }
        } elseif ($selectedAccount && $filters['start_date'] && $filters['end_date']) {
            $validator = Validator::make($filters, [
                'start_date' => ['required', 'date'],
                'end_date' => ['required', 'date', 'after_or_equal:start_date'],
            ], [
                'start_date.required' => 'Informe a data inicial do período.',
                'start_date.date' => 'A data inicial é inválida.',
                'end_date.required' => 'Informe a data final do período.',
                'end_date.date' => 'A data final é inválida.',
                'end_date.after_or_equal' => 'A data final deve ser igual ou posterior à data inicial.',
            ]);

            if ($validator->passes()) {
                $startDate = Carbon::parse($filters['start_date'])->startOfDay();
                $endDate = Carbon::parse($filters['end_date'])->endOfDay();
                $preview = $this->service->preview($condominiumId, $selectedAccount, $startDate, $endDate);
            } else {
                $request->session()->flash('preview_errors', $validator->errors());
            }
        }

        $reconciliations = BankAccountReconciliation::with([
                'bankAccount',
                'items' => fn ($query) => $query->select('id', 'reconciliation_id', 'label', 'direction', 'amount'),
            ])
            ->where('condominium_id', $condominiumId)
            ->latest('created_at')
            ->paginate(10)
            ->withQueryString();

        return view('finance.reconciliations.index', [
            'accounts' => $accounts,
            'filters' => $filters,
            'preview' => $preview,
            'selectedAccount' => $selectedAccount,
            'latestReconciliation' => $latestReconciliation,
            'suggestedStartDate' => $suggestedStartDate,
            'pendingForAccount' => $pendingForAccount,
            'accountPeriodDefaults' => $accountPeriodDefaults,
            'pendingOnly' => $pendingOnly,
            'reconciliations' => $reconciliations,
        ]);
    }

    /**
     * @return array{start_date: string, end_date: string}
     */
    private function defaultPeriodForAccount(int $condominiumId, BankAccount $account): array
    {
        $latestReconciliation = BankAccountReconciliation::where('bank_account_id', $account->id)
            ->where('condominium_id', $condominiumId)
            ->latest('created_at')
            ->first();

        $start = $latestReconciliation
            ? $latestReconciliation->end_date->copy()->addDay()->startOfDay()
            : now()->startOfMonth()->startOfDay();

        $end = $start->copy()->max(now()->startOfDay());

        return [
            'start_date' => $start->format('Y-m-d'),
            'end_date' => $end->format('Y-m-d'),
        ];
    }

    /**
     * @param array<string, mixed> $filters
     */
    private function normalizePeriodFilters(array &$filters): void
    {
        if (empty($filters['start_date']) || empty($filters['end_date'])) {
            return;
        }

        $start = Carbon::parse($filters['start_date'])->startOfDay();
        $end = Carbon::parse($filters['end_date'])->startOfDay();

        if ($end->lt($start)) {
            $filters['end_date'] = $start->copy()->max(now()->startOfDay())->format('Y-m-d');
        }
    }

    public function store(Request $request)
    {
        $user = Auth::user();
        $pendingOnly = $request->boolean('pending_only');

        $data = $request->validate([
            'account_id' => ['required', 'integer'],
            'start_date' => [$pendingOnly ? 'nullable' : 'required', 'date'],
            'end_date' => [$pendingOnly ? 'nullable' : 'required', 'date', 'after_or_equal:start_date'],
            'pending_only' => ['sometimes', 'boolean'],
        ], [
            'start_date.required' => 'Informe a data inicial do período.',
            'end_date.required' => 'Informe a data final do período.',
            'end_date.after_or_equal' => 'A data final deve ser igual ou posterior à data inicial.',
        ]);

        $condominiumId = $this->activeCondominiumId($user);

        $account = BankAccount::where('condominium_id', $condominiumId)
            ->where('id', $data['account_id'])
            ->firstOrFail();

        if ($pendingOnly) {
            $preview = $this->service->preview($condominiumId, $account, null, null);

            if (($preview['totals']['count_entries'] ?? 0) === 0) {
                return redirect()
                    ->route('bank-reconciliation.index', ['account_id' => $account->id])
                    ->withErrors([
                        'period' => 'Não há lançamentos pendentes de conciliação nesta conta.',
                    ]);
            }

            [$startDate, $endDate] = $this->service->resolvePeriodFromPreview($preview);
            $this->service->reconcile($user, $account, $startDate, $endDate, $preview);

            return redirect()
                ->route('bank-reconciliation.index', ['account_id' => $account->id])
                ->with('success', sprintf(
                    'Conciliação de pendências registrada com sucesso (%s a %s).',
                    $startDate->format('d/m/Y'),
                    $endDate->format('d/m/Y')
                ));
        }

        $startDate = Carbon::parse($data['start_date'])->startOfDay();
        $endDate = Carbon::parse($data['end_date'])->endOfDay();

        $this->service->reconcile($user, $account, $startDate, $endDate);

        return redirect()
            ->route('bank-reconciliation.index', [
                'account_id' => $account->id,
                'start_date' => $startDate->format('Y-m-d'),
                'end_date' => $endDate->format('Y-m-d'),
            ])
            ->with('success', 'Conciliação bancária registrada com sucesso.');
    }

    public function cancel(Request $request)
    {
        $user = Auth::user();
        $data = $request->validate([
            'account_id' => ['required', 'integer'],
        ]);

        $condominiumId = $this->activeCondominiumId($user);

        $account = BankAccount::where('condominium_id', $condominiumId)
            ->where('id', $data['account_id'])
            ->firstOrFail();

        $this->service->cancelLast($user, $account);

        return redirect()
            ->route('bank-reconciliation.index', ['account_id' => $account->id])
            ->with('success', 'Última conciliação cancelada com sucesso.');
    }
}

