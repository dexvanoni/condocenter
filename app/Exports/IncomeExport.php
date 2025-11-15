<?php

namespace App\Exports;

use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\FromView;
use Maatwebsite\Excel\Concerns\WithTitle;

class IncomeExport implements FromView, WithTitle
{
    public function __construct(
        protected array $data
    ) {
    }

    public function view(): View
    {
        return view('finance.income-expense.export.income-excel', [
            'data' => $this->data['data'],
            'total' => $this->data['total'],
            'startDate' => $this->data['startDate'],
            'endDate' => $this->data['endDate'],
            'condominium' => $this->data['condominium'],
        ]);
    }

    public function title(): string
    {
        return 'Entradas';
    }
}
