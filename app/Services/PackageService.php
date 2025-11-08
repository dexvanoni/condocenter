<?php

namespace App\Services;

use App\Jobs\SendPackageNotification;
use App\Models\Package;
use App\Models\Unit;
use App\Models\User;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;
use Illuminate\Validation\ValidationException;

class PackageService
{
    /**
     * Lista encomendas visíveis para o usuário autenticado
     */
    public function listPackages(User $user, array $filters = []): LengthAwarePaginator
    {
        $query = Package::with(['unit', 'registeredBy', 'collectedBy'])
            ->byCondominium($user->condominium_id);

        if ($user->isMorador() || $user->isAgregado()) {
            $unitId = $user->unit_id ?? $user->moradorVinculado?->unit_id;
            if ($unitId) {
                $query->forUnit($unitId);
            } else {
                $query->whereRaw('1 = 0');
            }
        }

        if (!empty($filters['status'])) {
            $statuses = array_intersect((array) $filters['status'], Package::STATUSES);
            if (!empty($statuses)) {
                $query->whereIn('status', $statuses);
            }
        }

        if (!empty($filters['type'])) {
            $types = array_intersect((array) $filters['type'], Package::TYPES);
            if (!empty($types)) {
                $query->whereIn('type', $types);
            }
        }

        if (!empty($filters['unit_id'])) {
            $query->forUnit($filters['unit_id']);
        }

        if (!empty($filters['search'])) {
            $term = trim($filters['search']);
            $query->where(function (Builder $builder) use ($term) {
                $builder
                    ->whereHas('unit', function (Builder $unitQuery) use ($term) {
                        $unitQuery->where('number', 'like', "%{$term}%")
                            ->orWhere('block', 'like', "%{$term}%");
                    })
                    ->orWhereHas('unit.users', function (Builder $userQuery) use ($term) {
                        $userQuery->where('name', 'like', "%{$term}%")
                            ->orWhere('cpf', 'like', "%{$term}%");
                    });
            });
        }

        $perPage = (int) ($filters['per_page'] ?? 15);
        $perPage = max(1, min(50, $perPage));

        return $query->orderByDesc('received_at')->paginate($perPage);
    }

    /**
     * Registra nova encomenda para uma unidade
     */
    public function register(User $porteiro, int $unitId, string $type): Package
    {
        if (!$porteiro->can('register_packages')) {
            throw new AuthorizationException('Você não tem permissão para registrar encomendas.');
        }

        $unit = Unit::query()
            ->byCondominium($porteiro->condominium_id)
            ->findOrFail($unitId);

        $package = Package::create([
            'condominium_id' => $porteiro->condominium_id,
            'unit_id' => $unit->id,
            'registered_by' => $porteiro->id,
            'type' => $type,
            'received_at' => now(),
            'status' => Package::STATUS_PENDING,
            'notification_sent' => false,
        ]);

        $package->load(['unit', 'registeredBy']);

        SendPackageNotification::dispatch($package->fresh(), 'arrived');

        return $package;
    }

    /**
     * Marca encomenda como retirada
     */
    public function markAsCollected(Package $package, User $porteiro): Package
    {
        if (!$porteiro->can('register_packages')) {
            throw new AuthorizationException('Você não tem permissão para registrar retiradas.');
        }

        if ($package->condominium_id !== $porteiro->condominium_id) {
            throw new AuthorizationException('Encomenda não pertence ao seu condomínio.');
        }

        if ($package->isCollected()) {
            throw ValidationException::withMessages([
                'status' => 'Esta encomenda já foi marcada como retirada.',
            ]);
        }

        $package->markAsCollected($porteiro->id);
        $package->load(['unit', 'collectedBy']);

        SendPackageNotification::dispatch($package->fresh(), 'collected');

        return $package;
    }

    /**
     * Retorna resumo das unidades com contagem de encomendas
     */
    public function summarizeUnits(User $user, array $filters = []): Collection
    {
        $query = Unit::query()
            ->withCount([
                'packages as pending_packages_count' => fn (Builder $q) => $q->pending(),
            ])
            ->with([
                'packages' => fn ($q) => $q->pending()->orderByDesc('received_at'),
                'users' => fn ($q) => $q->select('id', 'name', 'unit_id', 'cpf')
                    ->whereHas('roles', fn ($role) => $role->whereIn('name', ['Morador', 'Agregado'])),
            ])
            ->where('condominium_id', $user->condominium_id)
            ->orderBy('block')
            ->orderBy('number');

        if (!empty($filters['search'])) {
            $term = trim($filters['search']);
            $sanitizedCpf = preg_replace('/\D+/', '', $term);

            $query->where(function (Builder $builder) use ($term, $sanitizedCpf) {
                $builder
                    ->where('number', 'like', "%{$term}%")
                    ->orWhere('block', 'like', "%{$term}%")
                    ->orWhereHas('users', function (Builder $userQuery) use ($term, $sanitizedCpf) {
                        $userQuery->where(function (Builder $conditions) use ($term, $sanitizedCpf) {
                            $conditions
                                ->where('name', 'like', "%{$term}%")
                                ->orWhere('cpf', 'like', "%{$term}%");

                            if (!empty($sanitizedCpf) && $sanitizedCpf !== $term) {
                                $conditions->orWhere('cpf', 'like', "%{$sanitizedCpf}%");
                            }
                        });
                    });
            });
        }

        return $query->get()->map(function (Unit $unit) {
            $pendingPackages = $unit->packages->map(function (Package $package) {
                return [
                    'id' => $package->id,
                    'type' => $package->type,
                    'type_label' => $package->type_label,
                    'received_at' => $package->received_at,
                ];
            })->values();

            $residents = $unit->users->map(fn (User $resident) => [
                'id' => $resident->id,
                'name' => $resident->name,
                'cpf' => $resident->cpf,
            ])->values();

            return [
                'id' => $unit->id,
                'block' => $unit->block,
                'number' => $unit->number,
                'pending_packages_count' => $unit->pending_packages_count,
                'pending_packages' => $pendingPackages,
                'residents' => $residents,
            ];
        });
    }

    /**
     * Busca moradores e agregados por nome ou CPF
     */
    public function searchResidents(User $user, string $term): Collection
    {
        $term = trim($term);

        if ($term === '') {
            return collect();
        }

        return User::query()
            ->select('id', 'name', 'cpf', 'unit_id')
            ->byCondominium($user->condominium_id)
            ->whereHas('roles', fn ($q) => $q->whereIn('name', ['Morador', 'Agregado']))
            ->where(function (Builder $query) use ($term) {
                $query->where('name', 'like', "%{$term}%")
                    ->orWhere('cpf', 'like', "%{$term}%");
            })
            ->with('unit:id,block,number')
            ->limit(20)
            ->get()
            ->map(function (User $resident) {
                return [
                    'id' => $resident->id,
                    'name' => $resident->name,
                    'cpf' => $resident->cpf,
                    'unit' => $resident->unit?->only(['id', 'block', 'number']),
                ];
            });
    }
}

