<?php

namespace Tests\Unit;

use App\Models\Condominium;
use App\Models\Conversation;
use App\Models\Message;
use App\Models\User;
use App\Services\AnnouncementExpirationService;
use App\Services\ConversationInboxService;
use App\Services\SyndicConversationService;
use App\Services\SyndicConversationStatsService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ConversationInboxServiceTest extends TestCase
{
    use RefreshDatabase;

    public function test_direct_conversation_awaiting_me_when_last_message_from_other(): void
    {
        $condominium = Condominium::factory()->create();
        $owner = User::factory()->create(['condominium_id' => $condominium->id]);
        $other = User::factory()->create(['condominium_id' => $condominium->id]);

        $conversation = Conversation::create([
            'condominium_id' => $condominium->id,
            'created_by' => $other->id,
            'type' => 'direct',
            'channel' => Conversation::CHANNEL_PEER,
            'priority' => 'normal',
        ]);

        Message::create([
            'condominium_id' => $condominium->id,
            'conversation_id' => $conversation->id,
            'from_user_id' => $other->id,
            'type' => 'direct_message',
            'message' => 'Olá',
            'priority' => 'normal',
        ]);

        $service = new ConversationInboxService(
            app(SyndicConversationService::class),
            app(SyndicConversationStatsService::class),
            app(AnnouncementExpirationService::class),
        );

        $conversation->load('latestMessage');

        $this->assertSame('awaiting_me', $service->resolveInboxStatus($conversation, $owner));
    }
}
