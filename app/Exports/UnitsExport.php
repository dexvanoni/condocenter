<?php

namespace App\Exports;

use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class UnitsExport implements FromCollection, WithHeadings, WithMapping
{
    protected Collection $units;

    public function __construct(Collection $units)
    {
        $this->units = $units;
    }

    public function collection()
    {
        return $this->units;
    }

    public function headings(): array
    {
        return [
            'ID',
            'Número',
            'Bloco',
            'Tipo',
            'Situação',
            'Endereço',
            'CEP',
            'Quartos',
            'Banheiros',
            'Área (m²)',
            'Possui Dívidas',
            'Ativo',
        ];
    }

    public function map($unit): array
    {
        return [
            $unit->id,
            $unit->number,
            $unit->block ?? 'N/A',
            $unit->type === 'residential' ? 'Residencial' : 'Comercial',
            $unit->situacao_label,
            $unit->full_address ?? 'N/A',
            $unit->cep ?? 'N/A',
            $unit->num_quartos ?? 'N/A',
            $unit->num_banheiros ?? 'N/A',
            $unit->area ?? 'N/A',
            $unit->possui_dividas ? 'Sim' : 'Não',
            $unit->is_active ? 'Sim' : 'Não',
        ];
    }
}

