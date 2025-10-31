<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class InternalRegulationHistory extends Model
{
    use HasFactory;

    protected $table = 'internal_regulation_history';

    protected $fillable = [
        'internal_regulation_id',
        'condominium_id',
        'content',
        'changes_summary',
        'assembly_date',
        'assembly_details',
        'version',
        'updated_by',
        'changed_at',
    ];

    protected $casts = [
        'assembly_date' => 'date',
        'changed_at' => 'datetime',
    ];

    // Relacionamentos
    public function internalRegulation()
    {
        return $this->belongsTo(InternalRegulation::class);
    }

    public function condominium()
    {
        return $this->belongsTo(Condominium::class);
    }

    public function updatedBy()
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    // Atributos computados
    public function getFormattedChangedAtAttribute()
    {
        return $this->changed_at ? $this->changed_at->format('d/m/Y H:i') : null;
    }

    public function getFormattedAssemblyDateAttribute()
    {
        return $this->assembly_date ? $this->assembly_date->format('d/m/Y') : null;
    }
}
