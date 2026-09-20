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
        $isUnique = $sorted->count() === 1 || $gap >= 0.12;

        if ($topConfidence >= 0.88 && $isUnique) {
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
        $haystack = TextNormalizer::nameSearchHaystack($ocr->rawText);
        $units = $this->findCandidateUnits($condominiumId, $ocr, $haystack);

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

                $score = $this->scoreResident($resident, $unit, $ocr, $barcodeValue, $haystack);

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
     * @param  array{tokens: list<string>, blob: string}  $haystack
     * @return Collection<int, Unit>
     */
    private function findCandidateUnits(int $condominiumId, OcrResult $ocr, array $haystack): Collection
    {
        $query = Unit::query()->byCondominium($condominiumId)->where('is_active', true);
        $units = collect();

        $block = $ocr->possibleBlock;
        $number = $ocr->possibleUnit;

        if (filled($block) && filled($number)) {
            $units = $units->merge(
                (clone $query)
                    ->whereRaw('UPPER(TRIM(block)) = ?', [mb_strtoupper(trim((string) $block), 'UTF-8')])
                    ->whereRaw('UPPER(TRIM(number)) = ?', [mb_strtoupper(trim((string) $number), 'UTF-8')])
                    ->limit(10)
                    ->get()
            );
        }

        if (filled($number)) {
            $units = $units->merge(
                (clone $query)
                    ->whereRaw('UPPER(TRIM(number)) = ?', [mb_strtoupper(trim((string) $number), 'UTF-8')])
                    ->when($block, fn ($q) => $q->whereRaw('UPPER(TRIM(block)) = ?', [mb_strtoupper(trim((string) $block), 'UTF-8')]))
                    ->limit(20)
                    ->get()
            );
        }

        $units = $units->merge($this->findUnitsByExtractedName($condominiumId, $ocr->possibleName));
        $units = $units->merge($this->findUnitsByNamePresence($condominiumId, $haystack));

        return $units->unique('id')->values();
    }

    /**
     * @return Collection<int, Unit>
     */
    private function findUnitsByExtractedName(int $condominiumId, ?string $nameHint): Collection
    {
        if (!filled($nameHint)) {
            return collect();
        }

        $tokens = TextNormalizer::significantNameTokens($nameHint);
        if ($tokens === []) {
            return collect();
        }

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

        if ($unitIds->isEmpty()) {
            return collect();
        }

        return Unit::query()
            ->byCondominium($condominiumId)
            ->whereIn('id', $unitIds)
            ->limit(50)
            ->get();
    }

    /**
     * @param  array{tokens: list<string>, blob: string}  $haystack
     * @return Collection<int, Unit>
     */
    private function findUnitsByNamePresence(int $condominiumId, array $haystack): Collection
    {
        if ($haystack['tokens'] === []) {
            return collect();
        }

        $residents = User::query()
            ->select('id', 'name', 'unit_id')
            ->byCondominium($condominiumId)
            ->whereNotNull('unit_id')
            ->whereHas('roles', fn ($q) => $q->whereIn('name', ['Morador', 'Agregado']))
            ->limit(500)
            ->get();

        $unitIds = $residents
            ->filter(function (User $resident) use ($haystack) {
                $tokens = array_values(array_filter(
                    explode(' ', TextNormalizer::normalizeName($resident->name)),
                    fn (string $token) => mb_strlen($token) >= 3
                ));

                return TextNormalizer::namePresenceInHaystack($tokens, $haystack) >= 0.62;
            })
            ->pluck('unit_id')
            ->unique()
            ->filter()
            ->values();

        if ($unitIds->isEmpty()) {
            return collect();
        }

        return Unit::query()
            ->byCondominium($condominiumId)
            ->whereIn('id', $unitIds)
            ->get();
    }

    /**
     * @param  array{tokens: list<string>, blob: string}  $haystack
     */
    private function scoreResident(
        User $resident,
        Unit $unit,
        OcrResult $ocr,
        ?string $barcodeValue,
        array $haystack
    ): float {
        $extractedScore = 0.0;
        if ($ocr->possibleName) {
            $extractedScore = TextNormalizer::nameSimilarity($ocr->possibleName, $resident->name);
        }

        $nameTokens = array_values(array_filter(
            explode(' ', TextNormalizer::normalizeName($resident->name)),
            fn (string $token) => mb_strlen($token) >= 3
        ));
        $presenceScore = TextNormalizer::namePresenceInHaystack($nameTokens, $haystack);
        $nameScore = max($extractedScore, $presenceScore);

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

        if (!$ocr->possibleName && $presenceScore < 0.62 && $blockScore >= 1.0 && $numberScore >= 1.0) {
            $score = min(max($score, 0.80), 0.88);
        }

        if ($nameScore >= 0.88) {
            $score = max($score, 0.90);
        }

        if ($nameScore >= 0.88 && $blockScore >= 1.0 && $numberScore >= 1.0) {
            $score = max($score, 0.97);
        }

        if ($nameScore >= 0.94 && !$hasBlock && !$hasUnit) {
            $score = max($score, 0.92);
        }

        return max(0.0, min(1.0, $score));
    }
}
