<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use OwenIt\Auditing\Contracts\Auditable;

class InternalRegulation extends Model implements Auditable
{
    use HasFactory, SoftDeletes, \OwenIt\Auditing\Auditable;

    protected $fillable = [
        'condominium_id',
        'content',
        'assembly_date',
        'assembly_details',
        'version',
        'is_active',
        'updated_by',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'assembly_date' => 'date',
    ];

    protected static function boot()
    {
        parent::boot();

        // Criar histórico automaticamente ao atualizar
        static::updating(function ($regulation) {
            if ($regulation->isDirty('content')) {
                InternalRegulationHistory::create([
                    'internal_regulation_id' => $regulation->id,
                    'condominium_id' => $regulation->condominium_id,
                    'content' => $regulation->getOriginal('content'),
                    'assembly_date' => $regulation->getOriginal('assembly_date'),
                    'assembly_details' => $regulation->getOriginal('assembly_details'),
                    'version' => $regulation->getOriginal('version'),
                    'updated_by' => $regulation->updated_by,
                    'changed_at' => now(),
                ]);
                
                // Incrementar versão
                $regulation->version = ($regulation->getOriginal('version') ?? 1) + 1;
            }
        });
    }

    // Relacionamentos
    public function condominium()
    {
        return $this->belongsTo(Condominium::class);
    }

    public function updatedBy()
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    public function history()
    {
        return $this->hasMany(InternalRegulationHistory::class)->orderBy('version', 'desc');
    }

    // Scopes
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeByCondominium($query, $condominiumId)
    {
        return $query->where('condominium_id', $condominiumId);
    }

    // Atributos computados
    public function getFormattedAssemblyDateAttribute()
    {
        return $this->assembly_date ? $this->assembly_date->format('d/m/Y') : null;
    }
}
