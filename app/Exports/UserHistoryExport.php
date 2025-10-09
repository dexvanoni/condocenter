<?php

namespace App\Exports;

use App\Models\User;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\WithMultipleSheets;

class UserHistoryExport implements WithMultipleSheets
{
    protected User $user;
    protected array $history;

    public function __construct(User $user, array $history)
    {
        $this->user = $user;
        $this->history = $history;
    }

    public function sheets(): array
    {
        return [
            new UserInfoSheet($this->user, $this->history),
            new ReservationsSheet($this->history['reservations']),
            new TransactionsSheet($this->history['transactions']),
            new ChargesSheet($this->history['charges']),
        ];
    }
}

class UserInfoSheet implements FromCollection, WithHeadings, WithTitle
{
    protected User $user;
    protected array $history;

    public function __construct(User $user, array $history)
    {
        $this->user = $user;
        $this->history = $history;
    }

    public function collection()
    {
        return collect([
            [
                $this->user->name,
                $this->user->email,
                $this->user->cpf,
                $this->user->phone,
                $this->user->unit?->full_identifier ?? 'N/A',
                implode(', ', $this->user->roles->pluck('name')->toArray()),
                $this->user->is_active ? 'Sim' : 'Não',
            ]
        ]);
    }

    public function headings(): array
    {
        return ['Nome', 'Email', 'CPF', 'Telefone', 'Unidade', 'Perfis', 'Ativo'];
    }

    public function title(): string
    {
        return 'Informações';
    }
}

class ReservationsSheet implements FromCollection, WithHeadings, WithTitle
{
    protected $reservations;

    public function __construct($reservations)
    {
        $this->reservations = $reservations;
    }

    public function collection()
    {
        return $this->reservations->map(fn($r) => [
            $r['space'],
            $r['date'],
            $r['start_time'],
            $r['end_time'],
            $r['status'],
            'R$ ' . number_format($r['amount'], 2, ',', '.'),
        ]);
    }

    public function headings(): array
    {
        return ['Espaço', 'Data', 'Início', 'Fim', 'Status', 'Valor'];
    }

    public function title(): string
    {
        return 'Reservas';
    }
}

class TransactionsSheet implements FromCollection, WithHeadings, WithTitle
{
    protected $transactions;

    public function __construct($transactions)
    {
        $this->transactions = $transactions;
    }

    public function collection()
    {
        return $this->transactions->map(fn($t) => [
            $t['date'],
            $t['type'],
            $t['description'],
            'R$ ' . number_format($t['amount'], 2, ',', '.'),
            $t['category'],
        ]);
    }

    public function headings(): array
    {
        return ['Data', 'Tipo', 'Descrição', 'Valor', 'Categoria'];
    }

    public function title(): string
    {
        return 'Transações';
    }
}

class ChargesSheet implements FromCollection, WithHeadings, WithTitle
{
    protected $charges;

    public function __construct($charges)
    {
        $this->charges = $charges;
    }

    public function collection()
    {
        return $this->charges->map(fn($c) => [
            $c['due_date'],
            $c['type'],
            $c['description'],
            'R$ ' . number_format($c['amount'], 2, ',', '.'),
            $c['status'],
        ]);
    }

    public function headings(): array
    {
        return ['Vencimento', 'Tipo', 'Descrição', 'Valor', 'Status'];
    }

    public function title(): string
    {
        return 'Cobranças';
    }
}

