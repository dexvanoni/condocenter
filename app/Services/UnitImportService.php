<?php

namespace App\Services;

use App\Models\Condominium;
use App\Models\Unit;
use App\Models\User;
use App\Support\PublicPropertyKinds;
use App\Support\UnitImportColumns;
use App\Support\UnitModels;
use App\Support\UnitOccupancyRegimes;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Maatwebsite\Excel\Facades\Excel;

class UnitImportService
{
    public function __construct(
        private UnitOccupancyService $unitOccupancyService,
        private OrganizationQuotaService $organizationQuota,
    ) {}

    /**
     * @return array{
     *   ok: bool,
     *   created: int,
     *   message: string|null,
     *   errors: list<array{line: int, message: string}>
     * }
     */
    public function import(UploadedFile $file, int $condominiumId, User $actor): array
    {
        $parsed = $this->parseFile($file);
        if ($parsed['errors'] !== []) {
            return [
                'ok' => false,
                'created' => 0,
                'message' => 'Corrija a planilha antes de importar.',
                'errors' => $parsed['errors'],
            ];
        }

        $rows = $parsed['rows'];
        if ($rows === []) {
            return [
                'ok' => false,
                'created' => 0,
                'message' => 'Nenhuma linha de unidade encontrada na planilha.',
                'errors' => [['line' => 1, 'message' => 'Inclua ao menos uma unidade abaixo do cabeçalho.']],
            ];
        }

        $condominium = Condominium::query()->withCount('units')->findOrFail($condominiumId);
        $quotaError = $this->validateQuota($condominium, count($rows));
        if ($quotaError !== null) {
            return [
                'ok' => false,
                'created' => 0,
                'message' => $quotaError,
                'errors' => [['line' => 0, 'message' => $quotaError]],
            ];
        }

        $errors = [];
        $normalizedRows = [];
        $seenKeys = [];

        foreach ($rows as $row) {
            $line = (int) $row['line'];
            $data = $row['data'];

            $missing = collect(UnitImportColumns::requiredKeys())
                ->filter(fn (string $key) => ! array_key_exists($key, $data) || $this->cellIsEmpty($data[$key]))
                ->values()
                ->all();

            if ($missing !== []) {
                $errors[] = [
                    'line' => $line,
                    'message' => 'Colunas obrigatórias vazias: '.implode(', ', $missing).'.',
                ];
                continue;
            }

            try {
                $unitPayload = $this->mapRowToUnitPayload($data, $condominiumId);
            } catch (\InvalidArgumentException $e) {
                $errors[] = ['line' => $line, 'message' => $e->getMessage()];
                continue;
            }

            $uniqueKey = $this->uniqueKey($unitPayload['number'], $unitPayload['block'] ?? null);
            if (isset($seenKeys[$uniqueKey])) {
                $errors[] = [
                    'line' => $line,
                    'message' => "Unidade duplicada na planilha (mesmo número/bloco da linha {$seenKeys[$uniqueKey]}).",
                ];
                continue;
            }
            $seenKeys[$uniqueKey] = $line;

            $exists = Unit::query()
                ->where('condominium_id', $condominiumId)
                ->where('number', $unitPayload['number'])
                ->where('block', $unitPayload['block'])
                ->exists();

            if ($exists) {
                $blockText = $unitPayload['block'] ? " e bloco '{$unitPayload['block']}'" : '';
                $errors[] = [
                    'line' => $line,
                    'message' => "Já existe unidade '{$unitPayload['number']}'{$blockText} neste condomínio.",
                ];
                continue;
            }

            $normalizedRows[] = $unitPayload;
        }

        if ($errors !== []) {
            return [
                'ok' => false,
                'created' => 0,
                'message' => 'Nenhuma unidade foi importada. Corrija os erros abaixo.',
                'errors' => $errors,
            ];
        }

        $created = 0;

        DB::transaction(function () use ($normalizedRows, &$created) {
            foreach ($normalizedRows as $payload) {
                Unit::create($payload);
                $created++;
            }
        });

        $actor->logActivity(
            'create',
            'units',
            "Importou {$created} unidade(s) via planilha",
            ['count' => $created]
        );

        return [
            'ok' => true,
            'created' => $created,
            'message' => $created === 1
                ? '1 unidade importada com sucesso.'
                : "{$created} unidades importadas com sucesso.",
            'errors' => [],
        ];
    }

    /**
     * @return array{rows: list<array{line: int, data: array<string, mixed>}>, errors: list<array{line: int, message: string}>}
     */
    protected function parseFile(UploadedFile $file): array
    {
        $extension = strtolower($file->getClientOriginalExtension());

        try {
            if ($extension === 'csv') {
                $sheet = $this->readCsvSheet($file);
            } else {
                $sheet = Excel::toArray(null, $file)[0] ?? [];
            }
        } catch (\Throwable) {
            return [
                'rows' => [],
                'errors' => [['line' => 1, 'message' => 'Não foi possível ler o arquivo. Use o modelo .xlsx ou .csv.']],
            ];
        }

        return $this->parseSheetRows($sheet);
    }

    /**
     * @return list<list<mixed>>
     */
    protected function readCsvSheet(UploadedFile $file): array
    {
        $handle = fopen($file->getRealPath(), 'r');
        if ($handle === false) {
            return [];
        }

        $sheet = [];
        while (($row = fgetcsv($handle, 0, ',')) !== false) {
            if ($this->isEmptyRow($row)) {
                continue;
            }
            $sheet[] = $row;
        }
        fclose($handle);

        return $sheet;
    }

    /**
     * @param  list<list<mixed>>  $sheet
     * @return array{rows: list<array{line: int, data: array<string, mixed>}>, errors: list<array{line: int, message: string}>}
     */
    protected function parseSheetRows(array $sheet): array
    {
        if ($sheet === []) {
            return [
                'rows' => [],
                'errors' => [['line' => 1, 'message' => 'Planilha vazia.']],
            ];
        }

        $headerRow = array_shift($sheet);
        $headers = $this->normalizeHeaders($headerRow);

        if ($headers === []) {
            return [
                'rows' => [],
                'errors' => [['line' => 1, 'message' => 'Cabeçalho inválido. Baixe o modelo e preencha a aba Unidades.']],
            ];
        }

        $missingHeaders = array_diff(UnitImportColumns::requiredKeys(), array_values($headers));
        if ($missingHeaders !== []) {
            return [
                'rows' => [],
                'errors' => [[
                    'line' => 1,
                    'message' => 'Colunas obrigatórias ausentes no cabeçalho: '.implode(', ', $missingHeaders).'.',
                ]],
            ];
        }

        $rows = [];
        foreach ($sheet as $index => $line) {
            $lineNumber = $index + 2;
            if ($this->isEmptyRow($line)) {
                continue;
            }

            $assoc = [];
            foreach ($headers as $columnIndex => $key) {
                $assoc[$key] = $line[$columnIndex] ?? null;
            }

            $rows[] = [
                'line' => $lineNumber,
                'data' => $assoc,
            ];
        }

        return ['rows' => $rows, 'errors' => []];
    }

    /**
     * @param  list<mixed>  $headerRow
     * @return array<int, string>
     */
    protected function normalizeHeaders(array $headerRow): array
    {
        $headers = [];

        foreach ($headerRow as $index => $cell) {
            $key = $this->normalizeHeaderKey($cell);
            if ($key === '') {
                continue;
            }
            $headers[$index] = $key;
        }

        return $headers;
    }

    protected function normalizeHeaderKey(mixed $value): string
    {
        $text = trim((string) $value);
        if ($text === '') {
            return '';
        }

        $text = preg_replace('/^\xEF\xBB\xBF/', '', $text) ?? $text;

        $text = Str::ascii($text);
        $text = strtolower($text);
        $text = str_replace([' ', '-'], '_', $text);

        return preg_replace('/[^a-z0-9_]/', '', $text) ?? '';
    }

    /**
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    protected function mapRowToUnitPayload(array $data, int $condominiumId): array
    {
        $number = trim((string) ($data['numero'] ?? ''));
        if ($number === '') {
            throw new \InvalidArgumentException('O número da unidade é obrigatório.');
        }
        if (strlen($number) > 50) {
            throw new \InvalidArgumentException('Número da unidade deve ter no máximo 50 caracteres.');
        }

        $block = $this->nullableString($data['bloco'] ?? null, 50);
        $type = $this->parseType($data['uso'] ?? null);
        $unitModel = $this->parseUnitModel($data['modelo'] ?? null);
        $situacao = $this->parseSituacao($data['situacao'] ?? null);
        $regime = $this->parseRegime($data['regime_ocupacao'] ?? null);

        if ($regime === UnitOccupancyRegimes::ALUGUEL) {
            throw new \InvalidArgumentException(
                'Unidades em regime de aluguel devem ser cadastradas pela tela individual (exige proprietário e contrato).'
            );
        }

        $publicKind = null;
        if ($regime === UnitOccupancyRegimes::IMOVEL_PUBLICO) {
            $publicKind = $this->parsePublicPropertyKind($data['tipo_imovel_publico'] ?? null);
        }

        $idealFraction = $this->parseOptionalDecimal($data['fracao_ideal'] ?? null);

        $payload = [
            'condominium_id' => $condominiumId,
            'number' => $number,
            'block' => $block,
            'type' => $type,
            'unit_model' => $unitModel,
            'situacao' => $situacao,
            'occupancy_regime' => $regime,
            'public_property_kind' => $publicKind,
            'floor' => $this->parseOptionalInt($data['andar'] ?? null),
            'ideal_fraction' => $idealFraction ?? 1.0000,
            'is_active' => $this->parseBoolean($data['ativo'] ?? null, true),
            'possui_dividas' => $this->parseBoolean($data['possui_dividas'] ?? null, false),
            'cep' => null,
            'logradouro' => null,
            'numero' => null,
            'complemento' => null,
            'bairro' => null,
            'cidade' => null,
            'estado' => null,
            'area' => null,
            'num_quartos' => null,
            'num_banheiros' => null,
            'notes' => null,
            'foto' => null,
            'owner_user_id' => null,
            'rental_period' => null,
            'lease_contract_ends_at' => null,
        ];

        return $this->unitOccupancyService->normalizeRegimeFields($payload);
    }

    protected function validateQuota(Condominium $condominium, int $incomingCount): ?string
    {
        if ($condominium->hasUnitsQuota()) {
            $remaining = $condominium->unitsRemainingQuota() ?? 0;
            if ($incomingCount > $remaining) {
                return "A planilha contém {$incomingCount} unidade(s), mas só restam {$remaining} vaga(s) no limite do condomínio.";
            }
        }

        $organization = $condominium->organization;
        if ($organization?->isManagementCompany()) {
            $remainingOrg = $this->organizationQuota->remainingUnitSlots($organization);
            if ($remainingOrg !== null && $incomingCount > $remainingOrg) {
                return 'Limite de unidades do contrato da administradora seria excedido por esta importação.';
            }
        }

        return null;
    }

    protected function uniqueKey(string $number, ?string $block): string
    {
        return strtolower($number).'|'.strtolower($block ?? '');
    }

    protected function cellIsEmpty(mixed $value): bool
    {
        return trim((string) ($value ?? '')) === '';
    }

    /**
     * @param  list<mixed>  $row
     */
    protected function isEmptyRow(array $row): bool
    {
        foreach ($row as $cell) {
            if (trim((string) ($cell ?? '')) !== '') {
                return false;
            }
        }

        return true;
    }

    protected function nullableString(mixed $value, int $maxLength): ?string
    {
        $text = trim((string) ($value ?? ''));
        if ($text === '') {
            return null;
        }

        if (strlen($text) > $maxLength) {
            throw new \InvalidArgumentException("Texto excede {$maxLength} caracteres.");
        }

        return $text;
    }

    protected function parseType(mixed $value): string
    {
        $normalized = $this->normalizeEnumValue($value);
        $map = [
            'residencial' => 'residential',
            'residential' => 'residential',
            'comercial' => 'commercial',
            'commercial' => 'commercial',
        ];

        if (! isset($map[$normalized])) {
            throw new \InvalidArgumentException("Uso inválido: use residencial ou comercial.");
        }

        return $map[$normalized];
    }

    protected function parseUnitModel(mixed $value): string
    {
        $normalized = $this->normalizeEnumValue($value);
        $aliases = [
            'casa' => UnitModels::CASA,
            'apartamento' => UnitModels::APARTAMENTO,
            'apt' => UnitModels::APARTAMENTO,
            'kitnet' => UnitModels::KITNET,
            'quarto' => UnitModels::QUARTO,
            'flat' => UnitModels::FLAT,
        ];

        if (isset($aliases[$normalized])) {
            return $aliases[$normalized];
        }

        if (in_array($normalized, UnitModels::values(), true)) {
            return $normalized;
        }

        throw new \InvalidArgumentException('Modelo inválido. Use: '.implode(', ', UnitModels::values()).'.');
    }

    protected function parseSituacao(mixed $value): string
    {
        $normalized = $this->normalizeEnumValue($value);
        $allowed = ['habitado', 'fechado', 'indisponivel', 'em_obra'];
        $aliases = [
            'indisponivel' => 'indisponivel',
            'indisponível' => 'indisponivel',
            'em_obra' => 'em_obra',
            'em obra' => 'em_obra',
        ];

        $normalized = $aliases[$normalized] ?? $normalized;

        if (! in_array($normalized, $allowed, true)) {
            throw new \InvalidArgumentException('Situação inválida. Use: habitado, fechado, indisponivel ou em_obra.');
        }

        return $normalized;
    }

    protected function parseRegime(mixed $value): string
    {
        $normalized = $this->normalizeEnumValue($value);
        $aliases = [
            'particular' => UnitOccupancyRegimes::PARTICULAR,
            'aluguel' => UnitOccupancyRegimes::ALUGUEL,
            'imovel_publico' => UnitOccupancyRegimes::IMOVEL_PUBLICO,
            'imovel publico' => UnitOccupancyRegimes::IMOVEL_PUBLICO,
            'imóvel público' => UnitOccupancyRegimes::IMOVEL_PUBLICO,
        ];

        $resolved = $aliases[$normalized] ?? $normalized;

        if (! in_array($resolved, UnitOccupancyRegimes::values(), true)) {
            throw new \InvalidArgumentException('Regime de ocupação inválido. Use: particular ou imovel_publico.');
        }

        return $resolved;
    }

    protected function parsePublicPropertyKind(mixed $value): string
    {
        $normalized = $this->normalizeEnumValue($value);
        if ($normalized === '') {
            throw new \InvalidArgumentException('Informe tipo_imovel_publico para regime imovel_publico.');
        }

        $aliases = [
            'militar' => PublicPropertyKinds::MILITAR,
            'funcional_publico' => PublicPropertyKinds::FUNCIONAL_PUBLICO,
            'funcional publico' => PublicPropertyKinds::FUNCIONAL_PUBLICO,
            'funcional_privado' => PublicPropertyKinds::FUNCIONAL_PRIVADO,
            'funcional privado' => PublicPropertyKinds::FUNCIONAL_PRIVADO,
        ];

        $resolved = $aliases[$normalized] ?? $normalized;

        if (! in_array($resolved, PublicPropertyKinds::values(), true)) {
            throw new \InvalidArgumentException('tipo_imovel_publico inválido.');
        }

        return $resolved;
    }

    protected function parseOptionalInt(mixed $value): ?int
    {
        if ($this->cellIsEmpty($value)) {
            return null;
        }

        if (! is_numeric($value)) {
            throw new \InvalidArgumentException('Andar deve ser um número inteiro.');
        }

        return (int) $value;
    }

    protected function parseOptionalDecimal(mixed $value): ?float
    {
        if ($this->cellIsEmpty($value)) {
            return null;
        }

        $text = str_replace(',', '.', trim((string) $value));
        if (! is_numeric($text)) {
            throw new \InvalidArgumentException('Fração ideal deve ser numérica.');
        }

        return round((float) $text, 4);
    }

    protected function parseBoolean(mixed $value, bool $default): bool
    {
        if ($this->cellIsEmpty($value)) {
            return $default;
        }

        $normalized = $this->normalizeEnumValue($value);

        return match ($normalized) {
            'sim', 's', '1', 'true', 'yes' => true,
            'nao', 'não', 'n', '0', 'false', 'no' => false,
            default => throw new \InvalidArgumentException('Use sim ou nao nos campos ativo e possui_dividas.'),
        };
    }

    protected function normalizeEnumValue(mixed $value): string
    {
        $text = Str::ascii(trim((string) ($value ?? '')));
        $text = strtolower($text);
        $text = str_replace([' ', '-'], '_', $text);

        return $text;
    }
}
