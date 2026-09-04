<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Charge;
use App\Models\Unit;
use App\Services\AsaasService;
use App\Jobs\GenerateAsaasPayment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

class ChargeController extends Controller
{
    public function __construct(
        protected AsaasService $asaasService,
    ) {
        $this->middleware('can:view_charges')->only(['index', 'show']);
        $this->middleware('can:manage_charges')->only(['store', 'update', 'destroy', 'bulkCreate']);
    }

    /**
     * Lista todas as cobranças
     */
    public function index(Request $request)
    {
        $user = Auth::user();
        $condominiumId = $user->tenantCondominiumId();

        if (!$condominiumId) {
            return response()->json(['error' => 'Usuário não vinculado a um condomínio'], 403);
        }

        $baseQuery = Charge::with(['unit', 'payments'])
            ->where('condominium_id', $condominiumId);

        if ($user->isMorador() && !$user->can('manage_charges') && $user->unit_id) {
            $baseQuery->where('unit_id', $user->unit_id);
        } elseif ($request->filled('unit_id') && $user->can('manage_charges')) {
            $baseQuery->where('unit_id', $request->integer('unit_id'));
        }

        if ($request->has('status')) {
            $baseQuery->where('status', $request->status);
        }

        if ($request->has('start_date') && $request->has('end_date')) {
            $baseQuery->whereBetween('due_date', [$request->start_date, $request->end_date]);
        }

        if ($request->filled('search')) {
            $term = trim($request->search);
            $baseQuery->where(function ($q) use ($term) {
                $q->where('title', 'like', '%' . $term . '%')
                    ->orWhereHas('unit', function ($unitQuery) use ($term) {
                        $unitQuery->where('full_identifier', 'like', '%' . $term . '%');
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

        $perPage = $request->integer('per_page', 15);
        $charges = $chargesQuery->orderBy('due_date', 'desc')->paginate($perPage);

        $unitOptions = $user->isMorador() && $user->unit_id && !$user->can('manage_charges')
            ? Unit::where('id', $user->unit_id)->get(['id', 'full_identifier'])
            : Unit::where('condominium_id', $condominiumId)
                ->orderBy('block')
                ->orderBy('number')
                ->get(['id', 'full_identifier']);

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
        ]);
    }

    /**
     * Cria uma nova cobrança
     */
    public function store(Request $request)
    {
        $user = Auth::user();
        $condominiumId = $user->tenantCondominiumId();

        $validator = Validator::make($request->all(), [
            'unit_id' => [
                'required',
                Rule::exists('units', 'id')->where(fn ($query) => $query->where('condominium_id', $condominiumId)),
            ],
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'amount' => 'required|numeric|min:0',
            'due_date' => 'required|date',
            'fine_percentage' => 'nullable|numeric|min:0|max:100',
            'interest_rate' => 'nullable|numeric|min:0|max:100',
            'type' => 'required|in:regular,extra',
            'recurrence_period' => 'nullable|string|max:20',
            'metadata' => 'nullable|array',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $validated = $validator->validated();

        $charge = Charge::create([
            'condominium_id' => $condominiumId,
            'unit_id' => $validated['unit_id'],
            'title' => $validated['title'],
            'description' => $validated['description'] ?? null,
            'amount' => $validated['amount'],
            'due_date' => $validated['due_date'],
            'recurrence_period' => $validated['recurrence_period'] ?? null,
            'fine_percentage' => $validated['fine_percentage'] ?? 2.00,
            'interest_rate' => $validated['interest_rate'] ?? 1.00,
            'type' => $validated['type'],
            'status' => 'pending',
            'generated_by' => 'manual',
            'metadata' => $validated['metadata'] ?? null,
        ]);

        return response()->json([
            'message' => 'Cobrança criada com sucesso',
            'charge' => $charge,
        ], 201);
    }

    /**
     * Cria cobranças em lote para todas as unidades
     */
    public function bulkCreate(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'amount' => 'required|numeric|min:0',
            'due_date' => 'required|date',
            'type' => 'required|in:regular,extra',
            'apply_to_all_units' => 'boolean',
            'unit_ids' => 'required_if:apply_to_all_units,false|array',
            'recurrence_period' => 'nullable|string|max:20',
            'metadata' => 'nullable|array',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $user = Auth::user();
        $condominiumId = $user->tenantCondominiumId();

        if ($request->boolean('apply_to_all_units')) {
            $units = Unit::where('condominium_id', $condominiumId)
                ->where('is_active', true)
                ->get();
        } else {
            $units = Unit::whereIn('id', $request->unit_ids)
                ->where('condominium_id', $condominiumId)
                ->get();
        }

        $chargesCreated = [];

        foreach ($units as $unit) {
            $chargesCreated[] = Charge::create([
                'condominium_id' => $condominiumId,
                'unit_id' => $unit->id,
                'title' => $request->title,
                'description' => $request->description,
                'amount' => $request->amount,
                'due_date' => $request->due_date,
                'recurrence_period' => $request->recurrence_period,
                'fine_percentage' => $request->fine_percentage ?? 2.00,
                'interest_rate' => $request->interest_rate ?? 1.00,
                'type' => $request->type,
                'status' => 'pending',
                'generated_by' => 'manual',
                'metadata' => $request->metadata,
            ]);
        }

        return response()->json([
            'message' => count($chargesCreated) . ' cobranças criadas com sucesso',
            'charges' => $chargesCreated,
        ], 201);
    }

    /**
     * Gera pagamento no Asaas para uma cobrança
     */
    public function generateAsaasPayment(Request $request, $id)
    {
        $charge = Charge::with('unit.users')->findOrFail($id);

        $this->authorize('generatePayment', $charge);

        if ($charge->asaas_payment_id) {
            return response()->json([
                'error' => 'Esta cobrança já possui um pagamento gerado no Asaas',
            ], 400);
        }

        $customer = $charge->unit->users()->first();

        if (!$customer) {
            return response()->json([
                'error' => 'Nenhum morador encontrado para esta unidade',
            ], 400);
        }

        GenerateAsaasPayment::dispatch($charge, $customer);

        return response()->json([
            'message' => 'Pagamento está sendo gerado no Asaas. Você receberá uma notificação quando estiver pronto.',
            'charge' => $charge,
        ]);
    }

    /**
     * Exibe uma cobrança
     */
    public function show($id)
    {
        $charge = Charge::with(['unit', 'payments', 'condominium'])->findOrFail($id);

        $this->authorize('view', $charge);

        return response()->json($charge);
    }

    /**
     * Atualiza uma cobrança
     */
    public function update(Request $request, $id)
    {
        $charge = Charge::findOrFail($id);

        $this->authorize('update', $charge);

        $validator = Validator::make($request->all(), [
            'title' => 'sometimes|string|max:255',
            'description' => 'nullable|string',
            'amount' => 'sometimes|numeric|min:0',
            'due_date' => 'sometimes|date',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $charge->update($validator->validated());

        return response()->json([
            'message' => 'Cobrança atualizada com sucesso',
            'charge' => $charge,
        ]);
    }

    /**
     * Remove uma cobrança
     */
    public function destroy($id)
    {
        $charge = Charge::findOrFail($id);

        $this->authorize('delete', $charge);

        if ($charge->status === 'paid') {
            return response()->json([
                'error' => 'Não é possível remover uma cobrança já paga',
            ], 400);
        }

        $charge->delete();

        return response()->json([
            'message' => 'Cobrança removida com sucesso',
        ]);
    }
}
