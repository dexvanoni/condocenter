<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Services\SyndicConversationService;
use Illuminate\Http\Request;

class SyndicConversationWebController extends Controller
{
    public function __construct(
        private readonly SyndicConversationService $syndicConversationService,
    ) {
    }

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

        $conversation = $this->syndicConversationService->findOpenConversation($user);

        if (!$conversation) {
            $conversation = $this->syndicConversationService->createConversation($user);
        }

        return redirect()->route('syndic-conversations.chat', ['open' => $conversation->id]);
    }
}
