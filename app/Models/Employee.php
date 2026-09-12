<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Employee extends Model
{
    use HasFactory, SoftDeletes;

    public const STATUS_ACTIVE = 'active';

    public const STATUS_VACATION = 'vacation';

    public const STATUS_TERMINATED = 'terminated';

    protected $fillable = [
        'condominium_id',
        'name',
        'cpf',
        'rg',
        'position',
        'phone',
        'email',
        'admission_date',
        'termination_date',
        'base_salary',
        'work_schedule',
        'status',
        'notes',
        'created_by',
    ];

    protected $casts = [
        'admission_date' => 'date',
        'termination_date' => 'date',
        'base_salary' => 'decimal:2',
    ];

    public function condominium()
    {
        return $this->belongsTo(Condominium::class);
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function financialEntries()
    {
        return $this->hasMany(EmployeeFinancialEntry::class);
    }

    public function scopeByCondominium($query, int $condominiumId)
    {
        return $query->where('condominium_id', $condominiumId);
    }

    public function scopeActive($query)
    {
        return $query->where('status', self::STATUS_ACTIVE);
    }

    public function isActive(): bool
    {
        return $this->status === self::STATUS_ACTIVE;
    }

    public function isTerminated(): bool
    {
        return $this->status === self::STATUS_TERMINATED;
    }

    public static function statusLabels(): array
    {
        return [
            self::STATUS_ACTIVE => 'Ativo',
            self::STATUS_VACATION => 'Em férias',
            self::STATUS_TERMINATED => 'Desligado',
        ];
    }

    public function statusLabel(): string
    {
        return self::statusLabels()[$this->status] ?? $this->status;
    }
}
