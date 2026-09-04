<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Transaction;
use App\Models\Receipt;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class TransactionController extends Controller
{
    private const ALLOWED_SORT_COLUMNS = [
        'transaction_date',
        'amount',
        'created_at',
        'due_date',
        'status',
        'category',
    ];

    public function __construct()
    {
        $this->middleware('can:view_transactions')->only(['index', 'show', 'listReceipts']);
        $this->middleware('can:manage_transactions')->only(['store', 'update', 'destroy', 'uploadReceipt']);
    }

    /**
     * Lista todas as transações do condomínio
     */
    public function index(Request $request)
    {
        $user = Auth::user();
        $condominiumId = $user->tenantCondominiumId();

        if (!$condominiumId) {
            return response()->json(['error' => 'Usuário não vinculado a um condomínio'], 403);
        }

        $query = Transaction::with(['user', 'unit', 'receipts'])
            ->where('condominium_id', $condominiumId);

        if ($request->has('type')) {
            $query->where('type', $request->type);
        }

        if ($request->has('status')) {
            $query->where('status', $request->status);
        }

        if ($request->has('category')) {
            $query->where('category', $request->category);
        }

        if ($request->has('start_date') && $request->has('end_date')) {
            $query->whereBetween('transaction_date', [$request->start_date, $request->end_date]);
        }

        $sortBy = $request->get('sort_by', 'transaction_date');
        if (!in_array($sortBy, self::ALLOWED_SORT_COLUMNS, true)) {
            $sortBy = 'transaction_date';
        }

        $sortOrder = strtolower((string) $request->get('sort_order', 'desc')) === 'asc' ? 'asc' : 'desc';
        $query->orderBy($sortBy, $sortOrder);

        $perPage = $request->get('per_page', 15);
        $transactions = $query->paginate($perPage);

        return response()->json($transactions);
    }

    /**
     * Cria uma nova transação
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'type' => 'required|in:income,expense',
            'category' => 'required|string|max:255',
            'subcategory' => 'nullable|string|max:255',
            'description' => 'required|string',
            'amount' => 'required|numeric|min:0',
            'transaction_date' => 'required|date',
            'due_date' => 'nullable|date',
            'status' => 'required|in:pending,paid,overdue,cancelled',
            'payment_method' => 'nullable|in:cash,pix,bank_transfer,credit_card,debit_card,check,boleto,other',
            'store_location' => 'nullable|string|max:255',
            'is_recurring' => 'boolean',
            'recurrence_period' => 'nullable|string|in:monthly,yearly',
            'tags' => 'nullable|array',
            'notes' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $user = Auth::user();
        $validated = $validator->validated();

        $transaction = Transaction::create([
            'condominium_id' => $user->tenantCondominiumId(),
            'unit_id' => $request->unit_id,
            'user_id' => $user->id,
            'type' => $validated['type'],
            'category' => $validated['category'],
            'subcategory' => $validated['subcategory'] ?? null,
            'description' => $validated['description'],
            'amount' => $validated['amount'],
            'transaction_date' => $validated['transaction_date'],
            'due_date' => $validated['due_date'] ?? null,
            'paid_date' => $validated['status'] === 'paid' ? now() : null,
            'status' => $validated['status'],
            'payment_method' => $validated['payment_method'] ?? null,
            'store_location' => $validated['store_location'] ?? null,
            'is_recurring' => $request->boolean('is_recurring'),
            'recurrence_period' => $validated['recurrence_period'] ?? null,
            'tags' => $validated['tags'] ?? null,
            'notes' => $validated['notes'] ?? null,
        ]);

        return response()->json([
            'message' => 'Transação criada com sucesso',
            'transaction' => $transaction->load(['user', 'receipts']),
        ], 201);
    }

    /**
     * Exibe uma transação específica
     */
    public function show($id)
    {
        $transaction = Transaction::with(['user', 'unit', 'receipts', 'condominium'])
            ->findOrFail($id);

        if ($transaction->condominium_id !== Auth::user()->tenantCondominiumId()) {
            return response()->json(['error' => 'Não autorizado'], 403);
        }

        return response()->json($transaction);
    }

    /**
     * Atualiza uma transação
     */
    public function update(Request $request, $id)
    {
        $transaction = Transaction::findOrFail($id);

        if ($transaction->condominium_id !== Auth::user()->tenantCondominiumId()) {
            return response()->json(['error' => 'Não autorizado'], 403);
        }

        $validator = Validator::make($request->all(), [
            'type' => 'sometimes|in:income,expense',
            'category' => 'sometimes|string|max:255',
            'subcategory' => 'nullable|string|max:255',
            'description' => 'sometimes|string',
            'amount' => 'sometimes|numeric|min:0',
            'transaction_date' => 'sometimes|date',
            'due_date' => 'nullable|date',
            'status' => 'sometimes|in:pending,paid,overdue,cancelled',
            'payment_method' => 'nullable|in:cash,pix,bank_transfer,credit_card,debit_card,check,boleto,other',
            'store_location' => 'nullable|string|max:255',
            'is_recurring' => 'boolean',
            'recurrence_period' => 'nullable|string|in:monthly,yearly',
            'tags' => 'nullable|array',
            'notes' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $validated = $validator->validated();
        $transaction->update($validated);

        if (($validated['status'] ?? null) === 'paid' && !$transaction->paid_date) {
            $transaction->update(['paid_date' => now()]);
        }

        return response()->json([
            'message' => 'Transação atualizada com sucesso',
            'transaction' => $transaction->load(['user', 'receipts']),
        ]);
    }

    /**
     * Remove uma transação
     */
    public function destroy($id)
    {
        $transaction = Transaction::findOrFail($id);

        if ($transaction->condominium_id !== Auth::user()->tenantCondominiumId()) {
            return response()->json(['error' => 'Não autorizado'], 403);
        }

        $transaction->delete();

        return response()->json([
            'message' => 'Transação removida com sucesso',
        ]);
    }

    /**
     * Upload de comprovante
     */
    public function uploadReceipt(Request $request, $id)
    {
        $transaction = Transaction::findOrFail($id);

        if ($transaction->condominium_id !== Auth::user()->tenantCondominiumId()) {
            return response()->json(['error' => 'Não autorizado'], 403);
        }

        $validator = Validator::make($request->all(), [
            'file' => 'required|file|mimes:pdf,jpg,jpeg,png|max:5120',
            'description' => 'nullable|string|max:255',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $file = $request->file('file');

        $path = $file->store('receipts/' . $transaction->condominium_id, 'public');

        $receipt = Receipt::create([
            'transaction_id' => $transaction->id,
            'original_filename' => $file->getClientOriginalName(),
            'storage_path' => $path,
            'mime_type' => $file->getMimeType(),
            'file_size' => $file->getSize(),
            'description' => $request->description,
        ]);

        return response()->json([
            'message' => 'Comprovante enviado com sucesso',
            'receipt' => $receipt,
        ], 201);
    }

    /**
     * Lista comprovantes de uma transação
     */
    public function listReceipts($id)
    {
        $transaction = Transaction::findOrFail($id);

        if ($transaction->condominium_id !== Auth::user()->tenantCondominiumId()) {
            return response()->json(['error' => 'Não autorizado'], 403);
        }

        return response()->json($transaction->receipts);
    }
}
