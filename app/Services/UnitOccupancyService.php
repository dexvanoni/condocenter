<?php

namespace App\Services;

use App\Models\Charge;
use App\Models\Unit;
use App\Models\User;
use App\Support\UnitOccupancyRegimes;
use Illuminate\Support\Collection;
use Illuminate\Validation\ValidationException;

class UnitOccupancyService
{
    public function isRental(Unit $unit): bool
    {
        return $unit->occupancy_regime === UnitOccupancyRegimes::ALUGUEL;
    }

    public function ownedRentalUnits(User $user, ?int $condominiumId = null): Collection
    {
        $query = Unit::query()
            ->where('owner_user_id', $user->id)
            ->where('occupancy_regime', UnitOccupancyRegimes::ALUGUEL);

        if ($condominiumId !== null) {
            $query->where('condominium_id', $condominiumId);
        }

        return $query->get();
    }

    public function ownedUnitIds(User $user, int $condominiumId): array
    {
        return Unit::query()
            ->where('condominium_id', $condominiumId)
            ->where('owner_user_id', $user->id)
            ->pluck('id')
            ->map(fn ($id) => (int) $id)
            ->all();
    }

    public function userOwnsUnit(User $user, Unit $unit): bool
    {
        return $unit->owner_user_id !== null && (int) $unit->owner_user_id === (int) $user->id;
    }

    public function billingMorador(Unit $unit): ?User
    {
        return $unit->morador()->first();
    }

    public function resolveFineNotificationRecipient(User $infractor, Unit $unit): User
    {
        if ($this->isRental($unit) && $unit->owner_user_id) {
            $owner = User::query()->find($unit->owner_user_id);

            if ($owner && $owner->is_active) {
                return $owner;
            }
        }

        if ($infractor->isAgregado() && $infractor->morador_vinculado_id) {
            $responsible = $infractor->moradorVinculado;

            if ($responsible && $responsible->is_active) {
                return $responsible;
            }
        }

        return $infractor;
    }

    public function moradorCanVoteInAssembly(User $voter): bool
    {
        if (!$voter->unit_id) {
            return false;
        }

        $unit = Unit::query()->find($voter->unit_id);

        if (!$unit) {
            return false;
        }

        return !$this->isRental($unit);
    }

    /**
     * @return list<int>
     */
    public function assemblyVoteUnitIdsForVoter(User $voter, ?int $requestedUnitId = null): array
    {
        if ($voter->isProprietario()) {
            $owned = $this->ownedRentalUnits($voter, $voter->tenantCondominiumId())
                ->pluck('id')
                ->map(fn ($id) => (int) $id)
                ->values()
                ->all();

            if ($owned === []) {
                return [];
            }

            if ($requestedUnitId !== null) {
                if (!in_array($requestedUnitId, $owned, true)) {
                    throw ValidationException::withMessages([
                        'unit_id' => 'Selecione uma unidade de aluguel da qual você é proprietário.',
                    ]);
                }

                return [$requestedUnitId];
            }

            if (count($owned) === 1) {
                return $owned;
            }

            throw ValidationException::withMessages([
                'unit_id' => 'Informe a unidade de aluguel para registrar o voto.',
            ]);
        }

        if (!$this->moradorCanVoteInAssembly($voter)) {
            throw ValidationException::withMessages([
                'user' => 'Em unidades de aluguel, somente o proprietário pode votar em assembleias.',
            ]);
        }

        return [(int) $voter->unit_id];
    }

    public function syncOwnerRole(User $user): void
    {
        $stillOwns = Unit::query()->where('owner_user_id', $user->id)->exists();

        if ($stillOwns) {
            if (!$user->hasAssignedRole('Proprietário')) {
                $user->assignRole('Proprietário');
            }

            return;
        }

        if ($user->hasAssignedRole('Proprietário')) {
            $user->removeRole('Proprietário');
        }
    }

    public function userLivesInRentalUnit(User $user): bool
    {
        if (!$user->unit_id) {
            return false;
        }

        $unit = Unit::query()->find($user->unit_id);

        return $unit !== null && $this->isRental($unit);
    }

    public function agregadoLinkedToRentalMorador(User $user): bool
    {
        if (!$user->isAgregado() || !$user->morador_vinculado_id) {
            return false;
        }

        $morador = $user->moradorVinculado;

        return $morador !== null && $this->userLivesInRentalUnit($morador);
    }

    public function canAccessFinancialModule(User $user): bool
    {
        if ($user->isAdmin() || $user->isSindico() || $user->isConselhoFiscal() || $user->isSecretaria()) {
            return true;
        }

        if ($user->isProprietario()) {
            $tenantId = $user->tenantCondominiumId();

            return $tenantId !== null
                && $this->ownedRentalUnits($user, $tenantId)->isNotEmpty();
        }

        if ($this->agregadoLinkedToRentalMorador($user)) {
            return false;
        }

        if ($user->isMorador() && $this->userLivesInRentalUnit($user)) {
            return false;
        }

        return true;
    }

    public function isMoradorResponsibleCharge(Charge $charge): bool
    {
        $charge->loadMissing('unit');

        if ($charge->service_order_id) {
            return true;
        }

        if ($charge->generated_by === 'fine' || !empty($charge->metadata['fine_id'])) {
            return true;
        }

        if ($charge->generated_by === 'reservation' || !empty($charge->metadata['reservation_id'])) {
            return true;
        }

        return false;
    }

    public function canUserPayCharge(User $user, Charge $charge): bool
    {
        $charge->loadMissing('unit');
        $unit = $charge->unit;

        if (!$unit || !$this->isRental($unit)) {
            return $user->unit_id && (int) $charge->unit_id === (int) $user->unit_id;
        }

        if ($this->isMoradorResponsibleCharge($charge)) {
            if ($user->isMorador() && (int) $user->unit_id === (int) $charge->unit_id) {
                return true;
            }

            if ($user->isAgregado()) {
                return (int) $user->moradorVinculado?->unit_id === (int) $charge->unit_id;
            }

            return false;
        }

        return $user->isProprietario() && $this->userOwnsUnit($user, $unit);
    }

    public function canUserViewChargeInFinancialContext(User $user, Charge $charge): bool
    {
        if (!$this->canAccessFinancialModule($user)) {
            return false;
        }

        $charge->loadMissing('unit');
        $unit = $charge->unit;

        if ($user->isProprietario() && $unit && $this->userOwnsUnit($user, $unit)) {
            return true;
        }

        if ($user->isMorador() && $user->unit_id && (int) $charge->unit_id === (int) $user->unit_id) {
            return true;
        }

        return $user->can('manage_charges');
    }

    public function normalizeRegimeFields(array $data): array
    {
        $regime = $data['occupancy_regime'] ?? UnitOccupancyRegimes::PARTICULAR;

        if ($regime !== UnitOccupancyRegimes::ALUGUEL) {
            $data['rental_period'] = null;
            $data['owner_user_id'] = null;
            $data['lease_contract_ends_at'] = null;
        }

        if ($regime !== UnitOccupancyRegimes::IMOVEL_PUBLICO) {
            $data['public_property_kind'] = null;
        }

        if ($regime === UnitOccupancyRegimes::PARTICULAR) {
            $data['owner_user_id'] = null;
            $data['rental_period'] = null;
            $data['public_property_kind'] = null;
        }

        return $data;
    }
}
