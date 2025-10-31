<?php

namespace App\Helpers;

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
            'condominium_id' => $user->condominium_id,
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

