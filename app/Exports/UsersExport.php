<?php

namespace App\Exports;

use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class UsersExport implements FromCollection, WithHeadings, WithMapping
{
    protected Collection $users;

    public function __construct(Collection $users)
    {
        $this->users = $users;
    }

    public function collection()
    {
        return $this->users;
    }

    public function headings(): array
    {
        return [
            'ID',
            'Nome',
            'Email',
            'CPF',
            'Telefone',
            'Celular',
            'Unidade',
            'Perfis',
            'Data Entrada',
            'Idade',
            'Possui Dívidas',
            'Ativo',
        ];
    }

    public function map($user): array
    {
        return [
            $user->id,
            $user->name,
            $user->email,
            $user->cpf ?? 'N/A',
            $user->phone ?? 'N/A',
            $user->telefone_celular ?? 'N/A',
            $user->unit?->full_identifier ?? 'N/A',
            $user->roles->pluck('name')->implode(', '),
            $user->data_entrada?->format('d/m/Y') ?? 'N/A',
            $user->idade ?? 'N/A',
            $user->possui_dividas ? 'Sim' : 'Não',
            $user->is_active ? 'Sim' : 'Não',
        ];
    }
}

