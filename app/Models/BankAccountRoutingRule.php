<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class BankAccountRoutingRule extends Model
{
    public const SOURCE_KEYS = [
        'condominium_fee' => 'Taxas condominiais',
        'fine' => 'Multas',
        'reservation' => 'Reservas de espaços',
        'service_order' => 'Ordens de serviço',
        'manual_income' => 'Recebimentos avulsos',
        'expense' => 'Pagamentos / despesas',
        'transaction_income' => 'Transações (receitas)',
        'transaction_expense' => 'Transações (despesas)',
    ];

    protected $fillable = [
        'condominium_id',
        'source_key',
        'bank_account_id',
    ];

    public function condominium(): BelongsTo
    {
        return $this->belongsTo(Condominium::class);
    }

    public function bankAccount(): BelongsTo
    {
        return $this->belongsTo(BankAccount::class);
    }

    public function label(): string
    {
        return self::SOURCE_KEYS[$this->source_key] ?? $this->source_key;
    }
}
