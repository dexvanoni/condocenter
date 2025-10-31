<?php

namespace App\Http\Controllers;

use App\Models\Reservation;
use App\Models\Space;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ReservationManagementController extends Controller
{
    /**
     * Listar todas as reservas do condomínio
     */
    public function index(Request $request)
    {
        /** @var User $user */
        $user = Auth::user();
        
        // Verificar autorização
        if (!$user->can('manage_reservations') && !$user->can('approve_reservations')) {
            abort(403, 'Acesso negado. Apenas administradores e síndicos podem acessar esta área.');
        }

        if ($request->ajax()) {
            $query = Reservation::with(['space', 'user', 'unit'])
                ->whereHas('space', function ($query) {
                    $query->where('condominium_id', Auth::user()->condominium_id);
                })
                ->select('reservations.*');

            // Aplicar filtros
            $this->applyFilters($query, $request);

            $reservations = $query->orderBy('reservations.reservation_date', 'desc')
                ->orderBy('reservations.start_time', 'desc');

            $data = [];
            foreach ($reservations->get() as $reservation) {
                $data[] = [
                    'checkbox' => '<input type="checkbox" class="reservation-checkbox" value="' . $reservation->id . '">',
                    'user_name' => $reservation->user ? $reservation->user->name : 'N/A',
                    'unit_info' => $reservation->unit ? $reservation->unit->number . ' - ' . $reservation->unit->block : 'N/A',
                    'space_name' => $reservation->space ? $reservation->space->name : 'N/A',
                    'formatted_date' => \Carbon\Carbon::parse($reservation->reservation_date)->format('d/m/Y'),
                    'formatted_time' => \Carbon\Carbon::parse($reservation->start_time)->format('H:i') . ' - ' . 
                                       \Carbon\Carbon::parse($reservation->end_time)->format('H:i'),
                    'status_badge' => $this->getStatusBadge($reservation->status),
                    'is_recurring' => $reservation->recurring_reservation_id ? 
                        '<span class="badge bg-info"><i class="bi bi-arrow-repeat"></i> Recorrente</span>' : 
                        '<span class="badge bg-light text-dark">Individual</span>',
                    'notes' => $reservation->notes ?: '-',
                    'actions' => $this->getActionButtons($reservation),
                ];
            }

            return response()->json([
                'data' => $data,
                'recordsTotal' => count($data),
                'recordsFiltered' => count($data)
            ]);
        }

        return view('reservations.manage');
    }

    private function getStatusBadge($status)
    {
        $badgeClass = match($status) {
            'approved' => 'bg-success',
            'pending' => 'bg-warning',
            'rejected' => 'bg-danger',
            'cancelled' => 'bg-secondary',
            default => 'bg-secondary'
        };
        
        $statusText = match($status) {
            'approved' => 'Aprovada',
            'pending' => 'Pendente',
            'rejected' => 'Rejeitada',
            'cancelled' => 'Cancelada',
            default => ucfirst($status)
        };
        
        return '<span class="badge ' . $badgeClass . '">' . $statusText . '</span>';
    }

    private function getActionButtons($reservation)
    {
        $actions = '';
        
        // Botão Ver
        $actions .= '<button class="btn btn-sm btn-info me-1" onclick="viewReservation(' . $reservation->id . ')" title="Ver Detalhes">
            <i class="bi bi-eye"></i>
        </button>';
        
        // Botão Editar (apenas para reservas não canceladas)
        if ($reservation->status !== 'cancelled') {
            $actions .= '<button class="btn btn-sm btn-warning me-1" onclick="editReservation(' . $reservation->id . ')" title="Editar">
                <i class="bi bi-pencil"></i>
            </button>';
        }
        
        // Botão Excluir (apenas para reservas não canceladas)
        if ($reservation->status !== 'cancelled') {
            $actions .= '<button class="btn btn-sm btn-danger" onclick="deleteReservation(' . $reservation->id . ')" title="Excluir">
                <i class="bi bi-trash"></i>
            </button>';
        }
        
        return $actions;
    }

    /**
     * Aplicar filtros à query
     */
    private function applyFilters($query, $request)
    {
        // Filtro por período
        if ($request->filled('date_from')) {
            $query->where('reservation_date', '>=', $request->date_from);
        }
        
        if ($request->filled('date_to')) {
            $query->where('reservation_date', '<=', $request->date_to);
        }

        // Filtro por espaço
        if ($request->filled('space_id')) {
            $query->where('space_id', $request->space_id);
        }

        // Filtro por status
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        // Filtro por tipo (recorrente ou individual)
        if ($request->filled('type')) {
            if ($request->type === 'recurring') {
                $query->whereNotNull('recurring_reservation_id');
            } elseif ($request->type === 'individual') {
                $query->whereNull('recurring_reservation_id');
            }
        }

        // Filtro por morador
        if ($request->filled('user_name')) {
            $query->whereHas('user', function ($q) use ($request) {
                $q->where('name', 'like', '%' . $request->user_name . '%');
            });
        }

        // Filtro por unidade
        if ($request->filled('unit_number')) {
            $query->whereHas('unit', function ($q) use ($request) {
                $q->where('number', 'like', '%' . $request->unit_number . '%')
                  ->orWhere('block', 'like', '%' . $request->unit_number . '%');
            });
        }

        // Filtro por período do dia
        if ($request->filled('time_period')) {
            switch ($request->time_period) {
                case 'morning':
                    $query->whereBetween('start_time', ['06:00:00', '11:59:59']);
                    break;
                case 'afternoon':
                    $query->whereBetween('start_time', ['12:00:00', '17:59:59']);
                    break;
                case 'evening':
                    $query->whereBetween('start_time', ['18:00:00', '23:59:59']);
                    break;
                case 'night':
                    $query->where(function ($q) {
                        $q->whereBetween('start_time', ['00:00:00', '05:59:59']);
                    });
                    break;
            }
        }
    }

    /**
     * Visualizar detalhes de uma reserva
     */
    public function show($id)
    {
        $reservation = Reservation::with(['space', 'user', 'unit', 'recurringReservation'])
            ->whereHas('space', function ($query) {
                $query->where('condominium_id', Auth::user()->condominium_id);
            })
            ->findOrFail($id);

        return response()->json($reservation);
    }

    /**
     * Editar uma reserva
     */
    public function edit($id)
    {
        $reservation = Reservation::with(['space', 'user', 'unit'])
            ->whereHas('space', function ($query) {
                $query->where('condominium_id', Auth::user()->condominium_id);
            })
            ->findOrFail($id);

        $spaces = Space::where('condominium_id', Auth::user()->condominium_id)
            ->where('is_active', true)
            ->get();

        return response()->json([
            'reservation' => $reservation,
            'spaces' => $spaces
        ]);
    }

    /**
     * Atualizar uma reserva
     */
    public function update(Request $request, $id)
    {
        $reservation = Reservation::whereHas('space', function ($query) {
                $query->where('condominium_id', Auth::user()->condominium_id);
            })
            ->findOrFail($id);

        $validated = $request->validate([
            'space_id' => 'required|exists:spaces,id',
            'reservation_date' => 'required|date|after_or_equal:today',
            'start_time' => 'required',
            'end_time' => 'required|after:start_time',
            'status' => 'required|in:approved,pending,rejected,cancelled',
            'notes' => 'nullable|string|max:1000',
            'admin_reason' => 'nullable|string|max:1000',
        ]);

        // Verificar se é uma mudança significativa que requer notificação
        $significantChanges = [
            'space_id' => $reservation->space_id != $validated['space_id'],
            'reservation_date' => $reservation->reservation_date != $validated['reservation_date'],
            'start_time' => $reservation->start_time != $validated['start_time'],
            'end_time' => $reservation->end_time != $validated['end_time'],
            'status' => $reservation->status != $validated['status'],
        ];

        $hasSignificantChanges = in_array(true, $significantChanges);

        $reservation->update([
            'space_id' => $validated['space_id'],
            'reservation_date' => $validated['reservation_date'],
            'start_time' => $validated['start_time'],
            'end_time' => $validated['end_time'],
            'status' => $validated['status'],
            'notes' => $validated['notes'],
            'admin_action' => $hasSignificantChanges ? 'edited' : null,
            'admin_reason' => $hasSignificantChanges ? $validated['admin_reason'] : null,
            'admin_action_by' => $hasSignificantChanges ? Auth::id() : null,
            'admin_action_at' => $hasSignificantChanges ? now() : null,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Reserva atualizada com sucesso!'
        ]);
    }

    /**
     * Excluir uma reserva
     */
    public function destroy(Request $request, $id)
    {
        $reservation = Reservation::whereHas('space', function ($query) {
                $query->where('condominium_id', Auth::user()->condominium_id);
            })
            ->findOrFail($id);

        $validated = $request->validate([
            'admin_reason' => 'required|string|min:10|max:1000',
        ]);

        $reservation->update([
            'status' => 'cancelled',
            'cancelled_by' => Auth::id(),
            'cancelled_at' => now(),
            'cancellation_reason' => $validated['admin_reason'],
            'admin_action' => 'cancelled',
            'admin_reason' => $validated['admin_reason'],
            'admin_action_by' => Auth::id(),
            'admin_action_at' => now(),
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Reserva cancelada com sucesso!'
        ]);
    }

    /**
     * Ações em massa
     */
    public function bulkAction(Request $request)
    {
        $validated = $request->validate([
            'action' => 'required|in:approve,reject,cancel',
            'reservation_ids' => 'required|array|min:1',
            'reservation_ids.*' => 'integer|exists:reservations,id',
            'admin_reason' => 'nullable|string|max:1000',
        ]);

        $reservations = Reservation::whereIn('id', $validated['reservation_ids'])
            ->whereHas('space', function ($query) {
                $query->where('condominium_id', Auth::user()->condominium_id);
            })
            ->get();

        $updated = 0;
        foreach ($reservations as $reservation) {
            if ($reservation->status !== 'cancelled') {
                $newStatus = match($validated['action']) {
                    'approve' => 'approved',
                    'reject' => 'rejected',
                    'cancel' => 'cancelled',
                };

                $updateData = ['status' => $newStatus];
                
                if ($validated['action'] === 'cancel') {
                    $updateData['cancelled_by'] = Auth::id();
                    $updateData['cancelled_at'] = now();
                    $updateData['cancellation_reason'] = $validated['admin_reason'];
                }

                $updateData['admin_action'] = $validated['action'];
                $updateData['admin_reason'] = $validated['admin_reason'];
                $updateData['admin_action_by'] = Auth::id();
                $updateData['admin_action_at'] = now();

                $reservation->update($updateData);
                $updated++;
            }
        }

        return response()->json([
            'success' => true,
            'message' => "Ação realizada com sucesso! {$updated} reserva(s) atualizada(s)."
        ]);
    }

    /**
     * Obter espaços para filtro
     */
    public function getSpaces()
    {
        $spaces = Space::where('condominium_id', Auth::user()->condominium_id)
            ->where('is_active', true)
            ->select('id', 'name')
            ->orderBy('name')
            ->get();

        return response()->json($spaces);
    }
}

