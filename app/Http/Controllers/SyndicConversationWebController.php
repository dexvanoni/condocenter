<?php

namespace App\Http\Controllers;

use App\Models\Conversation;
use App\Models\ConversationParticipant;
use App\Models\User;
use App\Http\Controllers\Api\SyndicConversationController as SyndicConversationApiController;
use Illuminate\Http\Request;

class SyndicConversationWebController extends Controller
{
    public function chat(Request $request)
    {
        /** @var User $user */
        $user = $request->user();

        if ($user->isAdmin() && !$user->isSindico()) {
            abort(403, 'Canal sigiloso com o síndico indisponível para administradores.');
        }

        return view('conversations.syndic.chat', [
            'pageTitle' => 'Conversa Sigilosa com o Síndico',
            'privacyNotice' => true,
        ]);
    }

    public function manage(Request $request)
    {
        /** @var User $user */
        $user = $request->user();

        if (!$user->isSindico()) {
            abort(403, 'Somente o perfil Síndico pode gerenciar este canal sigiloso.');
        }

        return view('conversations.syndic.manage');
    }

    public function start(Request $request)
    {
        /** @var User $user */
        $user = $request->user();

        if ($user->isAdmin() && !$user->isSindico()) {
            abort(403, 'Canal sigiloso com o síndico indisponível para administradores.');
        }

        $conversation = Conversation::query()
            ->where('condominium_id', $user->tenantCondominiumId())
            ->where('channel', Conversation::CHANNEL_SYNDIC)
            ->where('is_closed', false)
            ->whereHas('participants', fn ($q) => $q->where('user_id', $user->id))
            ->latest('updated_at')
            ->first();

        if (!$conversation) {
            $conversation = Conversation::create([
                'condominium_id' => $user->tenantCondominiumId(),
                'created_by' => $user->id,
                'subject' => null,
                'type' => 'direct',
                'channel' => Conversation::CHANNEL_SYNDIC,
                'priority' => 'normal',
                'is_active' => true,
            ]);

            ConversationParticipant::create([
                'conversation_id' => $conversation->id,
                'user_id' => $user->id,
                'role' => 'owner',
                'joined_at' => now(),
            ]);

            SyndicConversationApiController::attachSyndicParticipants($conversation);
        }

        return redirect()->route('syndic-conversations.chat', ['open' => $conversation->id]);
    }
}
