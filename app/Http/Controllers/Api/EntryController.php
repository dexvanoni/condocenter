<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Entry;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class EntryController extends Controller
{
    /**
     * Lista entradas/saídas
     */
    public function index(Request $request)
    {
        $user = Auth::user();
        
        $query = Entry::with(['unit', 'registeredBy', 'authorizedBy'])
            ->where('condominium_id', $user->condominium_id);

        // Filtros
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
        $validator = Validator::make($request->all(), [
            'unit_id' => 'required|exists:units,id',
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

        $user = Auth::user();

        $entry = Entry::create([
            'condominium_id' => $user->condominium_id,
            'unit_id' => $request->unit_id,
            'registered_by' => $user->id,
            'type' => $request->type,
            'visitor_name' => $request->visitor_name,
            'visitor_document' => $request->visitor_document,
            'visitor_phone' => $request->visitor_phone,
            'vehicle_plate' => $request->vehicle_plate,
            'entry_type' => 'entry',
            'entry_time' => now(),
            'authorized' => $request->boolean('authorized'),
            'notes' => $request->notes,
        ]);

        return response()->json([
            'message' => 'Entrada registrada com sucesso',
            'entry' => $entry->load('unit')
        ], 201);
    }

    /**
     * Registra saída
     */
    public function update(Request $request, $id)
    {
        $entry = Entry::findOrFail($id);

        // Verificar permissão
        if ($entry->condominium_id !== Auth::user()->condominium_id) {
            return response()->json(['error' => 'Não autorizado'], 403);
        }

        // Se for registrar saída
        if ($request->has('register_exit') && $request->register_exit) {
            $entry->registerExit();
            
            return response()->json([
                'message' => 'Saída registrada com sucesso',
                'entry' => $entry
            ]);
        }

        $entry->update($request->all());

        return response()->json([
            'message' => 'Entrada atualizada com sucesso',
            'entry' => $entry
        ]);
    }

    /**
     * Exibe uma entrada
     */
    public function show($id)
    {
        $entry = Entry::with(['unit', 'registeredBy', 'authorizedBy'])
            ->findOrFail($id);

        // Verificar permissão
        if ($entry->condominium_id !== Auth::user()->condominium_id) {
            return response()->json(['error' => 'Não autorizado'], 403);
        }

        return response()->json($entry);
    }

    /**
     * Remove uma entrada
     */
    public function destroy($id)
    {
        $entry = Entry::findOrFail($id);

        /** @var \App\Models\User $user */
        $user = Auth::user();

        // Apenas síndico ou admin pode deletar
        if (!$user->isSindico() && !$user->isAdmin()) {
            return response()->json(['error' => 'Não autorizado'], 403);
        }

        $entry->delete();

        return response()->json([
            'message' => 'Registro removido com sucesso'
        ]);
    }
}
