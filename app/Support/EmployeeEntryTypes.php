<?php

namespace App\Support;

class EmployeeEntryTypes
{
    public const SALARY = 'salary';

    public const OVERTIME = 'overtime';

    public const EXTRA_PAYMENT = 'extra_payment';

    public const ADVANCE = 'advance';

    public const VACATION = 'vacation';

    public const EMPLOYER_TAX = 'employer_tax';

    public const BENEFIT = 'benefit';

    public const DEDUCTION = 'deduction';

    public const TERMINATION = 'termination';

    public const OTHER = 'other';

    public static function labels(): array
    {
        return [
            self::SALARY => 'Salário',
            self::OVERTIME => 'Horas extras',
            self::EXTRA_PAYMENT => 'Pagamento extra',
            self::ADVANCE => 'Adiantamento',
            self::VACATION => 'Férias',
            self::EMPLOYER_TAX => 'Encargos patronais',
            self::BENEFIT => 'Benefício',
            self::DEDUCTION => 'Desconto',
            self::TERMINATION => 'Rescisão',
            self::OTHER => 'Outro',
        ];
    }

    public static function label(string $type): string
    {
        return self::labels()[$type] ?? ucfirst(str_replace('_', ' ', $type));
    }

    public static function all(): array
    {
        return array_keys(self::labels());
    }
}
