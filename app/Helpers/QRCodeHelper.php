<?php

namespace App\Helpers;

use BaconQrCode\Common\ErrorCorrectionLevel;
use BaconQrCode\Encoder\Encoder;
use SimpleSoftwareIO\QrCode\Facades\QrCode;

class QRCodeHelper
{
    /**
     * Gera QR Code para um morador
     */
    public static function generateForResident($user)
    {
        $data = [
            'type' => 'resident',
            'user_id' => $user->id,
            'name' => $user->name,
            'unit_id' => $user->unit_id,
            'qr_code' => $user->qr_code,
            'condominium_id' => $user->tenantCondominiumId(),
        ];

        return QrCode::size(300)
            ->format('png')
            ->generate(json_encode($data));
    }

    /**
     * Gera QR Code para visitante pré-autorizado
     */
    public static function generateForVisitor($visitData)
    {
        $data = [
            'type' => 'visitor',
            'name' => $visitData['name'],
            'unit_id' => $visitData['unit_id'],
            'valid_until' => $visitData['valid_until'],
            'authorized_by' => $visitData['authorized_by'],
        ];

        return QrCode::size(200)
            ->format('png')
            ->generate(json_encode($data));
    }

    /**
     * Gera QR Code para liberação de visitante nomeado (tipo Outro).
     */
    public static function visitorAccessPayload(\App\Models\AccessAuthorization $authorization): string
    {
        return json_encode([
            'type' => 'visitor_access',
            'token' => $authorization->qr_token,
        ]);
    }

    public static function generateForVisitorAccess(\App\Models\AccessAuthorization $authorization): string
    {
        return QrCode::size(320)
            ->format('svg')
            ->errorCorrection('H')
            ->generate(self::visitorAccessPayload($authorization));
    }

    public static function generateForVisitorAccessPngBase64(\App\Models\AccessAuthorization $authorization): string
    {
        return base64_encode(self::generateForVisitorAccessPngBinary($authorization));
    }

    public static function generateForVisitorAccessPngBinary(\App\Models\AccessAuthorization $authorization): string
    {
        if (!function_exists('imagecreatetruecolor')) {
            throw new \RuntimeException('Extensão GD não disponível para gerar QR Code em PNG.');
        }

        $matrix = Encoder::encode(
            self::visitorAccessPayload($authorization),
            ErrorCorrectionLevel::H()
        )->getMatrix();

        $moduleCount = $matrix->getWidth();
        $scale = 6;
        $margin = 4;
        $imageSize = ($moduleCount + ($margin * 2)) * $scale;

        $image = imagecreatetruecolor($imageSize, $imageSize);
        $white = imagecolorallocate($image, 255, 255, 255);
        $black = imagecolorallocate($image, 0, 0, 0);
        imagefill($image, 0, 0, $white);

        for ($y = 0; $y < $moduleCount; $y++) {
            for ($x = 0; $x < $moduleCount; $x++) {
                if (!$matrix->get($x, $y)) {
                    continue;
                }

                $x1 = ($x + $margin) * $scale;
                $y1 = ($y + $margin) * $scale;
                imagefilledrectangle(
                    $image,
                    $x1,
                    $y1,
                    $x1 + $scale - 1,
                    $y1 + $scale - 1,
                    $black
                );
            }
        }

        ob_start();
        imagepng($image);
        $png = ob_get_clean() ?: '';
        imagedestroy($image);

        if ($png === '') {
            throw new \RuntimeException('Falha ao gerar QR Code em PNG.');
        }

        return $png;
    }

    /**
     * Extrai o código do pet a partir de URL, JSON ou código bruto.
     */
    public static function parsePetQrCode(string $qrData): ?string
    {
        $qrData = trim($qrData);

        if ($qrData === '') {
            return null;
        }

        try {
            $data = json_decode($qrData, true);

            if (is_array($data) && ($data['type'] ?? null) === 'pet' && !empty($data['qr_code'])) {
                return (string) $data['qr_code'];
            }
        } catch (\Throwable) {
            // segue para fallback
        }

        if (preg_match('~\/pets\/qr\/([^/?#\s]+)~i', $qrData, $matches)) {
            return urldecode($matches[1]);
        }

        if (preg_match('/^PET-[A-Z0-9]+-\d+$/i', $qrData)) {
            return $qrData;
        }

        return null;
    }

    public static function parseVisitorAccessToken(string $qrData): ?string
    {
        $qrData = trim($qrData);

        try {
            $data = json_decode($qrData, true);

            if (
                is_array($data)
                && ($data['type'] ?? null) === 'visitor_access'
                && !empty($data['token'])
            ) {
                return (string) $data['token'];
            }
        } catch (\Throwable) {
            // segue para fallback
        }

        if (preg_match('/^[A-Za-z0-9]{32,64}$/', $qrData)) {
            return $qrData;
        }

        return null;
    }

    /**
     * Gera QR Code para um pet
     */
    public static function generateForPet($pet)
    {
        $url = route('pets.show-qr', $pet->qr_code);

        return QrCode::size(400)
            ->format('svg')
            ->errorCorrection('H')
            ->generate($url);
    }

    /**
     * Valida um QR Code
     */
    public static function validate($qrCodeData)
    {
        try {
            $data = json_decode($qrCodeData, true);

            if (!isset($data['type'])) {
                return ['valid' => false, 'message' => 'QR Code inválido'];
            }

            if ($data['type'] === 'resident') {
                // Verificar se usuário existe e está ativo
                $user = \App\Models\User::where('qr_code', $data['qr_code'])
                    ->where('is_active', true)
                    ->first();

                if (!$user) {
                    return ['valid' => false, 'message' => 'Morador não encontrado ou inativo'];
                }

                return [
                    'valid' => true,
                    'type' => 'resident',
                    'user' => $user,
                    'message' => 'Acesso autorizado para morador'
                ];
            }

            if ($data['type'] === 'visitor') {
                // Verificar validade
                if (isset($data['valid_until']) && now() > $data['valid_until']) {
                    return ['valid' => false, 'message' => 'Autorização expirada'];
                }

                return [
                    'valid' => true,
                    'type' => 'visitor',
                    'data' => $data,
                    'message' => 'Visitante pré-autorizado'
                ];
            }

            if ($data['type'] === 'pet') {
                // Verificar se pet existe
                $pet = \App\Models\Pet::where('qr_code', $data['qr_code'])
                    ->where('is_active', true)
                    ->with(['owner', 'unit', 'condominium'])
                    ->first();

                if (!$pet) {
                    return ['valid' => false, 'message' => 'Pet não encontrado ou inativo'];
                }

                return [
                    'valid' => true,
                    'type' => 'pet',
                    'pet' => $pet,
                    'message' => 'Pet encontrado'
                ];
            }

            return ['valid' => false, 'message' => 'Tipo de QR Code desconhecido'];

        } catch (\Exception $e) {
            return ['valid' => false, 'message' => 'Erro ao validar QR Code'];
        }
    }
}

