<?php

namespace App\Support;

class MonthlyClosingSteps
{
    public const FEE_GENERATION = 'fee_generation';

    public const CHARGES = 'charges';

    public const FINES = 'fines';

    public const RESERVATIONS = 'reservations';

    public const EMPLOYEES = 'employees';

    public const MANUAL_CASHFLOW = 'manual_cashflow';

    public const BANK_RECONCILIATION = 'bank_reconciliation';

    public const ACCOUNTABILITY = 'accountability';

    public static function all(): array
    {
        return [
            self::FEE_GENERATION,
            self::CHARGES,
            self::FINES,
            self::RESERVATIONS,
            self::EMPLOYEES,
            self::MANUAL_CASHFLOW,
            self::BANK_RECONCILIATION,
            self::ACCOUNTABILITY,
        ];
    }

    public static function isValid(string $stepKey): bool
    {
        return in_array($stepKey, self::all(), true);
    }
}
