<?php

namespace Tests\Feature;

use App\Models\Condominium;
use App\Models\Conversation;
use App\Models\ConversationRecipient;
use App\Models\Message;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class AnnouncementUpdateTest extends TestCase
{
    use RefreshDatabase;

    public function test_sindico_can_update_announcement(): void
    {
        Permission::firstOrCreate(['name' => 'send_announcements', 'guard_name' => 'web']);
        $role = Role::firstOrCreate(['name' => 'Síndico', 'guard_name' => 'web']);
        $role->givePermissionTo('send_announcements');

        $condominium = Condominium::factory()->create();
        $sindico = User::factory()->create(['condominium_id' => $condominium->id]);
        $sindico->assignRole($role);

        $conversation = Conversation::create([
            'condominium_id' => $condominium->id,
            'created_by' => $sindico->id,
            'subject' => 'Aviso antigo',
            'type' => 'announcement',
            'priority' => 'normal',
            'is_active' => true,
            'is_closed' => false,
        ]);

        ConversationRecipient::create([
            'conversation_id' => $conversation->id,
            'target_type' => 'all',
            'target_value' => null,
        ]);

        Message::create([
            'condominium_id' => $condominium->id,
            'conversation_id' => $conversation->id,
            'from_user_id' => $sindico->id,
            'type' => 'announcement',
            'subject' => 'Aviso antigo',
            'message' => 'Texto antigo',
            'priority' => 'normal',
        ]);

        $response = $this->actingAs($sindico)->putJson("/api/conversations/{$conversation->id}/announcement", [
            'subject' => 'Aviso atualizado',
            'message' => 'Texto novo',
            'priority' => 'high',
            'recipients' => [
                ['type' => 'role', 'value' => 'Morador'],
            ],
            'expires_at' => now()->addDay()->toIso8601String(),
        ]);

        $response->assertOk();
        $conversation->refresh();
        $this->assertSame('Aviso atualizado', $conversation->subject);
        $this->assertSame('high', $conversation->priority);
        $this->assertFalse($conversation->is_closed);

        $this->assertDatabaseHas('conversation_recipients', [
            'conversation_id' => $conversation->id,
            'target_type' => 'role',
            'target_value' => 'Morador',
        ]);

        $this->assertDatabaseHas('messages', [
            'conversation_id' => $conversation->id,
            'message' => 'Texto novo',
        ]);
    }
}
