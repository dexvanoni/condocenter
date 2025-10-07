<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Condominium;
use App\Models\Transaction;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TransactionTest extends TestCase
{
    use RefreshDatabase;

    protected $user;
    protected $condominium;

    protected function setUp(): void
    {
        parent::setUp();
        
        $this->artisan('db:seed', ['--class' => 'Database\\Seeders\\RolesAndPermissionsSeeder']);
        
        $this->condominium = Condominium::factory()->create();
        
        $this->user = User::factory()->create([
            'condominium_id' => $this->condominium->id,
        ]);
        
        $this->user->assignRole('Síndico');
        $this->user->givePermissionTo('manage_transactions');
    }

    public function test_can_create_transaction(): void
    {
        $response = $this->actingAs($this->user)
            ->postJson('/api/transactions', [
                'type' => 'expense',
                'category' => 'Manutenção',
                'description' => 'Compra de materiais',
                'amount' => 500.00,
                'transaction_date' => now()->format('Y-m-d'),
                'status' => 'paid',
                'payment_method' => 'pix',
            ]);

        $response->assertStatus(201)
                 ->assertJsonStructure([
                     'message',
                     'transaction' => ['id', 'type', 'amount']
                 ]);

        $this->assertDatabaseHas('transactions', [
            'condominium_id' => $this->condominium->id,
            'type' => 'expense',
            'amount' => 500.00,
        ]);
    }

    public function test_can_list_transactions(): void
    {
        Transaction::factory()->count(5)->create([
            'condominium_id' => $this->condominium->id,
            'user_id' => $this->user->id,
        ]);

        $response = $this->actingAs($this->user)
            ->getJson('/api/transactions');

        $response->assertStatus(200)
                 ->assertJsonStructure([
                     'data' => [
                         '*' => ['id', 'type', 'amount', 'description']
                     ]
                 ]);
    }

    public function test_cannot_view_other_condominium_transactions(): void
    {
        $otherCondominium = Condominium::factory()->create();
        
        $transaction = Transaction::factory()->create([
            'condominium_id' => $otherCondominium->id,
        ]);

        $response = $this->actingAs($this->user)
            ->getJson("/api/transactions/{$transaction->id}");

        $response->assertStatus(403);
    }
}
