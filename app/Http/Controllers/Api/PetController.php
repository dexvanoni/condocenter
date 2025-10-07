<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Pet;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;

class PetController extends Controller
{
    /**
     * Lista pets
     */
    public function index(Request $request)
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();
        
        $query = Pet::with(['unit', 'owner'])
            ->whereHas('unit', function ($q) use ($user) {
                $q->where('condominium_id', $user->condominium_id);
            });

        // Se for morador, mostrar apenas seus pets
        if ($user->isMorador()) {
            $query->where('owner_id', $user->id);
        }

        // Filtros
        if ($request->has('type')) {
            $query->where('type', $request->type);
        }

        if ($request->has('unit_id')) {
            $query->where('unit_id', $request->unit_id);
        }

        $pets = $query->where('is_active', true)->orderBy('name')->get();

        return response()->json($pets);
    }

    /**
     * Cadastra um novo pet
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'type' => 'required|in:dog,cat,bird,other',
            'breed' => 'nullable|string|max:255',
            'color' => 'nullable|string|max:255',
            'birth_date' => 'nullable|date|before:today',
            'size' => 'required|in:small,medium,large',
            'photo' => 'nullable|image|mimes:jpeg,jpg,png|max:2048',
            'observations' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $user = Auth::user();

        // Upload de foto
        $photoPath = null;
        if ($request->hasFile('photo')) {
            $photoPath = $request->file('photo')->store('pets/' . $user->condominium_id, 'public');
        }

        $pet = Pet::create([
            'unit_id' => $user->unit_id,
            'owner_id' => $user->id,
            'name' => $request->name,
            'type' => $request->type,
            'breed' => $request->breed,
            'color' => $request->color,
            'birth_date' => $request->birth_date,
            'size' => $request->size,
            'photo' => $photoPath,
            'observations' => $request->observations,
            'is_active' => true,
        ]);

        return response()->json([
            'message' => 'Pet cadastrado com sucesso',
            'pet' => $pet
        ], 201);
    }

    /**
     * Exibe um pet
     */
    public function show($id)
    {
        $pet = Pet::with(['unit', 'owner'])->findOrFail($id);

        $user = Auth::user();

        // Verificar se pertence ao condomínio
        if ($pet->unit->condominium_id !== $user->condominium_id) {
            return response()->json(['error' => 'Não autorizado'], 403);
        }

        return response()->json($pet);
    }

    /**
     * Atualiza um pet
     */
    public function update(Request $request, $id)
    {
        $pet = Pet::findOrFail($id);

        /** @var \App\Models\User $user */
        $user = Auth::user();

        // Apenas o dono ou síndico pode editar
        if ($pet->owner_id !== $user->id && !$user->isSindico() && !$user->isAdmin()) {
            return response()->json(['error' => 'Não autorizado'], 403);
        }

        $validator = Validator::make($request->all(), [
            'name' => 'sometimes|string|max:255',
            'breed' => 'sometimes|string|max:255',
            'observations' => 'sometimes|string',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        // Upload de nova foto se fornecida
        if ($request->hasFile('photo')) {
            // Deletar foto antiga
            if ($pet->photo) {
                Storage::disk('public')->delete($pet->photo);
            }
            
            $photoPath = $request->file('photo')->store('pets/' . $user->condominium_id, 'public');
            $pet->photo = $photoPath;
        }

        $pet->update($request->except('photo'));

        return response()->json([
            'message' => 'Pet atualizado com sucesso',
            'pet' => $pet
        ]);
    }

    /**
     * Remove um pet
     */
    public function destroy($id)
    {
        $pet = Pet::findOrFail($id);

        /** @var \App\Models\User $user */
        $user = Auth::user();

        // Apenas o dono ou síndico pode deletar
        if ($pet->owner_id !== $user->id && !$user->isSindico() && !$user->isAdmin()) {
            return response()->json(['error' => 'Não autorizado'], 403);
        }

        // Deletar foto
        if ($pet->photo) {
            Storage::disk('public')->delete($pet->photo);
        }

        $pet->delete();

        return response()->json([
            'message' => 'Pet removido com sucesso'
        ]);
    }
}
