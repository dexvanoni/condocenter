<?php

namespace App\Exports;

use Illuminate\Contracts\View\View;
use Illuminate\Support\Carbon;
use Maatwebsite\Excel\Concerns\FromView;
use Maatwebsite\Excel\Concerns\WithTitle;

class AccountabilityExport implements FromView, WithTitle
{
    public function __construct(
        protected $condominium,
        protected array $data,
        protected Carbon $startDate,
        protected Carbon $endDate
    ) {
    }

    public function view(): View
    {
        return view('finance.accountability.excel', [
            'condominium' => $this->condominium,
            'data' => $this->data,
            'startDate' => $this->startDate,
            'endDate' => $this->endDate,
        ]);
    }

    public function title(): string
    {
        return 'Prestação de Contas';
    }
}

