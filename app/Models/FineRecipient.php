<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class FineRecipient extends Model
{
    protected $fillable = [
        'fine_id',
        'user_id',
        'unit_id',
        'notified_user_id',
        'charge_id',
    ];

    public function fine(): BelongsTo
    {
        return $this->belongsTo(Fine::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function unit(): BelongsTo
    {
        return $this->belongsTo(Unit::class);
    }

    public function notifiedUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'notified_user_id');
    }

    public function charge(): BelongsTo
    {
        return $this->belongsTo(Charge::class);
    }
}
