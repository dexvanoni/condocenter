<?php

namespace App\Http\Controllers;

use App\Models\Unit;
use App\Models\Condominium;
use App\Models\User;
use App\Http\Requests\StoreUnitRequest;
use App\Http\Requests\UpdateUnitRequest;
use App\Services\FileUploadService;
use Illuminate\Http\Request;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Support\Facades\Auth;

class UnitController extends Controller
{
    use AuthorizesRequests;

    protected FileUploadService $fileUploadService;

    public function __construct(FileUploadService $fileUploadService)
    {
        $this->fileUploadService = $fileUploadService;
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
        $query = Unit::with(['condominium', 'users'])
            ->byCondominium($user->condominium_id);

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

        $units = $query->paginate(20);

        return view('units.index', compact('units'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $this->authorize('create', Unit::class);
        
        $authUser = $this->authUser();
        $condominiums = Condominium::active()->get();
        $users = User::active()
            ->byCondominium($authUser->condominium_id)
            ->orderBy('name')
            ->get();

        return view('units.create', compact('condominiums', 'users'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreUnitRequest $request)
    {
        $this->authorize('create', Unit::class);
        
        $data = $request->validated();

        // Upload de foto se fornecida
        if ($request->hasFile('foto')) {
            $data['foto'] = $this->fileUploadService->uploadUnitPhoto($request->file('foto'));
        }

        $unit = Unit::create($data);

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
        
        $condominiums = Condominium::active()->get();
        $users = User::active()
            ->byCondominium($unit->condominium_id)
            ->orderBy('name')
            ->get();

        return view('units.edit', compact('unit', 'condominiums', 'users'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateUnitRequest $request, Unit $unit)
    {
        $this->authorize('update', $unit);
        
        $data = $request->validated();

        // Upload de nova foto se fornecida
        if ($request->hasFile('foto')) {
            // Deleta foto antiga
            if ($unit->foto) {
                $this->fileUploadService->deletePhoto($unit->foto);
            }
            
            $data['foto'] = $this->fileUploadService->uploadUnitPhoto(
                $request->file('foto'),
                $unit->id
            );
        }

        $unit->update($data);

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

        // Deleta foto se existir
        if ($unit->foto) {
            $this->fileUploadService->deletePhoto($unit->foto);
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
        $term = $request->get('term', '');
        /** @var \App\Models\User $user */
        $user = $request->user();

        $users = User::active()
            ->byCondominium($user->condominium_id)
            ->where(function($q) use ($term) {
                $q->where('name', 'like', "%{$term}%")
                  ->orWhere('cpf', 'like', "%{$term}%");
            })
            ->whereHas('roles', function($q) {
                $q->whereIn('name', ['Morador', 'Agregado']);
            })
            ->limit(10)
            ->get(['id', 'name', 'cpf', 'unit_id']);

        return response()->json($users);
    }
}

