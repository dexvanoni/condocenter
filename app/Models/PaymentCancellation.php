<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PaymentCancellation extends Model
{
    use HasFactory;

    protected $fillable = [
        'charge_id',
        'cancelled_by',
        'reason',
    ];

    public function charge()
    {
        return $this->belongsTo(Charge::class);
    }

    public function cancelledBy()
    {
        return $this->belongsTo(User::class, 'cancelled_by');
    }
}

