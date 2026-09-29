<?php

namespace App\Exports;

use App\Support\UnitImportColumns;
use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithTitle;

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
