<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Vote extends Model
{
    use HasFactory;

    protected $fillable = [
        'assembly_id', 'user_id', 'unit_id', 'agenda_item',
        'vote', 'encrypted_vote', 'delegated_from',
    ];

    public function assembly()
    {
        return $this->belongsTo(Assembly::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function unit()
    {
        return $this->belongsTo(Unit::class);
    }

    public function delegatedFrom()
    {
        return $this->belongsTo(User::class, 'delegated_from');
    }
}
