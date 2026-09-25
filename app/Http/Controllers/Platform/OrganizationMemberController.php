<?php

namespace App\Http\Controllers\Platform;

use App\Http\Controllers\Controller;
use App\Mail\AdminPasswordResetLinkMail;
use App\Mail\ClientWelcomeMail;
use App\Models\Organization;
use App\Models\User;
use App\Services\OrganizationProvisioningService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class OrganizationMemberController extends Controller
{
    public function __construct(private OrganizationProvisioningService $provisioning) {}

    public function create(Organization $organization): View
    {
        $this->authorize('manageUsers', $organization);
        abort_unless($organization->isManagementCompany(), 404);

        return view('platform.organizations.members.form', [
            'organization' => $organization,
            'member' => null,
        ]);
    }

    public function store(Request $request, Organization $organization): RedirectResponse
    {
        $this->authorize('manageUsers', $organization);
        abort_unless($organization->isManagementCompany(), 404);

        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', 'unique:users,email'],
            'phone' => ['nullable', 'string', 'max:30'],
            'role' => ['required', Rule::in(Organization::organizationRoles())],
        ]);

        $user = User::query()->create([
            'name' => $data['name'],
            'email' => $data['email'],
            'phone' => $data['phone'] ?? null,
            'condominium_id' => null,
            'password' => Hash::make(Str::password(32)),
            'senha_temporaria' => true,
            'is_active' => true,
            'registration_status' => 'approved',
        ]);

        $this->provisioning->attachUser($organization, (int) $user->id, $data['role']);

        if (in_array($data['role'], [Organization::ROLE_OWNER, Organization::ROLE_ADMIN, Organization::ROLE_MANAGER], true)
            && !$user->hasRole('Síndico')) {
            $user->assignRole('Síndico');
        }
        $this->sendWelcome($user, $organization);

        return redirect()
            ->route('platform.organizations.show', $organization)
            ->with('success', 'Usuário da administradora criado e ativado. O e-mail para definir a senha foi enviado.');
    }

    public function edit(Organization $organization, User $user): View
    {
        $this->authorize('manageUsers', $organization);
        abort_unless($this->belongsToOrganization($organization, $user), 404);

        return view('platform.organizations.members.form', [
            'organization' => $organization,
            'member' => $user,
        ]);
    }

    public function update(Request $request, Organization $organization, User $user): RedirectResponse
    {
        $this->authorize('manageUsers', $organization);
        abort_unless($this->belongsToOrganization($organization, $user), 404);

        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', Rule::unique('users', 'email')->ignore($user->id)],
            'phone' => ['nullable', 'string', 'max:30'],
            'role' => ['required', Rule::in(Organization::organizationRoles())],
            'is_active' => ['nullable', 'boolean'],
        ]);

        $user->update([
            'name' => $data['name'],
            'email' => $data['email'],
            'phone' => $data['phone'] ?? null,
            'is_active' => $request->boolean('is_active'),
        ]);

        $organization->users()->updateExistingPivot($user->id, ['role' => $data['role']]);

        return redirect()
            ->route('platform.organizations.show', $organization)
            ->with('success', 'Usuário da administradora atualizado.');
    }

    public function activate(Organization $organization, User $user): RedirectResponse
    {
        $this->authorize('manageUsers', $organization);
        abort_unless($this->belongsToOrganization($organization, $user), 404);

        $user->update([
            'is_active' => true,
            'registration_status' => 'approved',
        ]);

        return back()->with('success', 'Usuário ativado.');
    }

    public function deactivate(Organization $organization, User $user): RedirectResponse
    {
        $this->authorize('manageUsers', $organization);
        abort_unless($this->belongsToOrganization($organization, $user), 404);
        if ($user->id === auth()->id()) {
            return back()->with('error', 'Você não pode desativar o próprio usuário.');
        }

        $user->update(['is_active' => false]);

        return back()->with('success', 'Usuário desativado.');
    }

    public function resetPassword(Organization $organization, User $user): RedirectResponse
    {
        $this->authorize('manageUsers', $organization);
        abort_unless($this->belongsToOrganization($organization, $user), 404);

        if (blank($user->email)) {
            return back()->with('error', 'Este usuário não possui e-mail cadastrado.');
        }

        $token = Password::createToken($user);
        $resetUrl = route('password.reset', [
            'token' => $token,
            'email' => $user->email,
        ]);

        Mail::to($user->email)->send(new AdminPasswordResetLinkMail(
            $user,
            $resetUrl,
            auth()->user()->name,
        ));

        return back()->with('success', "Link de redefinição de senha enviado para {$user->email}.");
    }

    public function destroy(Organization $organization, User $user): RedirectResponse
    {
        $this->authorize('manageUsers', $organization);
        abort_unless($this->belongsToOrganization($organization, $user), 404);

        if ($user->id === auth()->id()) {
            return back()->with('error', 'Você não pode excluir o próprio usuário.');
        }

        $name = $user->name;
        $organization->users()->detach($user->id);

        if ($user->condominium_id === null && !$user->organizations()->exists()) {
            $user->delete();
        }

        return redirect()
            ->route('platform.organizations.show', $organization)
            ->with('success', "Usuário {$name} removido da administradora.");
    }

    protected function belongsToOrganization(Organization $organization, User $user): bool
    {
        return $organization->users()->whereKey($user->id)->exists();
    }

    protected function sendWelcome(User $user, Organization $organization): void
    {
        $token = Password::createToken($user);

        Mail::to($user->email)->send(new ClientWelcomeMail(
            $user,
            route('password.reset', ['token' => $token, 'email' => $user->email]),
            $organization->displayName(),
            ClientWelcomeMail::AUDIENCE_ADMINISTRADORA,
            (int) config('auth.passwords.users.expire', 60),
        ));
    }
}
