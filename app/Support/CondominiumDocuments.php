<?php

namespace App\Support;

use App\Models\Condominium;
use App\Models\User;

class CondominiumDocuments
{
    public static function resolveSindico(int $condominiumId): ?User
    {
        return User::query()
            ->where('condominium_id', $condominiumId)
            ->where('is_active', true)
            ->whereHas('roles', fn ($query) => $query->where('name', 'Síndico'))
            ->orderBy('id')
            ->first();
    }

    public static function formatAddress(?Condominium $condominium): ?string
    {
        if (!$condominium) {
            return null;
        }

        $parts = array_filter([
            $condominium->address,
            $condominium->neighborhood,
            $condominium->city && $condominium->state
                ? "{$condominium->city}/{$condominium->state}"
                : ($condominium->city ?? $condominium->state),
            $condominium->zip_code ? "CEP {$condominium->zip_code}" : null,
        ]);

        return $parts ? implode(' — ', $parts) : null;
    }

    public static function formatCnpj(?string $cnpj): ?string
    {
        if (!$cnpj) {
            return null;
        }

        $digits = preg_replace('/\D/', '', $cnpj);

        if (strlen($digits) !== 14) {
            return $cnpj;
        }

        return preg_replace(
            '/^(\d{2})(\d{3})(\d{3})(\d{4})(\d{2})$/',
            '$1.$2.$3/$4-$5',
            $digits
        );
    }

    public static function presentCondominium(?Condominium $condominium): array
    {
        if (!$condominium) {
            return [
                'name' => 'Condomínio',
                'cnpj' => null,
                'address' => null,
                'phone' => null,
                'email' => null,
            ];
        }

        return [
            'name' => $condominium->name,
            'cnpj' => self::formatCnpj($condominium->cnpj),
            'address' => self::formatAddress($condominium),
            'phone' => $condominium->phone,
            'email' => $condominium->email,
        ];
    }
}
