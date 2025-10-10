<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Space;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class SpaceController extends Controller
{
    /**
     * Lista espaços
     */
    public function index(Request $request)
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();
        
        $query = Space::where('condominium_id', $user->condominium_id);

        // Filtros
        if ($request->has('type')) {
            $query->where('type', $request->type);
        }

        if ($request->has('is_active')) {
            $query->where('is_active', $request->boolean('is_active'));
        } else {
            $query->where('is_active', true);
        }

        $spaces = $query->orderBy('name')->get();

        return response()->json($spaces);
    }

    /**
     * Cria um novo espaço
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'type' => 'required|in:party_hall,bbq,pool,sports_court,gym,meeting_room,other',
            'capacity' => 'nullable|integer|min:1',
            'price_per_hour' => 'required|numeric|min:0',
            'requires_approval' => 'boolean',
            'max_hours_per_reservation' => 'required|integer|min:1|max:24',
            'max_reservations_per_month_per_user' => 'required|integer|min:1',
            'available_from' => 'required|date_format:H:i',
            'available_until' => 'required|date_format:H:i',
            'rules' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        /** @var \App\Models\User $user */
        $user = Auth::user();

        // Verificar permissão
        if (!$user->can('manage_spaces')) {
            return response()->json(['error' => 'Sem permissão'], 403);
        }

        $space = Space::create([
            'condominium_id' => $user->condominium_id,
            'name' => $request->name,
            'description' => $request->description,
            'type' => $request->type,
            'capacity' => $request->capacity,
            'price_per_hour' => $request->price_per_hour,
            'requires_approval' => $request->boolean('requires_approval'),
            'max_hours_per_reservation' => $request->max_hours_per_reservation,
            'max_reservations_per_month_per_user' => $request->max_reservations_per_month_per_user,
            'available_from' => $request->available_from,
            'available_until' => $request->available_until,
            'is_active' => true,
            'rules' => $request->rules,
        ]);

        return response()->json([
            'message' => 'Espaço criado com sucesso',
            'space' => $space
        ], 201);
    }

    /**
     * Exibe um espaço
     */
    public function show($id)
    {
        $space = Space::with(['reservations' => function ($q) {
            $q->where('reservation_date', '>=', now())
              ->where('status', 'approved')
              ->orderBy('reservation_date');
        }])->findOrFail($id);

        /** @var \App\Models\User $user */
        $user = Auth::user();

        if ($space->condominium_id !== $user->condominium_id) {
            return response()->json(['error' => 'Não autorizado'], 403);
        }

        return response()->json($space);
    }

    /**
     * Atualiza um espaço
     */
    public function update(Request $request, $id)
    {
        $space = Space::findOrFail($id);

        /** @var \App\Models\User $user */
        $user = Auth::user();

        if (!$user->can('manage_spaces')) {
            return response()->json(['error' => 'Sem permissão'], 403);
        }

        $space->update($request->all());

        return response()->json([
            'message' => 'Espaço atualizado com sucesso',
            'space' => $space
        ]);
    }

    /**
     * Remove um espaço
     */
    public function destroy($id)
    {
        $space = Space::findOrFail($id);

        /** @var \App\Models\User $user */
        $user = Auth::user();

        if (!$user->can('manage_spaces')) {
            return response()->json(['error' => 'Sem permissão'], 403);
        }

        // Verificar se tem reservas futuras
        $futureReservations = $space->reservations()
            ->where('reservation_date', '>=', now())
            ->where('status', 'approved')
            ->count();

        if ($futureReservations > 0) {
            return response()->json([
                'error' => "Não é possível remover este espaço. Existem {$futureReservations} reserva(s) futuras."
            ], 400);
        }

        $space->delete();

        return response()->json([
            'message' => 'Espaço removido com sucesso'
        ]);
    }
}
