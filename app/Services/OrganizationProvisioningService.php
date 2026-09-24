<?php

namespace App\Services;

use App\Models\Condominium;
use App\Models\Organization;
use Illuminate\Support\Facades\DB;

class OrganizationProvisioningService
{
    /**
     * Garante que o condomínio tenha organização direta (Modelo A).
     * Não sobrescreve vínculo existente com administradora.
     */
    public function ensureDirectOrganization(Condominium $condominium): Organization
    {
        if ($condominium->organization_id) {
            $existing = $condominium->organization;

            if ($existing) {
                return $existing;
            }
        }

        return DB::transaction(function () use ($condominium) {
            $organization = Organization::query()->create([
                'type' => Organization::TYPE_CONDOMINIUM,
                'legal_name' => $condominium->name,
                'trade_name' => $condominium->name,
                'document' => $condominium->cnpj,
                'email' => $condominium->email,
                'phone' => $condominium->phone,
                'address' => $condominium->address,
                'neighborhood' => $condominium->neighborhood,
                'city' => $condominium->city,
                'state' => $condominium->state,
                'zip_code' => $condominium->zip_code,
                'status' => $condominium->is_active
                    ? Organization::STATUS_ACTIVE
                    : Organization::STATUS_SUSPENDED,
            ]);

            $condominium->forceFill([
                'organization_id' => $organization->id,
            ])->saveQuietly();

            return $organization;
        });
    }

    public function createManagementCompany(array $attributes): Organization
    {
        return Organization::query()->create(array_merge([
            'type' => Organization::TYPE_MANAGEMENT_COMPANY,
            'status' => Organization::STATUS_ACTIVE,
        ], $attributes));
    }

    public function attachUser(Organization $organization, int $userId, string $role): void
    {
        if (!in_array($role, Organization::organizationRoles(), true)) {
            throw new \InvalidArgumentException('Papel de organização inválido.');
        }

        $organization->users()->syncWithoutDetaching([
            $userId => ['role' => $role],
        ]);
    }

    public function syncCondominiumFromOrganization(Organization $organization, Condominium $condominium): void
    {
        if ($organization->isDirectCondominium()) {
            $condominium->forceFill([
                'organization_id' => $organization->id,
            ])->saveQuietly();
        }
    }
}
