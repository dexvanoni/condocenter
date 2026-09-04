<?php

namespace App\Http\Controllers\Finance;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Concerns\ResolvesActiveCondominium;
use App\Http\Requests\UpdateFinancialModeRequest;
use App\Helpers\SidebarHelper;
use App\Services\BankAccountRoutingService;
use Illuminate\Http\Request;

class FinancialSettingsController extends Controller
{
    use ResolvesActiveCondominium;

    public function __construct(
        private readonly BankAccountRoutingService $bankAccountRoutingService,
    ) {
        $this->middleware(function ($request, $next) {
            if (!SidebarHelper::canManageFinancialSettings($request->user())) {
                abort(403, 'Acesso negado.');
            }

            return $next($request);
        });
    }

    public function index(Request $request)
    {
        $user = $request->user();
        $condominium = $this->activeCondominium($user);

        return view('finance.settings.index', [
            'condominium' => $condominium,
            'currentMode' => $condominium->financial_mode ?? 'full',
            'bankAccounts' => $this->bankAccountRoutingService->accountsForCondominium($condominium->id),
            'routingRules' => $this->bankAccountRoutingService->rulesForCondominium($condominium->id),
            'routingSourceKeys' => \App\Models\BankAccountRoutingRule::SOURCE_KEYS,
        ]);
    }

    public function updateMode(UpdateFinancialModeRequest $request)
    {
        $user = $request->user();
        $condominium = $this->activeCondominium($user);

        $condominium->update([
            'financial_mode' => $request->financial_mode,
        ]);

        $label = $request->financial_mode === 'simplified'
            ? 'Ambiente Financeiro Simplificado'
            : 'Ambiente Financeiro Completo';

        return redirect()
            ->route('financial.settings.index')
            ->with('success', "Ambiente alterado para: {$label}.");
    }

    public function updateRoutingRules(Request $request)
    {
        $user = $request->user();
        $condominium = $this->activeCondominium($user);

        $validated = $request->validate([
            'rules' => ['required', 'array'],
            'rules.*' => ['nullable', 'integer', 'exists:bank_accounts,id'],
        ]);

        $this->bankAccountRoutingService->syncRules($condominium->id, $validated['rules']);

        return redirect()
            ->route('financial.settings.index')
            ->with('success', 'Regras de destino das contas bancárias atualizadas com sucesso.');
    }
}
