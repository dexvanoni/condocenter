<?php

namespace App\Http\Controllers\Organization;

use App\Http\Controllers\Controller;
use App\Models\Condominium;
use App\Models\Organization;
use App\Services\ActiveCondominiumService;
use App\Services\CondominiumSyndicService;
use App\Services\OrganizationQuotaService;
use App\Support\CondominiumModules;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class OrganizationCondominiumController extends Controller
{
    public function __construct(
        private ActiveCondominiumService $activeCondominium,
        private OrganizationQuotaService $quota,
        private CondominiumSyndicService $syndicService,
    ) {}

    public function create(Request $request): View
    {
        $organization = $this->organization($request);
        $this->authorize('manageCondominiums', $organization);
        $snapshot = $this->quota->snapshot($organization);

        return view('organization.condominiums.create', [
            'organization' => $organization,
            'snapshot' => $snapshot,
            'canCreate' => $this->quota->canCreateCondominium($organization),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $organization = $this->organization($request);
        $this->authorize('manageCondominiums', $organization);

        if (!$this->quota->canCreateCondominium($organization)) {
            return back()
                ->withInput()
                ->with('error', 'Limite de condomínios do contrato atingido.');
        }

        $remaining = $this->quota->remainingUnitSlots($organization);

        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'cnpj' => ['nullable', 'string', 'max:18', 'unique:condominiums,cnpj'],
            'address' => ['required', 'string', 'max:255'],
            'neighborhood' => ['nullable', 'string', 'max:100'],
            'city' => ['required', 'string', 'max:100'],
            'state' => ['required', 'string', 'size:2'],
            'zip_code' => ['required', 'string', 'max:10'],
            'phone' => ['nullable', 'string', 'max:20'],
            'email' => ['nullable', 'email', 'max:255'],
            'description' => ['nullable', 'string', 'max:2000'],
            'financial_mode' => ['required', Rule::in(['full', 'simplified'])],
            'units_limit' => ['required', 'integer', 'min:1', 'max:'.($remaining ?? 50000)],
            'syndic_name' => ['required', 'string', 'max:255'],
            'syndic_email' => ['required', 'email', 'max:255'],
            'syndic_phone' => ['nullable', 'string', 'max:30'],
        ]);

        if (!$this->quota->acceptsCondominiumUnitLimit($organization, (int) $data['units_limit'])) {
            return back()
                ->withInput()
                ->withErrors(['units_limit' => 'A cota de unidades ultrapassa o limite do contrato da administradora.']);
        }

        $condominium = Condominium::query()->create([
            ...collect($data)->except(['syndic_name', 'syndic_email', 'syndic_phone'])->all(),
            'state' => strtoupper($data['state']),
            'organization_id' => $organization->id,
            'registration_code' => Condominium::generateUniqueRegistrationCode(),
            'is_active' => true,
            'enabled_modules' => CondominiumModules::keys(),
        ]);

        $this->syndicService->attachSyndic(
            $condominium,
            $data['syndic_name'],
            $data['syndic_email'],
            $data['syndic_phone'] ?? null,
        );

        return redirect()
            ->route('organization.dashboard')
            ->with('success', "Condomínio \"{$condominium->name}\" criado. O síndico receberá o e-mail para definir a senha. Você continua com acesso de gestão.");
    }

    public function assignSyndic(Request $request, Condominium $condominium): RedirectResponse
    {
        $organization = $this->organization($request);
        $this->authorize('manageCondominiums', $organization);
        abort_unless((int) $condominium->organization_id === (int) $organization->id, 404);

        if ($this->syndicService->primarySyndic($condominium)) {
            return back()->with('error', 'Este condomínio já tem um síndico vinculado.');
        }

        $data = $request->validate([
            'syndic_name' => ['required', 'string', 'max:255'],
            'syndic_email' => ['required', 'email', 'max:255'],
            'syndic_phone' => ['nullable', 'string', 'max:30'],
        ]);

        try {
            $syndic = $this->syndicService->attachSyndic(
                $condominium,
                $data['syndic_name'],
                $data['syndic_email'],
                $data['syndic_phone'] ?? null,
            );
        } catch (ValidationException $e) {
            return back()->withErrors($e->errors())->withInput();
        }

        $message = $syndic->wasRecentlyCreated
            ? 'Síndico vinculado. O acesso foi enviado por e-mail.'
            : "Síndico {$syndic->name} vinculado a \"{$condominium->name}\".";

        return redirect()
            ->route('organization.dashboard')
            ->with('success', $message);
    }

    protected function organization(Request $request): Organization
    {
        $user = $request->user();
        abort_unless($user, 403);

        $organizationId = $this->activeCondominium->getActiveOrganizationId($user)
            ?: $this->activeCondominium->accessibleOrganizationIds($user)->first();

        abort_unless($organizationId, 403, 'Nenhuma administradora vinculada à sua conta.');

        $organization = Organization::query()->findOrFail($organizationId);
        abort_unless($organization->isManagementCompany(), 404);

        return $organization;
    }
}
