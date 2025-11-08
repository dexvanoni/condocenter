<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Package\CollectPackageRequest;
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

    /**
     * Lista encomendas
     */
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

    /**
     * Registra uma nova encomenda
     */
    public function store(StorePackageRequest $request): JsonResponse
    {
        try {
            $package = $this->packageService->register(
                $request->user(),
                (int) $request->validated('unit_id'),
                $request->validated('type')
            );

        return response()->json([
                'message' => 'Encomenda registrada com sucesso. Moradores foram notificados.',
                'package' => $package,
            ], 201);
        } catch (AuthorizationException $exception) {
            return response()->json(['error' => $exception->getMessage()], 403);
        }
    }

    /**
     * Registra retirada de encomenda
     */
    public function collect(CollectPackageRequest $request, Package $package): JsonResponse
    {
        try {
            $updatedPackage = $this->packageService->markAsCollected($package, $request->user());

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

    /**
     * Exibe uma encomenda
     */
    public function show($id): JsonResponse
    {
        $package = Package::with(['unit', 'registeredBy', 'collectedBy'])
            ->findOrFail($id);

        $user = Auth::user();

        // Verificar permissão
        if ($package->condominium_id !== $user->condominium_id) {
            return response()->json(['error' => 'Não autorizado'], 403);
        }

        // Morador só pode ver suas próprias encomendas
        if ($user->isMorador() && $package->unit_id !== $user->unit_id) {
            return response()->json(['error' => 'Não autorizado'], 403);
        }

        return response()->json($package);
    }

    /**
     * Resumo por unidade para painel do porteiro
     */
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

    /**
     * Busca moradores/agregados por nome ou CPF
     */
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

    /**
     * Atualiza uma encomenda
     */
    public function update(Request $request, $id): JsonResponse
    {
        $package = Package::findOrFail($id);

        // Verificar permissão (apenas porteiros e síndicos)
        $user = Auth::user();
        if (!$user->can('register_packages')) {
            return response()->json(['error' => 'Não autorizado'], 403);
        }

        $validator = Validator::make($request->all(), [
            'sender' => 'sometimes|string|max:255',
            'tracking_code' => 'sometimes|string|max:255',
            'description' => 'sometimes|string',
            'notes' => 'sometimes|string',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $package->update($request->all());

        return response()->json([
            'message' => 'Encomenda atualizada com sucesso',
            'package' => $package
        ]);
    }

    /**
     * Remove uma encomenda
     */
    public function destroy($id): JsonResponse
    {
        $package = Package::findOrFail($id);

        $user = Auth::user();

        // Apenas síndico ou admin pode deletar
        if (!$user->isSindico() && !$user->isAdmin()) {
            return response()->json(['error' => 'Não autorizado'], 403);
        }

        $package->delete();

        return response()->json([
            'message' => 'Encomenda removida com sucesso'
        ]);
    }
}
