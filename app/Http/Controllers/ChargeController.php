<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Concerns\ResolvesActiveCondominium;
use App\Models\Charge;
use App\Models\Condominium;
use App\Models\Unit;
use App\Services\ChargeReceiptService;
use App\Services\ChargeSettlementService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Collection;

class ChargeController extends Controller
{
    use ResolvesActiveCondominium;

    public function __construct(
        private readonly ChargeSettlementService $settlementService,
    ) {
    }

    public function index()
    {
        return view('charges.index');
    }

    public function data(Request $request): JsonResponse
    {
        $user = Auth::user();
        $condominiumId = $this->activeCondominiumId($user);

        $baseQuery = Charge::with(['unit', 'payments'])
            ->where('condominium_id', $condominiumId);

        if ($user->isProprietario() && !$user->isAdmin() && !$user->isSindico()) {
            $ownedIds = app(\App\Services\UnitOccupancyService::class)->ownedUnitIds($user, $condominiumId);
            if ($ownedIds !== []) {
                $baseQuery->whereIn('unit_id', $ownedIds);
            }
        } elseif ($user->isMorador() && !$user->isAdmin() && !$user->isSindico() && $user->unit_id) {
            $baseQuery->where('unit_id', $user->unit_id);
        }

        if ($request->filled('status')) {
            $status = $request->input('status');

            if ($status === 'overdue') {
                $baseQuery->effectivelyOverdue();
            } elseif ($status === 'pending') {
                $baseQuery->effectivelyPending();
            } else {
                $baseQuery->where('status', $status);
            }
        }

        if ($request->filled('unit_id')) {
            $baseQuery->where('unit_id', $request->input('unit_id'));
        }

        if ($request->filled('start_date') && $request->filled('end_date')) {
            $baseQuery->whereBetween('due_date', [
                $request->input('start_date'),
                $request->input('end_date'),
            ]);
        }

        if ($request->filled('search')) {
            $term = trim($request->input('search'));
            $baseQuery->where(function ($query) use ($term) {
                $query->where('title', 'like', "%{$term}%")
                    ->orWhereHas('unit', function ($unitQuery) use ($term) {
                        $unitQuery->where(function ($q) use ($term) {
                            $q->where('number', 'like', "%{$term}%")
                                ->orWhere('block', 'like', "%{$term}%")
                                ->orWhere('type', 'like', "%{$term}%");
                        })
                        ->orWhereHas('morador', function ($residentQuery) use ($term) {
                            $residentQuery->where('name', 'like', "%{$term}%");
                        });
                    });
            });
        }

        $chargesQuery = clone $baseQuery;

        $pendingCount = (clone $baseQuery)->effectivelyPending()->count();
        $overdueCount = (clone $baseQuery)->effectivelyOverdue()->count();
        $paidThisMonth = (clone $baseQuery)
            ->where('status', 'paid')
            ->whereBetween('due_date', [now()->startOfMonth(), now()->endOfMonth()])
            ->count();
        $amountToReceive = (clone $baseQuery)
            ->whereIn('status', ['pending', 'overdue'])
            ->sum('amount');

        $perPage = (int) $request->input('per_page', 15);
        $charges = $chargesQuery->orderByDesc('due_date')->paginate($perPage);

        $condominium = Condominium::query()->find($condominiumId);
        $onlinePaymentsEnabled = $condominium?->acceptsOnlinePayments() ?? false;
        $userCanPayOnline = $onlinePaymentsEnabled && filled($user->unit_id);

        $unitOptions = $user->isMorador() && $user->unit_id
            ? Unit::where('id', $user->unit_id)->get(['id', 'block', 'number'])
            : Unit::where('condominium_id', $condominiumId)
                ->orderBy('block')
                ->orderBy('number')
                ->get(['id', 'block', 'number']);

        $isResidentViewer = $user->isMorador() && !$user->isAdmin() && !$user->isSindico();

        return response()->json([
            'data' => collect($charges->items())->map(function (Charge $charge) use ($user, $userCanPayOnline, $isResidentViewer) {
                $payload = $charge->toArray();
                $payload['can_pay_online'] = $userCanPayOnline
                    && (int) $charge->unit_id === (int) $user->unit_id
                    && in_array($charge->status, ['pending', 'overdue'], true);
                $payload['competence_period'] = $charge->competencePeriod();
                $payload['competence_label'] = $charge->competenceLabel();
                $payload['display_status'] = $charge->displayStatus();
                $payload['status'] = $charge->effectiveStatus();
                $payload['payment_channel'] = $charge->paymentChannel();

                if ($isResidentViewer) {
                    $payload['amount_paid_display'] = $charge->status === 'paid'
                        ? $charge->residentPaidAmount()
                        : null;
                    $payload['payments'] = collect($charge->payments)->map(fn ($payment) => [
                        'payment_date' => $payment->payment_date,
                        'payment_method' => $payment->payment_method,
                        'amount_paid' => $payment->displayAmount(),
                    ])->values()->all();
                }

                return $payload;
            })->values(),
            'meta' => [
                'current_page' => $charges->currentPage(),
                'last_page' => $charges->lastPage(),
                'per_page' => $charges->perPage(),
                'total' => $charges->total(),
            ],
            'summary' => [
                'pending' => $pendingCount,
                'overdue' => $overdueCount,
                'paid_this_month' => $paidThisMonth,
                'amount_to_receive' => $amountToReceive,
            ],
            'filters' => [
                'units' => $unitOptions->map(fn ($unit) => [
                    'id' => $unit->id,
                    'label' => $unit->full_identifier,
                ])->values(),
            ],
            'permissions' => [
                'can_manage' => $user->can('manage_charges'),
                'online_payments_enabled' => $onlinePaymentsEnabled,
                'can_pay_online' => $userCanPayOnline,
            ],
        ]);
    }

    public function showTenantPayable(Request $request, Charge $charge): JsonResponse
    {
        $user = $request->user();
        $occupancy = app(\App\Services\UnitOccupancyService::class);

        $this->ensureResourceBelongsToActiveCondominium($user, (int) $charge->condominium_id);

        if (!$occupancy->isMoradorResponsibleCharge($charge)
            || !$occupancy->canUserPayCharge($user, $charge)) {
            abort(403, 'Esta cobrança não está disponível para você.');
        }

        return $this->show($request, $charge);
    }

    public function show(Request $request, Charge $charge): JsonResponse
    {
        $user = $request->user();
        $occupancy = app(\App\Services\UnitOccupancyService::class);

        $this->ensureResourceBelongsToActiveCondominium($user, (int) $charge->condominium_id);

        if ($user->isProprietario() && !$user->isAdmin() && !$user->isSindico()) {
            $charge->loadMissing('unit');
            if (!$charge->unit || !$occupancy->userOwnsUnit($user, $charge->unit)) {
                abort(403);
            }
        } elseif ($user->isMorador() && $user->unit_id && $charge->unit_id !== $user->unit_id) {
            abort(403);
        }

        $charge->load([
            'unit',
            'fee:id,name,billing_type',
            'payments:id,charge_id,payment_date,payment_method,amount_paid,created_at',
        ]);

        $isResidentViewer = $user->isMorador() && !$user->isAdmin() && !$user->isSindico();

        $paymentSummary = $charge->payments
            ->groupBy(fn ($payment) => strtoupper($payment->payment_method ?? 'OUTROS'))
            ->map(fn (Collection $group, $method) => [
                'method' => $method === 'OUTROS' ? 'Outros métodos' : $method,
                'transactions' => $group->count(),
                'total' => $group->sum(fn ($payment) => $isResidentViewer
                    ? $payment->displayAmount()
                    : (float) $payment->amount_paid),
            ])
            ->values();

        $condominium = $charge->condominium ?? Condominium::query()->find($charge->condominium_id);
        $onlinePaymentsEnabled = $condominium?->acceptsOnlinePayments() ?? false;
        $canPayOnline = $onlinePaymentsEnabled
            && $occupancy->canUserPayCharge($user, $charge)
            && in_array($charge->status, ['pending', 'overdue'], true);

        return response()->json([
            'charge' => array_merge($charge->toArray(), [
                'competence_period' => $charge->competencePeriod(),
                'competence_label' => $charge->competenceLabel(),
                'display_status' => $charge->displayStatus(),
                'status' => $charge->effectiveStatus(),
                'payment_channel' => $charge->paymentChannel(),
            ]),
            'payment_summary' => $paymentSummary,
            'can_manage' => $user->can('manage_charges'),
            'can_pay_online' => $canPayOnline,
            'online_payments_enabled' => $onlinePaymentsEnabled,
            'receipt_url' => $charge->status === 'paid'
                ? route('charges.receipt', $charge)
                : null,
        ]);
    }

    public function receipt(Request $request, Charge $charge, ChargeReceiptService $receiptService)
    {
        $user = $request->user();

        $this->ensureResourceBelongsToActiveCondominium($user, (int) $charge->condominium_id);

        if ($user->isMorador() && $user->unit_id && $charge->unit_id !== $user->unit_id) {
            abort(403);
        }

        return $receiptService->download($charge);
    }

    public function destroy(Request $request, Charge $charge): RedirectResponse|JsonResponse
    {
        $user = $request->user();

        if (! $user->can('manage_charges')) {
            abort(403);
        }

        $this->ensureResourceBelongsToActiveCondominium($user, (int) $charge->condominium_id);

        // Validação: motivo obrigatório
        $validated = $request->validate([
            'reason' => ['required', 'string', 'min:10'],
        ], [
            'reason.required' => 'O motivo do cancelamento é obrigatório.',
            'reason.min' => 'O motivo do cancelamento deve ter no mínimo 10 caracteres.',
        ]);

        try {
            $this->settlementService->cancelCharge(
                $charge,
                $validated['reason'],
                $user->id
            );
        } catch (ValidationException $exception) {
            if ($request->expectsJson()) {
                return response()->json([
                    'message' => $exception->getMessage(),
                    'errors' => $exception->errors(),
                ], 422);
            }

            return redirect()->back()->withErrors($exception->errors());
        }

        if ($request->expectsJson()) {
            return response()->json([
                'message' => 'Cobrança cancelada com sucesso.',
            ]);
        }

        return redirect()->route('charges.index')->with('success', 'Cobrança cancelada com sucesso.');
    }
}
