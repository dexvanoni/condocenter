<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Conversation;
use App\Models\ConversationParticipant;
use App\Models\User;
use Illuminate\Support\Facades\Auth;

class ConversationWebController extends Controller
{
    public function announcementForm(Request $request)
    {
        return view('conversations.announcement');
    }

    /**
     * Garante uma conversa direta entre o usuário autenticado e os Síndicos/Administradores.
     * Redireciona para a Central de Mensagens já abrindo a conversa.
     */
    public function startDirect(Request $request)
    {
        /** @var \App\Models\User $user */
        $user = $request->user();

        // Buscar conversa direta existente onde o usuário é participante
        $conversation = Conversation::query()
            ->where('condominium_id', $user->condominium_id)
            ->where('type', 'direct')
            ->where('is_closed', false)
            ->whereHas('participants', fn ($q) => $q->where('user_id', $user->id))
            ->latest('created_at')
            ->first();

        if (!$conversation) {
            // Criar nova conversa direta
            $conversation = Conversation::create([
                'condominium_id' => $user->condominium_id,
                'created_by' => $user->id,
                'subject' => null,
                'type' => 'direct',
                'priority' => 'normal',
                'is_active' => true,
            ]);

            // Participante: remetente (owner)
            ConversationParticipant::create([
                'conversation_id' => $conversation->id,
                'user_id' => $user->id,
                'role' => 'owner',
                'joined_at' => now(),
            ]);

            // Destinatários administrativos: Síndico/Admin do mesmo condomínio
            $admins = User::query()
                ->byCondominium($user->condominium_id)
                ->whereHas('roles', fn ($q) => $q->whereIn('name', ['Síndico', 'Administrador']))
                ->get(['id']);

            foreach ($admins as $admin) {
                ConversationParticipant::updateOrCreate(
                    ['conversation_id' => $conversation->id, 'user_id' => $admin->id],
                    ['role' => 'participant', 'joined_at' => now()]
                );
            }
        }

        return redirect()->route('messages.index', ['open' => $conversation->id]);
    }
}


