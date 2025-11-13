<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Jobs\SendReservationNotification;
use App\Models\Charge;
use App\Models\Notification;
use App\Models\Reservation;
use App\Models\Space;
use App\Services\OneSignalNotificationService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class ReservationController extends Controller
{
    /**
     * Lista reservas
     */
    public function index(Request $request)
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();
        $query = Reservation::with(['space', 'unit', 'user', 'approvedBy']);

        // Filtrar por condomínio através do space
        $query->whereHas('space', function ($q) use ($user) {
            $q->where('condominium_id', $user->condominium_id);
        });

        // Mostrar apenas as reservas do usuário logado (não por unidade)
        // Exceto para administradores e síndicos que podem ver todas as reservas
        if (!$user->isAdmin() && !$user->isSindico()) {
            $query->where('user_id', $user->id);
        }

        // Excluir reservas recorrentes de "Minhas Reservas"
        // As reservas recorrentes são uma funcionalidade administrativa
        $query->whereNull('recurring_reservation_id');

        // Filtros
        if ($request->has('space_id')) {
            $query->where('space_id', $request->space_id);
        }

        if ($request->has('status')) {
            $query->where('status', $request->status);
        }

        if ($request->has('date')) {
            $query->whereDate('reservation_date', $request->date);
        }

        $reservations = $query->orderBy('reservation_date', 'asc')
            ->orderBy('created_at', 'desc')
            ->paginate(15);

        return response()->json($reservations);
    }

    /**
     * Cria uma nova reserva
     */
    public function store(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
            'space_id' => 'required|exists:spaces,id',
            'reservation_date' => 'required|date|after_or_equal:today',
            'start_time' => 'nullable|date_format:H:i',
            'end_time' => 'nullable|date_format:H:i',
            'notes' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        /** @var \App\Models\User $user */
        $user = Auth::user();
        
        // Verificar se o usuário tem unidade associada
        if (!$user->unit_id) {
            return response()->json(['error' => 'Você precisa estar associado a uma unidade para fazer reservas'], 400);
        }

        // Verificar permissões para fazer reservas
        $canMakeReservations = false;
        
        if ($user->isAgregado()) {
            // Para agregados, verificar permissão específica
            $canMakeReservations = \App\Models\AgregadoPermission::hasPermission($user->id, 'spaces', 'crud');
        } else {
            // Para outros perfis, verificar permissão Spatie
            $canMakeReservations = $user->can('make_reservations');
        }
        
        if (!$canMakeReservations) {
            return response()->json(['error' => 'Você não tem permissão para fazer reservas. Apenas visualização permitida.'], 403);
        }
        
        $space = Space::findOrFail($request->space_id);

        // Verificar se o espaço pertence ao condomínio do usuário
        if ($space->condominium_id !== $user->condominium_id) {
            return response()->json(['error' => 'Espaço não pertence ao seu condomínio'], 403);
        }

        // Verificar se o espaço está ativo
        if (!$space->is_active) {
            return response()->json(['error' => 'Este espaço não está disponível para reservas'], 400);
        }

        // Validação de conflitos baseada no modo de reserva
        if ($space->reservation_mode === 'full_day') {
            // MODO DIA INTEIRO: Não permitir mais de uma reserva no mesmo dia
            $conflictSameDayAndPlace = Reservation::where('space_id', $request->space_id)
                ->where('reservation_date', $request->reservation_date)
                ->whereIn('status', ['pending', 'approved'])
                ->exists();

            if ($conflictSameDayAndPlace) {
                return response()->json([
                    'error' => 'Este espaço já está reservado para esta data. Por favor, escolha outra data.',
                    'conflict' => true
                ], 400);
            }
            
            // Usar horários do espaço
            $startTime = $space->available_from;
            $endTime = $space->available_until;
            
        } else {
            // MODO POR HORÁRIO: Validar conflitos de horário específico
            if (!$request->start_time || !$request->end_time) {
                return response()->json([
                    'error' => 'Para este espaço, você deve informar horário de início e término'
                ], 400);
            }
            
            $startTime = $request->start_time;
            $endTime = $request->end_time;
            
            // Verificar sobreposição de horários
            $hasConflict = Reservation::where('space_id', $request->space_id)
                ->where('reservation_date', $request->reservation_date)
                ->whereIn('status', ['pending', 'approved'])
                ->where(function($q) use ($startTime, $endTime) {
                    // Verifica se há sobreposição de horários
                    $q->where(function($query) use ($startTime, $endTime) {
                        // Novo horário começa durante reserva existente
                        $query->where('start_time', '<=', $startTime)
                              ->where('end_time', '>', $startTime);
                    })
                    ->orWhere(function($query) use ($startTime, $endTime) {
                        // Novo horário termina durante reserva existente
                        $query->where('start_time', '<', $endTime)
                              ->where('end_time', '>=', $endTime);
                    })
                    ->orWhere(function($query) use ($startTime, $endTime) {
                        // Novo horário engloba reserva existente
                        $query->where('start_time', '>=', $startTime)
                              ->where('end_time', '<=', $endTime);
                    });
                })
                ->exists();
            
            if ($hasConflict) {
                return response()->json([
                    'error' => 'Há um conflito de horário. Por favor, escolha outro horário.',
                    'conflict' => true
                ], 400);
            }
            
            // Validar duração
            $duration = (strtotime($endTime) - strtotime($startTime)) / 3600;
            
            if ($duration > $space->max_hours_per_reservation) {
                return response()->json([
                    'error' => "Duração máxima permitida: {$space->max_hours_per_reservation}h"
                ], 400);
            }
            
            if ($duration < $space->min_hours_per_reservation) {
                return response()->json([
                    'error' => "Duração mínima permitida: {$space->min_hours_per_reservation}h"
                ], 400);
            }
        }

        // Verificar limite mensal de reservas por usuário (não por unidade)
        $reservationsThisMonth = Reservation::where('space_id', $request->space_id)
            ->where('user_id', $user->id) // Limitar por usuário individual
            ->whereMonth('reservation_date', now()->month)
            ->whereYear('reservation_date', now()->year)
            ->whereIn('status', ['pending', 'approved'])
            ->count();

        if ($reservationsThisMonth >= $space->max_reservations_per_month_per_user) {
            return response()->json([
                'error' => "Limite de {$space->max_reservations_per_month_per_user} reserva(s) por mês atingido para este usuário neste espaço"
            ], 400);
        }

        // Determinar status da reserva baseado no tipo de aprovação do espaço
        $reservationData = [
            'space_id' => $request->space_id,
            'unit_id' => $user->unit_id,
            'user_id' => $user->id,
            'reservation_date' => $request->reservation_date,
            'start_time' => $startTime, // Usa o horário validado acima
            'end_time' => $endTime,     // Usa o horário validado acima
            'notes' => $request->notes,
        ];

        // Verificar tipo de aprovação do espaço
        if ($space->approval_type === 'prereservation') {
            // Pré-reserva: status pending, aguardando pagamento
            $reservationData['status'] = 'pending';
            $reservationData['prereservation_status'] = 'pending_payment';
            $reservationData['payment_deadline'] = $space->getPaymentDeadline();
            $reservationData['prereservation_amount'] = $space->price_per_hour;
        } elseif ($space->approval_type === 'manual') {
            // Aprovação manual: status pending, aguardando síndico
            $reservationData['status'] = 'pending';
        } else {
            // Aprovação automática: status approved imediatamente
            $reservationData['status'] = 'approved';
            $reservationData['approved_by'] = $user->id;
            $reservationData['approved_at'] = now();
        }

        $reservation = Reservation::create($reservationData);

        $generatedCharge = null;
        if ($space->approval_type === 'automatic') {
            $generatedCharge = $this->createImmediateApprovalCharge($reservation, $space);
        }

        $paymentData = null;
        $creditUsed = false;
        $remainingAmount = $space->price_per_hour;
        
        // Lógica de pagamento apenas para pré-reservas
        if ($space->approval_type === 'prereservation' && $space->price_per_hour > 0) {
            // Buscar créditos disponíveis do usuário
            $availableCredits = \App\Models\UserCredit::where('user_id', $user->id)
                ->where('condominium_id', $user->condominium_id)
                ->available()
                ->orderBy('created_at', 'asc') // FIFO - First In, First Out
                ->get();
            
            $totalCredits = $availableCredits->sum('amount');
            
            if ($totalCredits > 0) {
                // Aplicar créditos
                $amountToUse = min($totalCredits, $remainingAmount);
                $remainingToApply = $amountToUse;
                
                foreach ($availableCredits as $credit) {
                    if ($remainingToApply <= 0) break;
                    
                    $useAmount = min($credit->amount, $remainingToApply);
                    
                    // Marcar crédito como usado
                    $credit->markAsUsed($reservation->id);
                    
                    $remainingToApply -= $useAmount;
                }
                
                $remainingAmount -= $amountToUse;
                $creditUsed = true;
                
                Log::info("Créditos aplicados: R$ {$amountToUse}. Restante a pagar: R$ {$remainingAmount}");
            }
            
            // Se ainda sobrar valor, gerar cobrança via Asaas
            if ($remainingAmount > 0) {
                try {
                    $paymentData = $this->generatePaymentSync($reservation, $space, $remainingAmount);
                } catch (\Exception $e) {
                    Log::error('Erro ao gerar pagamento: ' . $e->getMessage());
                    // Continua mesmo com erro no pagamento
                }
            }
        }

        // Enviar notificação apropriada (em modo síncrono para evitar erros)
        try {
            if ($space->approval_type === 'prereservation') {
                SendReservationNotification::dispatchSync($reservation, 'pending_payment');
                $message = 'Pré-reserva criada! Realize o pagamento para confirmar.';
            } elseif ($space->approval_type === 'manual') {
                SendReservationNotification::dispatchSync($reservation, 'pending');
                $message = 'Reserva enviada para aprovação do síndico.';
            } else {
                SendReservationNotification::dispatchSync($reservation, 'approved');
                $message = 'Reserva confirmada automaticamente!';
            }
        } catch (\Exception $e) {
            Log::error('Erro ao enviar notificação: ' . $e->getMessage());
            // Continua mesmo com erro na notificação
            if ($space->approval_type === 'prereservation') {
                $message = 'Pré-reserva criada! Realize o pagamento para confirmar.';
            } elseif ($space->approval_type === 'manual') {
                $message = 'Reserva enviada para aprovação do síndico.';
            } else {
                $message = 'Reserva confirmada automaticamente!';
            }
        }

        if ($generatedCharge) {
            $message .= ' Foi gerada uma cobrança para esta reserva.';
        }
        
        if ($space->approval_type === 'prereservation' && $space->price_per_hour > 0) {
            if ($creditUsed) {
                $creditsApplied = $space->price_per_hour - $remainingAmount;
                $message .= " Créditos aplicados: R$ " . number_format($creditsApplied, 2, ',', '.');
                
                if ($remainingAmount > 0) {
                    $message .= " Restante a pagar: R$ " . number_format($remainingAmount, 2, ',', '.');
                } else {
                    $message .= " Reserva totalmente paga com créditos!";
                }
            } else if ($remainingAmount > 0) {
                $message .= " Será gerada uma cobrança de R$ " . number_format($remainingAmount, 2, ',', '.') . " via Asaas.";
            }
        }

        // Calcular créditos totais do usuário com tratamento de erro
        try {
            $totalCredits = $user->getTotalCredits();
        } catch (\Exception $e) {
            Log::error('Erro ao calcular créditos totais: ' . $e->getMessage());
            $totalCredits = 0;
        }

        // Carregar relacionamentos da reserva com tratamento de erro
        try {
            $reservationWithRelations = $reservation->load('space');
        } catch (\Exception $e) {
            Log::error('Erro ao carregar relacionamentos da reserva: ' . $e->getMessage());
            $reservationWithRelations = $reservation;
        }

        $response = [
            'message' => $message,
            'reservation' => $reservationWithRelations,
            'has_charge' => $remainingAmount > 0,
            'amount' => $remainingAmount,
            'credit_used' => $creditUsed,
            'credit_amount' => $creditUsed ? ($space->price_per_hour - $remainingAmount) : 0,
            'total_user_credits' => $totalCredits
        ];

        if ($generatedCharge) {
            $response['reservation_charge'] = [
                'id' => $generatedCharge->id,
                'amount' => $generatedCharge->amount,
                'due_date' => optional($generatedCharge->due_date)->format('Y-m-d'),
                'status' => $generatedCharge->status,
            ];
        }

        // Adicionar dados específicos para pré-reservas
        if ($space->approval_type === 'prereservation') {
            $response['is_prereservation'] = true;
            $response['payment_deadline'] = $reservation->payment_deadline;
            $response['payment_instructions'] = $space->prereservation_instructions;
            if ($paymentData) {
                $response['payment_data'] = $paymentData;
            }
        }

        return response()->json($response, 201);
        
    } catch (\Exception $e) {
        Log::error('Erro ao criar reserva: ' . $e->getMessage(), [
            'user_id' => $user->id,
            'space_id' => $request->space_id,
            'reservation_date' => $request->reservation_date,
            'start_time' => $request->start_time,
            'end_time' => $request->end_time,
            'stack_trace' => $e->getTraceAsString()
        ]);
        
        return response()->json([
            'error' => 'Erro interno do servidor ao criar reserva. Tente novamente.'
        ], 500);
    }
    }

    /**
     * Retorna disponibilidade de um espaço (sem dados pessoais)
     */
    public function availability($spaceId)
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();
        
        $space = Space::findOrFail($spaceId);
        
        // Verificar se pertence ao condomínio
        if ($space->condominium_id !== $user->condominium_id) {
            return response()->json(['error' => 'Não autorizado'], 403);
        }
        
        // Buscar TODAS as reservas aprovadas/pendentes deste espaço
        // SEM dados pessoais (apenas data e horários)
        $reservations = Reservation::where('space_id', $spaceId)
            ->whereIn('status', ['approved', 'pending'])
            ->whereNull('recurring_reservation_id') // Excluir reservas geradas a partir de recorrentes
            ->select('id', 'space_id', 'reservation_date', 'start_time', 'end_time', 'status', 
                     'prereservation_status', 'payment_deadline')
            ->orderBy('reservation_date')
            ->orderBy('start_time')
            ->get();
            
        // Debug: Log das reservas encontradas (desabilitado)
        // Log::info('Reservas encontradas para espaço ' . $spaceId . ':', [
        //     'total' => $reservations->count(),
        //     'reservations' => $reservations->toArray()
        // ]);
        
        $reservations = $reservations->map(function($reservation) {
                // Adicionar informações de pré-reserva se existir
                $data = $reservation->toArray();
                
                // Verificar se é pré-reserva pendente de pagamento
                if ($reservation->prereservation_status === 'pending_payment') {
                    $data['is_prereservation'] = true;
                    
                    if ($reservation->payment_deadline) {
                        $data['payment_deadline'] = $reservation->payment_deadline->toIso8601String();
                        $data['hours_until_expiration'] = now()->diffInHours($reservation->payment_deadline, false);
                    } else {
                        // Fallback se não houver deadline definido
                        $data['hours_until_expiration'] = 24; // Padrão de 24h
                    }
                } else {
                    $data['is_prereservation'] = false;
                }
                
                return $data;
            });

        // Buscar reservas recorrentes ativas deste espaço
        $recurringReservations = \App\Models\RecurringReservation::where('space_id', $spaceId)
            ->where('status', 'active')
            ->where('start_date', '<=', now()->addDays(30)->toDateString()) // Incluir reservas que começam nos próximos 30 dias
            ->where('end_date', '>=', now()->toDateString())
            ->get();

        // Converter reservas recorrentes em slots ocupados
        $recurringSlots = collect();
        foreach ($recurringReservations as $recurring) {
            $current = \Carbon\Carbon::parse($recurring->start_date);
            $end = \Carbon\Carbon::parse($recurring->end_date);
            
            while ($current->lte($end)) {
                if (in_array($current->dayOfWeek, array_map('intval', $recurring->days_of_week))) {
                    $recurringSlots->push([
                        'id' => 'recurring_' . $recurring->id . '_' . $current->toDateString(),
                        'space_id' => $spaceId,
                        'reservation_date' => $current->toDateString(),
                        'start_time' => $recurring->start_time,
                        'end_time' => $recurring->end_time,
                        'status' => 'approved',
                        'title' => $recurring->title,
                        'is_recurring' => true,
                    ]);
                }
                $current->addDay();
            }
        }

        // Combinar reservas normais e recorrentes
        $allSlots = $reservations->concat($recurringSlots)
            ->sortBy(['reservation_date', 'start_time'])
            ->values();
        
        return response()->json([
            'space_id' => $space->id,
            'space_name' => $space->name,
            'reservation_mode' => $space->reservation_mode,
            'occupied_slots' => $allSlots
        ]);
    }
    
    /**
     * Aprova uma reserva
     */
    public function approve($id)
    {
        $reservation = Reservation::findOrFail($id);

        // Verificar permissão
        /** @var \App\Models\User $user */
        $user = Auth::user();
        if ($reservation->space->condominium_id !== $user->condominium_id) {
            return response()->json(['error' => 'Não autorizado'], 403);
        }

        if (!$user->can('approve_reservations')) {
            return response()->json(['error' => 'Sem permissão para aprovar reservas'], 403);
        }

        $reservation->approve($user->id);

        // Notificar morador
        SendReservationNotification::dispatch($reservation, 'approved');

        return response()->json([
            'message' => 'Reserva aprovada com sucesso',
            'reservation' => $reservation
        ]);
    }

    /**
     * Rejeita uma reserva
     */
    public function reject(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'rejection_reason' => 'required|string|max:500',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $reservation = Reservation::findOrFail($id);

        // Verificar permissão
        /** @var \App\Models\User $user */
        $user = Auth::user();
        if ($reservation->space->condominium_id !== $user->condominium_id) {
            return response()->json(['error' => 'Não autorizado'], 403);
        }

        $reservation->reject($request->rejection_reason, $user->id);

        // Notificar morador
        SendReservationNotification::dispatch($reservation, 'rejected');

        return response()->json([
            'message' => 'Reserva rejeitada',
            'reservation' => $reservation
        ]);
    }

    /**
     * Exibe uma reserva
     */
    public function show($id)
    {
        $reservation = Reservation::with(['space', 'unit', 'user', 'approvedBy'])
            ->findOrFail($id);

        /** @var \App\Models\User $user */
        $user = Auth::user();
        
        // Verificar permissão
        if ($reservation->space->condominium_id !== $user->condominium_id) {
            return response()->json(['error' => 'Não autorizado'], 403);
        }

        // Morador só pode ver suas próprias reservas
        if ($user->isMorador() && $reservation->user_id !== $user->id) {
            return response()->json(['error' => 'Não autorizado'], 403);
        }

        return response()->json($reservation);
    }

    /**
     * Atualiza uma reserva
     */
    public function update(Request $request, $id)
    {
        $reservation = Reservation::findOrFail($id);

        /** @var \App\Models\User $user */
        $user = Auth::user();

        // Verificar permissão
        if ($reservation->user_id !== $user->id && !$user->can('manage_reservations')) {
            return response()->json(['error' => 'Não autorizado'], 403);
        }

        // Não permitir editar reservas aprovadas/rejeitadas
        if (in_array($reservation->status, ['approved', 'rejected', 'completed'])) {
            return response()->json([
                'error' => 'Não é possível editar uma reserva já processada'
            ], 400);
        }

        $validator = Validator::make($request->all(), [
            'reservation_date' => 'sometimes|date|after_or_equal:today',
            'start_time' => 'sometimes|date_format:H:i',
            'end_time' => 'sometimes|date_format:H:i',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $reservation->update($request->all());

        return response()->json([
            'message' => 'Reserva atualizada com sucesso',
            'reservation' => $reservation
        ]);
    }

    /**
     * Confirma pagamento de pré-reserva
     */
    public function confirmPayment(Request $request, $id)
    {
        $reservation = Reservation::findOrFail($id);

        /** @var \App\Models\User $user */
        $user = Auth::user();

        // Verificar se é o dono da reserva
        if ($reservation->user_id !== $user->id) {
            return response()->json(['error' => 'Não autorizado'], 403);
        }

        // Verificar se é uma pré-reserva pendente de pagamento
        if (!$reservation->isPrereservation() || !$reservation->isPendingPayment()) {
            return response()->json(['error' => 'Esta não é uma pré-reserva pendente de pagamento'], 400);
        }

        // Verificar se ainda está dentro do prazo
        if ($reservation->isPaymentExpired()) {
            return response()->json(['error' => 'Prazo de pagamento expirado'], 400);
        }

        // Marcar como paga e aprovada
        $reservation->markAsPaid($request->payment_reference ?? 'confirmed');

        // Enviar notificação de confirmação
        SendReservationNotification::dispatch($reservation, 'approved');

        return response()->json([
            'message' => 'Pré-reserva confirmada com sucesso!',
            'reservation' => $reservation->load('space')
        ]);
    }

    /**
     * Cancela uma reserva
     */
    public function destroy($id)
    {
        $reservation = Reservation::findOrFail($id)->load(['space', 'user', 'unit']);

        /** @var \App\Models\User $user */
        $user = Auth::user();
        
        $isSindico = $user->hasRole('Síndico');
        $isOwner = $reservation->user_id === $user->id;

        // Verificar permissão
        if (!$isOwner && !$user->can('manage_reservations')) {
            return response()->json(['error' => 'Não autorizado'], 403);
        }

        // Verificar se existe cobrança associada
        $charge = \App\Models\Charge::where('title', 'LIKE', "%Taxa de Reserva%{$reservation->space->name}%")
            ->where('unit_id', $reservation->unit_id)
            ->where('due_date', '>=', $reservation->reservation_date->copy()->subDays(2))
            ->where('due_date', '<=', $reservation->reservation_date->copy()->addDay())
            ->first();

        $creditGenerated = false;
        $chargeDeleted = false;

        if ($charge) {
            // Verificar se foi paga
            $payment = \App\Models\Payment::where('charge_id', $charge->id)
                ->where('status', 'paid')
                ->first();

            if ($payment) {
                // Cobrança foi PAGA → Gerar crédito
                \App\Models\UserCredit::create([
                    'condominium_id' => $reservation->space->condominium_id,
                    'user_id' => $reservation->user_id,
                    'amount' => $charge->amount,
                    'type' => 'refund',
                    'description' => "Estorno de reserva cancelada - {$reservation->space->name} ({$reservation->reservation_date->format('d/m/Y')})",
                    'reservation_id' => $reservation->id,
                    'charge_id' => $charge->id,
                    'status' => 'available',
                    'expires_at' => now()->addMonths(12), // Válido por 12 meses
                ]);
                
                $creditGenerated = true;
                
                Log::info("Crédito gerado para usuário {$user->id}: R$ {$charge->amount}");
            } else {
                // Cobrança NÃO foi paga → Deletar cobrança
                $charge->delete();
                $chargeDeleted = true;
                
                Log::info("Cobrança deletada (não paga): {$charge->id}");
            }
        }

        // Atualizar status da reserva
        $reservation->update([
            'status' => 'cancelled',
            'cancelled_by' => $user->id,
            'cancelled_at' => now(),
            'cancellation_reason' => $isSindico && !$isOwner ? 'Cancelado pela administração' : 'Cancelado pelo usuário'
        ]);

        // Enviar notificações
        $this->sendCancellationNotifications($reservation, $user, $isSindico, $isOwner);

        $message = 'Reserva cancelada com sucesso!';
        
        if ($creditGenerated) {
            $message .= " Um crédito de R$ " . number_format($charge->amount, 2, ',', '.') . " foi adicionado à sua carteira. Válido por 12 meses.";
        } elseif ($chargeDeleted) {
            $message .= " A cobrança pendente foi removida.";
        }

        return response()->json([
            'message' => $message,
            'notifications_sent' => true,
            'credit_generated' => $creditGenerated,
            'credit_amount' => $creditGenerated ? $charge->amount : 0,
            'charge_deleted' => $chargeDeleted
        ]);
    }
    
    private function createImmediateApprovalCharge(Reservation $reservation, Space $space): ?Charge
    {
        if ($space->price_per_hour <= 0 || !$reservation->unit_id) {
            return null;
        }

        $existingCharge = Charge::query()
            ->where('condominium_id', $space->condominium_id)
            ->where('unit_id', $reservation->unit_id)
            ->where('metadata->reservation_id', $reservation->id)
            ->first();

        if ($existingCharge) {
            return $existingCharge;
        }

        $amount = $this->calculateReservationAmount($reservation, $space);

        if ($amount <= 0) {
            return null;
        }

        $dueDate = now()->addHours(48);

        $startLabel = $reservation->start_time ?? '--';
        $endLabel = $reservation->end_time ?? '--';

        $charge = Charge::create([
            'condominium_id' => $space->condominium_id,
            'unit_id' => $reservation->unit_id,
            'title' => "Reserva de Espaço - {$space->name}",
            'description' => "Cobrança referente à reserva do espaço {$space->name} em {$reservation->reservation_date->format('d/m/Y')} das {$startLabel} às {$endLabel}.",
            'amount' => $amount,
            'due_date' => $dueDate,
            'recurrence_period' => null,
            'fine_percentage' => 0,
            'interest_rate' => 0,
            'status' => 'pending',
            'type' => 'extra',
            'generated_by' => 'reservation',
            'metadata' => [
                'reservation_id' => $reservation->id,
                'space_id' => $space->id,
                'automatic_approval' => true,
            ],
        ]);

        $this->notifyReservationCharge($reservation, $charge, $space);

        return $charge;
    }

    private function calculateReservationAmount(Reservation $reservation, Space $space): float
    {
        $baseAmount = (float) $space->price_per_hour;

        if ($baseAmount <= 0) {
            return 0.0;
        }

        if ($space->reservation_mode === 'hourly' && $reservation->start_time && $reservation->end_time) {
            try {
                $start = Carbon::createFromFormat('H:i', $reservation->start_time);
                $end = Carbon::createFromFormat('H:i', $reservation->end_time);
                $minutes = max(0, $start->diffInMinutes($end));

                if ($minutes > 0) {
                    $hours = $minutes / 60;

                    return round($baseAmount * max($hours, 1), 2);
                }
            } catch (\Exception $e) {
                // Mantém valor base em caso de erro de parsing
            }
        }

        return round($baseAmount, 2);
    }

    private function notifyReservationCharge(Reservation $reservation, Charge $charge, Space $space): void
    {
        try {
            $dueDate = optional($charge->due_date)?->format('d/m/Y');
            $amountLabel = 'R$ ' . number_format((float) $charge->amount, 2, ',', '.');

            Notification::create([
                'condominium_id' => $space->condominium_id,
                'user_id' => $reservation->user_id,
                'type' => 'reservation_charge_created',
                'title' => 'Cobrança gerada para sua reserva',
                'message' => "Foi gerada uma cobrança de {$amountLabel} referente à reserva do espaço {$space->name}. Vencimento em {$dueDate}.",
                'data' => [
                    'reservation_id' => $reservation->id,
                    'charge_id' => $charge->id,
                    'due_date' => $dueDate,
                    'amount' => $charge->amount,
                ],
                'channel' => 'database',
                'sent' => true,
                'sent_at' => now(),
            ]);

            /** @var OneSignalNotificationService $oneSignal */
            $oneSignal = app(OneSignalNotificationService::class);

            if ($oneSignal->isEnabled()) {
                $oneSignal->sendToUsers(
                    [$reservation->user_id],
                    "Foi gerada uma cobrança de {$amountLabel} para a reserva do espaço {$space->name}. Vencimento em {$dueDate}.",
                    'Cobrança de reserva',
                    [
                        'type' => 'reservation_charge_created',
                        'reservation_id' => $reservation->id,
                        'charge_id' => $charge->id,
                    ]
                );
            }
        } catch (\Exception $e) {
            Log::warning('Falha ao notificar cobrança de reserva: ' . $e->getMessage(), [
                'reservation_id' => $reservation->id,
                'charge_id' => $charge->id,
            ]);
        }
    }

    /**
     * Envia notificações de cancelamento
     */
    private function sendCancellationNotifications($reservation, $cancelledBy, $isSindico, $isOwner)
    {
        // Notificar o dono da reserva (se não foi ele quem cancelou)
        if (!$isOwner) {
            \App\Models\Notification::create([
                'condominium_id' => $reservation->space->condominium_id,
                'user_id' => $reservation->user_id,
                'type' => 'reservation_cancelled',
                'title' => 'Reserva Cancelada',
                'message' => "Sua reserva do(a) {$reservation->space->name} para o dia {$reservation->reservation_date->format('d/m/Y')} foi cancelada pela administração.",
                'priority' => 'high',
                'read_at' => null,
            ]);
            
            // Enviar email
            try {
                Mail::to($reservation->user->email)->send(
                    new \App\Mail\ReservationCancellationNotification($reservation, $cancelledBy, false)
                );
            } catch (\Exception $e) {
                Log::error('Erro ao enviar email de cancelamento: ' . $e->getMessage());
            }
        }
        
        // Notificar síndico (se não foi ele quem cancelou)
        if (!$isSindico) {
            $sindicos = \App\Models\User::role('Síndico')
                ->where('condominium_id', $reservation->space->condominium_id)
                ->get();
            
            foreach ($sindicos as $sindico) {
                \App\Models\Notification::create([
                    'condominium_id' => $reservation->space->condominium_id,
                    'user_id' => $sindico->id,
                    'type' => 'reservation_cancelled',
                    'title' => 'Reserva Cancelada por Morador',
                    'message' => "{$reservation->user->name} cancelou a reserva do(a) {$reservation->space->name} para {$reservation->reservation_date->format('d/m/Y')}.",
                    'priority' => 'normal',
                    'read_at' => null,
                ]);
                
                // Enviar email
                try {
                    Mail::to($sindico->email)->send(
                        new \App\Mail\ReservationCancellationNotification($reservation, $cancelledBy, true)
                    );
                } catch (\Exception $e) {
                    Log::error('Erro ao enviar email ao síndico: ' . $e->getMessage());
                }
            }
        }
    }
    
    /**
     * Gera pagamento de forma síncrona e retorna dados
     */
    private function generatePaymentSync($reservation, $space, $customAmount = null)
    {
        $asaasService = app(\App\Services\AsaasService::class);
        $user = $reservation->user;
        $amount = $customAmount ?? $space->price_per_hour;

        // Criar cobrança local primeiro
        $charge = \App\Models\Charge::create([
            'condominium_id' => $reservation->unit->condominium_id,
            'unit_id' => $reservation->unit_id,
            'title' => "Taxa de Reserva - {$space->name}",
            'description' => "Reserva do(a) {$space->name} para o dia {$reservation->reservation_date->format('d/m/Y')}",
            'amount' => $amount,
            'due_date' => $reservation->reservation_date->copy()->subDays(1), // 1 dia antes
            'recurrence_period' => $reservation->reservation_date->format('Y-m-d'),
            'fine_percentage' => 0,
            'interest_rate' => 0,
            'type' => 'extra',
            'status' => 'pending',
            'generated_by' => 'reservation',
            'metadata' => [
                'reservation_id' => $reservation->id,
                'space_id' => $space->id,
            ],
        ]);

        // Criar ou atualizar cliente no Asaas
        $customerData = [
            'name' => $user->name,
            'email' => $user->email,
            'phone' => $user->phone,
            'mobilePhone' => $user->phone,
            'cpfCnpj' => $user->cpf,
            'externalReference' => 'USER-' . $user->id,
        ];

        $asaasCustomer = $asaasService->createOrUpdateCustomer($customerData);

        if (!$asaasCustomer) {
            throw new \Exception('Falha ao criar cliente no Asaas');
        }

        // Criar cobrança no Asaas
        $paymentData = [
            'customer' => $asaasCustomer['id'],
            'billingType' => 'UNDEFINED', // Permite todos os métodos
            'dueDate' => $charge->due_date->format('Y-m-d'),
            'value' => $amount,
            'description' => $charge->title,
            'externalReference' => 'RESERVATION-' . $reservation->id,
        ];

        $payment = $asaasService->createPayment($paymentData);

        if (!$payment) {
            throw new \Exception('Falha ao criar pagamento no Asaas');
        }

        // Atualizar cobrança com dados do Asaas
        $charge->update([
            'asaas_payment_id' => $payment['id'],
            'boleto_url' => $payment['bankSlipUrl'] ?? null,
        ]);

        // Gerar PIX QR Code
        $pixData = $asaasService->getPixQRCode($payment['id']);
        
        if ($pixData) {
            $charge->update([
                'pix_code' => $pixData['payload'] ?? null,
                'pix_qrcode' => $pixData['encodedImage'] ?? null,
            ]);
        }

        // Retornar dados formatados para o frontend
        return [
            'id' => $payment['id'],
            'value' => $amount,
            'due_date' => $charge->due_date->format('Y-m-d'),
            'pix_code' => $pixData['payload'] ?? null,
            'pix_qrcode' => $pixData['encodedImage'] ?? null,
            'invoice_url' => $payment['invoiceUrl'] ?? null,
            'boleto_url' => $payment['bankSlipUrl'] ?? null,
            'charge_id' => $charge->id,
        ];
    }
}
