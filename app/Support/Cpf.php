<?php

namespace App\Support;

class Cpf
{
    public static function digits(?string $cpf): string
    {
        return preg_replace('/\D/', '', (string) $cpf) ?? '';
    }

    public static function isValid(?string $cpf): bool
    {
        $digits = self::digits($cpf);

        if (strlen($digits) !== 11) {
            return false;
        }

        if (preg_match('/^(\d)\1{10}$/', $digits)) {
            return false;
        }

        for ($t = 9; $t < 11; $t++) {
            $sum = 0;
            for ($i = 0; $i < $t; $i++) {
                $sum += (int) $digits[$i] * (($t + 1) - $i);
            }
            $digit = ((10 * $sum) % 11) % 10;
            if ((int) $digits[$t] !== $digit) {
                return false;
            }
        }

        return true;
    }
}
