<?php

namespace App\Services\Packages;

use App\DTO\OcrResult;
use App\Models\Unit;
use App\Models\User;
use App\Support\TextNormalizer;
use Illuminate\Support\Collection;

class PackageRecipientMatcher
{
    public const CONFIDENCE_HIGH = 0.90;
    public const CONFIDENCE_MEDIUM = 0.60;

    /**
     * @return array{
     *     level: string,
     *     confidence: float,
     *     candidates: array<int, array<string, mixed>>
     * }
     */
    public function match(int $condominiumId, OcrResult $ocr, ?string $barcodeValue = null): array
    {
        $candidates = $this->buildCandidates($condominiumId, $ocr, $barcodeValue);

        if ($candidates->isEmpty()) {
            return [
                'level' => 'low',
                'confidence' => 0.0,
                'candidates' => [],
            ];
        }

        $sorted = $candidates->sortByDesc('confidence')->values();
        $top = $sorted->first();
        $topConfidence = (float) $top['confidence'];
        $secondConfidence = (float) ($sorted->get(1)['confidence'] ?? 0.0);
        $gap = $topConfidence - $secondConfidence;

        // Nome forte e claramente único → alta confiança mesmo sem bloco/apto
        if ($topConfidence >= 0.88 && ($sorted->count() === 1 || $gap >= 0.12)) {
            return [
                'level' => 'high',
                'confidence' => $topConfidence,
                'candidates' => [$top],
            ];
        }

        if ($topConfidence >= self::CONFIDENCE_HIGH) {
            return [
                'level' => 'high',
                'confidence' => $topConfidence,
                'candidates' => [$top],
            ];
        }

        if ($topConfidence >= self::CONFIDENCE_MEDIUM) {
            return [
                'level' => 'medium',
                'confidence' => $topConfidence,
                'candidates' => $sorted->take(5)->values()->all(),
            ];
        }

        return [
            'level' => 'low',
            'confidence' => $topConfidence,
            'candidates' => $sorted->take(3)->values()->all(),
        ];
    }

    private function buildCandidates(int $condominiumId, OcrResult $ocr, ?string $barcodeValue): Collection
    {
        $block = $ocr->possibleBlock;
        $number = $ocr->possibleUnit;
        $nameHint = $ocr->possibleName;

        $units = $this->findCandidateUnits($condominiumId, $block, $number, $nameHint);

        $scores = collect();

        foreach ($units as $unit) {
            $residents = User::query()
                ->select('id', 'name', 'cpf', 'unit_id', 'condominium_id')
                ->byCondominium($condominiumId)
                ->where('unit_id', $unit->id)
                ->whereHas('roles', fn ($q) => $q->whereIn('name', ['Morador', 'Agregado']))
                ->get();

            foreach ($residents as $resident) {
                if ((int) $resident->condominium_id !== $condominiumId) {
                    continue;
                }

                $score = $this->scoreResident($resident, $unit, $ocr, $barcodeValue);

                if ($score < 0.35) {
                    continue;
                }

                $scores->push([
                    'resident_id' => $resident->id,
                    'name' => $resident->name,
                    'unit_id' => $unit->id,
                    'block' => $unit->block,
                    'number' => $unit->number,
                    'unit_label' => $unit->full_identifier,
                    'confidence' => round($score, 4),
                ]);
            }
        }

        return $scores;
    }

    /**
     * @return Collection<int, Unit>
     */
    private function findCandidateUnits(int $condominiumId, ?string $block, ?string $number, ?string $nameHint): Collection
    {
        $query = Unit::query()->byCondominium($condominiumId)->where('is_active', true);

        if ($block !== null && $block !== '' && $number !== null && $number !== '') {
            $exact = (clone $query)
                ->whereRaw('UPPER(TRIM(block)) = ?', [mb_strtoupper(trim($block), 'UTF-8')])
                ->whereRaw('UPPER(TRIM(number)) = ?', [mb_strtoupper(trim($number), 'UTF-8')])
                ->limit(10)
                ->get();

            if ($exact->isNotEmpty()) {
                return $exact;
            }
        }

        if ($number !== null && $number !== '') {
            $byNumber = (clone $query)
                ->whereRaw('UPPER(TRIM(number)) = ?', [mb_strtoupper(trim($number), 'UTF-8')])
                ->when($block, fn ($q) => $q->whereRaw('UPPER(TRIM(block)) = ?', [mb_strtoupper(trim($block), 'UTF-8')]))
                ->limit(20)
                ->get();

            if ($byNumber->isNotEmpty()) {
                return $byNumber;
            }
        }

        if ($nameHint) {
            $tokens = TextNormalizer::significantNameTokens($nameHint);

            if ($tokens !== []) {
                $unitIds = User::query()
                    ->byCondominium($condominiumId)
                    ->whereHas('roles', fn ($q) => $q->whereIn('name', ['Morador', 'Agregado']))
                    ->where(function ($q) use ($tokens) {
                        foreach ($tokens as $token) {
                            $q->orWhere('name', 'like', '%' . $token . '%');
                        }
                    })
                    ->whereNotNull('unit_id')
                    ->limit(50)
                    ->pluck('unit_id')
                    ->unique()
                    ->filter()
                    ->values();

                if ($unitIds->isNotEmpty()) {
                    return Unit::query()
                        ->byCondominium($condominiumId)
                        ->whereIn('id', $unitIds)
                        ->limit(50)
                        ->get();
                }
            }
        }

        return collect();
    }

    private function scoreResident(User $resident, Unit $unit, OcrResult $ocr, ?string $barcodeValue): float
    {
        $nameScore = 0.0;
        if ($ocr->possibleName) {
            $nameScore = TextNormalizer::nameSimilarity($ocr->possibleName, $resident->name);
        }

        $hasBlock = filled($ocr->possibleBlock);
        $hasUnit = filled($ocr->possibleUnit);

        $blockScore = 0.0;
        if ($hasBlock) {
            $blockScore = TextNormalizer::normalizeText($ocr->possibleBlock) === TextNormalizer::normalizeText((string) $unit->block)
                ? 1.0
                : TextNormalizer::similarity((string) $ocr->possibleBlock, (string) $unit->block);
        }

        $numberScore = 0.0;
        if ($hasUnit) {
            $numberScore = TextNormalizer::normalizeText($ocr->possibleUnit) === TextNormalizer::normalizeText((string) $unit->number)
                ? 1.0
                : TextNormalizer::similarity((string) $ocr->possibleUnit, (string) $unit->number);
        }

        $addressScore = 0.0;
        if ($ocr->possibleAddress) {
            $unitAddress = trim(implode(' ', array_filter([
                $unit->logradouro,
                $unit->numero,
                $unit->bairro,
                $unit->cep,
            ])));
            if ($unitAddress !== '') {
                $addressScore = TextNormalizer::similarity($ocr->possibleAddress, $unitAddress);
            }
        }

        // Sem bloco/apto no OCR: o nome concentra o peso (caso típico de etiquetas de e-commerce)
        if (!$hasBlock && !$hasUnit) {
            $score = ($nameScore * 0.90) + ($addressScore * 0.10);
        } elseif (!$hasBlock || !$hasUnit) {
            $score = ($nameScore * 0.70)
                + ($blockScore * 0.15)
                + ($numberScore * 0.10)
                + ($addressScore * 0.05);
        } else {
            $score = ($nameScore * 0.45)
                + ($blockScore * 0.25)
                + ($numberScore * 0.20)
                + ($addressScore * 0.10);
        }

        if ($barcodeValue || $ocr->trackingCode) {
            $score = min(1.0, $score + 0.01);
        }

        if (!$ocr->possibleName && $blockScore >= 1.0 && $numberScore >= 1.0) {
            $score = max($score, 0.92);
            $score = min($score, 0.88);
        }

        // Nome muito forte sozinho já é evidência suficiente para confirmação
        if ($nameScore >= 0.94 && !$hasBlock && !$hasUnit) {
            $score = max($score, 0.92);
        }

        return max(0.0, min(1.0, $score));
    }
}
