<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class MarketplaceItem extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'condominium_id', 'seller_id', 'unit_id', 'title', 'description',
        'price', 'category', 'condition', 'images', 'status', 'views',
    ];

    protected $casts = [
        'price' => 'decimal:2',
        'images' => 'array',
        'views' => 'integer',
    ];

    public function condominium()
    {
        return $this->belongsTo(Condominium::class);
    }

    public function seller()
    {
        return $this->belongsTo(User::class, 'seller_id');
    }

    public function unit()
    {
        return $this->belongsTo(Unit::class);
    }

    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    public function incrementViews()
    {
        $this->increment('views');
    }

    public function markAsSold()
    {
        $this->update(['status' => 'sold']);
    }
}
