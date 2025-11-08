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

class ChargeController extends Controller
{
    protected $asaasService;

    public function __construct(AsaasService $asaasService)
    {
        $this->asaasService = $asaasService;
    }

    /**
     * Lista todas as cobranças
     */
    public function index(Request $request)
    {
        $user = Auth::user();
        $condominiumId = $user->condominium_id;

        if (!$condominiumId) {
            return response()->json(['error' => 'Usuário não vinculado a um condomínio'], 403);
        }

        $query = Charge::with(['unit', 'payments'])
            ->where('condominium_id', $condominiumId);

        // Se for morador, mostrar apenas suas cobranças
        if ($user->isMorador() && $user->unit_id) {
            $query->where('unit_id', $user->unit_id);
        }

        // Filtros
        if ($request->has('status')) {
            $query->where('status', $request->status);
        }

        if ($request->has('unit_id')) {
            $query->where('unit_id', $request->unit_id);
        }

        if ($request->has('start_date') && $request->has('end_date')) {
            $query->whereBetween('due_date', [$request->start_date, $request->end_date]);
        }

        $charges = $query->orderBy('due_date', 'desc')->paginate(15);

        return response()->json($charges);
    }

    /**
     * Cria uma nova cobrança
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'unit_id' => 'required|exists:units,id',
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

        $user = Auth::user();

        $charge = Charge::create([
            'condominium_id' => $user->condominium_id,
            'unit_id' => $request->unit_id,
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

        return response()->json([
            'message' => 'Cobrança criada com sucesso',
            'charge' => $charge
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
        $condominiumId = $user->condominium_id;

        // Buscar unidades
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
            $charge = Charge::create([
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

            $chargesCreated[] = $charge;
        }

        return response()->json([
            'message' => count($chargesCreated) . ' cobranças criadas com sucesso',
            'charges' => $chargesCreated
        ], 201);
    }

    /**
     * Gera pagamento no Asaas para uma cobrança
     */
    public function generateAsaasPayment(Request $request, $id)
    {
        $charge = Charge::with('unit.users')->findOrFail($id);

        // Verificar permissão
        if ($charge->condominium_id !== Auth::user()->condominium_id) {
            return response()->json(['error' => 'Não autorizado'], 403);
        }

        // Verificar se já tem pagamento Asaas
        if ($charge->asaas_payment_id) {
            return response()->json([
                'error' => 'Esta cobrança já possui um pagamento gerado no Asaas'
            ], 400);
        }

        // Buscar morador da unidade
        $customer = $charge->unit->users()->first();

        if (!$customer) {
            return response()->json([
                'error' => 'Nenhum morador encontrado para esta unidade'
            ], 400);
        }

        // Despachar job para gerar pagamento
        GenerateAsaasPayment::dispatch($charge, $customer);

        return response()->json([
            'message' => 'Pagamento está sendo gerado no Asaas. Você receberá uma notificação quando estiver pronto.',
            'charge' => $charge
        ]);
    }

    /**
     * Exibe uma cobrança
     */
    public function show($id)
    {
        $charge = Charge::with(['unit', 'payments', 'condominium'])
            ->findOrFail($id);

        // Verificar permissão
        $user = Auth::user();
        if ($charge->condominium_id !== $user->condominium_id) {
            return response()->json(['error' => 'Não autorizado'], 403);
        }

        // Se for morador, só pode ver suas próprias cobranças
        if ($user->isMorador() && $charge->unit_id !== $user->unit_id) {
            return response()->json(['error' => 'Não autorizado'], 403);
        }

        return response()->json($charge);
    }

    /**
     * Atualiza uma cobrança
     */
    public function update(Request $request, $id)
    {
        $charge = Charge::findOrFail($id);

        // Verificar permissão
        if ($charge->condominium_id !== Auth::user()->condominium_id) {
            return response()->json(['error' => 'Não autorizado'], 403);
        }

        $validator = Validator::make($request->all(), [
            'title' => 'sometimes|string|max:255',
            'amount' => 'sometimes|numeric|min:0',
            'due_date' => 'sometimes|date',
            'status' => 'sometimes|in:pending,paid,overdue,cancelled',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $charge->update($request->all());

        return response()->json([
            'message' => 'Cobrança atualizada com sucesso',
            'charge' => $charge
        ]);
    }

    /**
     * Remove uma cobrança
     */
    public function destroy($id)
    {
        $charge = Charge::findOrFail($id);

        // Verificar permissão
        if ($charge->condominium_id !== Auth::user()->condominium_id) {
            return response()->json(['error' => 'Não autorizado'], 403);
        }

        // Não permitir deletar cobranças pagas
        if ($charge->status === 'paid') {
            return response()->json([
                'error' => 'Não é possível remover uma cobrança já paga'
            ], 400);
        }

        $charge->delete();

        return response()->json([
            'message' => 'Cobrança removida com sucesso'
        ]);
    }
}
