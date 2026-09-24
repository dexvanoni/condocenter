<?php

namespace App\Http\Controllers\Platform;

use App\Http\Controllers\Controller;
use App\Models\Organization;
use App\Models\SubscriptionPlan;
use App\Models\User;
use App\Services\OrganizationSubscriptionService;
use App\Services\SubscriptionBillingService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class OrganizationSubscriptionController extends Controller
{
    public function __construct(
        private OrganizationSubscriptionService $subscriptions,
        private SubscriptionBillingService $billing,
    ) {}

    public function edit(Request $request, Organization $organization): View|RedirectResponse
    {
        $this->adminUser();

        if ($organization->isDirectCondominium()) {
            $condominium = $organization->condominiums()->orderBy('id')->first();
            abort_unless($condominium, 404, 'Esta organização ainda não tem condomínio para vincular o contrato.');

            return redirect()->route('platform.subscriptions.edit', [
                'condominium' => $condominium,
                'from_organization' => $organization->id,
            ]);
        }

        abort_unless($organization->isManagementCompany(), 404);

        $organization->load('subscription.plan');
        $plans = SubscriptionPlan::query()
            ->activeForAudience(SubscriptionPlan::AUDIENCE_MANAGEMENT_COMPANY)
            ->get();

        $subscription = $organization->subscription;
        if ($subscription) {
            $this->subscriptions->refreshCalculatedAmounts($subscription, $organization);
            $subscription->save();
        }

        $billingFilters = $this->billing->filtersFromRequest($request);
        $billingReport = $subscription
            ? $this->billing->getBillingReport($subscription, $billingFilters)
            : null;

        $exportUrl = $subscription
            ? route('platform.organizations.subscription.charges.export', array_merge(
                ['organization' => $organization],
                $billingFilters
            ))
            : null;

        return view('platform.organizations.subscription', compact(
            'organization',
            'plans',
            'subscription',
            'billingReport',
            'billingFilters',
            'exportUrl',
        ));
    }

    public function store(Request $request, Organization $organization): RedirectResponse
    {
        $this->adminUser();
        abort_unless($organization->isManagementCompany(), 404);

        $data = $request->validate([
            'subscription_plan_id' => ['nullable', 'exists:subscription_plans,id'],
            'billing_metric' => ['nullable', 'in:unit,user,fixed'],
            'fixed_price' => ['nullable', 'numeric', 'min:0'],
            'unit_price' => ['nullable', 'numeric', 'min:0'],
            'user_price' => ['nullable', 'numeric', 'min:0'],
            'billing_cycle' => ['nullable', 'in:monthly,quarterly,semiannual,annual'],
            'trial_days' => ['nullable', 'integer', 'min:0', 'max:365'],
            'payment_method' => ['nullable', 'in:boleto,credit_card,pix_recurring,bank_deposit'],
            'contract_starts_at' => ['nullable', 'date'],
            'contract_ends_at' => ['nullable', 'date'],
            'financial_cnpj' => ['nullable', 'string', 'max:18'],
            'financial_contact_name' => ['nullable', 'string', 'max:255'],
            'financial_contact_email' => ['nullable', 'email', 'max:255'],
            'financial_contact_phone' => ['nullable', 'string', 'max:30'],
            'max_condominiums' => ['nullable', 'integer', 'min:1'],
            'max_units' => ['nullable', 'integer', 'min:1'],
            'max_users' => ['nullable', 'integer', 'min:1'],
            'admin_notes' => ['nullable', 'string', 'max:5000'],
        ]);

        if (!empty($data['subscription_plan_id'])) {
            $plan = SubscriptionPlan::query()->find($data['subscription_plan_id']);
            abort_unless($plan?->isForManagementCompany(), 422, 'Selecione um plano do catálogo de administradoras.');
        }

        $this->subscriptions->upsert($organization, $data, $request->user());

        return redirect()
            ->route('platform.organizations.subscription.edit', $organization)
            ->with('success', 'Contrato da administradora salvo.');
    }

    public function activate(Organization $organization): RedirectResponse
    {
        $this->adminUser();
        $subscription = $organization->subscription;
        abort_unless($subscription, 404);

        try {
            $this->subscriptions->activate($subscription, auth()->user());
        } catch (ValidationException $e) {
            return back()->withErrors($e->errors());
        }

        return back()->with('success', 'Assinatura ativada. A recorrência foi enviada ao Asaas quando o contrato usa cobrança automática.');
    }

    public function suspend(Organization $organization): RedirectResponse
    {
        $this->adminUser();
        $subscription = $organization->subscription;
        abort_unless($subscription, 404);

        $this->subscriptions->suspend($subscription, auth()->user());

        return back()->with('success', 'Assinatura suspensa.');
    }

    public function cancel(Request $request, Organization $organization): RedirectResponse
    {
        $this->adminUser();
        $subscription = $organization->subscription;
        abort_unless($subscription, 404);

        $this->subscriptions->cancel($subscription, auth()->user(), $request->input('notes'));

        return back()->with('success', 'Assinatura cancelada.');
    }

    public function syncAsaas(Organization $organization): RedirectResponse
    {
        $this->adminUser();
        $subscription = $organization->subscription;
        abort_unless($subscription, 404);

        try {
            $this->subscriptions->syncAsaasSubscription($subscription, auth()->user());
        } catch (ValidationException $e) {
            return back()->withErrors($e->errors());
        }

        return back()->with('success', 'Sincronização com Asaas concluída.');
    }

    public function storeCharge(Request $request, Organization $organization): RedirectResponse
    {
        $this->adminUser();
        $subscription = $organization->subscription;
        abort_unless($subscription, 404);

        $validated = $request->validate([
            'value' => ['required', 'numeric', 'min:0.01', 'max:999999.99'],
            'due_date' => ['required', 'date', 'after_or_equal:today'],
            'billing_type' => ['nullable', 'in:BOLETO,PIX,CREDIT_CARD'],
            'description' => ['nullable', 'string', 'max:255'],
        ]);

        try {
            $this->subscriptions->createManualAsaasCharge($subscription, [
                'value' => $validated['value'],
                'due_date' => $validated['due_date'],
                'billing_type' => $validated['billing_type'] ?? 'BOLETO',
                'description' => $validated['description'] ?? null,
            ]);
        } catch (ValidationException $e) {
            return back()->withErrors($e->errors());
        }

        return back()->with('success', 'Cobrança avulsa criada no Asaas.');
    }

    public function cancelCharge(Request $request, Organization $organization): RedirectResponse
    {
        $this->adminUser();
        $subscription = $organization->subscription;
        abort_unless($subscription, 404);

        $validated = $request->validate([
            'payment_id' => ['required', 'string', 'max:64'],
        ]);

        try {
            $this->subscriptions->cancelAsaasPayment($subscription, $validated['payment_id']);
        } catch (ValidationException $e) {
            return back()->withErrors($e->errors());
        }

        return back()->with('success', 'Cobrança cancelada no Asaas.');
    }

    public function refundCharge(Request $request, Organization $organization): RedirectResponse
    {
        $this->adminUser();
        $subscription = $organization->subscription;
        abort_unless($subscription, 404);

        $validated = $request->validate([
            'payment_id' => ['required', 'string', 'max:64'],
        ]);

        try {
            $this->subscriptions->refundAsaasPayment($subscription, $validated['payment_id']);
        } catch (ValidationException $e) {
            return back()->withErrors($e->errors());
        }

        return back()->with('success', 'Estorno solicitado no Asaas.');
    }

    public function exportCharges(Request $request, Organization $organization)
    {
        $this->adminUser();
        $subscription = $organization->subscription;
        abort_unless($subscription, 404, 'Nenhum contrato configurado.');

        return $this->billing->exportCsv(
            $subscription,
            $this->billing->filtersFromRequest($request),
            'cobrancas-organizacao'
        );
    }

    private function adminUser(): User
    {
        /** @var User|null $user */
        $user = auth()->user();
        abort_unless($user instanceof User && $user->isAdmin(), 403);

        return $user;
    }
}
