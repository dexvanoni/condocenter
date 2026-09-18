<?php

namespace App\Services;

use App\Models\Charge;
use App\Models\Condominium;
use App\Models\User;
use Illuminate\Support\Collection;

class DefaulterRestrictionService
{
    public function __construct(
        private readonly DefaulterAccessOverrideService $accessOverrideService,
    ) {}

    public const RESTRICTION_LABELS = [
        'reservations' => 'Realizar novas reservas de espaços',
        'service_orders' => 'Abrir novas ordens de serviço',
        'marketplace' => 'Anunciar e visualizar o marketplace',
        'rides' => 'Visualizar e participar de caronas',
        'assemblies_vote' => 'Votar em assembleias',
    ];

    private ?int $cachedUserId = null;

    /** @var array<string, mixed>|null */
    private ?array $cachedContext = null;

    public function isEnabled(?Condominium $condominium): bool
    {
        return (bool) ($condominium?->restrict_defaulters ?? false);
    }

    public function hasOverdueCharges(User $user): bool
    {
        if (!$user->unit_id) {
            return false;
        }

        return $this->overdueChargesQuery($user)->exists();
    }

    public function isRestricted(User $user): bool
    {
        return $this->getContextForUser($user)['active'];
    }

    public function getOverdueCharges(User $user): Collection
    {
        if (!$user->unit_id) {
            return collect();
        }

        return $this->overdueChargesQuery($user)
            ->orderBy('due_date')
            ->get();
    }

    /**
     * @return array{
     *     active: bool,
     *     overdue_charges: Collection,
     *     restrictions: array<int, string>,
     *     regularize_url: string,
     *     total_overdue: float,
     *     has_overdue_charges: bool,
     *     temporary_unlock: array{active: bool, expires_at: ?\Illuminate\Support\Carbon, days: ?int, granted_by: ?string}|null
     * }
     */
    public function getContextForUser(?User $user): array
    {
        $empty = [
            'active' => false,
            'overdue_charges' => collect(),
            'restrictions' => [],
            'regularize_url' => route('my-charges.index', ['status' => 'overdue']),
            'total_overdue' => 0.0,
            'has_overdue_charges' => false,
            'temporary_unlock' => [
                'active' => false,
                'expires_at' => null,
                'days' => null,
                'granted_by' => null,
            ],
        ];

        if (!$user) {
            return $empty;
        }

        if ($this->cachedUserId === $user->id && $this->cachedContext !== null) {
            return $this->cachedContext;
        }

        if ($this->isExemptFromRestrictions($user)) {
            return $this->rememberContext($user, $empty);
        }

        $condominium = $user->activeCondominium() ?? $user->condominium;

        if (!$this->isEnabled($condominium)) {
            return $this->rememberContext($user, $empty);
        }

        $overdue = $this->getOverdueCharges($user);
        $hasOverdue = $overdue->isNotEmpty();
        $activeOverride = $hasOverdue ? $this->accessOverrideService->getActiveOverride($user) : null;
        $active = $hasOverdue && $activeOverride === null;

        return $this->rememberContext($user, [
            'active' => $active,
            'overdue_charges' => $overdue,
            'restrictions' => $active ? array_values(self::RESTRICTION_LABELS) : [],
            'regularize_url' => route('my-charges.index', ['status' => 'overdue']),
            'total_overdue' => (float) $overdue->sum('amount'),
            'has_overdue_charges' => $hasOverdue,
            'temporary_unlock' => [
                'active' => $activeOverride !== null,
                'expires_at' => $activeOverride?->expires_at,
                'days' => $activeOverride?->days,
                'granted_by' => $activeOverride?->grantedBy?->name,
            ],
        ]);
    }

    /**
     * @param  array<string, mixed>  $context
     * @return array<string, mixed>
     */
    protected function rememberContext(User $user, array $context): array
    {
        $this->cachedUserId = $user->id;
        $this->cachedContext = $context;

        return $context;
    }

    public function blocksModuleAccess(User $user, string $module): bool
    {
        if (!$this->isRestricted($user)) {
            return false;
        }

        return in_array($module, ['marketplace', 'rides'], true);
    }

    public function blocksFeature(User $user, string $feature): bool
    {
        if (!$this->isRestricted($user)) {
            return false;
        }

        return in_array($feature, [
            'reservations.create',
            'service_orders.create',
            'marketplace',
            'rides',
            'assemblies.vote',
        ], true);
    }

    public function denialMessage(string $feature = ''): string
    {
        return 'Seu acesso está restrito por inadimplência. Regularize suas cobranças vencidas para liberar o uso do sistema.';
    }

    protected function isExemptFromRestrictions(User $user): bool
    {
        return $user->isAdmin() || $user->isSindico();
    }

    protected function overdueChargesQuery(User $user)
    {
        return Charge::query()
            ->where('unit_id', $user->unit_id)
            ->where(function ($query) {
                $query->where('status', 'overdue')
                    ->orWhere(function ($pending) {
                        $pending->where('status', 'pending')
                            ->whereDate('due_date', '<', now()->toDateString());
                    });
            });
    }
}
