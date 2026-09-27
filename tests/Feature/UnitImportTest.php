<?php

namespace Tests\Feature;

use App\Models\Condominium;
use App\Models\Unit;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;
use Tests\TestCase;

class UnitImportTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        app(PermissionRegistrar::class)->forgetCachedPermissions();
    }

    public function test_sindico_can_download_import_template(): void
    {
        $condominium = Condominium::factory()->create(['saas_complimentary' => true, 'units_limit' => 50]);
        $sindico = $this->makeSindicoWithCreateUnits($condominium);

        $this->actingAs($sindico)
            ->get(route('units.import.template', 'xlsx'))
            ->assertOk()
            ->assertHeader(
                'content-type',
                'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet'
            );

        $this->actingAs($sindico)
            ->get(route('units.import.template', 'csv'))
            ->assertOk();
    }

    public function test_csv_import_creates_units(): void
    {
        $condominium = Condominium::factory()->create(['saas_complimentary' => true, 'units_limit' => 50]);
        $sindico = $this->makeSindicoWithCreateUnits($condominium);

        $csv = implode("\n", [
            'numero,bloco,uso,modelo,situacao,regime_ocupacao,tipo_imovel_publico,andar,fracao_ideal,ativo,possui_dividas',
            '401,A,residencial,apartamento,fechado,particular,,1,,sim,nao',
            '402,A,residencial,apartamento,habitado,particular,,1,,sim,nao',
        ]);

        $file = UploadedFile::fake()->createWithContent('unidades.csv', $csv);

        $this->actingAs($sindico)
            ->post(route('units.import.store'), ['file' => $file])
            ->assertRedirect(route('units.index'))
            ->assertSessionHas('success');

        $this->assertSame(2, Unit::query()->where('condominium_id', $condominium->id)->count());
        $this->assertDatabaseHas('units', [
            'condominium_id' => $condominium->id,
            'number' => '401',
            'block' => 'A',
        ]);
    }

    public function test_import_rejects_duplicate_in_spreadsheet(): void
    {
        $condominium = Condominium::factory()->create(['saas_complimentary' => true, 'units_limit' => 50]);
        $sindico = $this->makeSindicoWithCreateUnits($condominium);

        $csv = implode("\n", [
            'numero,bloco,uso,modelo,situacao,regime_ocupacao,tipo_imovel_publico,andar,fracao_ideal,ativo,possui_dividas',
            '501,,residencial,apartamento,fechado,particular,,,,sim,nao',
            '501,,residencial,apartamento,fechado,particular,,,,sim,nao',
        ]);

        $file = UploadedFile::fake()->createWithContent('unidades.csv', $csv);

        $this->actingAs($sindico)
            ->post(route('units.import.store'), ['file' => $file])
            ->assertRedirect(route('units.import.form'))
            ->assertSessionHas('import_errors');

        $this->assertSame(0, Unit::query()->where('condominium_id', $condominium->id)->count());
    }

    public function test_import_respects_units_limit(): void
    {
        $condominium = Condominium::factory()->create(['saas_complimentary' => true, 'units_limit' => 1]);
        Unit::factory()->create(['condominium_id' => $condominium->id, 'number' => '100']);
        $sindico = $this->makeSindicoWithCreateUnits($condominium);

        $csv = implode("\n", [
            'numero,bloco,uso,modelo,situacao,regime_ocupacao,tipo_imovel_publico,andar,fracao_ideal,ativo,possui_dividas',
            '601,,residencial,apartamento,fechado,particular,,,,sim,nao',
        ]);

        $file = UploadedFile::fake()->createWithContent('unidades.csv', $csv);

        $this->actingAs($sindico)
            ->post(route('units.import.store'), ['file' => $file])
            ->assertRedirect(route('units.import.form'))
            ->assertSessionHas('error');

        $this->assertSame(1, $condominium->units()->count());
    }

    private function makeSindicoWithCreateUnits(Condominium $condominium): User
    {
        $permission = Permission::firstOrCreate(['name' => 'create_units', 'guard_name' => 'web']);
        $role = Role::firstOrCreate(['name' => 'Síndico', 'guard_name' => 'web']);
        $role->givePermissionTo($permission);

        $sindico = User::factory()->create([
            'condominium_id' => $condominium->id,
            'email_verified_at' => now(),
            'senha_temporaria' => false,
        ]);
        $sindico->assignRole($role);

        return $sindico;
    }
}
