<?php

namespace App\Http\Controllers\Platform;

use App\Http\Controllers\Controller;
use App\Http\Requests\ExtendCondominiumSubscriptionRequest;
use App\Http\Requests\StoreCondominiumSubscriptionRequest;
use App\Models\Condominium;
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
            ->where('is_active', true)
            ->orderBy('sort_order')
            ->orderBy('name')
            ->get();

        $subscription = $condominium->subscription;
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

        return view('platform.subscriptions.edit', compact(
            'condominium',
            'subscription',
            'syndics',
            'plans',
            'billingReport',
            'billingFilters',
            'exportUrl',
        ));
    }

    public function store(StoreCondominiumSubscriptionRequest $request, Condominium $condominium)
    {
        $this->subscriptions->upsert($condominium, $request->validated(), $request->user());

        return redirect()
            ->route('platform.subscriptions.edit', $condominium)
            ->with('success', 'Contrato de assinatura salvo.');
    }

    public function activate(Condominium $condominium)
    {
        $admin = $this->adminUser();
        $subscription = $condominium->subscription;

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
        $subscription = $condominium->subscription;
        abort_if(! $subscription, 404);

        $this->subscriptions->suspend($subscription, $admin, $request->input('notes'));

        return back()->with('success', 'Assinatura suspensa.');
    }

    public function cancel(Request $request, Condominium $condominium)
    {
        $admin = $this->adminUser();
        $subscription = $condominium->subscription;
        abort_if(! $subscription, 404);

        $this->subscriptions->cancel($subscription, $admin, $request->input('notes'));

        return back()->with('success', 'Assinatura cancelada.');
    }

    public function extend(ExtendCondominiumSubscriptionRequest $request, Condominium $condominium)
    {
        $subscription = $condominium->subscription;
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
        $subscription = $condominium->subscription;
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
        $subscription = $condominium->subscription;
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

        $subscription = $condominium->subscription;
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
