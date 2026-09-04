<?php

namespace App\Services;

use App\Models\Charge;
use App\Models\Condominium;
use App\Models\User;
use Illuminate\Support\Collection;

class DefaulterRestrictionService
{
    public const RESTRICTION_LABELS = [
        'reservations' => 'Realizar novas reservas de espaços',
        'service_orders' => 'Abrir novas ordens de serviço',
        'marketplace' => 'Anunciar e visualizar o marketplace',
        'rides' => 'Visualizar e participar de caronas',
        'assemblies_vote' => 'Votar em assembleias',
    ];

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
        if (!$user || $this->isExemptFromRestrictions($user)) {
            return false;
        }

        $condominium = $user->activeCondominium() ?? $user->condominium;

        if (!$this->isEnabled($condominium)) {
            return false;
        }

        return $this->hasOverdueCharges($user);
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
     *     total_overdue: float
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
        ];

        if (!$user) {
            return $empty;
        }

        $overdue = $this->getOverdueCharges($user);
        $active = $this->isRestricted($user);

        return [
            'active' => $active,
            'overdue_charges' => $overdue,
            'restrictions' => $active ? array_values(self::RESTRICTION_LABELS) : [],
            'regularize_url' => route('my-charges.index', ['status' => 'overdue']),
            'total_overdue' => (float) $overdue->sum('amount'),
        ];
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
