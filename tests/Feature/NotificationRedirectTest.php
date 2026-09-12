<?php

namespace Tests\Feature;

use App\Models\Charge;
use App\Models\Condominium;
use App\Models\Notification;
use App\Models\Unit;
use App\Models\User;
use App\Services\NotificationRedirectService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\PermissionRegistrar;
use Tests\TestCase;

class NotificationRedirectTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->artisan('db:seed', ['--class' => 'Database\\Seeders\\RolesAndPermissionsSeeder']);

        app(PermissionRegistrar::class)->forgetCachedPermissions();
    }

    public function test_show_marks_notification_as_read_and_redirects_to_related_screen(): void
    {
        $condominium = Condominium::factory()->create();
        $sindico = User::factory()->for($condominium)->create();
        $sindico->assignRole('Síndico');

        $unit = Unit::factory()->for($condominium)->create(['is_active' => true]);

        $charge = Charge::create([
            'condominium_id' => $condominium->id,
            'unit_id' => $unit->id,
            'title' => 'Taxa teste',
            'amount' => 100,
            'due_date' => now()->addDays(5),
            'status' => 'pending',
            'type' => 'regular',
            'generated_by' => 'manual',
        ]);

        $notification = Notification::create([
            'condominium_id' => $condominium->id,
            'user_id' => $sindico->id,
            'type' => 'payment_overdue',
            'title' => 'Pagamento em atraso',
            'message' => 'Sua cobrança está em atraso.',
            'data' => ['charge_id' => $charge->id],
            'channel' => 'database',
            'sent' => true,
            'sent_at' => now(),
            'is_read' => false,
        ]);

        $response = $this->actingAs($sindico)->get(route('notifications.show', $notification));

        $response->assertRedirect(route('charges.show', $charge));
        $this->assertTrue($notification->fresh()->is_read);
    }

    public function test_user_cannot_open_notification_from_another_user(): void
    {
        $condominium = Condominium::factory()->create();
        $owner = User::factory()->for($condominium)->create();
        $other = User::factory()->for($condominium)->create();

        $notification = Notification::create([
            'condominium_id' => $condominium->id,
            'user_id' => $owner->id,
            'type' => 'conversation_message',
            'title' => 'Mensagem',
            'message' => 'Olá',
            'data' => ['conversation_id' => 1],
            'channel' => 'database',
            'sent' => true,
            'sent_at' => now(),
        ]);

        $this->actingAs($other)
            ->get(route('notifications.show', $notification))
            ->assertForbidden();
    }

    public function test_redirect_service_uses_explicit_url_from_data(): void
    {
        $condominium = Condominium::factory()->create();
        $user = User::factory()->for($condominium)->create();

        $notification = Notification::create([
            'condominium_id' => $condominium->id,
            'user_id' => $user->id,
            'type' => 'saas_payment_created',
            'title' => 'Assinatura',
            'message' => 'Nova cobrança',
            'data' => ['url' => route('syndic-subscription.show')],
            'channel' => 'database',
            'sent' => true,
            'sent_at' => now(),
        ]);

        $url = app(NotificationRedirectService::class)->resolve($notification, $user);

        $this->assertSame(route('syndic-subscription.show'), $url);
    }
}
