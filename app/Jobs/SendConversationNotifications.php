<?php

namespace App\Jobs;

use App\Models\Conversation;
use App\Models\ConversationRecipient;
use App\Models\Notification;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class SendConversationNotifications implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(
        public int $conversationId,
        public int $senderUserId,
        public string $messageText,
        public string $priority
    ) {
    }

    public function handle(): void
    {
        $conversation = Conversation::with(['recipients', 'participants.user', 'creator'])
            ->find($this->conversationId);
        if (!$conversation) {
            Log::warning("Conversation {$this->conversationId} não encontrada para notificação.");
            return;
        }

        $condominiumId = $conversation->condominium_id;
        $targetUsers = $this->resolveTargetUsers($conversation, $condominiumId)
            ->filter(fn (User $u) => $u->id !== $this->senderUserId)
            ->unique('id')
            ->values();

        $title = $conversation->type === 'announcement'
            ? 'Novo aviso do condomínio'
            : 'Nova mensagem';

        $payload = [
            'conversation_id' => $conversation->id,
            'priority' => $this->priority,
        ];

        foreach ($targetUsers as $u) {
            Notification::create([
                'condominium_id' => $condominiumId,
                'user_id' => $u->id,
                'type' => 'conversation_message',
                'title' => $title,
                'message' => $this->messageText,
                'data' => $payload,
                'channel' => 'database',
                'sent' => true,
                'sent_at' => now(),
            ]);
        }

        if (in_array($this->priority, ['high', 'urgent'], true)) {
            // Email opcional (apenas se o app estiver configurado)
            if (config('mail.default') !== 'log') {
                foreach ($targetUsers as $u) {
                    try {
                        Mail::raw($this->messageText, function ($m) use ($u, $title) {
                            $m->to($u->email)->subject($title);
                        });
                    } catch (\Throwable $e) {
                        Log::error("Erro ao enviar email de conversa para {$u->email}: " . $e->getMessage());
                    }
                }
            }
        }
    }

    private function resolveTargetUsers(Conversation $conversation, int $condominiumId): Collection
    {
        // Para direct: todos participantes
        if ($conversation->type === 'direct') {
            return $conversation->participants->pluck('user')->filter();
        }

        // Para announcement: expandir recipients
        $users = collect();
        /** @var Collection<int,ConversationRecipient> $rcps */
        $rcps = $conversation->recipients;
        foreach ($rcps as $rcp) {
            if ($rcp->target_type === 'all') {
                $all = User::query()
                    ->byCondominium($condominiumId)
                    ->where('is_active', true)
                    ->get();
                $users = $users->merge($all);
            } elseif ($rcp->target_type === 'role' && $rcp->target_value) {
                $roleUsers = User::query()
                    ->byCondominium($condominiumId)
                    ->where('is_active', true)
                    ->whereHas('roles', fn ($q) => $q->where('name', $rcp->target_value))
                    ->get();
                $users = $users->merge($roleUsers);
            } elseif ($rcp->target_type === 'user' && $rcp->target_value) {
                $u = User::query()
                    ->byCondominium($condominiumId)
                    ->where('id', (int)$rcp->target_value)
                    ->where('is_active', true)
                    ->first();
                if ($u) {
                    $users->push($u);
                }
            }
        }

        return $users;
    }
}


