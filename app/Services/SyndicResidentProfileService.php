<?php

namespace App\Services;

use App\Models\Unit;
use App\Models\User;
use Illuminate\Validation\ValidationException;

class SyndicResidentProfileService
{
    public function __construct(
        private SyndicCondominiumLinkageService $syndicLinkage,
    ) {}

    public function canManageOwnMoradorProfile(User $user): bool
    {
        return $user->hasAssignedRole('Síndico') && $this->residentCondominiumId($user) !== null;
    }

    /**
     * Condomínio da moradia: cadastro da conta, condomínio ativo que ele administra, ou o único vínculo.
     */
    public function residentCondominiumId(User $user): ?int
    {
        if ($user->condominium_id) {
            return (int) $user->condominium_id;
        }

        if (!$user->hasAssignedRole('Síndico')) {
            return null;
        }

        $managed = $this->syndicLinkage->managedCondominiumIds($user);
        $activeId = session(ActiveCondominiumService::SESSION_KEY);

        if ($activeId !== null && in_array((int) $activeId, $managed, true)) {
            return (int) $activeId;
        }

        if (count($managed) === 1) {
            return $managed[0];
        }

        return null;
    }

    /**
     * Síndico morador: atribui ou remove o papel Morador na própria conta (mantém Síndico).
     */
    public function syncOwnMoradorProfile(User $syndic, bool $asMorador, ?int $unitId): User
    {
        if (!$this->canManageOwnMoradorProfile($syndic)) {
            throw ValidationException::withMessages([
                'syndic_also_morador' => 'Não foi possível atualizar o perfil de morador.',
            ]);
        }

        $condominiumId = $this->residentCondominiumId($syndic);
        if ($condominiumId === null) {
            throw ValidationException::withMessages([
                'syndic_also_morador' => 'Selecione o condomínio antes de vincular a unidade.',
            ]);
        }
        $roleNames = $syndic->roles->pluck('name')->all();

        if ($asMorador) {
            if ($unitId === null) {
                throw ValidationException::withMessages([
                    'unit_id' => 'Selecione a unidade em que você mora.',
                ]);
            }

            $unit = Unit::query()
                ->whereKey($unitId)
                ->where('condominium_id', $condominiumId)
                ->first();

            if (!$unit) {
                throw ValidationException::withMessages([
                    'unit_id' => 'Unidade inválida para este condomínio.',
                ]);
            }

            if (!in_array('Morador', $roleNames, true)) {
                $roleNames[] = 'Morador';
            }

            $syndic->update([
                'unit_id' => $unitId,
                'condominium_id' => $condominiumId,
            ]);
        } else {
            $roleNames = array_values(array_filter(
                $roleNames,
                fn (string $name) => $name !== 'Morador'
            ));
            $syndic->update(['unit_id' => null]);
        }

        if (!in_array('Síndico', $roleNames, true)) {
            $roleNames[] = 'Síndico';
        }

        $syndic->syncRoles($roleNames);
        $this->syndicLinkage->syncHomeCondominiumPivot($syndic->fresh());
        $syndic->refreshActiveProfileCache();

        return $syndic->fresh(['roles', 'unit', 'condominium']);
    }
}
