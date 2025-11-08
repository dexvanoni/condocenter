<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AssemblyVote extends Model
{
    use HasFactory;

    protected $fillable = [
        'assembly_id',
        'assembly_item_id',
        'voter_id',
        'unit_id',
        'choice',
        'encrypted_choice',
        'comment',
        'submitted_at',
    ];

    protected $casts = [
        'submitted_at' => 'datetime',
    ];

    protected static function booted(): void
    {
        static::creating(function (AssemblyVote $vote) {
            if (!$vote->submitted_at) {
                $vote->submitted_at = now();
            }
        });
    }

    public function assembly()
    {
        return $this->belongsTo(Assembly::class);
    }

    public function item()
    {
        return $this->belongsTo(AssemblyItem::class, 'assembly_item_id');
    }

    public function voter()
    {
        return $this->belongsTo(User::class, 'voter_id');
    }

    public function unit()
    {
        return $this->belongsTo(Unit::class);
    }
}

