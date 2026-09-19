<?php

namespace App\Support;

class UnitRentalPeriods
{
    public const DIARIA = 'diaria';

    public const MENSALISTA = 'mensalista';

    public static function values(): array
    {
        return [self::DIARIA, self::MENSALISTA];
    }

    public static function labels(): array
    {
        return [
            self::DIARIA => 'Diária',
            self::MENSALISTA => 'Mensalista',
        ];
    }

    public static function label(?string $value): string
    {
        if (!$value) {
            return '—';
        }

        return self::labels()[$value] ?? ucfirst($value);
    }

    public static function validationRule(): string
    {
        return 'in:' . implode(',', self::values());
    }
}
