<?php

namespace Tests\Unit;

use App\DTO\OcrResult;
use App\Models\Condominium;
use App\Models\Unit;
use App\Models\User;
use App\Services\Packages\PackageRecipientMatcher;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;
use Tests\TestCase;

class PackageRecipientMatcherTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        app(PermissionRegistrar::class)->forgetCachedPermissions();
    }

    public function test_high_confidence_match_with_name_block_and_unit(): void
    {
        [$condo, $unit, $resident] = $this->seedResident('João da Silva', 'B', '203');

        $matcher = new PackageRecipientMatcher();
        $result = $matcher->match($condo->id, new OcrResult(
            rawText: "JOAO DA SILVA\nBLOCO B\nAP 203",
            possibleName: 'JOAO SILVA',
            possibleBlock: 'B',
            possibleUnit: '203',
        ));

        $this->assertSame('high', $result['level']);
        $this->assertGreaterThanOrEqual(0.90, $result['confidence']);
        $this->assertSame($resident->id, $result['candidates'][0]['resident_id']);
    }

    public function test_medium_confidence_with_similar_names(): void
    {
        $condo = Condominium::factory()->create();
        $unitA = Unit::factory()->create(['condominium_id' => $condo->id, 'block' => 'B', 'number' => '203']);
        $unitB = Unit::factory()->create(['condominium_id' => $condo->id, 'block' => 'B', 'number' => '302']);

        $this->makeMorador($condo, $unitA, 'João da Silva');
        $this->makeMorador($condo, $unitB, 'João Silva');

        $matcher = new PackageRecipientMatcher();
        $result = $matcher->match($condo->id, new OcrResult(
            rawText: 'JOAO SILVA BLOCO B',
            possibleName: 'JOAO SILVA',
            possibleBlock: 'B',
            possibleUnit: null,
        ));

        $this->assertContains($result['level'], ['medium', 'high']);
        $this->assertNotEmpty($result['candidates']);
    }

    public function test_low_confidence_when_no_hints(): void
    {
        $condo = Condominium::factory()->create();
        Unit::factory()->create(['condominium_id' => $condo->id, 'block' => 'A', 'number' => '101']);

        $matcher = new PackageRecipientMatcher();
        $result = $matcher->match($condo->id, new OcrResult(rawText: 'xyz'));

        $this->assertSame('low', $result['level']);
        $this->assertSame([], $result['candidates']);
    }

    public function test_never_returns_resident_from_other_condominium(): void
    {
        [$condoA] = $this->seedResident('Maria Souza', 'A', '101');
        [$condoB, $unitB, $residentB] = $this->seedResident('Maria Souza', 'A', '101');

        $matcher = new PackageRecipientMatcher();
        $result = $matcher->match($condoA->id, new OcrResult(
            rawText: "MARIA SOUZA\nBLOCO A\nAP 101",
            possibleName: 'MARIA SOUZA',
            possibleBlock: 'A',
            possibleUnit: '101',
        ));

        $ids = collect($result['candidates'])->pluck('resident_id')->all();
        $this->assertNotContains($residentB->id, $ids);
    }

    public function test_unit_and_block_help_matching(): void
    {
        $condo = Condominium::factory()->create();
        $unitCorrect = Unit::factory()->create(['condominium_id' => $condo->id, 'block' => 'C', 'number' => '501']);
        $unitWrong = Unit::factory()->create(['condominium_id' => $condo->id, 'block' => 'C', 'number' => '502']);

        $correct = $this->makeMorador($condo, $unitCorrect, 'Ana Paula');
        $this->makeMorador($condo, $unitWrong, 'Ana Paula Costa');

        $matcher = new PackageRecipientMatcher();
        $result = $matcher->match($condo->id, new OcrResult(
            rawText: "ANA PAULA\nBLOCO C\nAP 501",
            possibleName: 'ANA PAULA',
            possibleBlock: 'C',
            possibleUnit: '501',
        ));

        $this->assertSame($correct->id, $result['candidates'][0]['resident_id']);
    }

    public function test_full_ocr_name_matches_shorter_registered_name_without_unit(): void
    {
        [$condo, $unit, $resident] = $this->seedResident('Tayna Fernandes', 'A', '9');

        $matcher = new PackageRecipientMatcher();
        $result = $matcher->match($condo->id, new OcrResult(
            rawText: "Tayna Karine da Silva Fernandes Elk\nEndereço: Rua Santa Teresa",
            possibleName: 'TAYNA KARINE SILVA FERNANDES ELK',
            possibleAddress: 'Endereço: Rua Santa Teresa SN, zona rural ERR',
        ));

        $this->assertSame('high', $result['level']);
        $this->assertGreaterThanOrEqual(0.88, $result['confidence']);
        $this->assertSame($resident->id, $result['candidates'][0]['resident_id']);
        $this->assertSame($unit->id, $result['candidates'][0]['unit_id']);
    }

    public function test_real_label_text_matches_short_registered_recipient(): void
    {
        [$condo, $unit, $resident] = $this->seedResident('Tayna Fernandes', 'A', '9');
        $ocrText = <<<'TEXT'
XPR1>XSP2> SMN1 > EMNS > X44
QUI 02/06/2022 NF: 13614
Tayna Karine da Silva Fernandes [=] [=]
(DASILVAFERNANDESTAYNAKARINE) rende
Endereço: Rua Santa Teresa SN, zona rural
CEP: 65715000
Complemento: Referencia: ao lado do lava jato da
famplemnto: inferir: no in java jato d
TEXT;

        $matcher = new PackageRecipientMatcher();
        $result = $matcher->match($condo->id, new OcrResult(
            rawText: $ocrText,
            possibleName: \App\Support\TextNormalizer::extractPossibleName($ocrText),
            possibleAddress: 'Endereço: Rua Santa Teresa SN, zona rural',
        ));

        $this->assertSame('high', $result['level']);
        $this->assertSame($resident->id, $result['candidates'][0]['resident_id']);
        $this->assertSame($unit->id, $result['candidates'][0]['unit_id']);
    }

    private function seedResident(string $name, string $block, string $number): array
    {
        $condo = Condominium::factory()->create();
        $unit = Unit::factory()->create([
            'condominium_id' => $condo->id,
            'block' => $block,
            'number' => $number,
        ]);
        $resident = $this->makeMorador($condo, $unit, $name);

        return [$condo, $unit, $resident];
    }

    private function makeMorador(Condominium $condo, Unit $unit, string $name): User
    {
        $role = Role::firstOrCreate(['name' => 'Morador', 'guard_name' => 'web']);
        $user = User::factory()->create([
            'name' => $name,
            'condominium_id' => $condo->id,
            'unit_id' => $unit->id,
        ]);
        $user->assignRole($role);

        return $user;
    }
}
