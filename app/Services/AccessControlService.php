<?php

namespace App\Services;

use App\Jobs\SendAccessNotification;
use App\Jobs\SendProhibitionAlertNotification;
use App\Models\AccessAuthorization;
use App\Models\AccessListGroup;
use App\Models\AccessListItem;
use App\Models\AccessMovement;
use App\Models\ServiceProvider;
use App\Models\Unit;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;

class AccessControlService
{
    public const INDIVIDUAL_VISITOR_PRESETS = [
        ['key' => 'uber_99', 'label' => 'Uber/99', 'icon' => 'bi-car-front-fill'],
        ['key' => 'farmacia', 'label' => 'Farmácia', 'icon' => 'bi-capsule'],
        ['key' => 'ifood', 'label' => 'IFood', 'icon' => 'bi-bag-check-fill'],
        ['key' => 'taxi', 'label' => 'Táxi', 'icon' => 'bi-taxi-front-fill'],
        ['key' => 'homecare', 'label' => 'HomeCare', 'icon' => 'bi-heart-pulse-fill'],
        ['key' => 'bombeiro', 'label' => 'Bombeiro', 'icon' => 'bi-fire'],
        ['key' => 'policia', 'label' => 'Polícia', 'icon' => 'bi-shield-fill-check'],
        ['key' => 'entregador', 'label' => 'Entregador', 'icon' => 'bi-box-seam-fill'],
    ];
    public function computeExpiresAt(Carbon $scheduledAt, ?Carbon $validUntil): Carbon
    {
        if ($validUntil) {
            return $validUntil->copy()->endOfMinute();
        }

        return $scheduledAt->copy()->addHours(24);
    }

    public function resolveNotifyUser(User $creator, ?Unit $unit = null): User
    {
        if ($creator->isAgregado()) {
            $morador = $creator->moradorVinculado;

            if (!$morador) {
                throw ValidationException::withMessages([
                    'user' => 'Agregado não possui morador vinculado.',
                ]);
            }

            return $morador;
        }

        if ($creator->isMorador()) {
            return $creator;
        }

        if ($unit) {
            $unit->loadMissing('morador');

            if ($unit->morador) {
                return $unit->morador;
            }
        }

        return $creator;
    }

    public function assertCanCreateAuthorization(User $user): void
    {
        if ($user->isMorador()) {
            if (!$user->unit_id) {
                throw new AuthorizationException('Morador sem unidade vinculada.');
            }

            return;
        }

        if ($user->isAgregado()) {
            $morador = $user->moradorVinculado;

            if (!$morador || !$morador->agregado_can_authorize_access) {
                throw new AuthorizationException('O morador não autorizou liberações por agregados.');
            }

            if (!$user->unit_id && !$morador->unit_id) {
                throw new AuthorizationException('Unidade não identificada para esta liberação.');
            }

            return;
        }

        if ($user->isSindico() || $user->isAdmin()) {
            return;
        }

        if ($user->can('process_access')) {
            return;
        }

        throw new AuthorizationException('Você não pode criar liberações de acesso.');
    }

    public function assertCanCreateProhibition(User $user): void
    {
        if ($user->can('process_access')) {
            return;
        }

        $this->assertCanCreateAuthorization($user);
    }

    public function resolveUnitForCreator(User $user, ?int $unitId = null): Unit
    {
        if ($unitId && ($user->isSindico() || $user->isAdmin())) {
            $unit = Unit::query()
                ->where('id', $unitId)
                ->where('condominium_id', $user->tenantCondominiumId())
                ->first();

            if (!$unit) {
                throw ValidationException::withMessages(['unit_id' => 'Unidade inválida.']);
            }

            return $unit;
        }

        $resolvedUnitId = $user->unit_id ?? $user->moradorVinculado?->unit_id;

        if (!$resolvedUnitId) {
            throw ValidationException::withMessages(['unit_id' => 'Unidade obrigatória.']);
        }

        $unit = Unit::query()
            ->where('id', $resolvedUnitId)
            ->where('condominium_id', $user->tenantCondominiumId())
            ->first();

        if (!$unit) {
            throw ValidationException::withMessages(['unit_id' => 'Unidade inválida.']);
        }

        return $unit;
    }

    public function createAuthorization(User $creator, array $data): AccessAuthorization
    {
        $this->assertCanCreateAuthorization($creator);

        if (!$creator->can('create_access_authorizations')) {
            throw new AuthorizationException('Sem permissão para criar liberações.');
        }

        $unit = $this->resolveUnitForCreator($creator, isset($data['unit_id']) ? (int) $data['unit_id'] : null);
        $notifyUser = $this->resolveNotifyUser($creator, $unit);
        $scheduledAt = Carbon::parse($data['scheduled_at']);
        $validUntil = !empty($data['valid_until']) ? Carbon::parse($data['valid_until']) : null;

        return AccessAuthorization::create([
            'condominium_id' => $unit->condominium_id,
            'unit_id' => $unit->id,
            'authorized_by' => $creator->id,
            'notify_user_id' => $notifyUser->id,
            'visitor_name' => $data['visitor_name'],
            'authorization_type' => $data['authorization_type'] ?? AccessAuthorization::TYPE_ALLOW,
            'scheduled_at' => $scheduledAt,
            'valid_until' => $validUntil,
            'expires_at' => $this->computeExpiresAt($scheduledAt, $validUntil),
            'status' => AccessAuthorization::STATUS_PENDING,
        ]);
    }

    public function createProhibition(User $creator, array $data): AccessAuthorization
    {
        $this->assertCanCreateProhibition($creator);

        if (!$creator->can('process_access') && !$creator->can('create_access_authorizations')) {
            throw new AuthorizationException('Sem permissão para registrar proibições.');
        }

        $unit = $this->resolveUnitForCreator($creator, isset($data['unit_id']) ? (int) $data['unit_id'] : null);
        $notifyUser = $this->resolveNotifyUser($creator, $unit);
        $neverExpires = (bool) ($data['never_expires'] ?? false);
        $expiresAt = null;

        if (!$neverExpires) {
            if (empty($data['expires_at'])) {
                throw ValidationException::withMessages([
                    'expires_at' => 'Informe a data de expiração ou marque "Nunca expira".',
                ]);
            }

            $expiresAt = Carbon::parse($data['expires_at'])->endOfDay();
        }

        return AccessAuthorization::create([
            'condominium_id' => $unit->condominium_id,
            'unit_id' => $unit->id,
            'authorized_by' => $creator->id,
            'notify_user_id' => $notifyUser->id,
            'visitor_name' => trim($data['visitor_name']),
            'visitor_document' => !empty($data['visitor_document']) ? trim($data['visitor_document']) : null,
            'authorization_type' => AccessAuthorization::TYPE_DENY,
            'never_expires' => $neverExpires,
            'scheduled_at' => null,
            'valid_until' => null,
            'expires_at' => $expiresAt,
            'status' => AccessAuthorization::STATUS_PENDING,
        ]);
    }

    public function createListGroup(User $creator, array $data): AccessListGroup
    {
        $this->assertCanCreateAuthorization($creator);

        if (!$creator->can('manage_access_lists')) {
            throw new AuthorizationException('Sem permissão para criar listas.');
        }

        $unit = $this->resolveUnitForCreator($creator, isset($data['unit_id']) ? (int) $data['unit_id'] : null);
        $notifyUser = $this->resolveNotifyUser($creator, $unit);
        $scheduledAt = Carbon::parse($data['scheduled_at']);
        $validUntil = !empty($data['valid_until']) ? Carbon::parse($data['valid_until']) : null;
        $names = array_values(array_filter(array_map('trim', $data['visitor_names'] ?? [])));

        if (count($names) < 1) {
            throw ValidationException::withMessages(['visitor_names' => 'Informe ao menos um nome.']);
        }

        return DB::transaction(function () use ($creator, $data, $unit, $notifyUser, $scheduledAt, $validUntil, $names) {
            $group = AccessListGroup::create([
                'condominium_id' => $unit->condominium_id,
                'unit_id' => $unit->id,
                'authorized_by' => $creator->id,
                'notify_user_id' => $notifyUser->id,
                'title' => $data['title'],
                'scheduled_at' => $scheduledAt,
                'valid_until' => $validUntil,
                'expires_at' => $this->computeExpiresAt($scheduledAt, $validUntil),
                'status' => AccessListGroup::STATUS_ACTIVE,
                'notes' => $data['notes'] ?? null,
            ]);

            foreach ($names as $name) {
                $group->items()->create(['visitor_name' => $name]);
            }

            return $group->load('items');
        });
    }

    public function createServiceProvider(User $creator, array $data, ?string $photoPath = null): ServiceProvider
    {
        $condominiumId = $creator->tenantCondominiumId();

        if (!$condominiumId) {
            throw new AuthorizationException('Condomínio não identificado.');
        }

        $scope = $data['scope'] ?? ServiceProvider::SCOPE_UNIT;

        if ($scope === ServiceProvider::SCOPE_CONDOMINIUM) {
            if (!$creator->can('manage_condominium_service_providers')) {
                throw new AuthorizationException('Somente síndico pode cadastrar prestadores do condomínio.');
            }

            $unitId = null;
        } else {
            if (!$creator->can('manage_service_providers')) {
                throw new AuthorizationException('Sem permissão para cadastrar prestadores.');
            }

            $unit = $this->resolveUnitForCreator($creator, isset($data['unit_id']) ? (int) $data['unit_id'] : null);
            $unitId = $unit->id;
        }

        return ServiceProvider::create([
            'condominium_id' => $condominiumId,
            'unit_id' => $unitId,
            'authorized_by' => $creator->id,
            'scope' => $scope,
            'name' => $data['name'],
            'document' => $data['document'] ?? null,
            'phone' => $data['phone'] ?? null,
            'company' => $data['company'] ?? null,
            'photo_path' => $photoPath,
            'contract_valid_until' => $data['contract_valid_until'],
            'notes' => $data['notes'] ?? null,
            'is_active' => true,
        ]);
    }

    public function updateServiceProvider(
        User $user,
        ServiceProvider $provider,
        array $data,
        ?string $photoPath = null,
        bool $removePhoto = false
    ): ServiceProvider {
        $this->assertCanManageServiceProvider($user, $provider);

        $scope = $data['scope'] ?? $provider->scope;
        $unitId = $provider->unit_id;

        if ($scope === ServiceProvider::SCOPE_CONDOMINIUM) {
            if (!$user->can('manage_condominium_service_providers')) {
                throw new AuthorizationException('Somente síndico pode definir prestadores do condomínio.');
            }

            $unitId = null;
        } elseif ($scope === ServiceProvider::SCOPE_UNIT) {
            if (!$user->can('manage_service_providers')) {
                throw new AuthorizationException('Sem permissão para editar prestadores.');
            }

            if ($provider->scope === ServiceProvider::SCOPE_UNIT && $provider->unit_id) {
                $unitId = $provider->unit_id;
            } else {
                $unit = $this->resolveUnitForCreator($user, isset($data['unit_id']) ? (int) $data['unit_id'] : null);
                $unitId = $unit->id;
            }
        }

        $updates = [
            'scope' => $scope,
            'unit_id' => $unitId,
            'name' => $data['name'],
            'document' => $data['document'] ?? null,
            'phone' => $data['phone'] ?? null,
            'company' => $data['company'] ?? null,
            'contract_valid_until' => $data['contract_valid_until'],
            'notes' => $data['notes'] ?? null,
            'is_active' => $data['is_active'] ?? $provider->is_active,
        ];

        if ($photoPath) {
            if ($provider->photo_path) {
                Storage::disk('public')->delete($provider->photo_path);
            }

            $updates['photo_path'] = $photoPath;
        } elseif ($removePhoto && $provider->photo_path) {
            Storage::disk('public')->delete($provider->photo_path);
            $updates['photo_path'] = null;
        }

        $provider->update($updates);

        return $provider->fresh(['unit', 'authorizedBy']);
    }

    public function deactivateServiceProvider(User $user, ServiceProvider $provider): ServiceProvider
    {
        $this->assertCanManageServiceProvider($user, $provider);
        $provider->update(['is_active' => false]);

        return $provider->fresh(['unit', 'authorizedBy']);
    }

    protected function assertCanManageServiceProvider(User $user, ServiceProvider $provider): void
    {
        if ($provider->condominium_id !== $user->tenantCondominiumId()) {
            throw new AuthorizationException('Prestador de outro condomínio.');
        }

        if ($user->isSindico() || $user->isAdmin()) {
            return;
        }

        if ((int) $user->id === (int) $provider->authorized_by) {
            return;
        }

        if ($provider->scope === ServiceProvider::SCOPE_UNIT && $provider->unit_id) {
            $unitId = $user->unit_id ?? $user->moradorVinculado?->unit_id;

            if ($unitId && (int) $provider->unit_id === (int) $unitId && $user->can('manage_service_providers')) {
                return;
            }
        }

        throw new AuthorizationException('Não autorizado a gerenciar este prestador.');
    }

    public function expireStaleRecords(int $condominiumId): void
    {
        AccessAuthorization::forCondominium($condominiumId)
            ->where('status', AccessAuthorization::STATUS_PENDING)
            ->where('never_expires', false)
            ->whereNotNull('expires_at')
            ->where('expires_at', '<=', now())
            ->update(['status' => AccessAuthorization::STATUS_EXPIRED]);

        AccessListGroup::forCondominium($condominiumId)
            ->where('status', AccessListGroup::STATUS_ACTIVE)
            ->where('expires_at', '<=', now())
            ->update(['status' => AccessListGroup::STATUS_EXPIRED]);
    }

    public function porteiroPanel(int $condominiumId): array
    {
        $this->expireStaleRecords($condominiumId);

        $authorizations = AccessAuthorization::with(['unit', 'authorizedBy', 'notifyUser'])
            ->forCondominium($condominiumId)
            ->pending()
            ->allows()
            ->orderBy('scheduled_at')
            ->get();

        $prohibitions = AccessAuthorization::with(['unit', 'authorizedBy', 'notifyUser'])
            ->forCondominium($condominiumId)
            ->pending()
            ->prohibitions()
            ->orderBy('visitor_name')
            ->get();

        $lists = AccessListGroup::with(['unit', 'authorizedBy', 'notifyUser', 'items' => fn ($q) => $q->where('status', AccessListItem::STATUS_PENDING)])
            ->forCondominium($condominiumId)
            ->active()
            ->whereHas('items', fn ($q) => $q->where('status', AccessListItem::STATUS_PENDING))
            ->orderBy('scheduled_at')
            ->get();

        $providers = ServiceProvider::with(['unit', 'authorizedBy'])
            ->forCondominium($condominiumId)
            ->activeValid()
            ->orderBy('name')
            ->get();

        $units = Unit::query()
            ->where('condominium_id', $condominiumId)
            ->orderBy('block')
            ->orderBy('number')
            ->get(['id', 'block', 'number']);

        return [
            'authorizations' => $authorizations,
            'prohibitions' => $prohibitions,
            'lists' => $lists,
            'service_providers' => $providers,
            'units' => $units,
            'updated_at' => now()->toIso8601String(),
        ];
    }

    public function alertProhibitionAttempt(
        User $porteiro,
        AccessAuthorization $authorization,
        ?string $notes = null
    ): AccessMovement {
        if (!$porteiro->can('process_access')) {
            throw new AuthorizationException('Sem permissão para alertar morador.');
        }

        if ($authorization->condominium_id !== $porteiro->tenantCondominiumId()) {
            throw new AuthorizationException('Proibição de outro condomínio.');
        }

        if (!$authorization->isDeny()) {
            throw ValidationException::withMessages([
                'authorization' => 'Somente proibições podem gerar alerta ao morador.',
            ]);
        }

        if ($authorization->status !== AccessAuthorization::STATUS_PENDING || $authorization->isExpired()) {
            throw ValidationException::withMessages(['status' => 'Proibição não está ativa ou expirou.']);
        }

        $authorization->loadMissing(['unit', 'authorizedBy', 'notifyUser']);

        $movement = $this->recordMovement(
            $authorization->condominium_id,
            $authorization->unit_id,
            $authorization->notify_user_id,
            $authorization->authorized_by,
            $porteiro,
            AccessMovement::SOURCE_AUTHORIZATION,
            $authorization->id,
            AccessMovement::ACTION_DENIED,
            $authorization->visitor_name,
            $authorization->unit?->full_identifier,
            [
                'authorization_type' => AccessAuthorization::TYPE_DENY,
                'prohibition_alert' => true,
                'prohibited_by' => $authorization->authorizedBy?->name,
                'visitor_document' => $authorization->visitor_document,
                'porteiro_notes' => $notes,
            ]
        );

        SendProhibitionAlertNotification::dispatchSync($movement);

        return $movement;
    }

    public function processAuthorization(
        User $porteiro,
        AccessAuthorization $authorization,
        string $action,
        ?string $notes = null,
        bool $earlyEntryConfirmed = false
    ): AccessMovement {
        if (!$porteiro->can('process_access')) {
            throw new AuthorizationException('Sem permissão para processar acesso.');
        }

        if ($authorization->condominium_id !== $porteiro->tenantCondominiumId()) {
            throw new AuthorizationException('Liberação de outro condomínio.');
        }

        if ($authorization->status !== AccessAuthorization::STATUS_PENDING || $authorization->isExpired()) {
            throw ValidationException::withMessages(['status' => 'Liberação não está pendente ou expirou.']);
        }

        if ($authorization->isDeny()) {
            throw ValidationException::withMessages([
                'authorization' => 'Proibições devem ser tratadas na aba Proibidos com "Alertar morador".',
            ]);
        }

        $scheduleMetadata = $this->buildEntryScheduleMetadata(
            $authorization->scheduled_at,
            $action,
            $earlyEntryConfirmed
        );

        $status = $action === AccessMovement::ACTION_ENTERED
            ? AccessAuthorization::STATUS_ENTERED
            : AccessAuthorization::STATUS_DENIED;

        $authorization->update([
            'status' => $status,
            'processed_by' => $porteiro->id,
            'processed_at' => now(),
            'porteiro_notes' => $notes,
        ]);

        $movement = $this->recordMovement(
            $authorization->condominium_id,
            $authorization->unit_id,
            $authorization->notify_user_id,
            $authorization->authorized_by,
            $porteiro,
            AccessMovement::SOURCE_AUTHORIZATION,
            $authorization->id,
            $action,
            $authorization->visitor_name,
            $authorization->unit?->full_identifier,
            array_merge([
                'authorization_type' => $authorization->authorization_type,
                'unit' => $authorization->unit?->full_identifier,
            ], $scheduleMetadata)
        );

        SendAccessNotification::dispatchSync($movement);

        return $movement;
    }

    public function processListItem(
        User $porteiro,
        AccessListItem $item,
        string $action,
        ?string $notes = null,
        bool $earlyEntryConfirmed = false
    ): AccessMovement {
        if (!$porteiro->can('process_access')) {
            throw new AuthorizationException('Sem permissão.');
        }

        $group = $item->group()->firstOrFail();

        if ($group->condominium_id !== $porteiro->tenantCondominiumId()) {
            throw new AuthorizationException('Lista de outro condomínio.');
        }

        if ($item->status !== AccessListItem::STATUS_PENDING) {
            throw ValidationException::withMessages(['status' => 'Convidado já processado.']);
        }

        if ($group->status !== AccessListGroup::STATUS_ACTIVE || $group->expires_at->isPast()) {
            throw ValidationException::withMessages(['status' => 'Lista expirada ou inativa.']);
        }

        $scheduleMetadata = $this->buildEntryScheduleMetadata(
            $group->scheduled_at,
            $action,
            $earlyEntryConfirmed
        );

        $item->update([
            'status' => $action === AccessMovement::ACTION_ENTERED
                ? AccessListItem::STATUS_ENTERED
                : AccessListItem::STATUS_DENIED,
            'processed_by' => $porteiro->id,
            'processed_at' => now(),
            'porteiro_notes' => $notes,
        ]);

        if ($group->items()->where('status', AccessListItem::STATUS_PENDING)->doesntExist()) {
            $group->update(['status' => AccessListGroup::STATUS_COMPLETED]);
        }

        $movement = $this->recordMovement(
            $group->condominium_id,
            $group->unit_id,
            $group->notify_user_id,
            $group->authorized_by,
            $porteiro,
            AccessMovement::SOURCE_LIST_ITEM,
            $item->id,
            $action,
            $item->visitor_name,
            $group->title,
            array_merge([
                'list_title' => $group->title,
                'unit' => $group->unit?->full_identifier,
            ], $scheduleMetadata)
        );

        SendAccessNotification::dispatchSync($movement);

        return $movement;
    }

    public function processServiceProviderEntry(User $porteiro, ServiceProvider $provider, ?string $notes = null): AccessMovement
    {
        if (!$porteiro->can('process_access')) {
            throw new AuthorizationException('Sem permissão.');
        }

        if ($provider->condominium_id !== $porteiro->tenantCondominiumId()) {
            throw new AuthorizationException('Prestador de outro condomínio.');
        }

        if (!$provider->is_active || !$provider->isContractValid()) {
            throw ValidationException::withMessages(['provider' => 'Prestador inativo ou contrato vencido.']);
        }

        $provider->load(['unit.morador']);

        $notifyUserId = $provider->scope === ServiceProvider::SCOPE_CONDOMINIUM
            ? $provider->authorized_by
            : ($provider->unit?->morador?->id ?? $provider->authorized_by);

        $movement = $this->recordMovement(
            $provider->condominium_id,
            $provider->unit_id,
            $notifyUserId,
            $provider->authorized_by,
            $porteiro,
            AccessMovement::SOURCE_SERVICE_PROVIDER,
            $provider->id,
            AccessMovement::ACTION_ENTERED,
            $provider->name,
            $provider->company,
            [
                'scope' => $provider->scope,
                'company' => $provider->company,
                'unit' => $provider->unit?->full_identifier,
            ]
        );

        SendAccessNotification::dispatchSync($movement);

        return $movement;
    }

    protected function buildEntryScheduleMetadata(Carbon $scheduledAt, string $action, bool $earlyEntryConfirmed): array
    {
        $metadata = [
            'scheduled_at' => $scheduledAt->toIso8601String(),
            'early_entry' => false,
        ];

        if ($action !== AccessMovement::ACTION_ENTERED) {
            return $metadata;
        }

        if (now()->lt($scheduledAt)) {
            if (!$earlyEntryConfirmed) {
                throw ValidationException::withMessages([
                    'early_entry_confirmed' => 'Entrada antes do horário liberado. Avise o morador e confirme que ele autorizou a entrada antecipada.',
                ]);
            }

            $metadata['early_entry'] = true;
            $metadata['early_entry_confirmed'] = true;
            $metadata['early_entry_confirmed_by_porteiro_at'] = now()->toIso8601String();
        }

        return $metadata;
    }

    protected function recordMovement(
        int $condominiumId,
        ?int $unitId,
        int $notifyUserId,
        ?int $authorizedBy,
        User $porteiro,
        string $sourceType,
        int $sourceId,
        string $action,
        string $visitorName,
        ?string $referenceLabel,
        array $metadata = []
    ): AccessMovement {
        return AccessMovement::create([
            'condominium_id' => $condominiumId,
            'unit_id' => $unitId,
            'notify_user_id' => $notifyUserId,
            'authorized_by' => $authorizedBy,
            'processed_by' => $porteiro->id,
            'source_type' => $sourceType,
            'source_id' => $sourceId,
            'action' => $action,
            'visitor_name' => $visitorName,
            'reference_label' => $referenceLabel,
            'metadata' => $metadata,
            'occurred_at' => now(),
        ]);
    }

    public function listMovements(int $condominiumId, array $filters = []): Collection
    {
        $query = AccessMovement::with(['unit', 'notifyUser', 'authorizedBy', 'processedBy'])
            ->forCondominium($condominiumId)
            ->orderByDesc('occurred_at');

        if (!empty($filters['from'])) {
            $query->whereDate('occurred_at', '>=', $filters['from']);
        }

        if (!empty($filters['to'])) {
            $query->whereDate('occurred_at', '<=', $filters['to']);
        }

        if (!empty($filters['unit_id'])) {
            $query->where('unit_id', $filters['unit_id']);
        }

        return $query->limit(500)->get();
    }

    public function cancelAuthorization(User $user, AccessAuthorization $authorization): AccessAuthorization
    {
        if ($authorization->notify_user_id !== $user->id && $authorization->authorized_by !== $user->id && !$user->isSindico() && !$user->isAdmin()) {
            throw new AuthorizationException('Não autorizado.');
        }

        if ($authorization->status !== AccessAuthorization::STATUS_PENDING) {
            throw ValidationException::withMessages(['status' => 'Somente liberações pendentes podem ser canceladas.']);
        }

        $authorization->update(['status' => AccessAuthorization::STATUS_CANCELLED]);

        return $authorization;
    }

    public function updateListGroup(User $user, AccessListGroup $group, array $data): AccessListGroup
    {
        $this->assertCanManageAccessResource($user, (int) $group->authorized_by, (int) $group->notify_user_id);

        if ($group->status !== AccessListGroup::STATUS_ACTIVE) {
            throw ValidationException::withMessages(['status' => 'Somente listas ativas podem ser editadas.']);
        }

        $scheduledAt = Carbon::parse($data['scheduled_at']);
        $validUntil = !empty($data['valid_until']) ? Carbon::parse($data['valid_until']) : null;

        $group->update([
            'title' => $data['title'],
            'scheduled_at' => $scheduledAt,
            'valid_until' => $validUntil,
            'expires_at' => $this->computeExpiresAt($scheduledAt, $validUntil),
            'notes' => $data['notes'] ?? $group->notes,
        ]);

        if (array_key_exists('visitor_names', $data)) {
            $names = array_values(array_filter(array_map('trim', $data['visitor_names'] ?? [])));

            if (count($names) < 1) {
                throw ValidationException::withMessages(['visitor_names' => 'Informe ao menos um convidado.']);
            }

            $hasProcessed = $group->items()
                ->whereIn('status', [AccessListItem::STATUS_ENTERED, AccessListItem::STATUS_DENIED])
                ->exists();

            if ($hasProcessed) {
                throw ValidationException::withMessages([
                    'visitor_names' => 'Não é possível alterar convidados após o porteiro registrar entradas.',
                ]);
            }

            $group->items()->delete();

            foreach ($names as $name) {
                $group->items()->create(['visitor_name' => $name]);
            }
        }

        return $group->fresh(['unit', 'items']);
    }

    public function cancelListGroup(User $user, AccessListGroup $group): AccessListGroup
    {
        $this->assertCanManageAccessResource($user, (int) $group->authorized_by, (int) $group->notify_user_id);

        if ($group->status !== AccessListGroup::STATUS_ACTIVE) {
            throw ValidationException::withMessages(['status' => 'Somente listas ativas podem ser canceladas.']);
        }

        $group->update(['status' => AccessListGroup::STATUS_CANCELLED]);

        return $group;
    }

    protected function assertCanManageAccessResource(User $user, int $authorizedBy, int $notifyUserId): void
    {
        if (
            $user->id !== $authorizedBy
            && $user->id !== $notifyUserId
            && !$user->isSindico()
            && !$user->isAdmin()
        ) {
            throw new AuthorizationException('Não autorizado.');
        }
    }
}
