<?php

namespace Tests\Unit;

use App\Support\TextNormalizer;
use PHPUnit\Framework\TestCase;

class TextNormalizerTest extends TestCase
{
    public function test_normalize_name_removes_accents_and_stop_words(): void
    {
        $this->assertSame(
            'JOSE SILVA',
            TextNormalizer::normalizeName('José da Silva')
        );
    }

    public function test_normalize_text_collapses_spaces_and_uppercases(): void
    {
        $this->assertSame(
            'BLOCO B AP 203',
            TextNormalizer::normalizeText("  bloco   b\nap 203  ")
        );
    }

    public function test_extract_block_and_unit(): void
    {
        $parts = TextNormalizer::extractBlockAndUnit("JOAO DA SILVA\nBLOCO B\nAP 203");

        $this->assertSame('B', $parts['block']);
        $this->assertSame('203', $parts['number']);
    }

    public function test_extract_tracking_code_correios(): void
    {
        $this->assertSame(
            'AB123456789BR',
            TextNormalizer::extractTrackingCode('Rastreio AB123456789BR Correios')
        );
    }

    public function test_similarity_high_for_similar_names(): void
    {
        $score = TextNormalizer::similarity('João da Silva', 'JOSE DA SILVA');
        $this->assertGreaterThan(0.5, $score);
    }

    public function test_extract_possible_name(): void
    {
        $name = TextNormalizer::extractPossibleName("MERCADO LIVRE\nJoão da Silva\nBLOCO B AP 203");
        $this->assertSame('JOAO SILVA', $name);
    }

    public function test_name_similarity_matches_short_registered_name_inside_full_ocr_name(): void
    {
        $score = TextNormalizer::nameSimilarity(
            'Tayna Fernandes',
            'TAYNA KARINE SILVA FERNANDES ELK'
        );

        $this->assertGreaterThanOrEqual(0.94, $score);
    }

    public function test_extract_possible_name_prioritizes_recipient_before_address(): void
    {
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

        $this->assertSame(
            'TAYNA KARINE SILVA FERNANDES',
            TextNormalizer::extractPossibleName($ocrText)
        );
    }
}
