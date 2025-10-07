<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Transaction;
use App\Models\Receipt;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;

class TransactionController extends Controller
{
    /**
     * Lista todas as transações do condomínio
     */
    public function index(Request $request)
    {
        $user = Auth::user();
        $condominiumId = $user->condominium_id;

        if (!$condominiumId) {
            return response()->json(['error' => 'Usuário não vinculado a um condomínio'], 403);
        }

        $query = Transaction::with(['user', 'unit', 'receipts'])
            ->where('condominium_id', $condominiumId);

        // Filtros
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

        // Ordenação
        $sortBy = $request->get('sort_by', 'transaction_date');
        $sortOrder = $request->get('sort_order', 'desc');
        $query->orderBy($sortBy, $sortOrder);

        // Paginação
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
        
        $transaction = Transaction::create([
            'condominium_id' => $user->condominium_id,
            'unit_id' => $request->unit_id,
            'user_id' => $user->id,
            'type' => $request->type,
            'category' => $request->category,
            'subcategory' => $request->subcategory,
            'description' => $request->description,
            'amount' => $request->amount,
            'transaction_date' => $request->transaction_date,
            'due_date' => $request->due_date,
            'paid_date' => $request->status === 'paid' ? now() : null,
            'status' => $request->status,
            'payment_method' => $request->payment_method,
            'store_location' => $request->store_location,
            'is_recurring' => $request->boolean('is_recurring'),
            'recurrence_period' => $request->recurrence_period,
            'tags' => $request->tags,
            'notes' => $request->notes,
        ]);

        return response()->json([
            'message' => 'Transação criada com sucesso',
            'transaction' => $transaction->load(['user', 'receipts'])
        ], 201);
    }

    /**
     * Exibe uma transação específica
     */
    public function show($id)
    {
        $transaction = Transaction::with(['user', 'unit', 'receipts', 'condominium'])
            ->findOrFail($id);

        // Verificar se pertence ao condomínio do usuário
        if ($transaction->condominium_id !== Auth::user()->condominium_id) {
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

        // Verificar permissão
        if ($transaction->condominium_id !== Auth::user()->condominium_id) {
            return response()->json(['error' => 'Não autorizado'], 403);
        }

        $validator = Validator::make($request->all(), [
            'type' => 'sometimes|in:income,expense',
            'category' => 'sometimes|string|max:255',
            'description' => 'sometimes|string',
            'amount' => 'sometimes|numeric|min:0',
            'transaction_date' => 'sometimes|date',
            'status' => 'sometimes|in:pending,paid,overdue,cancelled',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $transaction->update($request->all());

        // Se mudou para pago, atualizar data
        if ($request->status === 'paid' && !$transaction->paid_date) {
            $transaction->update(['paid_date' => now()]);
        }

        return response()->json([
            'message' => 'Transação atualizada com sucesso',
            'transaction' => $transaction->load(['user', 'receipts'])
        ]);
    }

    /**
     * Remove uma transação
     */
    public function destroy($id)
    {
        $transaction = Transaction::findOrFail($id);

        // Verificar permissão
        if ($transaction->condominium_id !== Auth::user()->condominium_id) {
            return response()->json(['error' => 'Não autorizado'], 403);
        }

        $transaction->delete();

        return response()->json([
            'message' => 'Transação removida com sucesso'
        ]);
    }

    /**
     * Upload de comprovante
     */
    public function uploadReceipt(Request $request, $id)
    {
        $transaction = Transaction::findOrFail($id);

        // Verificar permissão
        if ($transaction->condominium_id !== Auth::user()->condominium_id) {
            return response()->json(['error' => 'Não autorizado'], 403);
        }

        $validator = Validator::make($request->all(), [
            'file' => 'required|file|mimes:pdf,jpg,jpeg,png|max:5120', // 5MB
            'description' => 'nullable|string|max:255',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $file = $request->file('file');
        
        // Salvar arquivo
        $path = $file->store('receipts/' . $transaction->condominium_id, 'public');

        // Criar registro
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
            'receipt' => $receipt
        ], 201);
    }

    /**
     * Lista comprovantes de uma transação
     */
    public function listReceipts($id)
    {
        $transaction = Transaction::findOrFail($id);

        // Verificar permissão
        if ($transaction->condominium_id !== Auth::user()->condominium_id) {
            return response()->json(['error' => 'Não autorizado'], 403);
        }

        $receipts = $transaction->receipts;

        return response()->json($receipts);
    }
}
