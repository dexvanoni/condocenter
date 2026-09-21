<?php

namespace Tests\Feature;

use App\Models\Condominium;
use App\Models\Unit;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ApiUserSearchByUnitTest extends TestCase
{
    use RefreshDatabase;

    public function test_search_finds_morador_by_unit_number(): void
    {
        $condominium = Condominium::factory()->create();
        $sindico = User::factory()->create(['condominium_id' => $condominium->id]);

        $unit = Unit::factory()->create([
            'condominium_id' => $condominium->id,
            'block' => 'B',
            'number' => '205',
        ]);

        $morador = User::factory()->create([
            'condominium_id' => $condominium->id,
            'unit_id' => $unit->id,
            'name' => 'Maria Teste',
        ]);

        $response = $this->actingAs($sindico)
            ->getJson('/api/users/search?term=205');

        $response->assertOk();
        $response->assertJsonFragment(['id' => $morador->id, 'name' => 'Maria Teste']);
    }
}
