<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AiFinancialConsultation extends Model
{
    protected $fillable = [
        'condominium_id',
        'user_id',
        'question_key',
        'indicators_hash',
        'provider',
        'model',
        'input_tokens',
        'output_tokens',
        'total_tokens',
        'estimated_cost',
        'response_time_ms',
        'status',
    ];

    protected function casts(): array
    {
        return [
            'input_tokens' => 'integer',
            'output_tokens' => 'integer',
            'total_tokens' => 'integer',
            'estimated_cost' => 'decimal:6',
            'response_time_ms' => 'integer',
        ];
    }

    public function condominium(): BelongsTo
    {
        return $this->belongsTo(Condominium::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
