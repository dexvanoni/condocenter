<?php

namespace App\Services\Packages;

use App\Support\TextNormalizer;

class PackageSenderDetector
{
    private const SENDERS = [
        'MERCADO LIVRE' => ['MERCADO LIVRE', 'MERCADOLIVRE', 'MELI', 'MERCADO ENVIOS'],
        'SHOPEE' => ['SHOPEE', 'SPX'],
        'AMAZON' => ['AMAZON', 'AMZL'],
        'CORREIOS' => ['CORREIOS', 'ECT', 'EMPRESA BRASILEIRA DE CORREIOS'],
        'JADLOG' => ['JADLOG'],
        'LOGGI' => ['LOGGI'],
        'TOTAL EXPRESS' => ['TOTAL EXPRESS', 'TOTALEXPRESS'],
        'AZUL CARGO' => ['AZUL CARGO', 'AZULCARGO'],
        'LATAM CARGO' => ['LATAM CARGO'],
        'DHL' => ['DHL'],
        'FEDEX' => ['FEDEX'],
        'MAGALU' => ['MAGALU', 'MAGAZINE LUIZA', 'MAGALOG'],
        'AMERICANAS' => ['AMERICANAS', 'B2W'],
        'SHEIN' => ['SHEIN'],
        'ALIEXPRESS' => ['ALIEXPRESS', 'CAINIAO'],
    ];

    public function detect(?string $text): ?string
    {
        if ($text === null || trim($text) === '') {
            return null;
        }

        $normalized = TextNormalizer::normalizeText($text);

        foreach (self::SENDERS as $label => $keywords) {
            foreach ($keywords as $keyword) {
                $needle = TextNormalizer::normalizeText($keyword);
                if ($needle !== '' && str_contains($normalized, $needle)) {
                    return $label;
                }
            }
        }

        return null;
    }
}
