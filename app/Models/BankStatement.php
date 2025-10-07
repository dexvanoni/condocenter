<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class BankStatement extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'condominium_id', 'uploaded_by', 'original_filename',
        'storage_path', 'statement_date', 'period_start', 'period_end',
        'status', 'total_transactions', 'reconciled_transactions',
        'unmatched_items', 'notes',
    ];

    protected $casts = [
        'statement_date' => 'date',
        'period_start' => 'date',
        'period_end' => 'date',
        'unmatched_items' => 'array',
    ];

    public function condominium()
    {
        return $this->belongsTo(Condominium::class);
    }

    public function uploadedBy()
    {
        return $this->belongsTo(User::class, 'uploaded_by');
    }

    public function getReconciliationPercentageAttribute()
    {
        if ($this->total_transactions == 0) {
            return 0;
        }
        return round(($this->reconciled_transactions / $this->total_transactions) * 100, 2);
    }
}
