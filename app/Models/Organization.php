<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;

class Organization extends Model
{
    use HasFactory, SoftDeletes;

    public const TYPE_CONDOMINIUM = 'condominium';
    public const TYPE_MANAGEMENT_COMPANY = 'management_company';

    public const STATUS_ACTIVE = 'active';
    public const STATUS_SUSPENDED = 'suspended';
    public const STATUS_BLOCKED = 'blocked';

    public const ROLE_OWNER = 'organization_owner';
    public const ROLE_ADMIN = 'organization_admin';
    public const ROLE_MANAGER = 'organization_manager';
    public const ROLE_OPERATOR = 'organization_operator';

    protected $fillable = [
        'type',
        'legal_name',
        'trade_name',
        'document',
        'email',
        'phone',
        'address',
        'neighborhood',
        'city',
        'state',
        'zip_code',
        'status',
        'trial_ends_at',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'trial_ends_at' => 'datetime',
        ];
    }

    public function condominiums(): HasMany
    {
        return $this->hasMany(Condominium::class);
    }

    public function users(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'organization_user')
            ->withPivot('role')
            ->withTimestamps();
    }

    public function subscription(): HasOne
    {
        return $this->hasOne(OrganizationSubscription::class);
    }

    public function isManagementCompany(): bool
    {
        return $this->type === self::TYPE_MANAGEMENT_COMPANY;
    }

    public function isDirectCondominium(): bool
    {
        return $this->type === self::TYPE_CONDOMINIUM;
    }

    public function isActive(): bool
    {
        return $this->status === self::STATUS_ACTIVE;
    }

    public function typeLabel(): string
    {
        return match ($this->type) {
            self::TYPE_MANAGEMENT_COMPANY => 'Administradora profissional',
            default => 'Síndico / Condomínio',
        };
    }

    public function statusLabel(): string
    {
        return match ($this->status) {
            self::STATUS_SUSPENDED => 'Suspensa',
            self::STATUS_BLOCKED => 'Bloqueada',
            default => 'Ativa',
        };
    }

    public function displayName(): string
    {
        return $this->trade_name ?: $this->legal_name;
    }

    public static function organizationRoles(): array
    {
        return [
            self::ROLE_OWNER,
            self::ROLE_ADMIN,
            self::ROLE_MANAGER,
            self::ROLE_OPERATOR,
        ];
    }

    public static function organizationRoleLabel(string $role): string
    {
        return match ($role) {
            self::ROLE_OWNER => 'Proprietário',
            self::ROLE_ADMIN => 'Administrador',
            self::ROLE_MANAGER => 'Gerente',
            self::ROLE_OPERATOR => 'Operador',
            default => $role,
        };
    }
}
