<?php

namespace Tests\Unit;

use App\Models\Condominium;
use App\Models\Conversation;
use App\Models\User;
use App\Services\AnnouncementExpirationService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AnnouncementExpirationServiceTest extends TestCase
{
    use RefreshDatabase;

    public function test_closes_announcement_when_expires_at_is_past(): void
    {
        $condominium = Condominium::factory()->create();
        $user = User::factory()->create(['condominium_id' => $condominium->id]);

        $conversation = Conversation::create([
            'condominium_id' => $condominium->id,
            'created_by' => $user->id,
            'type' => 'announcement',
            'priority' => 'normal',
            'is_active' => true,
            'is_closed' => false,
            'expires_at' => now()->subMinute(),
        ]);

        $service = app(AnnouncementExpirationService::class);
        $closed = $service->closeExpired($condominium->id);

        $this->assertSame(1, $closed);
        $conversation->refresh();
        $this->assertTrue($conversation->is_closed);
        $this->assertFalse($conversation->is_active);
        $this->assertNotNull($conversation->closed_at);
    }

    public function test_does_not_close_future_announcement(): void
    {
        $condominium = Condominium::factory()->create();
        $user = User::factory()->create(['condominium_id' => $condominium->id]);

        $conversation = Conversation::create([
            'condominium_id' => $condominium->id,
            'created_by' => $user->id,
            'type' => 'announcement',
            'priority' => 'normal',
            'is_active' => true,
            'is_closed' => false,
            'expires_at' => now()->addDay(),
        ]);

        $service = app(AnnouncementExpirationService::class);
        $closed = $service->closeExpired($condominium->id);

        $this->assertSame(0, $closed);
        $conversation->refresh();
        $this->assertFalse($conversation->is_closed);
    }
}
