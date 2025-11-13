<?php

namespace App\Http\Controllers;

use App\Models\Charge;
use App\Models\Unit;
use App\Services\ChargeSettlementService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Collection;

class ChargeController extends Controller
{
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
        $condominiumId = $user->condominium_id;

        $baseQuery = Charge::with(['unit', 'payments'])
            ->where('condominium_id', $condominiumId);

        if ($user->isMorador() && !$user->isAdmin() && !$user->isSindico() && $user->unit_id) {
            $baseQuery->where('unit_id', $user->unit_id);
        }

        if ($request->filled('status')) {
            $baseQuery->where('status', $request->input('status'));
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

        $pendingCount = (clone $baseQuery)->where('status', 'pending')->count();
        $overdueCount = (clone $baseQuery)->where('status', 'overdue')->count();
        $paidThisMonth = (clone $baseQuery)
            ->where('status', 'paid')
            ->whereBetween('due_date', [now()->startOfMonth(), now()->endOfMonth()])
            ->count();
        $amountToReceive = (clone $baseQuery)
            ->whereIn('status', ['pending', 'overdue'])
            ->sum('amount');

        $perPage = (int) $request->input('per_page', 15);
        $charges = $chargesQuery->orderByDesc('due_date')->paginate($perPage);

        $unitOptions = $user->isMorador() && $user->unit_id
            ? Unit::where('id', $user->unit_id)->get(['id', 'block', 'number'])
            : Unit::where('condominium_id', $condominiumId)
                ->orderBy('block')
                ->orderBy('number')
                ->get(['id', 'block', 'number']);

        return response()->json([
            'data' => $charges->items(),
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
            ],
        ]);
    }

    public function show(Request $request, Charge $charge): JsonResponse
    {
        $user = $request->user();

        if ($charge->condominium_id !== $user->condominium_id) {
            abort(403);
        }

        if ($user->isMorador() && $user->unit_id && $charge->unit_id !== $user->unit_id) {
            abort(403);
        }

        $charge->load([
            'unit',
            'fee:id,name,billing_type',
            'payments:id,charge_id,payment_date,payment_method,amount_paid,created_at',
        ]);

        $paymentSummary = $charge->payments
            ->groupBy(fn ($payment) => strtoupper($payment->payment_method ?? 'OUTROS'))
            ->map(fn (Collection $group, $method) => [
                'method' => $method === 'OUTROS' ? 'Outros métodos' : $method,
                'transactions' => $group->count(),
                'total' => $group->sum('amount_paid'),
            ])
            ->values();

        return response()->json([
            'charge' => $charge,
            'payment_summary' => $paymentSummary,
            'can_manage' => $user->can('manage_charges'),
        ]);
    }

    public function destroy(Request $request, Charge $charge): RedirectResponse|JsonResponse
    {
        $user = $request->user();

        if (! $user->can('manage_charges')) {
            abort(403);
        }

        if ($charge->condominium_id !== $user->condominium_id) {
            abort(403);
        }

        try {
            $this->settlementService->cancelCharge(
                $charge,
                $request->input('reason') ?? null,
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
