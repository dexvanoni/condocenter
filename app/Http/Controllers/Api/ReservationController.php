<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Jobs\SendReservationNotification;
use App\Models\Condominium;
use App\Models\RecurringReservation;
use App\Models\Reservation;
use App\Models\Space;
use App\Services\CondominiumAsaasSettingsService;
use App\Services\ReservationChargeService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class ReservationController extends Controller
{
    public function __construct(
        private readonly ReservationChargeService $reservationChargeService,
        private readonly CondominiumAsaasSettingsService $condominiumAsaasSettings,
    ) {
    }

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
            $q->where('condominium_id', $user->tenantCondominiumId());
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

        $condominium = Condominium::query()->find($user->tenantCondominiumId());
        $onlinePaymentsEnabled = $condominium
            && $this->condominiumAsaasSettings->acceptsOnlinePayments($condominium);

        // Verificar se o espaço pertence ao condomínio do usuário
        if ($space->condominium_id !== $user->tenantCondominiumId()) {
            return response()->json(['error' => 'Espaço não pertence ao seu condomínio'], 403);
        }

        // Verificar se o espaço está ativo
        if (!$space->is_active) {
            return response()->json(['error' => 'Este espaço não está disponível para reservas'], 400);
        }

        // Validação de conflitos baseada no modo de reserva
        if ($space->reservation_mode === 'full_day') {
            if ($this->isDayBlocked($space->id, $request->reservation_date)) {
                return response()->json([
                    'error' => 'Este espaço já está reservado para esta data. Por favor, escolha outra data.',
                    'conflict' => true
                ], 400);
            }

            $startTime = $this->formatTimeValue($space->available_from);
            $endTime = $this->formatTimeValue($space->available_until);

        } else {
            // MODO POR HORÁRIO: Validar conflitos de horário específico
            if (!$request->start_time || !$request->end_time) {
                return response()->json([
                    'error' => 'Para este espaço, você deve informar horário de início e término'
                ], 400);
            }

            $startTime = $this->formatTimeValue($request->start_time);
            $endTime = $this->formatTimeValue($request->end_time);

            if ($this->hasSchedulingConflict($space->id, $request->reservation_date, $startTime, $endTime)) {
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
            $reservationData['prereservation_amount'] = $this->reservationChargeService->calculateAmount(
                new Reservation($reservationData),
                $space
            );
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
        if ($space->price_per_hour > 0 && $space->approval_type === 'automatic') {
            $generatedCharge = $this->reservationChargeService->createForReservation(
                $reservation,
                $space,
                context: 'automatic'
            );
        }

        $creditUsed = false;
        $totalReservationAmount = $this->reservationChargeService->calculateAmount($reservation, $space);
        $remainingAmount = $totalReservationAmount;
        
        // Lógica de pagamento para pré-reservas com taxa
        if ($space->approval_type === 'prereservation' && $totalReservationAmount > 0) {
            // Buscar créditos disponíveis do usuário
            $availableCredits = \App\Models\UserCredit::where('user_id', $user->id)
                ->where('condominium_id', $user->tenantCondominiumId())
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
            
            // Se ainda sobrar valor, gerar cobrança e checkout
            if ($remainingAmount > 0) {
                $generatedCharge = $this->reservationChargeService->createForReservation(
                    $reservation,
                    $space,
                    $remainingAmount,
                    'prereservation'
                );
            } elseif ($creditUsed) {
                $reservation->markAsPaid('credits');
            }
        }

        // Enviar notificação apropriada (em modo síncrono para evitar erros)
        try {
            if ($space->approval_type === 'prereservation') {
                SendReservationNotification::dispatchSync($reservation, 'pending_payment');
                $message = $onlinePaymentsEnabled
                    ? 'Pré-reserva criada! Realize o pagamento para confirmar.'
                    : 'Pré-reserva criada! Procure a administração do condomínio para efetuar o pagamento.';
            } elseif ($space->approval_type === 'manual') {
                SendReservationNotification::dispatchSync($reservation, 'pending');
                $message = 'Reserva enviada para aprovação do síndico.';
            } else {
                SendReservationNotification::dispatchSync($reservation, 'approved');
                $message = 'Reserva confirmada automaticamente!';
            }
        } catch (\Exception $e) {
            Log::error('Erro ao enviar notificação: ' . $e->getMessage());
            if ($space->approval_type === 'prereservation') {
                $message = $onlinePaymentsEnabled
                    ? 'Pré-reserva criada! Realize o pagamento para confirmar.'
                    : 'Pré-reserva criada! Procure a administração do condomínio para efetuar o pagamento.';
            } elseif ($space->approval_type === 'manual') {
                $message = 'Reserva enviada para aprovação do síndico.';
            } else {
                $message = 'Reserva confirmada automaticamente!';
            }
        }

        if ($generatedCharge) {
            $message .= $onlinePaymentsEnabled
                ? ' Foi gerada uma cobrança para esta reserva. Você pode pagar online em Minhas Cobranças.'
                : ' Foi gerada uma cobrança para esta reserva. O pagamento deve ser feito conforme orientação da administração.';
        }
        
        if ($space->approval_type === 'prereservation' && $totalReservationAmount > 0) {
            if ($creditUsed) {
                $creditsApplied = $totalReservationAmount - $remainingAmount;
                $message .= " Créditos aplicados: R$ " . number_format($creditsApplied, 2, ',', '.');
                
                if ($remainingAmount > 0) {
                    $message .= " Restante a pagar: R$ " . number_format($remainingAmount, 2, ',', '.');
                    if (!$onlinePaymentsEnabled) {
                        $message .= ' Procure a administração para efetuar o pagamento.';
                    }
                } else {
                    $message .= " Reserva totalmente paga com créditos!";
                }
            } elseif ($remainingAmount > 0) {
                $message .= " Valor pendente: R$ " . number_format($remainingAmount, 2, ',', '.') . '.';
                if ($onlinePaymentsEnabled) {
                    $message .= ' O pagamento online estará disponível em Minhas Cobranças.';
                } else {
                    $message .= ' Procure a administração do condomínio para efetuar o pagamento.';
                }
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
            'has_charge' => (bool) ($generatedCharge || $remainingAmount > 0),
            'online_payments_enabled' => $onlinePaymentsEnabled,
            'amount' => $remainingAmount,
            'credit_used' => $creditUsed,
            'credit_amount' => $creditUsed ? ($totalReservationAmount - $remainingAmount) : 0,
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
        if ($space->condominium_id !== $user->tenantCondominiumId()) {
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
                $data['start_time'] = $this->formatTimeValue($reservation->start_time);
                $data['end_time'] = $this->formatTimeValue($reservation->end_time);
                
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
                        'start_time' => $this->formatTimeValue($recurring->start_time),
                        'end_time' => $this->formatTimeValue($recurring->end_time),
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
        if ($reservation->space->condominium_id !== $user->tenantCondominiumId()) {
            return response()->json(['error' => 'Não autorizado'], 403);
        }

        if (!$user->can('approve_reservations')) {
            return response()->json(['error' => 'Sem permissão para aprovar reservas'], 403);
        }

        $reservation->approve($user->id);

        $reservation->load('space');
        $space = $reservation->space;

        if ($space && $space->price_per_hour > 0) {
            $this->reservationChargeService->createForReservation(
                $reservation,
                $space,
                context: 'manual_approval'
            );
        }

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
        if ($reservation->space->condominium_id !== $user->tenantCondominiumId()) {
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
        if ($reservation->space->condominium_id !== $user->tenantCondominiumId()) {
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

        $charge = $this->reservationChargeService->findForReservation($reservation);

        if ($charge && $charge->status === 'paid') {
            $reservation->markAsPaid($charge->asaas_payment_id ?: ('charge:' . $charge->id));
        } elseif ($charge) {
            return response()->json([
                'error' => 'A cobrança desta pré-reserva ainda não foi paga. Utilize Minhas Cobranças para pagar.',
                'charge_id' => $charge->id,
            ], 400);
        } else {
            $reservation->markAsPaid($request->payment_reference ?? 'confirmed');
        }

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
        $cancellationResult = $this->reservationChargeService->handleReservationCancellation($reservation);
        $creditGenerated = $cancellationResult['credit_generated'];
        $chargeDeleted = $cancellationResult['charge_cancelled'];
        $creditAmount = $cancellationResult['credit_amount'];

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
            $message .= " Um crédito de R$ " . number_format($creditAmount, 2, ',', '.') . " foi adicionado à sua carteira. Válido por 12 meses.";
        } elseif ($chargeDeleted) {
            $message .= " A cobrança pendente foi removida.";
        }

        return response()->json([
            'message' => $message,
            'notifications_sent' => true,
            'credit_generated' => $creditGenerated,
            'credit_amount' => $creditGenerated ? $creditAmount : 0,
            'charge_deleted' => $chargeDeleted
        ]);
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
    
    private function formatTimeValue(?string $time): string
    {
        if (!$time) {
            return '00:00';
        }

        return substr((string) $time, 0, 5);
    }

    private function timesOverlap(string $startA, string $endA, string $startB, string $endB): bool
    {
        $startA = $this->formatTimeValue($startA);
        $endA = $this->formatTimeValue($endA);
        $startB = $this->formatTimeValue($startB);
        $endB = $this->formatTimeValue($endB);

        return $startA < $endB && $endA > $startB;
    }

    private function hasSchedulingConflict(int $spaceId, string $date, string $startTime, string $endTime): bool
    {
        $reservations = Reservation::where('space_id', $spaceId)
            ->where('reservation_date', $date)
            ->whereIn('status', ['pending', 'approved'])
            ->get(['start_time', 'end_time']);

        foreach ($reservations as $reservation) {
            if ($this->timesOverlap($startTime, $endTime, $reservation->start_time, $reservation->end_time)) {
                return true;
            }
        }

        $dayOfWeek = Carbon::parse($date)->dayOfWeek;
        $recurringReservations = RecurringReservation::where('space_id', $spaceId)
            ->where('status', 'active')
            ->where('start_date', '<=', $date)
            ->where('end_date', '>=', $date)
            ->get();

        foreach ($recurringReservations as $recurring) {
            if (!in_array($dayOfWeek, array_map('intval', $recurring->days_of_week ?? []), true)) {
                continue;
            }

            if ($this->timesOverlap($startTime, $endTime, $recurring->start_time, $recurring->end_time)) {
                return true;
            }
        }

        return false;
    }

    private function isDayBlocked(int $spaceId, string $date): bool
    {
        $hasReservation = Reservation::where('space_id', $spaceId)
            ->where('reservation_date', $date)
            ->whereIn('status', ['pending', 'approved'])
            ->exists();

        if ($hasReservation) {
            return true;
        }

        $dayOfWeek = Carbon::parse($date)->dayOfWeek;

        return RecurringReservation::where('space_id', $spaceId)
            ->where('status', 'active')
            ->where('start_date', '<=', $date)
            ->where('end_date', '>=', $date)
            ->get()
            ->contains(function (RecurringReservation $recurring) use ($dayOfWeek) {
                return in_array($dayOfWeek, array_map('intval', $recurring->days_of_week ?? []), true);
            });
    }
}
