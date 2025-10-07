<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Message;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class MessageController extends Controller
{
    /**
     * Lista mensagens
     */
    public function index(Request $request)
    {
        $user = Auth::user();
        
        $query = Message::with(['fromUser', 'toUser'])
            ->where('condominium_id', $user->condominium_id)
            ->where(function ($q) use ($user) {
                $q->where('to_user_id', $user->id)
                  ->orWhere('from_user_id', $user->id)
                  ->orWhereNull('to_user_id'); // Mensagens para todos
            });

        // Filtros
        if ($request->has('type')) {
            $query->where('type', $request->type);
        }

        if ($request->has('is_read')) {
            $query->where('is_read', $request->boolean('is_read'));
        }

        $messages = $query->orderBy('created_at', 'desc')->paginate(20);

        return response()->json($messages);
    }

    /**
     * Cria uma nova mensagem
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'to_user_id' => 'nullable|exists:users,id',
            'type' => 'required|in:announcement,sindico_message,marketplace_inquiry,panic_alert',
            'subject' => 'nullable|string|max:255',
            'message' => 'required|string',
            'priority' => 'nullable|in:low,normal,high,urgent',
            'related_item_id' => 'nullable|integer',
            'related_item_type' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        /** @var \App\Models\User $user */
        $user = Auth::user();

        // Se for anúncio, verificar permissão
        if ($request->type === 'announcement' && !$user->can('send_announcements')) {
            return response()->json(['error' => 'Sem permissão para enviar anúncios'], 403);
        }

        // Se for alerta de pânico, marcar prioridade urgente
        $priority = $request->priority ?? 'normal';
        if ($request->type === 'panic_alert') {
            $priority = 'urgent';
        }

        $message = Message::create([
            'condominium_id' => $user->condominium_id,
            'from_user_id' => $user->id,
            'to_user_id' => $request->to_user_id,
            'type' => $request->type,
            'subject' => $request->subject,
            'message' => $request->message,
            'priority' => $priority,
            'related_item_id' => $request->related_item_id,
            'related_item_type' => $request->related_item_type,
        ]);

        // Se for alerta de pânico, notificar TODOS do condomínio
        if ($request->type === 'panic_alert') {
            // TODO: Disparar job para notificar todos via email/push
        }

        return response()->json([
            'message' => 'Mensagem enviada com sucesso',
            'data' => $message
        ], 201);
    }

    /**
     * Exibe uma mensagem
     */
    public function show($id)
    {
        $message = Message::with(['fromUser', 'toUser'])->findOrFail($id);

        $user = Auth::user();

        // Verificar permissão de leitura
        if ($message->condominium_id !== $user->condominium_id) {
            return response()->json(['error' => 'Não autorizado'], 403);
        }

        // Marcar como lida se for destinatário
        if ($message->to_user_id === $user->id && !$message->is_read) {
            $message->markAsRead();
        }

        return response()->json($message);
    }

    /**
     * Atualiza uma mensagem
     */
    public function update(Request $request, $id)
    {
        $message = Message::findOrFail($id);

        $user = Auth::user();

        // Apenas o remetente pode editar
        if ($message->from_user_id !== $user->id) {
            return response()->json(['error' => 'Não autorizado'], 403);
        }

        // Não permitir editar mensagens lidas
        if ($message->is_read) {
            return response()->json([
                'error' => 'Não é possível editar uma mensagem já lida'
            ], 400);
        }

        $message->update($request->only(['subject', 'message', 'priority']));

        return response()->json([
            'message' => 'Mensagem atualizada com sucesso',
            'data' => $message
        ]);
    }

    /**
     * Remove uma mensagem
     */
    public function destroy($id)
    {
        $message = Message::findOrFail($id);

        /** @var \App\Models\User $user */
        $user = Auth::user();

        // Apenas o remetente ou síndico pode deletar
        if ($message->from_user_id !== $user->id && !$user->isSindico() && !$user->isAdmin()) {
            return response()->json(['error' => 'Não autorizado'], 403);
        }

        $message->delete();

        return response()->json([
            'message' => 'Mensagem removida com sucesso'
        ]);
    }
}
