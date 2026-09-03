<?php

namespace App\Services;

use App\Models\User;

class UserRoleLinkageService
{
    /**
     * Ajusta vínculos de unidade e morador conforme os perfis solicitados.
     *
     * @param  array<string, mixed>  $data
     */
    public function applyLinkageRules(User $user, array $requestedRoles, array &$data): void
    {
        $willBeAgregado = in_array('Agregado', $requestedRoles, true);
        $willBeMorador = in_array('Morador', $requestedRoles, true);
        $wasAgregado = $user->exists && $user->isAgregado();

        if (!$willBeAgregado) {
            $data['morador_vinculado_id'] = null;
        }

        if ($willBeMorador && !$willBeAgregado) {
            $data['morador_vinculado_id'] = null;

            if ($wasAgregado) {
                $submittedUnitId = $data['unit_id'] ?? null;
                $submittedUnitId = $submittedUnitId === '' ? null : $submittedUnitId;

                if ($submittedUnitId === null) {
                    $data['unit_id'] = null;
                }
            }
        }

        if ($willBeAgregado && !$willBeMorador) {
            $moradorId = $data['morador_vinculado_id'] ?? null;

            if ($moradorId) {
                $morador = User::query()->find($moradorId);
                if ($morador?->unit_id) {
                    $data['unit_id'] = $morador->unit_id;
                }
            }
        }
    }

    /**
     * @param  array<string, mixed>  $data
     */
    public function applyLinkageRulesForCreate(array $requestedRoles, array &$data): void
    {
        $willBeAgregado = in_array('Agregado', $requestedRoles, true);
        $willBeMorador = in_array('Morador', $requestedRoles, true);

        if (!$willBeAgregado) {
            $data['morador_vinculado_id'] = null;
        }

        if ($willBeMorador && !$willBeAgregado) {
            $data['morador_vinculado_id'] = null;
        }

        if ($willBeAgregado && !$willBeMorador && !empty($data['morador_vinculado_id'])) {
            $morador = User::query()->find($data['morador_vinculado_id']);
            if ($morador?->unit_id) {
                $data['unit_id'] = $morador->unit_id;
            }
        }
    }
}
