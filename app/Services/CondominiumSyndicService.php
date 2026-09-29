<?php

namespace App\Services;

use App\Mail\ClientWelcomeMail;
use App\Models\Condominium;
use App\Models\User;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class CondominiumSyndicService
{
    public function __construct(
        private SyndicCondominiumLinkageService $syndicLinkage,
    ) {}

    /**
     * Síndicos vinculados ao condomínio (pivot + cadastro com condominium_id).
     *
     * @return Collection<int, User>
     */
    public function syndicsFor(Condominium $condominium): Collection
    {
        $fromPivot = $condominium->syndics()
            ->whereHas('roles', fn ($query) => $query->where('name', 'Síndico'))
            ->get();

        $fromHome = User::query()
            ->where('condominium_id', $condominium->id)
            ->whereHas('roles', fn ($query) => $query->where('name', 'Síndico'))
            ->get();

        return $fromPivot->merge($fromHome)->unique('id')->values();
    }

    public function primarySyndic(Condominium $condominium): ?User
    {
        return $this->syndicsFor($condominium)->first();
    }

    public function attachSyndic(
        Condominium $condominium,
        string $name,
        string $email,
        ?string $phone = null,
        bool $sendWelcomeEmail = true,
    ): User {
        $syndic = User::query()->where('email', $email)->first();

        if ($syndic) {
            if ($syndic->isManagementCompanyMember()) {
                throw ValidationException::withMessages([
                    'syndic_email' => 'Este e-mail é da administradora. Informe outra pessoa para ser o síndico.',
                ]);
            }

            if (!$syndic->hasAssignedRole('Síndico')) {
                $syndic->assignRole('Síndico');
            }

            if ($name !== '' && $syndic->name !== $name) {
                $syndic->update(['name' => $name]);
            }

            if ($phone !== null && $phone !== '') {
                $syndic->update(['phone' => $phone]);
            }

            $condominium->syndics()->syncWithoutDetaching([$syndic->id]);
            $this->syndicLinkage->syncHomeCondominiumPivot($syndic);

            return $syndic->fresh();
        }

        $syndic = User::query()->create([
            'name' => $name,
            'email' => $email,
            'phone' => $phone,
            'condominium_id' => null,
            'password' => Hash::make(Str::password(32)),
            'senha_temporaria' => true,
            'is_active' => true,
            'registration_status' => 'approved',
        ]);
        $syndic->assignRole('Síndico');
        $condominium->syndics()->syncWithoutDetaching([$syndic->id]);

        if ($sendWelcomeEmail) {
            $token = Password::createToken($syndic);
            Mail::to($syndic->email)->send(new ClientWelcomeMail(
                $syndic,
                route('password.reset', ['token' => $token, 'email' => $syndic->email]),
                $condominium->name,
                ClientWelcomeMail::AUDIENCE_SINDICO,
                (int) config('auth.passwords.users.expire', 60),
            ));
        }

        return $syndic;
    }

    public function updateSyndic(User $syndic, Condominium $condominium, array $data): User
    {
        $this->assertSyndicLinked($syndic, $condominium);

        $syndic->update([
            'name' => $data['name'],
            'email' => $data['email'],
            'phone' => $data['phone'] ?? null,
        ]);

        return $syndic->fresh();
    }

    public function detachSyndic(User $syndic, Condominium $condominium): void
    {
        $this->assertSyndicLinked($syndic, $condominium);

        $condominium->syndics()->detach($syndic->id);

        if ((int) $syndic->condominium_id === (int) $condominium->id) {
            $syndic->update(['condominium_id' => null]);
        }
    }

    protected function assertSyndicLinked(User $syndic, Condominium $condominium): void
    {
        if (!$syndic->hasAssignedRole('Síndico')) {
            abort(404);
        }

        $linked = $this->syndicsFor($condominium)->contains(fn (User $u) => (int) $u->id === (int) $syndic->id);

        abort_unless($linked, 404);
    }
}
