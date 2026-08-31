<?php

namespace App\Http\Controllers;

use App\Models\Unit;
use App\Models\User;
use App\Services\ActiveCondominiumService;
use App\Http\Requests\StoreUnitRequest;
use App\Http\Requests\UpdateUnitRequest;
use Illuminate\Http\Request;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class UnitController extends Controller
{
    use AuthorizesRequests;

    public function __construct(
        private ActiveCondominiumService $activeCondominiumService
    ) {}

    private function activeCondominiumId(): int
    {
        $id = $this->activeCondominiumService->getActiveCondominiumId($this->authUser());

        if (!$id) {
            abort(403, 'Selecione um condomínio para continuar.');
        }

        return $id;
    }

    /**
     * Get authenticated user with proper type hint
     * @return User
     */
    private function authUser(): User
    {
        /** @var User $user */
        $user = Auth::user();
        return $user;
    }

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $this->authorize('viewAny', Unit::class);
        
        /** @var \App\Models\User $user */
        $user = $request->user();
        $query = Unit::with(['condominium', 'users', 'morador'])
            ->byCondominium($this->activeCondominiumId());

        // Filtros
        if ($request->filled('search')) {
            $query->search($request->search);
        }

        if ($request->filled('type')) {
            $query->where('type', $request->type);
        }

        if ($request->filled('situacao')) {
            $query->where('situacao', $request->situacao);
        }

        if ($request->has('possui_dividas')) {
            $query->where('possui_dividas', $request->boolean('possui_dividas'));
        }

        if ($request->filled('sort')) {
            $direction = $request->get('direction', 'asc');
            $query->orderBy($request->sort, $direction);
        } else {
            $query->orderBy('number');
        }

        $units = $query->paginate(20)->withQueryString();

        return view('units.index', compact('units'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $this->authorize('create', Unit::class);

        $activeCondominium = $this->activeCondominiumService->getActiveCondominium($this->authUser());

        return view('units.create', compact('activeCondominium'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreUnitRequest $request)
    {
        $this->authorize('create', Unit::class);

        $validated = $request->validated();
        $moradorId = !empty($validated['morador_id']) ? (int) $validated['morador_id'] : null;
        unset($validated['morador_id']);

        $validated['condominium_id'] = $this->activeCondominiumId();

        $unit = Unit::create($this->sanitizeUnitData($validated));
        $this->syncMorador($moradorId, $unit);

        // Log da atividade
        $this->authUser()->logActivity(
            'create',
            'units',
            "Criou a unidade {$unit->full_identifier}",
            ['unit_id' => $unit->id]
        );

        return redirect()->route('units.show', $unit)
            ->with('success', 'Unidade cadastrada com sucesso!');
    }

    /**
     * Display the specified resource.
     */
    public function show(Unit $unit)
    {
        $this->authorize('view', $unit);
        
        $unit->load(['condominium', 'users.roles', 'charges', 'reservations']);

        return view('units.show', compact('unit'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Unit $unit)
    {
        $this->authorize('update', $unit);
        
        $unit->load(['condominium', 'morador']);
        $selectedMorador = $unit->morador;
        $activeCondominium = $this->activeCondominiumService->getActiveCondominium($this->authUser());

        return view('units.edit', compact('unit', 'selectedMorador', 'activeCondominium'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateUnitRequest $request, Unit $unit)
    {
        $this->authorize('update', $unit);

        $validated = $request->validated();
        $moradorId = !empty($validated['morador_id']) ? (int) $validated['morador_id'] : null;
        unset($validated['morador_id'], $validated['condominium_id']);

        $unit->update($this->sanitizeUnitData($validated));
        $this->syncMorador($moradorId, $unit);

        // Log da atividade
        $this->authUser()->logActivity(
            'update',
            'units',
            "Atualizou a unidade {$unit->full_identifier}",
            ['unit_id' => $unit->id]
        );

        return redirect()->route('units.show', $unit)
            ->with('success', 'Unidade atualizada com sucesso!');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Unit $unit)
    {
        $this->authorize('delete', $unit);
        
        $identifier = $unit->full_identifier;

        if ($unit->foto) {
            Storage::disk('public')->delete($unit->foto);
        }

        $unit->delete();

        // Log da atividade
        $this->authUser()->logActivity(
            'delete',
            'units',
            "Excluiu a unidade {$identifier}",
            ['unit_number' => $unit->number]
        );

        return redirect()->route('units.index')
            ->with('success', 'Unidade excluída com sucesso!');
    }

    /**
     * Busca usuários para vincular à unidade (AJAX)
     */
    public function searchUsers(Request $request)
    {
        $this->authorize('viewAny', Unit::class);

        $term = trim((string) $request->get('term', ''));
        /** @var \App\Models\User $user */
        $user = $request->user();

        if (strlen($term) < 2) {
            return response()->json([]);
        }

        $users = User::active()
            ->byCondominium($this->activeCondominiumId())
            ->with('unit:id,number,block')
            ->where(function ($q) use ($term) {
                $q->where('name', 'like', "%{$term}%")
                    ->orWhere('cpf', 'like', "%{$term}%")
                    ->orWhere('email', 'like', "%{$term}%");
            })
            ->whereHas('roles', function ($q) {
                $q->where('name', 'Morador');
            })
            ->orderBy('name')
            ->limit(10)
            ->get(['id', 'name', 'cpf', 'email', 'unit_id'])
            ->map(function (User $morador) {
                return [
                    'id' => $morador->id,
                    'name' => $morador->name,
                    'cpf' => $morador->cpf,
                    'email' => $morador->email,
                    'unit' => $morador->unit?->full_identifier,
                    'text' => trim($morador->name . ($morador->cpf ? ' - ' . $morador->cpf : '')),
                ];
            });

        return response()->json($users);
    }

    private function syncMorador(?int $moradorId, Unit $unit): void
    {
        User::query()
            ->where('unit_id', $unit->id)
            ->whereHas('roles', fn ($q) => $q->where('name', 'Morador'))
            ->when($moradorId, fn ($q) => $q->where('id', '!=', $moradorId))
            ->update(['unit_id' => null]);

        if (!$moradorId) {
            return;
        }

        User::query()
            ->where('id', $moradorId)
            ->where('condominium_id', $unit->condominium_id)
            ->whereHas('roles', fn ($q) => $q->where('name', 'Morador'))
            ->update(['unit_id' => $unit->id]);
    }

    private function sanitizeUnitData(array $data): array
    {
        return array_merge($data, [
            'cep' => null,
            'logradouro' => null,
            'numero' => null,
            'complemento' => null,
            'bairro' => null,
            'cidade' => null,
            'estado' => null,
            'area' => null,
            'num_quartos' => null,
            'num_banheiros' => null,
            'notes' => null,
            'foto' => null,
        ]);
    }
}
