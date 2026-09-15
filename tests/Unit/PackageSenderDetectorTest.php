<?php

namespace Tests\Unit;

use App\Services\Packages\PackageSenderDetector;
use PHPUnit\Framework\TestCase;

class PackageSenderDetectorTest extends TestCase
{
    public function test_detects_mercado_livre(): void
    {
        $detector = new PackageSenderDetector();
        $this->assertSame('MERCADO LIVRE', $detector->detect('Etiqueta Mercado Livre MELI'));
    }

    public function test_detects_shopee(): void
    {
        $detector = new PackageSenderDetector();
        $this->assertSame('SHOPEE', $detector->detect('SPX Express Shopee'));
    }

    public function test_detects_correios(): void
    {
        $detector = new PackageSenderDetector();
        $this->assertSame('CORREIOS', $detector->detect('Empresa Brasileira de Correios'));
    }

    public function test_returns_null_when_unknown(): void
    {
        $detector = new PackageSenderDetector();
        $this->assertNull($detector->detect('Pacote sem identificação clara'));
    }
}
