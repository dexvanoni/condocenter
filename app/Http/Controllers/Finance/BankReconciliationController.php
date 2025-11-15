<?php

namespace App\Http\Controllers\Finance;

use App\Http\Controllers\Controller;
use App\Models\BankAccount;
use App\Models\BankAccountReconciliation;
use App\Services\BankReconciliationService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class BankReconciliationController extends Controller
{
    public function __construct(
        private readonly BankReconciliationService $service,
    ) {
    }

    public function index(Request $request)
    {
        $user = Auth::user();
        $condominiumId = $user->condominium_id;

        $accounts = BankAccount::where('condominium_id', $condominiumId)
            ->orderBy('name')
            ->get();

        $filters = [
            'account_id' => $request->input('account_id'),
            'start_date' => $request->input('start_date'),
            'end_date' => $request->input('end_date'),
        ];

        $preview = null;
        $selectedAccount = null;
        $latestReconciliation = null;

        if ($filters['account_id']) {
            $selectedAccount = $accounts->firstWhere('id', (int) $filters['account_id']);
        }

        if ($selectedAccount && $filters['start_date'] && $filters['end_date']) {
            $validator = Validator::make($filters, [
                'start_date' => ['required', 'date'],
                'end_date' => ['required', 'date', 'after_or_equal:start_date'],
            ]);

            if ($validator->passes()) {
                $startDate = Carbon::parse($filters['start_date'])->startOfDay();
                $endDate = Carbon::parse($filters['end_date'])->endOfDay();
                $preview = $this->service->preview($condominiumId, $selectedAccount, $startDate, $endDate);
            } else {
                $request->session()->flash('preview_errors', $validator->errors());
            }
        }

        if ($selectedAccount) {
            $latestReconciliation = BankAccountReconciliation::where('bank_account_id', $selectedAccount->id)
                ->where('condominium_id', $condominiumId)
                ->latest('created_at')
                ->first();
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
            'reconciliations' => $reconciliations,
        ]);
    }

    public function store(Request $request)
    {
        $user = Auth::user();
        $data = $request->validate([
            'account_id' => ['required', 'integer'],
            'start_date' => ['required', 'date'],
            'end_date' => ['required', 'date', 'after_or_equal:start_date'],
        ]);

        $account = BankAccount::where('condominium_id', $user->condominium_id)
            ->where('id', $data['account_id'])
            ->firstOrFail();

        $startDate = Carbon::parse($data['start_date'])->startOfDay();
        $endDate = Carbon::parse($data['end_date'])->endOfDay();

        // Validação: Verifica se já existe conciliação com sobreposição de período
        $existingReconciliation = BankAccountReconciliation::where('bank_account_id', $account->id)
            ->where('condominium_id', $user->condominium_id)
            ->where(function ($query) use ($startDate, $endDate) {
                $query->whereBetween('start_date', [$startDate, $endDate])
                    ->orWhereBetween('end_date', [$startDate, $endDate])
                    ->orWhere(function ($q) use ($startDate, $endDate) {
                        $q->where('start_date', '<=', $startDate)
                            ->where('end_date', '>=', $endDate);
                    });
            })
            ->first();

        if ($existingReconciliation) {
            return redirect()
                ->route('bank-reconciliation.index', [
                    'account_id' => $account->id,
                    'start_date' => $startDate->format('Y-m-d'),
                    'end_date' => $endDate->format('Y-m-d'),
                ])
                ->withErrors([
                    'period' => sprintf(
                        'Já existe uma conciliação para este período: %s a %s. Por favor, selecione um período diferente ou cancele a conciliação existente.',
                        $existingReconciliation->start_date->format('d/m/Y'),
                        $existingReconciliation->end_date->format('d/m/Y')
                    ),
                ]);
        }

        // Sugestão de período baseado na última conciliação
        $latestReconciliation = BankAccountReconciliation::where('bank_account_id', $account->id)
            ->where('condominium_id', $user->condominium_id)
            ->latest('created_at')
            ->first();

        if ($latestReconciliation && $startDate->lessThanOrEqualTo($latestReconciliation->end_date)) {
            $suggestedStart = $latestReconciliation->end_date->copy()->addDay();
            return redirect()
                ->route('bank-reconciliation.index', [
                    'account_id' => $account->id,
                    'start_date' => $startDate->format('Y-m-d'),
                    'end_date' => $endDate->format('Y-m-d'),
                ])
                ->withErrors([
                    'period' => sprintf(
                        'O período selecionado sobrepõe ou antecede a última conciliação. Sugestão de período: %s a %s.',
                        $suggestedStart->format('d/m/Y'),
                        $endDate->format('d/m/Y')
                    ),
                ]);
        }

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

        $account = BankAccount::where('condominium_id', $user->condominium_id)
            ->where('id', $data['account_id'])
            ->firstOrFail();

        $this->service->cancelLast($user, $account);

        return redirect()
            ->route('bank-reconciliation.index', ['account_id' => $account->id])
            ->with('success', 'Última conciliação cancelada com sucesso.');
    }
}

