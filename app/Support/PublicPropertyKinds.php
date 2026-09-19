<?php

namespace App\Support;

class PublicPropertyKinds
{
    public const MILITAR = 'militar';

    public const FUNCIONAL_PUBLICO = 'funcional_publico';

    public const FUNCIONAL_PRIVADO = 'funcional_privado';

    public static function values(): array
    {
        return [
            self::MILITAR,
            self::FUNCIONAL_PUBLICO,
            self::FUNCIONAL_PRIVADO,
        ];
    }

    public static function labels(): array
    {
        return [
            self::MILITAR => 'Imóvel militar',
            self::FUNCIONAL_PUBLICO => 'Funcional público',
            self::FUNCIONAL_PRIVADO => 'Funcional privado',
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
