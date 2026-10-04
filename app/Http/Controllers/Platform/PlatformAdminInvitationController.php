<?php

namespace App\Http\Controllers\Platform;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Services\PlatformAdminInvitationService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rules\Password;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class PlatformAdminInvitationController extends Controller
{
    public function __construct(private PlatformAdminInvitationService $invitations) {}

    public function search(Request $request): JsonResponse
    {
        abort_unless($request->user()?->isAdmin(), 403);

        $term = (string) $request->query('term', '');

        return response()->json($this->invitations->searchUsers($term));
    }

    public function invite(Request $request): RedirectResponse
    {
        abort_unless($request->user()?->isAdmin(), 403);

        $data = $request->validate([
            'email' => ['required_without:user_id', 'nullable', 'email', 'max:255'],
            'user_id' => ['required_without:email', 'nullable', 'integer', 'exists:users,id'],
        ], [
            'email.required_without' => 'Informe o e-mail do novo administrador.',
            'email.email' => 'Informe um e-mail válido.',
            'user_id.required_without' => 'Selecione um usuário cadastrado na busca.',
            'user_id.exists' => 'Usuário não encontrado.',
        ]);

        try {
            if (! empty($data['user_id'])) {
                $this->invitations->inviteExistingUser((int) $data['user_id'], $request->user());
            } else {
                $this->invitations->inviteByEmail((string) $data['email'], $request->user());
            }
        } catch (ValidationException $e) {
            return back()->withErrors($e->errors())->withInput();
        }

        return back()->with('success', 'Convite de Administrador da plataforma enviado por e-mail.');
    }

    public function show(Request $request): View|RedirectResponse
    {
        $invite = $this->invitations->resolveInvite(
            (string) $request->query('email', ''),
            (int) $request->query('user', 0),
        );

        if ($invite['user']?->hasAssignedRole(PlatformAdminInvitationService::ROLE)) {
            return redirect()
                ->route('login')
                ->with('status', 'Este usuário já é administrador da plataforma. Entre com o perfil Administrador.');
        }

        return view('auth.platform-admin-invite', [
            'email' => $invite['email'],
            'existingUser' => $invite['user'],
            'expireDays' => PlatformAdminInvitationService::EXPIRE_DAYS,
            'formAction' => $request->fullUrl(),
        ]);
    }

    public function complete(Request $request): RedirectResponse
    {
        $invite = $this->invitations->resolveInvite(
            (string) $request->query('email', ''),
            (int) $request->query('user', 0),
        );

        if ($invite['user']?->hasAssignedRole(PlatformAdminInvitationService::ROLE)) {
            return redirect()
                ->route('login')
                ->with('status', 'Este usuário já é administrador da plataforma.');
        }

        if ($invite['user']) {
            return $this->acceptExisting($request, $invite['user']);
        }

        $this->logoutIfDifferentUser($request, null);

        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'password' => ['required', 'confirmed', Password::min(8)],
        ], [
            'name.required' => 'Informe o seu nome.',
            'password.required' => 'Crie uma senha.',
            'password.min' => 'A senha deve ter no mínimo 8 caracteres.',
            'password.confirmed' => 'A confirmação de senha não confere.',
        ]);

        try {
            $user = $this->invitations->createFromInvite(
                $invite['email'],
                $data['name'],
                $data['password'],
            );
        } catch (ValidationException $e) {
            return redirect()
                ->to($request->fullUrl())
                ->withErrors($e->errors())
                ->withInput();
        }

        Auth::login($user);
        $request->session()->regenerate();
        session(['active_role' => PlatformAdminInvitationService::ROLE]);

        return redirect()
            ->route('platform.dashboard')
            ->with('success', 'Cadastro concluído. Bem-vindo à plataforma SindCON.');
    }

    protected function acceptExisting(Request $request, User $user): RedirectResponse
    {
        $wasInvitee = $request->user() && (int) $request->user()->id === (int) $user->id;
        $this->logoutIfDifferentUser($request, $user);

        $this->invitations->grantRole($user);

        if ($wasInvitee) {
            Auth::login($user);
            $request->session()->regenerate();
            session(['active_role' => PlatformAdminInvitationService::ROLE]);

            return redirect()
                ->route('platform.dashboard')
                ->with('success', 'Perfil Administrador da plataforma ativado.');
        }

        return redirect()
            ->route('login')
            ->with('status', 'Convite aceito. Entre com seu e-mail e senha e selecione o perfil Administrador.');
    }

    protected function logoutIfDifferentUser(Request $request, ?User $invitee): void
    {
        $actor = $request->user();

        if (! $actor) {
            return;
        }

        if ($invitee && (int) $actor->id === (int) $invitee->id) {
            return;
        }

        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
    }
}
