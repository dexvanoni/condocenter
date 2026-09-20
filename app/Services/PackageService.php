<?php

namespace App\Services;

use App\DTO\OcrResult;
use App\Services\Ocr\LabelOcrResultBuilder;
use App\Services\Ocr\OcrEngineResolver;
use App\Services\Ocr\PaddleOcrService;
use App\Services\Ocr\TesseractOcrService;
use App\Support\OcrEngine;
use App\Jobs\SendPackageNotification;
use App\Models\Package;
use App\Models\Unit;
use App\Models\User;
use App\Services\Packages\LabelImagePreprocessor;
use App\Services\Packages\PackageRecipientMatcher;
use App\Services\Packages\PackageSenderDetector;
use App\Support\TextNormalizer;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;

class PackageService
{
    private const TRIVIAL_CODES = [
        '0000', '1111', '2222', '3333', '4444', '5555', '6666', '7777', '8888', '9999',
        '1234', '4321', '0123', '3210',
    ];

    public function __construct(
        private readonly OcrEngineResolver $ocrResolver,
        private readonly LabelImagePreprocessor $preprocessor,
        private readonly PackageRecipientMatcher $matcher,
        private readonly PackageSenderDetector $senderDetector,
        private readonly TesseractOcrService $tesseractOcr,
        private readonly PaddleOcrService $paddleOcr,
        private readonly LabelOcrResultBuilder $labelOcrResultBuilder,
    ) {
    }

    public function listPackages(User $user, array $filters = []): LengthAwarePaginator
    {
        $query = Package::with(['unit', 'registeredBy', 'collectedBy'])
            ->byCondominium($user->tenantCondominiumId());

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
     * @return \Illuminate\Contracts\Pagination\LengthAwarePaginator|Collection<int, Package>
     */
    public function listMovements(User $user, array $filters = [], bool $paginate = false): LengthAwarePaginator|Collection
    {
        if (!$user->can('view_packages')) {
            throw new AuthorizationException('Você não tem permissão para visualizar encomendas.');
        }

        $query = $this->buildMovementsQuery($user, $filters)
            ->orderByDesc('received_at');

        if ($paginate) {
            return $query->paginate(50)->withQueryString();
        }

        return $query->limit(5000)->get();
    }

    /**
     * @return array{total: int, pending: int, collected: int, avg_hours_to_collect: ?float}
     */
    public function movementStatistics(User $user, array $filters = []): array
    {
        if (!$user->can('view_packages')) {
            throw new AuthorizationException('Você não tem permissão para visualizar encomendas.');
        }

        $packages = $this->buildMovementsQuery($user, $filters)->get();

        $collected = $packages->where('status', Package::STATUS_COLLECTED);
        $avgHours = null;

        if ($collected->isNotEmpty()) {
            $totalHours = $collected->sum(function (Package $package) {
                if (!$package->received_at || !$package->collected_at) {
                    return 0;
                }

                return $package->received_at->diffInMinutes($package->collected_at) / 60;
            });

            $avgHours = round($totalHours / $collected->count(), 1);
        }

        return [
            'total' => $packages->count(),
            'pending' => $packages->where('status', Package::STATUS_PENDING)->count(),
            'collected' => $collected->count(),
            'avg_hours_to_collect' => $avgHours,
        ];
    }

    private function buildMovementsQuery(User $user, array $filters): Builder
    {
        $query = Package::with(['unit', 'registeredBy', 'collectedBy', 'identifiedResident'])
            ->byCondominium($user->tenantCondominiumId());

        if (!empty($filters['from'])) {
            $query->whereDate('received_at', '>=', $filters['from']);
        }

        if (!empty($filters['to'])) {
            $query->whereDate('received_at', '<=', $filters['to']);
        }

        if (!empty($filters['status']) && in_array($filters['status'], Package::STATUSES, true)) {
            $query->where('status', $filters['status']);
        }

        if (!empty($filters['unit_id'])) {
            $query->forUnit((int) $filters['unit_id']);
        }

        if (!empty($filters['search'])) {
            $term = trim((string) $filters['search']);
            $query->where(function (Builder $builder) use ($term) {
                $builder
                    ->where('sender', 'like', "%{$term}%")
                    ->orWhere('tracking_code', 'like', "%{$term}%")
                    ->orWhere('picked_up_by_name', 'like', "%{$term}%")
                    ->orWhereHas('unit', function (Builder $unitQuery) use ($term) {
                        $unitQuery->where('number', 'like', "%{$term}%")
                            ->orWhere('block', 'like', "%{$term}%");
                    })
                    ->orWhereHas('unit.users', function (Builder $userQuery) use ($term) {
                        $userQuery->where('name', 'like', "%{$term}%");
                    })
                    ->orWhereHas('registeredBy', fn (Builder $q) => $q->where('name', 'like', "%{$term}%"))
                    ->orWhereHas('collectedBy', fn (Builder $q) => $q->where('name', 'like', "%{$term}%"));
            });
        }

        return $query;
    }

    /**
     * Preview OCR + matching (não persiste encomenda).
     *
     * @return array<string, mixed>
     */
    public function previewLabel(User $porteiro, UploadedFile $image, ?string $barcodeValue = null): array
    {
        if (!$porteiro->can('register_packages')) {
            throw new AuthorizationException('Você não tem permissão para registrar encomendas.');
        }

        @set_time_limit(max(210, (int) config('ocr.paddle_timeout', 120) + 90));

        $condominiumId = (int) $porteiro->tenantCondominiumId();

        $relativePath = $this->preprocessor->storeOriginal($image, $condominiumId);
        $absoluteOriginal = $this->preprocessor->absolutePath($relativePath);
        $processedPaths = [$this->preprocessor->prepareForOcr($absoluteOriginal)];
        $ocr = $this->ocrResolver->forCondominium($condominiumId);
        $ocrEngine = $this->ocrResolver->engineKeyForCondominium($condominiumId);
        $hints = ['user_words' => $this->ocrUserWords($condominiumId)];

        try {
            $results = collect($processedPaths)
                ->map(fn (string $path) => $this->extractPreviewOcr($condominiumId, $path, $hints, $barcodeValue));

            $ocrResult = $results
                ->sortByDesc(fn (\App\DTO\OcrResult $result) => $this->ocrResultQuality($result))
                ->first() ?? new \App\DTO\OcrResult();
        } finally {
            $this->preprocessor->cleanupTemps(
                array_filter($processedPaths, fn (string $path) => $path !== $absoluteOriginal)
            );
        }

        if ($barcodeValue) {
            $barcodeValue = trim($barcodeValue);
            if ($ocrResult->trackingCode === null && $barcodeValue !== '') {
                $ocrResult = new \App\DTO\OcrResult(
                    rawText: $ocrResult->rawText,
                    confidence: $ocrResult->confidence,
                    trackingCode: TextNormalizer::extractTrackingCode($barcodeValue) ?? $barcodeValue,
                    possibleName: $ocrResult->possibleName,
                    possibleAddress: $ocrResult->possibleAddress,
                    possibleUnit: $ocrResult->possibleUnit,
                    possibleBlock: $ocrResult->possibleBlock,
                    possibleSender: $ocrResult->possibleSender ?? $this->senderDetector->detect($barcodeValue),
                    extra: $ocrResult->extra,
                );
            }
        }

        $match = $this->matcher->match($condominiumId, $ocrResult, $barcodeValue);

        $method = Package::METHOD_OCR;
        if ($barcodeValue && !$ocrResult->isEmpty()) {
            $method = Package::METHOD_HYBRID;
        } elseif ($barcodeValue && $ocrResult->isEmpty()) {
            $method = Package::METHOD_BARCODE;
        }

        $porteiro->logActivity('package_label_preview', 'packages', 'Pré-visualização de etiqueta de encomenda', [
            'match_level' => $match['level'],
            'match_confidence' => $match['confidence'],
            'ocr_confidence' => $ocrResult->confidence,
            'identification_method' => $method,
            'label_image_path' => $relativePath,
        ]);

        $ocrError = $ocrResult->extra['error'] ?? null;

        return [
            'ocr' => $ocrResult->toArray(),
            'match' => $match,
            'sender' => $ocrResult->possibleSender,
            'tracking_code' => $ocrResult->trackingCode,
            'identification_method' => $method,
            'label_image_path' => $relativePath,
            'ocr_available' => $ocr->isAvailable(),
            'ocr_engine' => $ocrResult->extra['engine'] ?? $ocrEngine,
            'ocr_engine_configured' => $ocrEngine,
            'error' => $ocrError,
            'message' => $ocrError
                ?: ($match['level'] === 'low'
                    ? 'Não foi possível identificar o destinatário.'
                    : null),
        ];
    }

    /**
     * Match em tempo real a partir do texto OCR do navegador (sem upload de imagem).
     *
     * @return array<string, mixed>
     */
    public function matchLabelText(
        User $porteiro,
        string $rawText,
        ?float $confidence,
        ?string $barcodeValue = null,
        string $engine = 'paddle-js-v6'
    ): array {
        if (!$porteiro->can('register_packages')) {
            throw new AuthorizationException('Você não tem permissão para registrar encomendas.');
        }

        $condominiumId = (int) $porteiro->tenantCondominiumId();
        $ocrResult = $this->labelOcrResultBuilder->fromRawText($rawText, $confidence, [
            'engine' => $engine,
            'client_side' => true,
        ]);
        $ocrResult = $this->mergeBarcodeIntoOcrResult($ocrResult, $barcodeValue);
        $match = $this->matcher->match($condominiumId, $ocrResult, $barcodeValue);

        return [
            'ocr' => $ocrResult->toArray(),
            'match' => $match,
            'sender' => $ocrResult->possibleSender,
            'tracking_code' => $ocrResult->trackingCode,
            'ocr_engine' => $engine,
            'ocr_engine_configured' => $this->ocrResolver->engineKeyForCondominium($condominiumId),
        ];
    }

    /**
     * Confirma preview após OCR no cliente: persiste foto e reutiliza o texto já lido.
     *
     * @return array<string, mixed>
     */
    public function previewLabelFromClientOcr(
        User $porteiro,
        UploadedFile $image,
        string $rawText,
        ?float $confidence,
        ?string $barcodeValue = null,
        string $engine = 'paddle-js-v6'
    ): array {
        if (!$porteiro->can('register_packages')) {
            throw new AuthorizationException('Você não tem permissão para registrar encomendas.');
        }

        $condominiumId = (int) $porteiro->tenantCondominiumId();
        $relativePath = $this->preprocessor->storeOriginal($image, $condominiumId);
        $ocrEngineConfigured = $this->ocrResolver->engineKeyForCondominium($condominiumId);

        $ocrResult = $this->labelOcrResultBuilder->fromRawText($rawText, $confidence, [
            'engine' => $engine,
            'client_side' => true,
        ]);
        $ocrResult = $this->mergeBarcodeIntoOcrResult($ocrResult, $barcodeValue);
        $match = $this->matcher->match($condominiumId, $ocrResult, $barcodeValue);

        $method = Package::METHOD_OCR;
        if ($barcodeValue && !$ocrResult->isEmpty()) {
            $method = Package::METHOD_HYBRID;
        } elseif ($barcodeValue && $ocrResult->isEmpty()) {
            $method = Package::METHOD_BARCODE;
        }

        $porteiro->logActivity('package_label_preview', 'packages', 'Pré-visualização de etiqueta (OCR no navegador)', [
            'match_level' => $match['level'],
            'match_confidence' => $match['confidence'],
            'ocr_confidence' => $ocrResult->confidence,
            'identification_method' => $method,
            'label_image_path' => $relativePath,
            'ocr_engine' => $engine,
        ]);

        $ocrError = $ocrResult->extra['error'] ?? null;

        return [
            'ocr' => $ocrResult->toArray(),
            'match' => $match,
            'sender' => $ocrResult->possibleSender,
            'tracking_code' => $ocrResult->trackingCode,
            'identification_method' => $method,
            'label_image_path' => $relativePath,
            'ocr_available' => true,
            'ocr_engine' => $engine,
            'ocr_engine_configured' => $ocrEngineConfigured,
            'error' => $ocrError,
            'message' => $ocrError
                ?: ($match['level'] === 'low'
                    ? 'Não foi possível identificar o destinatário.'
                    : null),
        ];
    }

    private function mergeBarcodeIntoOcrResult(OcrResult $ocrResult, ?string $barcodeValue): OcrResult
    {
        $barcodeValue = trim((string) $barcodeValue);
        if ($barcodeValue === '' || $ocrResult->trackingCode !== null) {
            return $ocrResult;
        }

        return new OcrResult(
            rawText: $ocrResult->rawText,
            confidence: $ocrResult->confidence,
            trackingCode: TextNormalizer::extractTrackingCode($barcodeValue) ?? $barcodeValue,
            possibleName: $ocrResult->possibleName,
            possibleAddress: $ocrResult->possibleAddress,
            possibleUnit: $ocrResult->possibleUnit,
            possibleBlock: $ocrResult->possibleBlock,
            possibleSender: $ocrResult->possibleSender ?? $this->senderDetector->detect($barcodeValue),
            extra: $ocrResult->extra,
        );
    }

    private function extractPreviewOcr(int $condominiumId, string $path, array $hints, ?string $barcodeValue): OcrResult
    {
        $hints['preview'] = true;

        if (app()->environment('testing')) {
            return $this->ocrResolver->extractWithFallback($condominiumId, $path, $hints);
        }

        $configuredEngine = $this->ocrResolver->engineKeyForCondominium($condominiumId);

        if ($configuredEngine !== OcrEngine::PADDLE) {
            return $this->ocrResolver->extractWithFallback($condominiumId, $path, $hints);
        }

        $tesseractResult = $this->tesseractOcr->isAvailable()
            ? $this->tesseractOcr->extract($path, $hints)
            : null;

        if (
            config('ocr.preview_tesseract_fast_path', true)
            && $tesseractResult !== null
            && $this->previewMatchIsGoodEnough($condominiumId, $tesseractResult, $barcodeValue)
        ) {
            return new OcrResult(
                rawText: $tesseractResult->rawText,
                confidence: $tesseractResult->confidence,
                trackingCode: $tesseractResult->trackingCode,
                possibleName: $tesseractResult->possibleName,
                possibleAddress: $tesseractResult->possibleAddress,
                possibleUnit: $tesseractResult->possibleUnit,
                possibleBlock: $tesseractResult->possibleBlock,
                possibleSender: $tesseractResult->possibleSender,
                extra: array_merge($tesseractResult->extra, [
                    'engine' => OcrEngine::TESSERACT,
                    'preview_fast_path' => true,
                    'ocr_engine_configured' => OcrEngine::PADDLE,
                ]),
            );
        }

        $paddleResult = $this->paddleOcr->extract($path, $hints);
        if (trim($paddleResult->rawText) !== '' && empty($paddleResult->extra['error'])) {
            return $paddleResult;
        }

        if ($tesseractResult !== null && trim($tesseractResult->rawText) !== '' && empty($tesseractResult->extra['error'])) {
            return new OcrResult(
                rawText: $tesseractResult->rawText,
                confidence: $tesseractResult->confidence,
                trackingCode: $tesseractResult->trackingCode,
                possibleName: $tesseractResult->possibleName,
                possibleAddress: $tesseractResult->possibleAddress,
                possibleUnit: $tesseractResult->possibleUnit,
                possibleBlock: $tesseractResult->possibleBlock,
                possibleSender: $tesseractResult->possibleSender,
                extra: array_merge($tesseractResult->extra, [
                    'engine' => OcrEngine::TESSERACT,
                    'engine_fallback_from' => OcrEngine::PADDLE,
                ]),
            );
        }

        return $paddleResult;
    }

    private function previewMatchIsGoodEnough(int $condominiumId, OcrResult $ocrResult, ?string $barcodeValue): bool
    {
        if (trim($ocrResult->rawText) === '' || !empty($ocrResult->extra['error'])) {
            return false;
        }

        $match = $this->matcher->match($condominiumId, $ocrResult, $barcodeValue);
        $level = (string) ($match['level'] ?? 'low');
        $confidence = (float) ($match['confidence'] ?? 0);

        return in_array($level, ['high', 'medium'], true) && $confidence >= 0.55;
    }

    private function ocrResultQuality(\App\DTO\OcrResult $result): float
    {
        $quality = (float) ($result->confidence ?? 0);

        if ($result->possibleName) {
            $quality += 1.0;
        }
        if ($result->possibleAddress) {
            $quality += 0.25;
        }
        if ($result->trackingCode) {
            $quality += 0.35;
        }
        if ($result->possibleUnit || $result->possibleBlock) {
            $quality += 0.25;
        }

        return $quality;
    }

    /**
     * Palavras do cadastro para o Tesseract priorizar nomes reais do condomínio.
     *
     * @return list<string>
     */
    private function ocrUserWords(int $condominiumId): array
    {
        $names = User::query()
            ->byCondominium($condominiumId)
            ->whereHas('roles', fn ($q) => $q->whereIn('name', ['Morador', 'Agregado']))
            ->limit(500)
            ->pluck('name');

        $words = [];
        foreach ($names as $name) {
            foreach (explode(' ', TextNormalizer::normalizeName((string) $name)) as $token) {
                if (mb_strlen($token) >= 3) {
                    $words[] = $token;
                }
            }
        }

        return array_values(array_unique($words));
    }

    /**
     * Confirma destinatário e registra encomenda a partir da leitura de etiqueta.
     *
     * @param  array<string, mixed>  $data
     * @return array{package: Package, pickup_code: string, whatsapp_status: string}
     */
    public function registerFromLabel(User $porteiro, array $data): array
    {
        if (!$porteiro->can('register_packages')) {
            throw new AuthorizationException('Você não tem permissão para registrar encomendas.');
        }

        $condominiumId = (int) $porteiro->tenantCondominiumId();
        $unitId = (int) $data['unit_id'];

        $unit = Unit::query()
            ->byCondominium($condominiumId)
            ->findOrFail($unitId);

        $residentId = isset($data['resident_id']) ? (int) $data['resident_id'] : null;
        if ($residentId) {
            $resident = User::query()
                ->byCondominium($condominiumId)
                ->where('unit_id', $unit->id)
                ->whereHas('roles', fn ($q) => $q->whereIn('name', ['Morador', 'Agregado']))
                ->findOrFail($residentId);
            $residentId = $resident->id;
        }

        $type = $data['type'] ?? Package::TYPE_LEVE;
        $result = $this->createPackageWithPickupCode($porteiro, $unit, [
            'type' => $type,
            'sender' => $data['sender'] ?? null,
            'tracking_code' => $data['tracking_code'] ?? null,
            'description' => $data['description'] ?? null,
            'notes' => $data['notes'] ?? null,
            'label_image_path' => $data['label_image_path'] ?? null,
            'ocr_text' => $data['ocr_text'] ?? null,
            'ocr_confidence' => $data['ocr_confidence'] ?? null,
            'identification_method' => $data['identification_method'] ?? Package::METHOD_OCR,
            'identification_confidence' => $data['identification_confidence'] ?? null,
            'identified_resident_id' => $residentId,
        ], 'package_registered_label');

        return $result;
    }

    /**
     * Registra nova encomenda manualmente.
     *
     * @return array{package: Package, pickup_code: string, whatsapp_status: string}|Package
     */
    public function register(User $porteiro, int $unitId, string $type, array $extra = []): Package
    {
        if (!$porteiro->can('register_packages')) {
            throw new AuthorizationException('Você não tem permissão para registrar encomendas.');
        }

        $unit = Unit::query()
            ->byCondominium($porteiro->tenantCondominiumId() ?? $porteiro->condominium_id)
            ->findOrFail($unitId);

        $result = $this->createPackageWithPickupCode($porteiro, $unit, array_merge([
            'type' => $type,
            'identification_method' => Package::METHOD_MANUAL,
        ], $extra), 'package_registered');

        return $result['package'];
    }

    /**
     * @param  array<string, mixed>  $attributes
     * @return array{package: Package, pickup_code: string, whatsapp_status: string}
     */
    private function createPackageWithPickupCode(User $porteiro, Unit $unit, array $attributes, string $auditAction): array
    {
        $plainCode = null;

        $package = DB::transaction(function () use ($porteiro, $unit, $attributes, $auditAction, &$plainCode) {
            $plainCode = $this->generatePickupCode();

            $package = Package::create([
                'condominium_id' => $unit->condominium_id,
                'unit_id' => $unit->id,
                'registered_by' => $porteiro->id,
                'type' => $attributes['type'],
                'sender' => $attributes['sender'] ?? null,
                'tracking_code' => $attributes['tracking_code'] ?? null,
                'description' => $attributes['description'] ?? null,
                'notes' => $attributes['notes'] ?? null,
                'received_at' => now(),
                'status' => Package::STATUS_PENDING,
                'notification_sent' => false,
                'pickup_code_hash' => Hash::make($plainCode),
                'label_image_path' => $attributes['label_image_path'] ?? null,
                'ocr_text' => $attributes['ocr_text'] ?? null,
                'ocr_confidence' => $attributes['ocr_confidence'] ?? null,
                'identification_method' => $attributes['identification_method'] ?? Package::METHOD_MANUAL,
                'identification_confidence' => $attributes['identification_confidence'] ?? null,
                'identified_resident_id' => $attributes['identified_resident_id'] ?? null,
                'whatsapp_delivery_status' => Package::WHATSAPP_PENDING,
            ]);

            $porteiro->logActivity($auditAction, 'packages', 'Encomenda registrada', [
                'package_id' => $package->id,
                'unit_id' => $unit->id,
                'identification_method' => $package->identification_method,
                'identification_confidence' => $package->identification_confidence,
            ]);

            return $package;
        });

        $package->load(['unit', 'registeredBy']);

        SendPackageNotification::dispatch($package->fresh(), 'arrived', $plainCode)
            ->afterCommit();

        $package = $package->fresh();

        return [
            'package' => $package,
            'pickup_code' => $plainCode,
            'whatsapp_status' => $package->whatsapp_delivery_status ?? Package::WHATSAPP_PENDING,
        ];
    }

    public function generatePickupCode(): string
    {
        do {
            $code = str_pad((string) random_int(0, 9999), 4, '0', STR_PAD_LEFT);
        } while (in_array($code, self::TRIVIAL_CODES, true));

        return $code;
    }

    public function verifyPickupCode(Package $package, string $code): bool
    {
        if (empty($package->pickup_code_hash)) {
            return true; // legado sem senha
        }

        return Hash::check($code, $package->pickup_code_hash);
    }

    /**
     * Localiza uma encomenda pendente pela senha dentro do condomínio ativo.
     * O hash exige comparação em memória, mas somente sobre pendências do tenant.
     */
    public function findPendingByPickupCode(User $porteiro, string $pickupCode): Package
    {
        if (!$porteiro->can('register_packages')) {
            throw new AuthorizationException('Você não tem permissão para registrar retiradas.');
        }

        $package = Package::query()
            ->with(['unit.users' => fn ($query) => $query
                ->select('id', 'name', 'unit_id')
                ->whereHas('roles', fn ($role) => $role->whereIn('name', ['Morador', 'Agregado']))])
            ->byCondominium((int) $porteiro->tenantCondominiumId())
            ->pending()
            ->whereNotNull('pickup_code_hash')
            ->orderByDesc('received_at')
            ->get()
            ->first(fn (Package $candidate) => $this->verifyPickupCode($candidate, $pickupCode));

        if (!$package) {
            throw ValidationException::withMessages([
                'pickup_code' => 'Nenhuma encomenda pendente foi encontrada com esta senha.',
            ]);
        }

        return $package;
    }

    public function markAsCollected(
        Package $package,
        User $porteiro,
        ?string $pickupCode = null,
        ?string $pickedUpByName = null
    ): Package
    {
        if (!$porteiro->can('register_packages')) {
            throw new AuthorizationException('Você não tem permissão para registrar retiradas.');
        }

        if ((int) $package->condominium_id !== (int) $porteiro->tenantCondominiumId()) {
            throw new AuthorizationException('Encomenda não pertence ao seu condomínio.');
        }

        if ($package->isCollected()) {
            throw ValidationException::withMessages([
                'status' => 'Esta encomenda já foi marcada como retirada.',
            ]);
        }

        if ($package->requiresPickupCode()) {
            if ($pickupCode === null || $pickupCode === '') {
                throw ValidationException::withMessages([
                    'pickup_code' => 'Informe a senha de 4 dígitos fornecida pelo morador.',
                ]);
            }

            if (!$this->verifyPickupCode($package, $pickupCode)) {
                throw ValidationException::withMessages([
                    'pickup_code' => 'Senha de retirada inválida.',
                ]);
            }
        }

        $pickedUpByName = filled($pickedUpByName) ? trim($pickedUpByName) : null;

        DB::transaction(function () use ($package, $porteiro, $pickedUpByName) {
            $package->markAsCollected($porteiro->id, $pickedUpByName);

            $porteiro->logActivity('package_collected', 'packages', 'Retirada de encomenda confirmada', [
                'package_id' => $package->id,
                'unit_id' => $package->unit_id,
                'picked_up_by_name' => $pickedUpByName,
                'pickup_code_verified' => $package->requiresPickupCode(),
                'pickup_verified_at' => $package->pickup_verified_at?->toIso8601String(),
            ]);
        });

        $package->load(['unit', 'collectedBy']);

        SendPackageNotification::dispatch($package->fresh(), 'collected')
            ->afterCommit();

        return $package;
    }

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
            ->where('condominium_id', $user->tenantCondominiumId())
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
                    'requires_pickup_code' => $package->requires_pickup_code,
                    'sender' => $package->sender,
                    'tracking_code' => $package->tracking_code,
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

    public function searchResidents(User $user, string $term): Collection
    {
        $term = trim($term);

        if ($term === '') {
            return collect();
        }

        return User::query()
            ->select('id', 'name', 'cpf', 'unit_id')
            ->byCondominium($user->tenantCondominiumId())
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
