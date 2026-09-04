<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Entry;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

class EntryController extends Controller
{
    public function __construct()
    {
        $this->middleware('can:register_entries');
    }

    /**
     * Lista entradas/saídas
     */
    public function index(Request $request)
    {
        $user = Auth::user();

        $query = Entry::with(['unit', 'registeredBy', 'authorizedBy'])
            ->where('condominium_id', $user->tenantCondominiumId());

        if ($request->has('type')) {
            $query->where('type', $request->type);
        }

        if ($request->has('entry_type')) {
            $query->where('entry_type', $request->entry_type);
        }

        if ($request->has('unit_id')) {
            $query->where('unit_id', $request->unit_id);
        }

        if ($request->has('date')) {
            $query->whereDate('entry_time', $request->date);
        }

        $entries = $query->orderBy('entry_time', 'desc')->paginate(20);

        return response()->json($entries);
    }

    /**
     * Registra uma nova entrada
     */
    public function store(Request $request)
    {
        $user = Auth::user();
        $condominiumId = $user->tenantCondominiumId();

        $validator = Validator::make($request->all(), [
            'unit_id' => [
                'required',
                Rule::exists('units', 'id')->where(fn ($query) => $query->where('condominium_id', $condominiumId)),
            ],
            'type' => 'required|in:resident,visitor,service_provider,delivery',
            'visitor_name' => 'required_unless:type,resident|string|max:255',
            'visitor_document' => 'nullable|string|max:50',
            'visitor_phone' => 'nullable|string|max:20',
            'vehicle_plate' => 'nullable|string|max:10',
            'authorized' => 'boolean',
            'notes' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $validated = $validator->validated();

        $entry = Entry::create([
            'condominium_id' => $condominiumId,
            'unit_id' => $validated['unit_id'],
            'registered_by' => $user->id,
            'type' => $validated['type'],
            'visitor_name' => $validated['visitor_name'] ?? null,
            'visitor_document' => $validated['visitor_document'] ?? null,
            'visitor_phone' => $validated['visitor_phone'] ?? null,
            'vehicle_plate' => $validated['vehicle_plate'] ?? null,
            'entry_type' => 'entry',
            'entry_time' => now(),
            'authorized' => $request->boolean('authorized'),
            'notes' => $validated['notes'] ?? null,
        ]);

        return response()->json([
            'message' => 'Entrada registrada com sucesso',
            'entry' => $entry->load('unit'),
        ], 201);
    }

    /**
     * Registra saída
     */
    public function update(Request $request, $id)
    {
        $entry = Entry::findOrFail($id);
        $user = Auth::user();

        if ($entry->condominium_id !== $user->tenantCondominiumId()) {
            return response()->json(['error' => 'Não autorizado'], 403);
        }

        if ($request->has('register_exit') && $request->register_exit) {
            $entry->registerExit();

            return response()->json([
                'message' => 'Saída registrada com sucesso',
                'entry' => $entry,
            ]);
        }

        $validator = Validator::make($request->all(), [
            'visitor_name' => 'sometimes|string|max:255',
            'visitor_document' => 'nullable|string|max:50',
            'visitor_phone' => 'nullable|string|max:20',
            'vehicle_plate' => 'nullable|string|max:10',
            'authorized' => 'boolean',
            'notes' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $entry->update($validator->validated());

        return response()->json([
            'message' => 'Entrada atualizada com sucesso',
            'entry' => $entry,
        ]);
    }

    /**
     * Exibe uma entrada
     */
    public function show($id)
    {
        $entry = Entry::with(['unit', 'registeredBy', 'authorizedBy'])
            ->findOrFail($id);

        if ($entry->condominium_id !== Auth::user()->tenantCondominiumId()) {
            return response()->json(['error' => 'Não autorizado'], 403);
        }

        return response()->json($entry);
    }

    /**
     * Registra saída de uma entrada
     */
    public function registerExit($id)
    {
        $entry = Entry::findOrFail($id);
        $user = Auth::user();

        if ($entry->condominium_id !== $user->tenantCondominiumId()) {
            return response()->json(['error' => 'Não autorizado'], 403);
        }

        $entry->registerExit();

        return response()->json([
            'message' => 'Saída registrada com sucesso',
            'entry' => $entry,
        ]);
    }

    /**
     * Remove uma entrada
     */
    public function destroy($id)
    {
        $entry = Entry::findOrFail($id);
        $user = Auth::user();

        if ($entry->condominium_id !== $user->tenantCondominiumId()) {
            return response()->json(['error' => 'Não autorizado'], 403);
        }

        if (!$user->isSindico() && !$user->isAdmin()) {
            return response()->json(['error' => 'Não autorizado'], 403);
        }

        $entry->delete();

        return response()->json([
            'message' => 'Registro removido com sucesso',
        ]);
    }
}
