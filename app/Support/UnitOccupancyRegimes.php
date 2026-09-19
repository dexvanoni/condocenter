<?php

namespace App\Support;

class UnitOccupancyRegimes
{
    public const PARTICULAR = 'particular';

    public const ALUGUEL = 'aluguel';

    public const IMOVEL_PUBLICO = 'imovel_publico';

    public static function values(): array
    {
        return [
            self::PARTICULAR,
            self::ALUGUEL,
            self::IMOVEL_PUBLICO,
        ];
    }

    public static function labels(): array
    {
        return [
            self::PARTICULAR => 'Particular',
            self::ALUGUEL => 'Aluguel',
            self::IMOVEL_PUBLICO => 'Imóvel público',
        ];
    }

    public static function label(?string $value): string
    {
        if (!$value) {
            return '—';
        }

        return self::labels()[$value] ?? ucfirst(str_replace('_', ' ', $value));
    }

    public static function validationRule(): string
    {
        return 'in:' . implode(',', self::values());
    }
}
