<?php

namespace App\Exports;

use App\Models\OccurrenceBookEntry;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithTitle;

class OccurrenceBookExport implements FromCollection, WithHeadings, WithMapping, WithTitle
{
    public function __construct(
        private readonly Collection $entries,
    ) {}

    public function collection(): Collection
    {
        return $this->entries;
    }

    public function headings(): array
    {
        return [
            'Referência',
            'Data',
            'Tipo',
            'Título',
            'Descrição',
            'Morador',
            'Unidade',
            'WhatsApp solicitado',
            'Ciência em',
            'Registrado por',
            'Observação da ciência',
        ];
    }

    public function map($entry): array
    {
        /** @var OccurrenceBookEntry $entry */
        return [
            $entry->referenceCode(),
            $entry->created_at?->format('d/m/Y H:i'),
            $entry->typeLabel(),
            $entry->title,
            $entry->body,
            $entry->author?->name,
            $entry->unit?->full_identifier,
            $entry->notify_whatsapp ? 'Sim' : 'Não',
            $entry->acknowledged_at?->format('d/m/Y H:i') ?? 'Pendente',
            $entry->acknowledgedBy?->name,
            $entry->acknowledgment_note,
        ];
    }

    public function title(): string
    {
        return 'Livro de Ocorrências';
    }
}
