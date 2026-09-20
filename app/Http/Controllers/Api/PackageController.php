<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Package\CollectPackageRequest;
use App\Http\Requests\Package\ConfirmLabelPackageRequest;
use App\Http\Requests\Package\FindPackageByPickupCodeRequest;
use App\Http\Requests\Package\MatchLabelTextRequest;
use App\Http\Requests\Package\PreviewLabelClientRequest;
use App\Http\Requests\Package\PreviewLabelRequest;
use App\Http\Requests\Package\StorePackageRequest;
use App\Models\Package;
use App\Services\PackageService;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;

class PackageController extends Controller
{
    public function __construct(
        private readonly PackageService $packageService
    ) {
    }

    public function index(Request $request): JsonResponse
    {
        $user = Auth::user();
        $packages = $this->packageService->listPackages($user, [
            'status' => $request->input('status'),
            'type' => $request->input('type'),
            'unit_id' => $request->input('unit_id'),
            'search' => $request->input('search'),
            'per_page' => $request->input('per_page'),
        ]);

        return response()->json($packages);
    }

    public function store(StorePackageRequest $request): JsonResponse
    {
        try {
            $package = $this->packageService->register(
                $request->user(),
                (int) $request->validated('unit_id'),
                $request->validated('type'),
                [
                    'sender' => $request->validated('sender') ?? null,
                    'tracking_code' => $request->validated('tracking_code') ?? null,
                    'description' => $request->validated('description') ?? null,
                    'notes' => $request->validated('notes') ?? null,
                ]
            );

            return response()->json([
                'message' => 'Encomenda registrada com sucesso. Moradores foram notificados.',
                'package' => $package,
                'whatsapp_status' => $package->whatsapp_delivery_status,
            ], 201);
        } catch (AuthorizationException $exception) {
            return response()->json(['error' => $exception->getMessage()], 403);
        }
    }

    public function previewLabel(PreviewLabelRequest $request): JsonResponse
    {
        try {
            $result = $this->packageService->previewLabel(
                $request->user(),
                $request->file('image'),
                $request->validated('barcode_value')
            );

            return response()->json($result);
        } catch (AuthorizationException $exception) {
            return response()->json(['error' => $exception->getMessage()], 403);
        }
    }

    public function matchLabelText(MatchLabelTextRequest $request): JsonResponse
    {
        try {
            $validated = $request->validated();

            return response()->json($this->packageService->matchLabelText(
                $request->user(),
                (string) $validated['ocr_text'],
                isset($validated['ocr_confidence']) ? (float) $validated['ocr_confidence'] : null,
                $validated['barcode_value'] ?? null,
                (string) ($validated['ocr_engine'] ?? 'paddle-js-v6'),
            ));
        } catch (AuthorizationException $exception) {
            return response()->json(['error' => $exception->getMessage()], 403);
        }
    }

    public function previewLabelClient(PreviewLabelClientRequest $request): JsonResponse
    {
        try {
            $validated = $request->validated();

            return response()->json($this->packageService->previewLabelFromClientOcr(
                $request->user(),
                $request->file('image'),
                (string) $validated['ocr_text'],
                isset($validated['ocr_confidence']) ? (float) $validated['ocr_confidence'] : null,
                $validated['barcode_value'] ?? null,
                (string) ($validated['ocr_engine'] ?? 'paddle-js-v6'),
            ));
        } catch (AuthorizationException $exception) {
            return response()->json(['error' => $exception->getMessage()], 403);
        }
    }

    public function confirmLabel(ConfirmLabelPackageRequest $request): JsonResponse
    {
        try {
            $result = $this->packageService->registerFromLabel(
                $request->user(),
                $request->validated()
            );

            $whatsappStatus = $result['whatsapp_status'];
            $message = $whatsappStatus === Package::WHATSAPP_PENDING
                ? 'Encomenda registrada. WhatsApp pendente.'
                : 'Encomenda registrada com sucesso.';

            return response()->json([
                'message' => $message,
                'package' => $result['package'],
                'whatsapp_status' => $whatsappStatus,
            ], 201);
        } catch (AuthorizationException $exception) {
            return response()->json(['error' => $exception->getMessage()], 403);
        } catch (ValidationException $exception) {
            return response()->json(['errors' => $exception->errors()], 422);
        }
    }

    public function collect(CollectPackageRequest $request, Package $package): JsonResponse
    {
        try {
            $updatedPackage = $this->packageService->markAsCollected(
                $package,
                $request->user(),
                $request->validated('pickup_code') ?? null,
                $request->validated('picked_up_by_name') ?? null
            );

            return response()->json([
                'message' => 'Retirada de encomenda registrada com sucesso',
                'package' => $updatedPackage,
            ]);
        } catch (AuthorizationException $exception) {
            return response()->json(['error' => $exception->getMessage()], 403);
        } catch (ValidationException $exception) {
            return response()->json(['errors' => $exception->errors()], 422);
        }
    }

    public function findByPickupCode(FindPackageByPickupCodeRequest $request): JsonResponse
    {
        try {
            $package = $this->packageService->findPendingByPickupCode(
                $request->user(),
                $request->validated('pickup_code')
            );

            return response()->json([
                'package' => [
                    'id' => $package->id,
                    'type_label' => $package->type_label,
                    'sender' => $package->sender,
                    'tracking_code' => $package->tracking_code,
                    'received_at' => $package->received_at,
                    'unit' => $package->unit?->only(['id', 'block', 'number']),
                    'residents' => $package->unit?->users
                        ->map(fn ($resident) => ['id' => $resident->id, 'name' => $resident->name])
                        ->values()
                        ->all() ?? [],
                ],
            ]);
        } catch (AuthorizationException $exception) {
            return response()->json(['error' => $exception->getMessage()], 403);
        } catch (ValidationException $exception) {
            return response()->json(['errors' => $exception->errors()], 422);
        }
    }

    public function show($id): JsonResponse
    {
        $package = Package::with(['unit', 'registeredBy', 'collectedBy'])
            ->findOrFail($id);

        $user = Auth::user();

        if ($package->condominium_id !== $user->tenantCondominiumId()) {
            return response()->json(['error' => 'Não autorizado'], 403);
        }

        if ($user->isMorador() && $package->unit_id !== $user->unit_id) {
            return response()->json(['error' => 'Não autorizado'], 403);
        }

        return response()->json($package);
    }

    public function summary(Request $request): JsonResponse
    {
        $user = $request->user();

        if (!$user->can('register_packages')) {
            return response()->json(['error' => 'Não autorizado'], 403);
        }

        $units = $this->packageService->summarizeUnits($user, [
            'search' => $request->input('search'),
        ]);

        return response()->json(['data' => $units]);
    }

    public function residents(Request $request): JsonResponse
    {
        $user = $request->user();

        if (!$user->can('register_packages')) {
            return response()->json(['error' => 'Não autorizado'], 403);
        }

        $results = $this->packageService->searchResidents(
            $user,
            (string) $request->input('search', '')
        );

        return response()->json(['data' => $results]);
    }

    public function update(Request $request, $id): JsonResponse
    {
        $package = Package::findOrFail($id);

        $user = Auth::user();
        if (!$user->can('register_packages')) {
            return response()->json(['error' => 'Não autorizado'], 403);
        }

        if ((int) $package->condominium_id !== (int) $user->tenantCondominiumId()) {
            return response()->json(['error' => 'Não autorizado'], 403);
        }

        $validated = $request->validate([
            'sender' => 'sometimes|string|max:255',
            'tracking_code' => 'sometimes|string|max:255',
            'description' => 'sometimes|string',
            'notes' => 'sometimes|string',
        ]);

        $package->update($validated);

        return response()->json([
            'message' => 'Encomenda atualizada com sucesso',
            'package' => $package,
        ]);
    }

    public function destroy($id): JsonResponse
    {
        $package = Package::findOrFail($id);

        $user = Auth::user();

        if (!$user->isSindico() && !$user->isAdmin()) {
            return response()->json(['error' => 'Não autorizado'], 403);
        }

        if ((int) $package->condominium_id !== (int) $user->tenantCondominiumId()) {
            return response()->json(['error' => 'Não autorizado'], 403);
        }

        $package->delete();

        return response()->json([
            'message' => 'Encomenda removida com sucesso',
        ]);
    }
}
