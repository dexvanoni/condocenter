<?php

namespace App\Http\Controllers;

use App\Models\RecurringReservation;
use App\Models\Space;
use App\Models\Reservation;
use App\Models\Notification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class RecurringReservationController extends Controller
{
    public function __construct()
    {
        // Middleware será aplicado nas rotas
    }

    /**
     * Lista todas as reservas recorrentes
     */
    public function index()
    {
        $recurringReservations = RecurringReservation::where('condominium_id', Auth::user()->condominium_id)
            ->with(['space', 'creator'])
            ->orderBy('created_at', 'desc')
            ->get();

        return view('recurring-reservations.index', compact('recurringReservations'));
    }

    /**
     * Formulário de criação
     */
    public function create()
    {
        $spaces = Space::where('condominium_id', Auth::user()->condominium_id)
            ->where('is_active', true)
            ->get();

        return view('recurring-reservations.create', compact('spaces'));
    }

    /**
     * Salvar nova reserva recorrente
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'space_id' => 'required|exists:spaces,id',
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'days_of_week' => 'required|array|min:1',
            'days_of_week.*' => 'integer|between:0,6',
            'start_time' => 'required|date_format:H:i',
            'end_time' => 'required|date_format:H:i|after:start_time',
            'start_date' => 'required|date|after_or_equal:today',
            'duration_months' => 'required|integer|min:1|max:12',
        ]);

        $startDate = Carbon::parse($validated['start_date']);
        $endDate = $startDate->copy()->addMonths((int) $validated['duration_months']);

        $recurringReservation = RecurringReservation::create([
            'condominium_id' => Auth::user()->condominium_id,
            'space_id' => $validated['space_id'],
            'created_by' => Auth::id(),
            'title' => $validated['title'],
            'description' => $validated['description'],
            'days_of_week' => $validated['days_of_week'],
            'start_time' => $validated['start_time'],
            'end_time' => $validated['end_time'],
            'start_date' => $startDate,
            'end_date' => $endDate,
        ]);

        // Gerar reservas individuais
        $this->generateReservations($recurringReservation);

        return redirect()->route('recurring-reservations.index')
            ->with('success', 'Reserva recorrente criada com sucesso!');
    }

    /**
     * Mostrar detalhes
     */
    public function show(RecurringReservation $recurringReservation)
    {
        // Verificar se o usuário pode ver esta reserva recorrente
        if ($recurringReservation->condominium_id !== Auth::user()->condominium_id) {
            abort(403, 'Acesso negado.');
        }

        $recurringReservation->load(['space', 'creator', 'reservations.user']);

        return view('recurring-reservations.show', compact('recurringReservation'));
    }

    /**
     * Editar reserva recorrente
     */
    public function edit(RecurringReservation $recurringReservation)
    {
        // Verificar se o usuário pode editar esta reserva recorrente
        if ($recurringReservation->condominium_id !== Auth::user()->condominium_id) {
            abort(403, 'Acesso negado.');
        }

        $spaces = Space::where('condominium_id', Auth::user()->condominium_id)
            ->where('is_active', true)
            ->get();

        return view('recurring-reservations.edit', compact('recurringReservation', 'spaces'));
    }

    /**
     * Atualizar reserva recorrente
     */
    public function update(Request $request, RecurringReservation $recurringReservation)
    {
        // Verificar se o usuário pode editar esta reserva recorrente
        if ($recurringReservation->condominium_id !== Auth::user()->condominium_id) {
            abort(403, 'Acesso negado.');
        }

        $validated = $request->validate([
            'space_id' => 'required|exists:spaces,id',
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'days_of_week' => 'required|array|min:1',
            'days_of_week.*' => 'integer|between:0,6',
            'start_time' => 'required|date_format:H:i',
            'end_time' => 'required|date_format:H:i|after:start_time',
            'start_date' => 'required|date',
            'end_date' => 'required|date|after:start_date',
            'status' => 'required|in:active,inactive',
            'admin_reason' => 'required_if:status,inactive|nullable|string',
        ]);

        // Remover reservas futuras existentes
        $recurringReservation->reservations()
            ->where('reservation_date', '>', now()->toDateString())
            ->delete();

        $recurringReservation->update($validated);

        // Gerar novas reservas se estiver ativo
        if ($validated['status'] === 'active') {
            $this->generateReservations($recurringReservation);
        }

        return redirect()->route('recurring-reservations.index')
            ->with('success', 'Reserva recorrente atualizada com sucesso!');
    }

    /**
     * Cancelar reserva recorrente
     */
    public function destroy(Request $request, RecurringReservation $recurringReservation)
    {
        // Verificar se o usuário pode deletar esta reserva recorrente
        if ($recurringReservation->condominium_id !== Auth::user()->condominium_id) {
            abort(403, 'Acesso negado.');
        }

        $validated = $request->validate([
            'admin_reason' => 'required|string|min:10',
        ]);

        // Cancelar reservas futuras
        $futureReservations = $recurringReservation->reservations()
            ->where('reservation_date', '>', now()->toDateString())
            ->get();

        foreach ($futureReservations as $reservation) {
            $this->cancelReservation($reservation, $validated['admin_reason']);
        }

        $recurringReservation->update([
            'status' => 'cancelled',
            'admin_notes' => $validated['admin_reason'],
        ]);

        return redirect()->route('recurring-reservations.index')
            ->with('success', 'Reserva recorrente cancelada com sucesso!');
    }

    /**
     * Gerar reservas individuais
     */
    private function generateReservations(RecurringReservation $recurringReservation)
    {
        $reservations = $recurringReservation->generateReservations();
        
        foreach ($reservations as $reservationData) {
            // Verificar se já existe uma reserva para esta data/horário
            $existingReservation = Reservation::where('space_id', $reservationData['space_id'])
                ->where('reservation_date', $reservationData['reservation_date'])
                ->where('start_time', $reservationData['start_time'])
                ->where('end_time', $reservationData['end_time'])
                ->first();

            if (!$existingReservation) {
                Reservation::create($reservationData);
            }
        }
    }

    /**
     * Cancelar reserva individual
     */
    private function cancelReservation(Reservation $reservation, string $reason)
    {
        $reservation->update([
            'status' => 'cancelled',
            'cancelled_by' => Auth::id(),
            'cancelled_at' => now(),
            'cancellation_reason' => $reason,
            'admin_action' => 'cancelled',
            'admin_reason' => $reason,
            'admin_action_by' => Auth::id(),
            'admin_action_at' => now(),
        ]);

        // Enviar notificação para o usuário
        Notification::create([
            'condominium_id' => Auth::user()->condominium_id,
            'user_id' => $reservation->user_id,
            'title' => 'Reserva Cancelada pelo Administrador',
            'message' => "Sua reserva de {$reservation->space->name} em {$reservation->reservation_date->format('d/m/Y')} foi cancelada pelo administrador. Motivo: {$reason}",
            'type' => 'reservation_cancelled',
            'data' => json_encode([
                'reservation_id' => $reservation->id,
                'admin_reason' => $reason,
            ]),
        ]);

        // TODO: Enviar email de notificação
    }
}
