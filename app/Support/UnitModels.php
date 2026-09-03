<?php

namespace App\Support;

class UnitModels
{
    public const CASA = 'casa';

    public const APARTAMENTO = 'apartamento';

    public const KITNET = 'kitnet';

    public const QUARTO = 'quarto';

    public const FLAT = 'flat';

    public static function values(): array
    {
        return [
            self::CASA,
            self::APARTAMENTO,
            self::KITNET,
            self::QUARTO,
            self::FLAT,
        ];
    }

    public static function labels(): array
    {
        return [
            self::CASA => 'Casa',
            self::APARTAMENTO => 'Apartamento',
            self::KITNET => 'Kitnet',
            self::QUARTO => 'Quarto',
            self::FLAT => 'Flat',
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

    public static function formatList(?array $models): string
    {
        if (empty($models)) {
            return 'Todos os modelos';
        }

        return collect($models)
            ->map(fn ($model) => self::label($model))
            ->implode(', ');
    }

    public static function normalizeSelection(?array $models): ?array
    {
        if (empty($models)) {
            return null;
        }

        $normalized = collect($models)
            ->filter()
            ->unique()
            ->values()
            ->all();

        return empty($normalized) ? null : $normalized;
    }

    public static function matches(?array $feeModels, ?string $unitModel): bool
    {
        if (empty($feeModels)) {
            return true;
        }

        return in_array($unitModel, $feeModels, true);
    }
}
