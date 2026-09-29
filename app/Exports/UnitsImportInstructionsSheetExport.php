<?php

namespace App\Exports;

use App\Support\UnitImportColumns;
use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithTitle;

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
