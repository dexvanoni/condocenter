<?php

namespace App\Exports;

use App\Support\UnitImportColumns;
use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMultipleSheets;
use Maatwebsite\Excel\Concerns\WithTitle;

class UnitsImportTemplateExport implements WithMultipleSheets
{
    public function sheets(): array
    {
        return [
            new UnitsImportDataSheetExport,
            new UnitsImportInstructionsSheetExport,
        ];
    }
}

class UnitsImportDataSheetExport implements FromArray, WithHeadings, WithTitle
{
    public function title(): string
    {
        return 'Unidades';
    }

    public function headings(): array
    {
        return UnitImportColumns::headings();
    }

    public function array(): array
    {
        return UnitImportColumns::sampleRows();
    }
}

class UnitsImportInstructionsSheetExport implements FromArray, WithHeadings, WithTitle
{
    public function title(): string
    {
        return 'Instrucoes';
    }

    public function headings(): array
    {
        return ['coluna', 'obrigatorio', 'descricao', 'valores_aceitos'];
    }

    public function array(): array
    {
        return collect(UnitImportColumns::instructions())
            ->map(fn (array $row) => [
                $row['coluna'],
                $row['obrigatorio'],
                $row['descricao'],
                $row['valores'],
            ])
            ->all();
    }
}
