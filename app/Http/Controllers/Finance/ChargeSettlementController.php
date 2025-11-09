<?php

namespace App\Http\Controllers\Finance;

use App\Http\Controllers\Controller;
use App\Models\Charge;
use App\Models\Fee;
use App\Services\ChargeSettlementService;
use Carbon\Carbon;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;

class ChargeSettlementController extends Controller
{
    public function __construct(
        private readonly ChargeSettlementService $settlementService
    ) {
        $this->middleware(['can:manage_transactions']);
    }

    public function markPaid(Request $request, Charge $charge): RedirectResponse
    {
        $this->authorizeCharge($charge);

        $data = $request->validate([
            'paid_at' => ['required', 'date'],
            'payment_method' => ['required', Rule::in(['cash', 'pix', 'bank_transfer', 'credit_card', 'debit_card', 'boleto', 'payroll', 'other'])],
            'notes' => ['nullable', 'string', 'max:500'],
        ]);

        $this->settlementService->markAsPaid(
            $charge,
            Carbon::parse($data['paid_at']),
            $data['payment_method'],
            $data['notes'] ?? null,
            Auth::id()
        );

        return redirect($request->input('return_url', url()->previous()))
            ->with('success', 'Cobrança marcada como paga com sucesso.');
    }

    public function revokePayroll(Request $request, Charge $charge): RedirectResponse
    {
        $this->authorizeCharge($charge);

        $data = $request->validate([
            'reason' => ['nullable', 'string', 'max:500'],
        ]);

        $this->settlementService->revokePayrollSettlement(
            $charge,
            $data['reason'] ?? null,
            Auth::id()
        );

        return redirect($request->input('return_url', url()->previous()))
            ->with('success', 'Liquidação por desconto em folha revogada. A unidade retorna como pendente.');
    }

    public function markAllPaid(Request $request, Fee $fee): RedirectResponse
    {
        $this->authorizeFee($fee);

        $data = $request->validate([
            'paid_at' => ['required', 'date'],
            'payment_method' => ['required', Rule::in(['cash', 'pix', 'bank_transfer', 'credit_card', 'debit_card', 'boleto', 'payroll', 'other'])],
            'notes' => ['nullable', 'string', 'max:500'],
        ]);

        $charges = $fee->charges()
            ->where('status', '!=', 'paid')
            ->get();

        foreach ($charges as $charge) {
            $this->settlementService->markAsPaid(
                $charge,
                Carbon::parse($data['paid_at']),
                $data['payment_method'],
                $data['notes'] ?? null,
                Auth::id()
            );
        }

        return redirect($request->input('return_url', url()->previous()))
            ->with('success', 'Todas as cobranças pendentes foram marcadas como pagas.');
    }

    private function authorizeCharge(Charge $charge): void
    {
        if ($charge->condominium_id !== Auth::user()->condominium_id) {
            abort(403);
        }
    }

    private function authorizeFee(Fee $fee): void
    {
        if ($fee->condominium_id !== Auth::user()->condominium_id) {
            abort(403);
        }
    }
}

