<?php

namespace App\Http\Controllers;

use App\Models\ProfileSelection;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ProfileSelectorController extends Controller
{
    /**
     * Get authenticated user with proper type hint
     * @return \App\Models\User
     */
    private function authUser(): \App\Models\User
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();
        return $user;
    }

    /**
     * Exibe tela de seleção de perfil
     */
    public function select()
    {
        $user = $this->authUser();

        // Se usuário tem apenas um perfil, redireciona direto
        if (!$user->hasMultipleRoles()) {
            $role = $user->roles->first();
            
            if ($role) {
                return $this->setProfile($role->name);
            }
        }

        $roles = $user->roles;

        return view('auth.select-profile', compact('roles'));
    }

    /**
     * Define o perfil ativo
     */
    public function set(Request $request)
    {
        $request->validate([
            'role' => 'required|string',
        ]);

        return $this->setProfile($request->role);
    }

    /**
     * Lógica para definir perfil
     */
    protected function setProfile(string $roleName)
    {
        $user = $this->authUser();

        // Valida se o usuário tem esse perfil
        if (!$user->hasRole($roleName)) {
            return redirect()->route('profile.select')
                ->with('error', 'Perfil inválido.');
        }

        // Define na sessão
        session(['active_role' => $roleName]);

        // Registra seleção no banco
        ProfileSelection::create([
            'user_id' => $user->id,
            'role_name' => $roleName,
            'selected_at' => now(),
            'ip_address' => request()->ip(),
        ]);

        // Log da atividade
        $user->logActivity(
            'select_profile',
            'authentication',
            "Selecionou o perfil: {$roleName}",
            ['role' => $roleName]
        );

        return redirect()->route('dashboard')
            ->with('success', "Perfil {$roleName} ativado com sucesso!");
    }

    /**
     * Troca de perfil (para usuários já logados)
     */
    public function switch(Request $request)
    {
        $request->validate([
            'role' => 'required|string',
        ]);

        $user = $this->authUser();
        $roleName = $request->role;

        // Valida se o usuário tem esse perfil
        if (!$user->hasRole($roleName)) {
            return response()->json([
                'success' => false,
                'message' => 'Perfil inválido.',
            ], 403);
        }

        // Define na sessão
        session(['active_role' => $roleName]);

        // Registra seleção no banco
        ProfileSelection::create([
            'user_id' => $user->id,
            'role_name' => $roleName,
            'selected_at' => now(),
            'ip_address' => request()->ip(),
        ]);

        // Log da atividade
        $user->logActivity(
            'switch_profile',
            'authentication',
            "Trocou para o perfil: {$roleName}",
            ['role' => $roleName]
        );

        return response()->json([
            'success' => true,
            'message' => "Perfil alterado para {$roleName}",
            'role' => $roleName,
        ]);
    }

    /**
     * Retorna o perfil ativo atual (AJAX)
     */
    public function current()
    {
        $activeRole = session('active_role');
        $user = $this->authUser();

        return response()->json([
            'active_role' => $activeRole,
            'all_roles' => $user->roles->pluck('name')->toArray(),
            'has_multiple' => $user->hasMultipleRoles(),
        ]);
    }
}

