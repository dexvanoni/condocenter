<?php

namespace App\Http\Controllers\Finance;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Concerns\ResolvesActiveCondominium;
use App\Http\Requests\StoreBankAccountRequest;
use App\Http\Requests\UpdateBankAccountRequest;
use App\Models\BankAccount;
use App\Models\BankAccountBalance;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class BankAccountController extends Controller
{
    use ResolvesActiveCondominium;

    public function __construct()
    {
        $this->middleware(function ($request, $next) {
            $user = $request->user();

            if ($user?->can('view_bank_statements') || $user?->can('manage_bank_statements')) {
                return $next($request);
            }

            abort(403, 'Acesso não autorizado.');
        })->only(['index', 'show']);

        $this->middleware(['can:manage_bank_statements'])->except(['index', 'show']);
    }

    public function index(Request $request)
    {
        $condominiumId = $this->activeCondominiumId(Auth::user());

        $accounts = BankAccount::with('balances')
            ->where('condominium_id', $condominiumId)
            ->orderBy('name')
            ->paginate(15)
            ->withQueryString();

        return view('finance.bank-accounts.index', compact('accounts'));
    }

    public function create()
    {
        return view('finance.bank-accounts.create');
    }

    public function store(StoreBankAccountRequest $request)
    {
        $data = $request->validated();
        $data['condominium_id'] = $this->activeCondominiumId($request->user());

        $account = BankAccount::create($data);

        if ($request->boolean('is_primary')) {
            $this->syncPrimaryAccount($account);
        }

        if (!empty($data['current_balance'])) {
            $recordedAt = $data['balance_updated_at'] ?? now();

            $account->forceFill([
                'balance_updated_at' => $recordedAt,
            ])->save();

            BankAccountBalance::create([
                'bank_account_id' => $account->id,
                'balance' => $data['current_balance'],
                'recorded_at' => $recordedAt instanceof \DateTimeInterface ? $recordedAt->format('Y-m-d') : $recordedAt,
                'reference' => 'Saldo inicial',
            ]);
        }

        return redirect()
            ->route('financial.bank-accounts.index')
            ->with('success', 'Conta bancária cadastrada com sucesso!');
    }

    public function edit(BankAccount $bankAccount)
    {
        $this->authorizeAccount($bankAccount);

        return view('finance.bank-accounts.edit', [
            'bankAccount' => $bankAccount,
        ]);
    }

    public function update(UpdateBankAccountRequest $request, BankAccount $bankAccount)
    {
        $this->authorizeAccount($bankAccount);

        $data = $request->validated();

        $bankAccount->update($data);

        if ($request->boolean('is_primary')) {
            $this->syncPrimaryAccount($bankAccount);
        }

        if ($request->boolean('register_balance_history') && $request->filled('history_balance')) {
            $historyRecordedAt = $request->input('history_recorded_at') ?: ($bankAccount->balance_updated_at?->format('Y-m-d') ?? now()->toDateString());

            BankAccountBalance::create([
                'bank_account_id' => $bankAccount->id,
                'balance' => $request->input('history_balance'),
                'recorded_at' => $historyRecordedAt,
                'reference' => $request->input('history_reference'),
                'notes' => $request->input('history_notes'),
            ]);

            $bankAccount->forceFill([
                'current_balance' => $request->input('history_balance'),
                'balance_updated_at' => $historyRecordedAt,
            ])->save();
        }

        return redirect()
            ->route('financial.bank-accounts.index')
            ->with('success', 'Conta bancária atualizada com sucesso!');
    }

    public function destroy(Request $request, BankAccount $bankAccount)
    {
        $this->authorizeAccount($bankAccount);

        $bankAccount->delete();

        return redirect()
            ->route('financial.bank-accounts.index')
            ->with('success', 'Conta bancária removida com sucesso!');
    }

    private function authorizeAccount(BankAccount $bankAccount): void
    {
        $this->ensureResourceBelongsToActiveCondominium(Auth::user(), (int) $bankAccount->condominium_id);
    }

    private function syncPrimaryAccount(BankAccount $account): void
    {
        BankAccount::query()
            ->where('condominium_id', $account->condominium_id)
            ->where('id', '!=', $account->id)
            ->update(['is_primary' => false]);

        $account->forceFill(['is_primary' => true])->save();
    }
}

