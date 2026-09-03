<?php

namespace App\Support;

class PaymentMethods
{
    public static function labels(): array
    {
        return [
            'cash' => 'Dinheiro',
            'pix' => 'PIX',
            'bank_transfer' => 'Transferência bancária',
            'credit_card' => 'Cartão de crédito',
            'debit_card' => 'Cartão de débito',
            'boleto' => 'Boleto',
            'payroll' => 'Desconto em folha',
            'other' => 'Outros',
        ];
    }

    public static function label(?string $method): string
    {
        if (!$method) {
            return 'Não informado';
        }

        return self::labels()[$method] ?? ucfirst(str_replace('_', ' ', $method));
    }
}
