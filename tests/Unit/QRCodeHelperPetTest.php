<?php

namespace Tests\Unit;

use App\Helpers\QRCodeHelper;
use Tests\TestCase;

class QRCodeHelperPetTest extends TestCase
{
    public function test_parse_pet_qr_code_from_public_url(): void
    {
        $code = 'PET-TESTQR123-1700000000';
        $url = url('/pets/qr/' . $code);

        $this->assertSame($code, QRCodeHelper::parsePetQrCode($url));
    }

    public function test_parse_pet_qr_code_from_json_payload(): void
    {
        $payload = json_encode([
            'type' => 'pet',
            'qr_code' => 'PET-ABC123-123',
        ]);

        $this->assertSame('PET-ABC123-123', QRCodeHelper::parsePetQrCode($payload));
    }

    public function test_parse_pet_qr_code_from_raw_code(): void
    {
        $this->assertSame(
            'PET-RAWCODE99-999',
            QRCodeHelper::parsePetQrCode('PET-RAWCODE99-999')
        );
    }
}
