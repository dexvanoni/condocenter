<?php

namespace Tests\Unit;

use App\Models\User;
use App\Services\Learning\LearningCatalogService;
use App\Support\Learning\LearningCatalog;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class LearningCatalogServiceTest extends TestCase
{
    use RefreshDatabase;

    public function test_catalog_has_financial_critical_tutorials(): void
    {
        $financial = collect(LearningCatalog::tutorials())
            ->where('module', 'financial')
            ->where('critical', true);

        $this->assertTrue($financial->contains('slug', 'conciliacao-extrato-csv-ofx'));
        $this->assertTrue($financial->contains('slug', 'taxas-e-cobrancas'));
        $this->assertGreaterThanOrEqual(5, $financial->count());
    }

    public function test_search_finds_conciliacao(): void
    {
        Role::findOrCreate('Síndico');
        $user = User::factory()->create();
        $user->assignRole('Síndico');

        $results = app(LearningCatalogService::class)->search($user, 'OFX');

        $this->assertTrue($results->contains('slug', 'conciliacao-extrato-csv-ofx'));
    }

    public function test_morador_cannot_open_center(): void
    {
        Role::findOrCreate('Morador');
        $user = User::factory()->create();
        $user->assignRole('Morador');

        $this->assertFalse(app(LearningCatalogService::class)->userCanOpenCenter($user));
    }
}
