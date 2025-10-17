<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Unit;
use App\Models\Condominium;
use App\Models\AgregadoPermission;
use App\Http\Requests\StoreUserRequest;
use App\Http\Requests\UpdateUserRequest;
use App\Http\Requests\UpdateProfileRequest;
use App\Services\FileUploadService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\Rule;
use Spatie\Permission\Models\Role;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Support\Facades\Auth;

class UserController extends Controller
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
        $this->authorize('viewAny', User::class);
        
        /** @var \App\Models\User $authUser */
        $authUser = $request->user();
        $query = User::with(['unit', 'roles', 'condominium'])
            ->byCondominium($authUser->condominium_id);

        // Filtros
        if ($request->filled('search')) {
            $query->search($request->search);
        }

        if ($request->filled('role')) {
            $query->whereHas('roles', function($q) use ($request) {
                $q->where('name', $request->role);
            });
        }

        if ($request->filled('unit_id')) {
            $query->where('unit_id', $request->unit_id);
        }

        if ($request->filled('is_active')) {
            $query->where('is_active', $request->boolean('is_active'));
        }

        if ($request->filled('possui_dividas')) {
            $query->where('possui_dividas', $request->boolean('possui_dividas'));
        }

        if ($request->filled('sort')) {
            $direction = $request->get('direction', 'asc');
            $query->orderBy($request->sort, $direction);
        } else {
            $query->orderBy('name');
        }

        $users = $query->paginate(20)->withQueryString();

        // Para filtros
        $roles = Role::all();
        $units = Unit::active()
            ->byCondominium($authUser->condominium_id)
            ->orderBy('number')
            ->get();

        // Debug: Log da query SQL (remover em produção)
        if (config('app.debug')) {
            Log::info('Users Query SQL: ' . $query->toSql());
            Log::info('Users Query Bindings: ' . json_encode($query->getBindings()));
        }

        return view('users.index', compact('users', 'roles', 'units'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $this->authorize('create', User::class);
        
        $authUser = $this->authUser();
        $condominiums = Condominium::active()->get();
        $units = Unit::active()
            ->byCondominium($authUser->condominium_id)
            ->orderBy('number')
            ->get();
        $roles = Role::all();
        
        // Moradores para vincular agregados
        $moradores = User::active()
            ->byCondominium($authUser->condominium_id)
            ->moradores()
            ->orderBy('name')
            ->get();

        // Permissões disponíveis para agregados
        $agregadoPermissions = AgregadoPermission::getAvailablePermissions();

        return view('users.create', compact('condominiums', 'units', 'roles', 'moradores', 'agregadoPermissions'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreUserRequest $request)
    {
        $this->authorize('create', User::class);
        
        $data = $request->validated();

        // Senha padrão
        $data['password'] = Hash::make('12345678');
        $data['senha_temporaria'] = true;

        // Upload de foto se fornecida
        if ($request->hasFile('photo')) {
            $data['photo'] = $this->fileUploadService->uploadUserPhoto($request->file('photo'));
        }

        // Extrai roles antes de criar o usuário
        $roles = $data['roles'] ?? [];
        unset($data['roles']);

        $user = User::create($data);

        // Atribui roles
        if (!empty($roles)) {
            $user->syncRoles($roles);
        }

        // Processa permissões de agregado se aplicável
        if (in_array('Agregado', $roles) && $request->has('agregado_permissions')) {
            $this->processAgregadoPermissions($user, $request->input('agregado_permissions', []));
        }

        // Gera QR Code
        $user->generateQRCode();

        // Log da atividade
        $this->authUser()->logActivity(
            'create',
            'users',
            "Criou o usuário {$user->name}",
            ['user_id' => $user->id, 'roles' => $roles]
        );

        return redirect()->route('users.show', $user)
            ->with('success', 'Usuário cadastrado com sucesso! Senha padrão: 12345678');
    }

    /**
     * Display the specified resource.
     */
    public function show(User $user)
    {
        $this->authorize('view', $user);
        
        $user->load([
            'unit.charges',
            'condominium',
            'roles',
            'moradorVinculado',
            'agregados',
            'reservations',
            'pets',
            'agregadoPermissions.grantedBy'
        ]);

        return view('users.show', compact('user'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(User $user)
    {
        $this->authorize('update', $user);
        
        // Verificar se o usuário está editando a si mesmo
        $isEditingSelf = Auth::user()->id === $user->id;
        
        // Verificar se o usuário tem permissão para gerenciar usuários (Admin/Síndico)
        $userRoles = Auth::user()->roles->pluck('name')->toArray();
        $canManageUsers = in_array('Administrador', $userRoles) || in_array('Síndico', $userRoles);
        
        if ($isEditingSelf && !$canManageUsers) {
            // Usuário comum editando a si mesmo - usar view simplificada
            return view('users.profile-edit', compact('user'));
        } else {
            // Admin/Síndico editando qualquer usuário - usar view completa
            $condominiums = Condominium::active()->get();
            $units = Unit::active()
                ->byCondominium($user->condominium_id)
                ->orderBy('number')
                ->get();
            $roles = Role::all();
            
            // Moradores para vincular agregados
            $moradores = User::active()
                ->byCondominium($user->condominium_id)
                ->moradores()
                ->where('id', '!=', $user->id)
                ->orderBy('name')
                ->get();

            // Permissões disponíveis para agregados
            $agregadoPermissions = AgregadoPermission::getAvailablePermissions();

            return view('users.edit', compact('user', 'condominiums', 'units', 'roles', 'moradores', 'agregadoPermissions'));
        }
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, User $user)
    {
        $this->authorize('update', $user);
        
        // Verificar se o usuário está editando a si mesmo
        $isEditingSelf = Auth::user()->id === $user->id;
        
        // Verificar se o usuário tem permissão para gerenciar usuários (Admin/Síndico)
        $userRoles = Auth::user()->roles->pluck('name')->toArray();
        $canManageUsers = in_array('Administrador', $userRoles) || in_array('Síndico', $userRoles);
        
        if ($isEditingSelf && !$canManageUsers) {
            // Usuário comum editando a si mesmo - usar request simplificado
            $validatedData = $request->validate([
                'name' => ['required', 'string', 'max:255'],
                'email' => ['required', 'email', Rule::unique('users')->ignore($user->id)],
                'phone' => ['nullable', 'string', 'max:20'],
                'data_nascimento' => ['nullable', 'date', 'before:today'],
                'telefone_residencial' => ['nullable', 'string', 'max:20'],
                'telefone_celular' => ['nullable', 'string', 'max:20'],
                'telefone_comercial' => ['nullable', 'string', 'max:20'],
                'local_trabalho' => ['nullable', 'string', 'max:255'],
                'contato_comercial' => ['nullable', 'string', 'max:20'],
                'photo' => ['nullable', 'image', 'max:2048'],
            ]);
            
            // Upload de nova foto se fornecida
            if ($request->hasFile('photo')) {
                // Deleta foto antiga
                if ($user->photo) {
                    $this->fileUploadService->deletePhoto($user->photo);
                }
                
                $validatedData['photo'] = $this->fileUploadService->uploadUserPhoto(
                    $request->file('photo'),
                    $user->id
                );
            }
            
            $user->update($validatedData);
            
            // Log da atividade
            $this->authUser()->logActivity(
                'update',
                'profile',
                "Atualizou seu próprio perfil",
                ['user_id' => $user->id]
            );
            
            return redirect()->route('users.edit', $user)
                ->with('success', 'Perfil atualizado com sucesso!');
                
        } else {
            // Admin/Síndico editando qualquer usuário - usar request completo
            $updateUserRequest = new UpdateUserRequest();
            $updateUserRequest->setContainer(app());
            $updateUserRequest->setRedirector(app('redirect'));
            $updateUserRequest->setRequest($request);
            
            $data = $updateUserRequest->validated();

            // Upload de nova foto se fornecida
            if ($request->hasFile('photo')) {
                // Deleta foto antiga
                if ($user->photo) {
                    $this->fileUploadService->deletePhoto($user->photo);
                }
                
                $data['photo'] = $this->fileUploadService->uploadUserPhoto(
                    $request->file('photo'),
                    $user->id
                );
            }

            // Atualiza senha se fornecida
            if ($request->filled('password')) {
                $data['password'] = Hash::make($request->password);
                $data['senha_temporaria'] = false;
            } else {
                // Remove senha do array se não foi fornecida
                unset($data['password']);
            }

            // Extrai roles antes de atualizar
            $roles = $data['roles'] ?? null;
            unset($data['roles']);

            $user->update($data);

            // Atualiza roles se fornecidas
            if ($roles !== null) {
                $user->syncRoles($roles);
                
                // Processa permissões de agregado se aplicável
                if (in_array('Agregado', $roles) && $request->has('agregado_permissions')) {
                    $this->processAgregadoPermissions($user, $request->input('agregado_permissions', []));
                } elseif (!in_array('Agregado', $roles)) {
                    // Remove todas as permissões de agregado se não for mais agregado
                    $user->agregadoPermissions()->delete();
                }
            }

            // Log da atividade
            $this->authUser()->logActivity(
                'update',
                'users',
                "Atualizou o usuário {$user->name}",
                ['user_id' => $user->id]
            );

            return redirect()->route('users.show', $user)
                ->with('success', 'Usuário atualizado com sucesso!');
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(User $user)
    {
        $this->authorize('delete', $user);
        
        $name = $user->name;

        // Deleta foto se existir
        if ($user->photo) {
            $this->fileUploadService->deletePhoto($user->photo);
        }

        $user->delete();

        // Log da atividade
        $this->authUser()->logActivity(
            'delete',
            'users',
            "Excluiu o usuário {$name}",
            ['user_name' => $name]
        );

        return redirect()->route('users.index')
            ->with('success', 'Usuário excluído com sucesso!');
    }

    /**
     * Busca usuários via AJAX
     */
    public function search(Request $request)
    {
        $term = $request->get('term', '');
        $exclude = $request->get('exclude', []);
        /** @var \App\Models\User $authUser */
        $authUser = $request->user();

        $query = User::active()
            ->byCondominium($authUser->condominium_id)
            ->where(function($q) use ($term) {
                $q->where('name', 'like', "%{$term}%")
                  ->orWhere('cpf', 'like', "%{$term}%")
                  ->orWhere('email', 'like', "%{$term}%");
            });

        if (!empty($exclude)) {
            $query->whereNotIn('id', $exclude);
        }

        $users = $query->limit(10)
            ->get(['id', 'name', 'cpf', 'email', 'unit_id'])
            ->map(function($user) {
                return [
                    'id' => $user->id,
                    'name' => $user->name,
                    'cpf' => $user->cpf,
                    'email' => $user->email,
                    'unit' => $user->unit?->full_identifier,
                    'text' => "{$user->name} - {$user->cpf}",
                ];
            });

        return response()->json($users);
    }

    /**
     * Reseta senha do usuário para padrão
     */
    public function resetPassword(User $user)
    {
        $this->authorize('update', $user);

        $user->update([
            'password' => Hash::make('12345678'),
            'senha_temporaria' => true,
        ]);

        // Log da atividade
        $this->authUser()->logActivity(
            'reset_password',
            'users',
            "Resetou a senha do usuário {$user->name}",
            ['user_id' => $user->id]
        );

        return back()->with('success', 'Senha resetada para: 12345678');
    }

    /**
     * Processa as permissões personalizadas de agregado
     */
    private function processAgregadoPermissions(User $user, array $permissions): void
    {
        $grantedById = $this->authUser()->id;
        $availablePermissions = AgregadoPermission::getAvailablePermissions();
        
        // Remove todas as permissões atuais
        $user->agregadoPermissions()->delete();
        
        // Adiciona as novas permissões selecionadas
        foreach ($permissions as $permission) {
            $module = $permission['module'] ?? null;
            $level = $permission['level'] ?? 'view';
            
            if ($module && array_key_exists($module, $availablePermissions)) {
                AgregadoPermission::grantPermission(
                    $user->id,
                    $module,
                    $level,
                    $grantedById,
                    "Permissão concedida por {$this->authUser()->name}"
                );
            }
        }
    }
}

