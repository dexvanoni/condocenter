<?php

namespace App\Http\Controllers;

use App\Models\Space;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class SpaceController extends Controller
{
    /**
     * Lista espaços do condomínio
     */
    public function index()
    {
        $spaces = Space::where('condominium_id', Auth::user()->condominium_id)
            ->withCount('reservations')
            ->orderBy('name')
            ->get();

        return view('spaces.index', compact('spaces'));
    }

    /**
     * Formulário de criação
     */
    public function create()
    {
        return view('spaces.create');
    }

    /**
     * Salva novo espaço
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'type' => 'required|in:party_hall,bbq,pool,sports_court,gym,meeting_room,other',
            'capacity' => 'nullable|integer|min:1',
            'price_per_reservation' => 'required|numeric|min:0',
            'reservation_mode' => 'required|in:full_day,hourly',
            'min_hours_per_reservation' => 'nullable|integer|min:1',
            'max_hours_per_reservation' => 'nullable|integer|min:1',
            'max_reservations_per_month_per_unit' => 'required|integer|min:1',
            'available_from' => 'required',
            'available_until' => 'required',
            'rules' => 'nullable|string',
        ]);

        $space = Space::create([
            'condominium_id' => Auth::user()->condominium_id,
            'name' => $validated['name'],
            'description' => $validated['description'],
            'type' => $validated['type'],
            'capacity' => $validated['capacity'],
            'price_per_hour' => $validated['price_per_reservation'],
            'requires_approval' => false, // Sempre aprovação automática
            'reservation_mode' => $validated['reservation_mode'],
            'min_hours_per_reservation' => $validated['min_hours_per_reservation'] ?? 1,
            'max_hours_per_reservation' => $validated['max_hours_per_reservation'] ?? 24,
            'max_reservations_per_month_per_unit' => $validated['max_reservations_per_month_per_unit'],
            'available_from' => $validated['available_from'],
            'available_until' => $validated['available_until'],
            'is_active' => true,
            'rules' => $validated['rules'],
        ]);

        return redirect()->route('spaces.index')
            ->with('success', 'Espaço criado com sucesso!');
    }

    /**
     * Formulário de edição
     */
    public function edit($id)
    {
        $space = Space::where('condominium_id', Auth::user()->condominium_id)
            ->findOrFail($id);

        return view('spaces.edit', compact('space'));
    }

    /**
     * Atualiza espaço
     */
    public function update(Request $request, $id)
    {
        $space = Space::where('condominium_id', Auth::user()->condominium_id)
            ->findOrFail($id);

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'type' => 'required|in:party_hall,bbq,pool,sports_court,gym,meeting_room,other',
            'capacity' => 'nullable|integer|min:1',
            'price_per_reservation' => 'required|numeric|min:0',
            'reservation_mode' => 'required|in:full_day,hourly',
            'min_hours_per_reservation' => 'nullable|integer|min:1',
            'max_hours_per_reservation' => 'nullable|integer|min:1',
            'max_reservations_per_month_per_unit' => 'required|integer|min:1',
            'available_from' => 'required',
            'available_until' => 'required',
            'rules' => 'nullable|string',
            'is_active' => 'boolean',
        ]);

        $space->update([
            'name' => $validated['name'],
            'description' => $validated['description'],
            'type' => $validated['type'],
            'capacity' => $validated['capacity'],
            'price_per_hour' => $validated['price_per_reservation'],
            'reservation_mode' => $validated['reservation_mode'],
            'min_hours_per_reservation' => $validated['min_hours_per_reservation'] ?? 1,
            'max_hours_per_reservation' => $validated['max_hours_per_reservation'] ?? 24,
            'max_reservations_per_month_per_unit' => $validated['max_reservations_per_month_per_unit'],
            'available_from' => $validated['available_from'],
            'available_until' => $validated['available_until'],
            'rules' => $validated['rules'],
            'is_active' => $request->boolean('is_active', true),
        ]);

        return redirect()->route('spaces.index')
            ->with('success', 'Espaço atualizado com sucesso!');
    }

    /**
     * Remove espaço
     */
    public function destroy($id)
    {
        $space = Space::where('condominium_id', Auth::user()->condominium_id)
            ->findOrFail($id);

        // Verificar se tem reservas futuras
        $futureReservations = $space->reservations()
            ->where('reservation_date', '>=', now())
            ->count();

        if ($futureReservations > 0) {
            return back()->with('error', "Não é possível remover este espaço. Existem {$futureReservations} reserva(s) futuras.");
        }

        $space->delete();

        return redirect()->route('spaces.index')
            ->with('success', 'Espaço removido com sucesso!');
    }
}
