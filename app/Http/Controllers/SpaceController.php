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
            'approval_type' => 'required|in:automatic,manual,prereservation',
            'prereservation_payment_hours' => 'nullable|integer|in:24,48,72',
            'prereservation_auto_cancel' => 'boolean',
            'prereservation_instructions' => 'nullable|string',
            'photo' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:5120', // 5MB max
        ]);

        // Upload da foto se fornecida
        $photoPath = null;
        if ($request->hasFile('photo')) {
            $photoPath = $request->file('photo')->store('spaces/photos', 'public');
        }

        $space = Space::create([
            'condominium_id' => Auth::user()->condominium_id,
            'name' => $validated['name'],
            'description' => $validated['description'],
            'photo_path' => $photoPath,
            'type' => $validated['type'],
            'capacity' => $validated['capacity'],
            'price_per_hour' => $validated['price_per_reservation'],
            'requires_approval' => $validated['approval_type'] === 'manual',
            'reservation_mode' => $validated['reservation_mode'],
            'min_hours_per_reservation' => $validated['min_hours_per_reservation'] ?? 1,
            'max_hours_per_reservation' => $validated['max_hours_per_reservation'] ?? 24,
            'max_reservations_per_month_per_unit' => $validated['max_reservations_per_month_per_unit'],
            'available_from' => $validated['available_from'],
            'available_until' => $validated['available_until'],
            'is_active' => true,
            'rules' => $validated['rules'],
            'approval_type' => $validated['approval_type'],
            'prereservation_payment_hours' => $validated['prereservation_payment_hours'],
            'prereservation_auto_cancel' => $request->boolean('prereservation_auto_cancel', true),
            'prereservation_instructions' => $validated['prereservation_instructions'],
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
            'approval_type' => 'required|in:automatic,manual,prereservation',
            'prereservation_payment_hours' => 'nullable|integer|in:24,48,72',
            'prereservation_auto_cancel' => 'boolean',
            'prereservation_instructions' => 'nullable|string',
            'photo' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:5120', // 5MB max
        ]);

        // Upload da nova foto se fornecida
        if ($request->hasFile('photo')) {
            // Remover foto anterior se existir
            if ($space->photo_path && \Storage::disk('public')->exists($space->photo_path)) {
                \Storage::disk('public')->delete($space->photo_path);
            }
            
            // Upload da nova foto
            $photoPath = $request->file('photo')->store('spaces/photos', 'public');
            $validated['photo_path'] = $photoPath;
        }

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
            'requires_approval' => $validated['approval_type'] === 'manual',
            'approval_type' => $validated['approval_type'],
            'prereservation_payment_hours' => $validated['prereservation_payment_hours'],
            'prereservation_auto_cancel' => $request->boolean('prereservation_auto_cancel', true),
            'prereservation_instructions' => $validated['prereservation_instructions'],
            'photo_path' => $validated['photo_path'] ?? $space->photo_path,
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
