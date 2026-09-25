<?php

namespace App\Http\Controllers\Organization;

use App\Http\Controllers\Controller;
use App\Models\Assembly;
use App\Models\OccurrenceBookEntry;
use App\Models\Organization;
use App\Models\Package;
use App\Models\Reservation;
use App\Services\ActiveCondominiumService;
use App\Services\OrganizationQuotaService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Schema;
use Illuminate\View\View;

class OrganizationDashboardController extends Controller
{
    public function __construct(
        private ActiveCondominiumService $activeCondominium,
        private OrganizationQuotaService $quota,
    ) {}

    public function __invoke(Request $request): View
    {
        $user = $request->user();
        abort_unless($user, 403);

        $organizationId = $this->activeCondominium->getActiveOrganizationId($user);

        if (!$organizationId) {
            $organizationId = $this->activeCondominium->accessibleOrganizationIds($user)->first();
        }

        abort_unless($organizationId, 403, 'Nenhuma administradora vinculada à sua conta.');

        $organization = Organization::query()->findOrFail($organizationId);
        $this->authorize('view', $organization);

        abort_unless(
            $user->isAdmin() || $organization->isManagementCompany(),
            403,
            'Dashboard disponível apenas para administradoras.'
        );

        $this->activeCondominium->setActiveOrganization($user, (int) $organization->id);
        $this->ensureOperationalRole($user, $organization);

        $condominiums = $organization->condominiums()
            ->withCount(['units', 'users'])
            ->with(['syndics:id,name,email'])
            ->orderBy('name')
            ->get();

        $condoIds = $condominiums->pluck('id');

        $metrics = [
            'condominiums' => $condominiums->count(),
            'units' => (int) $condominiums->sum('units_count'),
            'users' => (int) $condominiums->sum('users_count'),
            'occurrences' => $this->safeCountByCondominium(OccurrenceBookEntry::class, 'occurrence_book_entries', $condoIds),
            'packages' => $this->safeCountByCondominium(Package::class, 'packages', $condoIds),
            'reservations' => $this->safeReservationCount($condoIds),
            'assemblies' => $this->safeCountByCondominium(Assembly::class, 'assemblies', $condoIds),
        ];

        $quota = $this->quota->snapshot($organization, $condominiums);

        return view('organization.dashboard', compact('organization', 'condominiums', 'metrics', 'quota'));
    }

    /** @var array<string, bool> */
    private static array $schemaTableExists = [];

    protected function ensureOperationalRole($user, Organization $organization): void
    {
        if ($user->isAdmin() || $user->hasRole('Síndico')) {
            return;
        }

        $role = $user->organizationRoleFor((int) $organization->id);

        if (in_array($role, [Organization::ROLE_OWNER, Organization::ROLE_ADMIN, Organization::ROLE_MANAGER], true)) {
            $user->assignRole('Síndico');
        }
    }

    /**
     * @param  class-string  $model
     * @param  \Illuminate\Support\Collection<int, int>|array<int>  $condoIds
     */
    protected function safeCountByCondominium(string $model, string $table, $condoIds): int
    {
        if (!$this->tableExists($table) || collect($condoIds)->isEmpty()) {
            return 0;
        }

        try {
            return (int) $model::query()
                ->whereIn('condominium_id', collect($condoIds)->all())
                ->count();
        } catch (\Throwable) {
            return 0;
        }
    }

    /**
     * @param  \Illuminate\Support\Collection<int, int>|array<int>  $condoIds
     */
    protected function safeReservationCount($condoIds): int
    {
        if (!$this->tableExists('reservations') || !$this->tableExists('spaces') || collect($condoIds)->isEmpty()) {
            return 0;
        }

        try {
            return (int) Reservation::query()
                ->whereHas('space', fn ($q) => $q->whereIn('condominium_id', collect($condoIds)->all()))
                ->count();
        } catch (\Throwable) {
            return 0;
        }
    }

    protected function tableExists(string $table): bool
    {
        if (!array_key_exists($table, self::$schemaTableExists)) {
            self::$schemaTableExists[$table] = Schema::hasTable($table);
        }

        return self::$schemaTableExists[$table];
    }
}
