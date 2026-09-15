<?php

namespace App\Exports;

use App\Models\Package;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithTitle;

class PackageMovementsExport implements FromCollection, WithHeadings, WithMapping, WithTitle
{
    public function __construct(
        private readonly Collection $packages,
    ) {}

    public function collection(): Collection
    {
        return $this->packages;
    }

    public function headings(): array
    {
        return [
            'Recebida em',
            'Unidade',
            'Tipo',
            'Status',
            'Remetente',
            'Rastreamento',
            'Identificação',
            'Registrado por',
            'Retirada em',
            'Retirado por',
            'Quem retirou (informado)',
            'Senha verificada em',
            'WhatsApp',
        ];
    }

    public function map($package): array
    {
        /** @var Package $package */
        return [
            $package->received_at?->format('d/m/Y H:i'),
            $package->unit?->full_identifier ?? '—',
            $package->type_label,
            $package->status_label,
            $package->sender ?? '—',
            $package->tracking_code ?? '—',
            $package->identification_method_label,
            $package->registeredBy?->name ?? '—',
            $package->collected_at?->format('d/m/Y H:i') ?? '—',
            $package->collectedBy?->name ?? '—',
            $package->picked_up_by_name ?? '—',
            $package->pickup_verified_at?->format('d/m/Y H:i') ?? '—',
            $this->whatsappLabel($package->whatsapp_delivery_status),
        ];
    }

    public function title(): string
    {
        return 'Movimentações de Encomendas';
    }

    private function whatsappLabel(?string $status): string
    {
        return match ($status) {
            Package::WHATSAPP_SENT => 'Enviado',
            Package::WHATSAPP_FAILED => 'Falhou',
            Package::WHATSAPP_PENDING => 'Pendente',
            default => '—',
        };
    }
}
