<?php

namespace App\Http\Controllers\Platform;

use App\Http\Controllers\Controller;
use App\Http\Requests\ExtendCondominiumSubscriptionRequest;
use App\Http\Requests\StoreCondominiumSubscriptionRequest;
use App\Models\Condominium;
use App\Models\CondominiumSubscription;
use App\Models\CondominiumSubscriptionDocument;
use App\Models\SubscriptionPlan;
use App\Models\User;
use App\Services\CondominiumSubscriptionService;
use App\Services\SubscriptionBillingService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;

class CondominiumSubscriptionController extends Controller
{
    public function __construct(
        private CondominiumSubscriptionService $subscriptions,
        private SubscriptionBillingService $billing,
    ) {}

    private function contract(Condominium $condominium): CondominiumSubscription
    {
        $id = (int) request('subscription_id', request('contract'));
        $subscription = $id
            ? $condominium->subscriptions()->whereKey($id)->first()
            : $condominium->subscriptions()->first();

        abort_unless($subscription, 404, 'Contrato não encontrado neste condomínio.');

        return $subscription;
    }

    private function adminUser(): User
    {
        /** @var User|null $user */
        $user = auth()->user();
        abort_unless($user instanceof User && $user->isAdmin(), 403);

        return $user;
    }

    public function edit(Request $request, Condominium $condominium)
    {
        $this->adminUser();

        $condominium->load([
            'subscription.documents',
            'subscription.logs.user',
            'subscription.financialResponsible',
            'units',
        ]);

        $syndics = $condominium->users()
            ->whereHas('roles', fn ($q) => $q->where('name', 'Síndico'))
            ->orderBy('name')
            ->get(['id', 'name', 'email']);

        $plans = SubscriptionPlan::query()
            ->activeForAudience(SubscriptionPlan::AUDIENCE_CONDOMINIUM)
            ->get();

        $contracts = $condominium->subscriptions()->with('plan')->get();
        $creating = $request->boolean('novo');
        $subscription = null;
        if (!$creating) {
            $selectedId = $request->integer('contract');
            $subscription = $selectedId
                ? $contracts->firstWhere('id', $selectedId)
                : $contracts->first();
        }
        if ($subscription) {
            $this->subscriptions->refreshCalculatedAmounts($subscription, $condominium);
            $subscription->save();
        }

        $billingFilters = $this->billing->filtersFromRequest($request);
        $billingReport = $subscription
            ? $this->billing->getBillingReport($subscription, $billingFilters)
            : null;

        $exportUrl = route('platform.subscriptions.charges.export', array_merge(
            ['condominium' => $condominium],
            $billingFilters
        ));

        $backUrl = route('condominiums.show', $condominium);
        $backLabel = $condominium->name;
        $fromOrganization = $request->integer('from_organization');
        if ($fromOrganization > 0 && (int) $condominium->organization_id === $fromOrganization) {
            $backUrl = route('platform.organizations.show', $fromOrganization);
            $backLabel = 'Organização';
        }

        return view('platform.subscriptions.edit', compact(
            'condominium',
            'subscription',
            'contracts',
            'syndics',
            'plans',
            'billingReport',
            'billingFilters',
            'exportUrl',
            'backUrl',
            'backLabel',
        ));
    }

    public function store(StoreCondominiumSubscriptionRequest $request, Condominium $condominium)
    {
        $data = $request->validated();
        $data['auto_renew'] = $request->boolean('auto_renew');
        if (!empty($data['subscription_plan_id'])) {
            $plan = SubscriptionPlan::query()->find($data['subscription_plan_id']);
            abort_unless($plan && !$plan->isForManagementCompany(), 422, 'Selecione um plano do catálogo de síndico/condomínio.');
        }

        $saved = $this->subscriptions->upsert($condominium, $data, $request->user());

        return redirect()
            ->route('platform.subscriptions.edit', ['condominium' => $condominium, 'contract' => $saved->id])
            ->with('success', 'Contrato de assinatura salvo.');
    }

    public function activate(Condominium $condominium)
    {
        $admin = $this->adminUser();
        $subscription = $this->contract($condominium);

        abort_if(! $subscription, 404, 'Configure o contrato antes de ativar.');

        try {
            $this->subscriptions->activate($subscription, $admin);
        } catch (ValidationException $e) {
            return back()->withErrors($e->errors());
        }

        return back()->with('success', 'Assinatura ativada com sucesso.');
    }

    public function suspend(Request $request, Condominium $condominium)
    {
        $admin = $this->adminUser();
        $subscription = $this->contract($condominium);
        abort_if(! $subscription, 404);

        $this->subscriptions->suspend($subscription, $admin, $request->input('notes'));

        return back()->with('success', 'Assinatura suspensa.');
    }

    public function cancel(Request $request, Condominium $condominium)
    {
        $admin = $this->adminUser();
        $subscription = $this->contract($condominium);
        abort_if(! $subscription, 404);

        $this->subscriptions->cancel($subscription, $admin, $request->input('notes'));

        return back()->with('success', 'Assinatura cancelada.');
    }

    public function reactivate(Condominium $condominium)
    {
        $admin = $this->adminUser();
        $subscription = $this->contract($condominium);
        abort_if(! $subscription, 404);

        try {
            $this->subscriptions->reactivate($subscription, $admin);
        } catch (ValidationException $e) {
            return back()->withErrors($e->errors());
        }

        return back()->with('success', 'Assinatura reativada.');
    }

    public function resetContract(Request $request, Condominium $condominium)
    {
        $admin = $this->adminUser();
        $subscription = $this->contract($condominium);
        abort_if(! $subscription, 404);

        $this->subscriptions->resetForNewContract($subscription, $admin, $request->input('notes'));

        return back()->with('success', 'Contrato reiniciado em rascunho. Ajuste os dados e ative novamente.');
    }

    public function cancelCharge(Request $request, Condominium $condominium)
    {
        $admin = $this->adminUser();
        $subscription = $this->contract($condominium);
        abort_if(! $subscription, 404);

        $validated = $request->validate([
            'payment_id' => ['required', 'string', 'max:64'],
        ]);

        try {
            $this->subscriptions->cancelAsaasPayment($subscription, $admin, $validated['payment_id']);
        } catch (ValidationException $e) {
            return back()->withErrors($e->errors());
        }

        return back()->with('success', 'Cobrança cancelada no Asaas.');
    }

    public function refundCharge(Request $request, Condominium $condominium)
    {
        $admin = $this->adminUser();
        $subscription = $this->contract($condominium);
        abort_if(! $subscription, 404);

        $validated = $request->validate([
            'payment_id' => ['required', 'string', 'max:64'],
        ]);

        try {
            $this->subscriptions->refundAsaasPayment($subscription, $admin, $validated['payment_id']);
        } catch (ValidationException $e) {
            return back()->withErrors($e->errors());
        }

        return back()->with('success', 'Estorno solicitado no Asaas.');
    }

    public function storeCharge(Request $request, Condominium $condominium)
    {
        $admin = $this->adminUser();
        $subscription = $this->contract($condominium);
        abort_if(! $subscription, 404);

        $validated = $request->validate([
            'value' => ['required', 'numeric', 'min:0.01', 'max:999999.99'],
            'due_date' => ['required', 'date', 'after_or_equal:today'],
            'billing_type' => ['nullable', 'in:BOLETO,PIX,CREDIT_CARD'],
            'description' => ['nullable', 'string', 'max:255'],
        ]);

        try {
            $this->subscriptions->createManualAsaasCharge($subscription, $admin, [
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

    public function extend(ExtendCondominiumSubscriptionRequest $request, Condominium $condominium)
    {
        $subscription = $this->contract($condominium);
        abort_if(! $subscription, 404);

        $this->subscriptions->extend(
            $subscription,
            $request->user(),
            Carbon::parse($request->input('extended_until')),
            $request->input('notes')
        );

        return back()->with('success', 'Contrato prorrogado.');
    }

    public function syncAsaas(Condominium $condominium)
    {
        $admin = $this->adminUser();
        $subscription = $this->contract($condominium);
        abort_if(! $subscription, 404);

        try {
            $this->subscriptions->syncAsaasSubscription($subscription, $admin);
        } catch (ValidationException $e) {
            return back()->withErrors($e->errors());
        }

        return back()->with('success', 'Sincronização com Asaas concluída.');
    }

    public function uploadDocument(Request $request, Condominium $condominium)
    {
        $this->adminUser();
        $subscription = $this->contract($condominium);
        abort_if(! $subscription, 404);

        $request->validate([
            'document' => ['required', 'file', 'max:10240', 'mimes:pdf,jpg,jpeg,png,doc,docx'],
            'title' => ['nullable', 'string', 'max:255'],
        ]);

        $this->subscriptions->storeDocument(
            $subscription,
            $request->user(),
            $request->file('document'),
            $request->input('title')
        );

        return back()->with('success', 'Documento enviado.');
    }

    public function downloadDocument(Condominium $condominium, CondominiumSubscriptionDocument $document)
    {
        $this->adminUser();
        abort_if($document->subscription?->condominium_id !== $condominium->id, 404);

        return Storage::disk('public')->download($document->file_path, $document->original_name);
    }

    public function destroyDocument(Condominium $condominium, CondominiumSubscriptionDocument $document)
    {
        $admin = $this->adminUser();
        abort_if($document->subscription?->condominium_id !== $condominium->id, 404);

        $this->subscriptions->deleteDocument($document, $admin);

        return back()->with('success', 'Documento removido.');
    }

    public function exportCharges(Request $request, Condominium $condominium)
    {
        $this->adminUser();

        $subscription = $this->contract($condominium);
        abort_if(! $subscription, 404, 'Nenhum contrato configurado.');

        $filters = $this->billing->filtersFromRequest($request);

        return $this->billing->exportCsv($subscription, $filters, 'cobrancas-saas');
    }

    public function updateComplimentary(Request $request, Condominium $condominium)
    {
        $admin = $this->adminUser();

        $validated = $request->validate([
            'saas_complimentary' => ['nullable', 'boolean'],
            'saas_complimentary_notes' => ['nullable', 'string', 'max:1000'],
        ]);

        $isComplimentary = $request->boolean('saas_complimentary');

        $condominium->update([
            'saas_complimentary' => $isComplimentary,
            'saas_complimentary_notes' => $validated['saas_complimentary_notes'] ?? null,
        ]);

        $admin->logActivity(
            'update_saas_complimentary',
            'platform',
            $isComplimentary
                ? "Marcou {$condominium->name} como uso gratuito da plataforma"
                : "Removeu uso gratuito da plataforma de {$condominium->name}",
            ['condominium_id' => $condominium->id]
        );

        return back()->with(
            'success',
            $isComplimentary
                ? 'Condomínio configurado para uso gratuito da plataforma.'
                : 'Uso gratuito removido. O condomínio passa a seguir as regras normais de assinatura.'
        );
    }
}
