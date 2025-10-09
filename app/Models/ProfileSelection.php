<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProfileSelection extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'role_name',
        'selected_at',
        'ip_address',
    ];

    protected $casts = [
        'selected_at' => 'datetime',
    ];

    // Relacionamentos
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    // Scopes
    public function scopeByUser($query, $userId)
    {
        return $query->where('user_id', $userId);
    }

    public function scopeRecent($query)
    {
        return $query->orderBy('selected_at', 'desc');
    }
}

