<?php

namespace App\Http\Controllers\Platform;

use App\Http\Controllers\Controller;
use App\Models\Organization;
use App\Services\Finance\FinanceAiQuotaService;
use App\Services\OrganizationProvisioningService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class OrganizationController extends Controller
{
    public function index(Request $request): View
    {
        abort_unless(auth()->user()?->isAdmin(), 403);

        $organizations = Organization::query()
            ->withCount(['condominiums', 'users'])
            ->when($request->filled('type'), fn ($q) => $q->where('type', $request->string('type')))
            ->when($request->filled('status'), fn ($q) => $q->where('status', $request->string('status')))
            ->when($request->filled('search'), function ($q) use ($request) {
                $term = '%'.$request->string('search').'%';
                $q->where(function ($inner) use ($term) {
                    $inner->where('legal_name', 'like', $term)
                        ->orWhere('trade_name', 'like', $term)
                        ->orWhere('document', 'like', $term)
                        ->orWhere('email', 'like', $term)
                        ->orWhere('city', 'like', $term);
                });
            })
            ->orderBy('legal_name')
            ->paginate(20)
            ->withQueryString();

        return view('platform.organizations.index', compact('organizations'));
    }

    public function show(Organization $organization, FinanceAiQuotaService $quotaService): View
    {
        abort_unless(auth()->user()?->isAdmin(), 403);

        $organization->load([
            'condominiums' => fn ($q) => $q->orderBy('name')->withCount(['units', 'users'])->with('subscription.plan'),
            'users',
            'subscription.plan',
        ]);

        $llmQuota = $quotaService->quotaForOrganization($organization);

        return view('platform.organizations.show', compact('organization', 'llmQuota'));
    }

    public function create(): RedirectResponse
    {
        abort_unless(auth()->user()?->isAdmin(), 403);

        return redirect()->route('platform.clients.create-management');
    }

    public function store(Request $request, OrganizationProvisioningService $provisioning): RedirectResponse
    {
        abort_unless(auth()->user()?->isAdmin(), 403);

        $organization = $provisioning->createManagementCompany($this->validatedProfile($request));

        return redirect()
            ->route('platform.organizations.show', $organization)
            ->with('success', 'Administradora criada com sucesso.');
    }

    public function edit(Organization $organization): View
    {
        abort_unless(auth()->user()?->isAdmin(), 403);

        $condominium = null;
        $syndics = collect();

        if ($organization->isDirectCondominium()) {
            $condominium = $organization->condominiums()->first();
            if ($condominium) {
                $syndics = app(\App\Services\CondominiumSyndicService::class)->syndicsFor($condominium);
            }
        }

        return view('platform.organizations.edit', compact('organization', 'condominium', 'syndics'));
    }

    public function update(Request $request, Organization $organization): RedirectResponse
    {
        abort_unless(auth()->user()?->isAdmin(), 403);

        $organization->update($this->validatedProfile($request));

        return redirect()
            ->route('platform.organizations.show', $organization)
            ->with('success', 'Dados da organização atualizados.');
    }

    public function updateStatus(Request $request, Organization $organization): RedirectResponse
    {
        abort_unless(auth()->user()?->isAdmin(), 403);

        $data = $request->validate([
            'status' => ['required', Rule::in([
                Organization::STATUS_ACTIVE,
                Organization::STATUS_SUSPENDED,
                Organization::STATUS_BLOCKED,
            ])],
        ]);

        $organization->update(['status' => $data['status']]);

        return back()->with('success', 'Status da organização atualizado.');
    }

    public function updateLlmLimit(Request $request, Organization $organization): RedirectResponse
    {
        abort_unless(auth()->user()?->isAdmin(), 403);

        $data = $request->validate([
            'llm_monthly_limit' => ['nullable', 'integer', 'min:0', 'max:100000'],
        ]);

        $organization->update([
            'llm_monthly_limit' => $data['llm_monthly_limit'] ?? null,
        ]);

        return back()->with('success', 'Limite mensal do Consultor Financeiro atualizado.');
    }

    /**
     * @return array<string, mixed>
     */
    private function validatedProfile(Request $request): array
    {
        $data = $request->validate([
            'legal_name' => ['required', 'string', 'max:255'],
            'trade_name' => ['nullable', 'string', 'max:255'],
            'document' => ['nullable', 'string', 'max:18'],
            'email' => ['nullable', 'email', 'max:255'],
            'phone' => ['nullable', 'string', 'max:30'],
            'address' => ['nullable', 'string', 'max:255'],
            'neighborhood' => ['nullable', 'string', 'max:120'],
            'city' => ['nullable', 'string', 'max:120'],
            'state' => ['nullable', 'string', 'size:2'],
            'zip_code' => ['nullable', 'string', 'max:12'],
            'notes' => ['nullable', 'string', 'max:5000'],
        ]);

        if (! empty($data['state'])) {
            $data['state'] = strtoupper($data['state']);
        }

        return $data;
    }
}
