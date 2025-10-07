<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Package;
use App\Jobs\SendPackageNotification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class PackageController extends Controller
{
    /**
     * Lista encomendas
     */
    public function index(Request $request)
    {
        $user = Auth::user();
        $query = Package::with(['unit', 'registeredBy', 'collectedBy'])
            ->where('condominium_id', $user->condominium_id);

        // Se for morador, mostrar apenas suas encomendas
        if ($user->isMorador() && $user->unit_id) {
            $query->where('unit_id', $user->unit_id);
        }

        // Filtros
        if ($request->has('status')) {
            $query->where('status', $request->status);
        }

        if ($request->has('unit_id')) {
            $query->where('unit_id', $request->unit_id);
        }

        $packages = $query->orderBy('received_at', 'desc')->paginate(15);

        return response()->json($packages);
    }

    /**
     * Registra uma nova encomenda
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'unit_id' => 'required|exists:units,id',
            'sender' => 'nullable|string|max:255',
            'tracking_code' => 'nullable|string|max:255',
            'description' => 'nullable|string',
            'notes' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $user = Auth::user();

        $package = Package::create([
            'condominium_id' => $user->condominium_id,
            'unit_id' => $request->unit_id,
            'registered_by' => $user->id,
            'sender' => $request->sender,
            'tracking_code' => $request->tracking_code,
            'description' => $request->description,
            'received_at' => now(),
            'status' => 'pending',
            'notes' => $request->notes,
            'notification_sent' => false,
        ]);

        // Enviar notificação para o morador
        SendPackageNotification::dispatch($package, 'arrived');

        return response()->json([
            'message' => 'Encomenda registrada com sucesso. Morador será notificado.',
            'package' => $package->load('unit')
        ], 201);
    }

    /**
     * Registra retirada de encomenda
     */
    public function collect(Request $request, $id)
    {
        $package = Package::findOrFail($id);

        // Verificar permissão
        $user = Auth::user();
        if ($package->condominium_id !== $user->condominium_id) {
            return response()->json(['error' => 'Não autorizado'], 403);
        }

        // Verificar se já foi coletada
        if ($package->status === 'collected') {
            return response()->json([
                'error' => 'Esta encomenda já foi retirada'
            ], 400);
        }

        $validator = Validator::make($request->all(), [
            'collected_by' => 'required|exists:users,id',
            'notes' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $package->markAsCollected($request->collected_by);
        
        if ($request->notes) {
            $package->update(['notes' => $request->notes]);
        }

        return response()->json([
            'message' => 'Retirada de encomenda registrada com sucesso',
            'package' => $package->load(['unit', 'collectedBy'])
        ]);
    }

    /**
     * Exibe uma encomenda
     */
    public function show($id)
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
     * Atualiza uma encomenda
     */
    public function update(Request $request, $id)
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
    public function destroy($id)
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
