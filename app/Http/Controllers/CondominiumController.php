<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreCondominiumRequest;
use App\Http\Requests\UpdateCondominiumRequest;
use App\Models\Condominium;
use App\Services\ActiveCondominiumService;
use App\Services\SubscriptionBillingService;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\Request;

class CondominiumController extends Controller
{
    use AuthorizesRequests;

    public function index(Request $request)
    {
        $this->authorize('viewAny', Condominium::class);

        $query = Condominium::query()
            ->with('subscription')
            ->withCount(['users', 'units']);

        if ($request->filled('search')) {
            $term = $request->string('search')->trim();
            $query->where(function ($q) use ($term) {
                $q->where('name', 'like', "%{$term}%")
                    ->orWhere('city', 'like', "%{$term}%")
                    ->orWhere('cnpj', 'like', "%{$term}%")
                    ->orWhere('registration_code', 'like', "%{$term}%");
            });
        }

        if ($request->filled('status')) {
            if ($request->status === 'active') {
                $query->where('is_active', true);
            } elseif ($request->status === 'inactive') {
                $query->where('is_active', false);
            }
        }

        $condominiums = $query
            ->orderByDesc('created_at')
            ->paginate(15)
            ->withQueryString();

        return view('condominiums.index', compact('condominiums'));
    }

    public function create()
    {
        $this->authorize('create', Condominium::class);

        return view('condominiums.create');
    }

    public function store(StoreCondominiumRequest $request)
    {
        $this->authorize('create', Condominium::class);

        $data = $this->normalizePayload($request->validated());
        $data['registration_code'] = Condominium::generateUniqueRegistrationCode();
        $data['is_active'] = $request->boolean('is_active', true);
        $data['marketplace_allow_agregados'] = $request->boolean('marketplace_allow_agregados');

        $condominium = Condominium::create($data);

        return redirect()
            ->route('condominiums.show', $condominium)
            ->with('success', "Condomínio \"{$condominium->name}\" cadastrado com sucesso.");
    }

    public function show(Request $request, Condominium $condominium)
    {
        $this->authorize('view', $condominium);

        $user = $request->user();
        if ($user && $user->isAdmin()) {
            app(ActiveCondominiumService::class)->setActiveCondominium($user, (int) $condominium->id);
        }

        $condominium->loadCount(['users', 'units', 'spaces']);
        $condominium->load('subscription');

        $billingReport = null;
        $billingFilters = [];
        $exportUrl = null;

        if ($user && $user->isAdmin() && $condominium->subscription) {
            $billingService = app(SubscriptionBillingService::class);
            $billingFilters = $billingService->filtersFromRequest($request);
            $billingReport = $billingService->getBillingReport($condominium->subscription, $billingFilters);
            $exportUrl = route('platform.subscriptions.charges.export', array_merge(
                ['condominium' => $condominium],
                $billingFilters
            ));
        }

        return view('condominiums.show', compact(
            'condominium',
            'billingReport',
            'billingFilters',
            'exportUrl',
        ));
    }

    public function edit(Condominium $condominium)
    {
        $this->authorize('update', $condominium);

        return view('condominiums.edit', compact('condominium'));
    }

    public function update(UpdateCondominiumRequest $request, Condominium $condominium)
    {
        $this->authorize('update', $condominium);

        $user = $request->user();
        $data = $this->normalizePayload($request->validated());
        $data['marketplace_allow_agregados'] = $request->boolean('marketplace_allow_agregados');

        if ($user->isAdmin()) {
            $data['is_active'] = $request->boolean('is_active');
        } else {
            unset($data['financial_mode'], $data['is_active']);
        }

        $condominium->update($data);

        return redirect()
            ->route('condominiums.show', $condominium)
            ->with('success', 'Condomínio atualizado com sucesso.');
    }

    public function destroy(Condominium $condominium)
    {
        $this->authorize('delete', $condominium);

        $name = $condominium->name;
        $condominium->delete();

        return redirect()
            ->route('condominiums.index')
            ->with('success', "Condomínio \"{$name}\" removido.");
    }

    public function toggleActive(Condominium $condominium)
    {
        $this->authorize('toggleActive', $condominium);

        $condominium->update(['is_active' => !$condominium->is_active]);

        $label = $condominium->is_active ? 'ativado' : 'desativado';

        return back()->with('success', "Condomínio {$label} com sucesso.");
    }

    public function regenerateRegistrationCode(Condominium $condominium)
    {
        $this->authorize('regenerateRegistrationCode', $condominium);

        $code = $condominium->regenerateRegistrationCode();

        return back()->with('success', "Novo código de autocadastro gerado: {$code}");
    }

    private function normalizePayload(array $data): array
    {
        if (!empty($data['state'])) {
            $data['state'] = strtoupper($data['state']);
        }

        if (!empty($data['cnpj'])) {
            $data['cnpj'] = preg_replace('/\D/', '', $data['cnpj']);
        }

        return $data;
    }
}
