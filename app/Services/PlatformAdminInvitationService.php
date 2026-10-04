<?php

namespace App\Services;

use App\Mail\PlatformAdminInvitationMail;
use App\Models\User;
use Illuminate\Database\Eloquent\Collection as EloquentCollection;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\URL;
use Illuminate\Validation\ValidationException;

class PlatformAdminInvitationService
{
    public const EXPIRE_DAYS = 7;

    public const ROLE = 'Administrador';

    public function listAdmins(): EloquentCollection
    {
        return User::role(self::ROLE)
            ->with('condominium:id,name')
            ->orderBy('name')
            ->get();
    }

    /**
     * @return list<array<string, mixed>>
     */
    public function searchUsers(string $term, int $limit = 10): array
    {
        $term = trim($term);

        if (mb_strlen($term) < 2) {
            return [];
        }

        $digits = preg_replace('/\D+/', '', $term) ?? '';

        $users = User::query()
            ->with('condominium:id,name')
            ->whereDoesntHave('roles', function ($query) {
                $query->where('name', self::ROLE);
            })
            ->where(function ($query) use ($term, $digits) {
                $query->where('name', 'like', '%'.$term.'%')
                    ->orWhere('email', 'like', '%'.$term.'%')
                    ->orWhere('cpf', 'like', '%'.$term.'%');

                if (strlen($digits) >= 2) {
                    $query->orWhereRaw(
                        "REPLACE(REPLACE(REPLACE(cpf, '.', ''), '-', ''), ' ', '') LIKE ?",
                        ['%'.$digits.'%']
                    );
                }
            })
            ->orderBy('name')
            ->limit($limit)
            ->get(['id', 'name', 'email', 'cpf', 'condominium_id', 'is_active']);

        return $users->map(function (User $user) {
            $hasEmail = filled($user->email);

            return [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'cpf' => $user->cpf,
                'condominium' => $user->condominium?->name,
                'is_active' => (bool) $user->is_active,
                'can_invite' => $hasEmail,
                'text' => trim($user->name.($user->email ? ' — '.$user->email : '')),
            ];
        })->all();
    }

    public function inviteByEmail(string $email, User $invitedBy): void
    {
        $email = mb_strtolower(trim($email));

        $existing = User::query()->where('email', $email)->first();

        if ($existing) {
            $this->inviteExistingUser($existing, $invitedBy);

            return;
        }

        $this->sendInvite($email, null, $invitedBy);
    }

    public function inviteExistingUser(User|int $user, User $invitedBy): void
    {
        if (is_int($user)) {
            $user = User::query()->find($user);
        }

        if (! $user) {
            throw ValidationException::withMessages([
                'user_id' => 'Usuário não encontrado.',
            ]);
        }

        if ($user->hasAssignedRole(self::ROLE)) {
            throw ValidationException::withMessages([
                'user_id' => 'Este usuário já é administrador da plataforma.',
            ]);
        }

        if (blank($user->email)) {
            throw ValidationException::withMessages([
                'user_id' => 'Este usuário não possui e-mail cadastrado. Envie o convite pelo e-mail.',
            ]);
        }

        $this->sendInvite((string) $user->email, $user, $invitedBy);
    }

    public function inviteUrl(string $email, ?User $existingUser = null): string
    {
        return URL::temporarySignedRoute(
            'platform.admins.invite.show',
            now()->addDays(self::EXPIRE_DAYS),
            [
                'email' => mb_strtolower(trim($email)),
                'user' => $existingUser?->id ?? 0,
            ]
        );
    }

    /**
     * @return array{type: 'new', email: string, user: null}|array{type: 'existing', email: string, user: User}
     */
    public function resolveInvite(string $email, int $userId): array
    {
        $email = mb_strtolower(trim($email));

        if ($email === '' || ! filter_var($email, FILTER_VALIDATE_EMAIL)) {
            abort(403, 'Convite inválido.');
        }

        if ($userId > 0) {
            $user = User::query()->find($userId);
            abort_unless($user && mb_strtolower((string) $user->email) === $email, 403, 'Convite inválido.');

            return ['type' => 'existing', 'email' => $email, 'user' => $user];
        }

        $existing = User::query()->where('email', $email)->first();

        if ($existing) {
            return ['type' => 'existing', 'email' => $email, 'user' => $existing];
        }

        return ['type' => 'new', 'email' => $email, 'user' => null];
    }

    public function grantRole(User $user): void
    {
        if (! $user->hasAssignedRole(self::ROLE)) {
            $user->assignRole(self::ROLE);
        }

        $user->forceFill([
            'is_active' => true,
            'registration_status' => 'approved',
            'email_verified_at' => $user->email_verified_at ?? now(),
        ])->save();

        $user->refreshActiveProfileCache();
    }

    public function createFromInvite(string $email, string $name, string $password): User
    {
        $existing = User::query()->where('email', $email)->first();

        if ($existing) {
            throw ValidationException::withMessages([
                'email' => 'Este e-mail já possui cadastro. Entre com sua conta para aceitar o convite.',
            ]);
        }

        $user = User::query()->create([
            'name' => $name,
            'email' => $email,
            'password' => $password,
            'condominium_id' => null,
            'is_active' => true,
            'senha_temporaria' => false,
            'registration_status' => 'approved',
            'email_verified_at' => now(),
        ]);

        $this->grantRole($user);

        return $user;
    }

    protected function sendInvite(string $email, ?User $existingUser, User $invitedBy): void
    {
        Mail::to($email)->send(new PlatformAdminInvitationMail(
            $this->inviteUrl($email, $existingUser),
            $invitedBy->name,
            $existingUser,
            $email,
            self::EXPIRE_DAYS,
        ));
    }
}
