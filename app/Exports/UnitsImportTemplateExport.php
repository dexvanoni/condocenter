<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\WithMultipleSheets;

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
