<?php

namespace App\Http\Controllers;

use App\Http\Requests\UpdateSyndicPaymentMethodRequest;
use App\Services\ActiveCondominiumService;
use App\Services\SubscriptionBillingService;
use App\Services\SyndicSubscriptionPaymentService;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class SyndicSubscriptionController extends Controller
{
    public function __construct(
        private ActiveCondominiumService $activeCondominium,
        private SubscriptionBillingService $billing,
        private SyndicSubscriptionPaymentService $payments,
    ) {}

    public function show(Request $request)
    {
        ['condominium' => $condominium, 'subscription' => $subscription] = $this->resolveContext();

        $billingFilters = $this->billing->filtersFromRequest($request);
        $billingReport = $this->billing->getBillingReport($subscription, $billingFilters);
        $asaasSummary = $this->payments->getAsaasSubscriptionSummary($subscription);

        return view('syndic-subscription.show', [
            'condominium' => $condominium,
            'subscription' => $subscription,
            'billingReport' => $billingReport,
            'billingFilters' => $billingFilters,
            'exportUrl' => route('syndic-subscription.charges.export', $billingFilters),
            'asaasSummary' => $asaasSummary,
            'pixFlash' => session('pix_checkout'),
        ]);
    }

    public function exportCharges(Request $request)
    {
        ['subscription' => $subscription] = $this->resolveContext();
        $filters = $this->billing->filtersFromRequest($request);

        return $this->billing->exportCsv($subscription, $filters, 'minha-assinatura');
    }

    public function pixCheckout(string $paymentId)
    {
        ['subscription' => $subscription] = $this->resolveContext();

        return response()->json(
            $this->payments->getPixCheckout($subscription, $paymentId)
        );
    }

    public function payEarly(Request $request)
    {
        ['subscription' => $subscription] = $this->resolveContext();

        $request->validate([
            'billing_type' => ['nullable', 'in:PIX,BOLETO,CREDIT_CARD'],
        ]);

        $method = match ($request->input('billing_type')) {
            'PIX' => 'pix_recurring',
            'CREDIT_CARD' => 'credit_card',
            'BOLETO' => 'boleto',
            default => $subscription->payment_method,
        };

        $billingType = $request->input('billing_type')
            ?: match ($subscription->payment_method) {
                'credit_card' => 'CREDIT_CARD',
                'pix_recurring' => 'PIX',
                default => 'BOLETO',
            };

        $charge = $this->payments->createEarlyPayment($subscription, $billingType);

        if (!empty($charge['pix'])) {
            return redirect()
                ->route('syndic-subscription.show')
                ->with('success', 'Cobrança antecipada gerada. Utilize o PIX abaixo para pagar.')
                ->with('pix_checkout', array_merge($charge['pix'], [
                    'payment_id' => $charge['id'],
                    'value' => $charge['value'],
                ]));
        }

        $redirect = redirect()->route('syndic-subscription.show')
            ->with('success', 'Cobrança antecipada gerada com sucesso.');

        if (!empty($charge['invoice_url'])) {
            return $redirect->with('open_invoice_url', $charge['invoice_url']);
        }

        return $redirect;
    }

    public function updatePaymentMethod(UpdateSyndicPaymentMethodRequest $request)
    {
        ['subscription' => $subscription] = $this->resolveContext();

        try {
            $this->payments->updatePaymentMethod(
                $subscription,
                $request->input('payment_method'),
                $request->only([
                    'holder_name', 'email', 'cpf_cnpj', 'postal_code',
                    'address_number', 'phone', 'number', 'expiry_month', 'expiry_year', 'ccv',
                ])
            );
        } catch (ValidationException $e) {
            return back()->withErrors($e->errors())->withInput();
        }

        return back()->with('success', 'Forma de pagamento atualizada com sucesso.');
    }

    protected function resolveContext(): array
    {
        $user = auth()->user();
        abort_unless($user && ($user->isSindico() || $user->isAdmin()), 403);

        $condominium = $this->activeCondominium->getActiveCondominium($user) ?? $user->condominium;
        abort_if(!$condominium, 404);

        $subscription = $condominium->subscription()
            ->with(['plan', 'financialResponsible'])
            ->first();

        abort_if(!$subscription, 404, 'Nenhum contrato de assinatura configurado para este condomínio.');

        $this->payments->assertCanManage($user, $subscription, (int) $condominium->id);

        return compact('user', 'condominium', 'subscription');
    }
}
