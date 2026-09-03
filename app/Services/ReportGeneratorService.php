<?php

namespace App\Services;

use App\Models\User;
use App\Support\UnitModels;
use Barryvdh\DomPDF\Facade\Pdf;
use Maatwebsite\Excel\Facades\Excel;
use Illuminate\Support\Collection;

class ReportGeneratorService
{
    protected UserHistoryService $historyService;

    public function __construct(UserHistoryService $historyService)
    {
        $this->historyService = $historyService;
    }

    /**
     * Gera relatório em PDF do histórico do usuário
     *
     * @param User $user
     * @return \Illuminate\Http\Response
     */
    public function generateUserHistoryPDF(User $user)
    {
        $history = $this->historyService->getCompleteHistory($user);
        
        $pdf = Pdf::loadView('reports.user-history-pdf', [
            'user' => $user,
            'history' => $history,
            'generated_at' => now()->format('d/m/Y H:i:s'),
        ]);

        $pdf->setPaper('a4', 'portrait');
        
        $filename = 'historico_' . \Illuminate\Support\Str::slug($user->name) . '_' . date('Y-m-d') . '.pdf';
        
        return $pdf->download($filename);
    }

    /**
     * Gera relatório em Excel do histórico do usuário
     *
     * @param User $user
     * @return \Symfony\Component\HttpFoundation\BinaryFileResponse
     */
    public function generateUserHistoryExcel(User $user)
    {
        $history = $this->historyService->getCompleteHistory($user);
        
        $filename = 'historico_' . \Illuminate\Support\Str::slug($user->name) . '_' . date('Y-m-d') . '.xlsx';
        
        return Excel::download(
            new \App\Exports\UserHistoryExport($user, $history),
            $filename
        );
    }

    /**
     * Gera dados para impressão do histórico
     *
     * @param User $user
     * @return array
     */
    public function generateUserHistoryPrint(User $user): array
    {
        return $this->historyService->getCompleteHistory($user);
    }

    /**
     * Gera relatório de unidades
     *
     * @param Collection $units
     * @param string $format (pdf, excel, csv)
     * @param array $filters Filtros aplicados na listagem
     * @return mixed
     */
    public function generateUnitsReport(Collection $units, string $format = 'pdf', array $filters = [])
    {
        $byModel = $units
            ->groupBy(fn ($unit) => $unit->unit_model ?? 'apartamento')
            ->map(fn ($group) => $group->count())
            ->sortKeys();

        $byModelLabels = $byModel
            ->mapWithKeys(fn ($count, $model) => [UnitModels::label($model) => $count])
            ->all();

        $data = [
            'units' => $units,
            'total' => $units->count(),
            'habitadas' => $units->where('situacao', 'habitado')->count(),
            'fechadas' => $units->where('situacao', 'fechado')->count(),
            'com_dividas' => $units->where('possui_dividas', true)->count(),
            'by_model' => $byModelLabels,
            'filters' => $filters,
            'filters_label' => $this->formatUnitsFiltersLabel($filters),
            'generated_at' => now()->format('d/m/Y H:i:s'),
        ];

        return match($format) {
            'pdf' => $this->generateUnitsPDF($data),
            'excel' => $this->generateUnitsExcel($data),
            'csv' => $this->generateUnitsCSV($data),
            default => $data,
        };
    }

    protected function formatUnitsFiltersLabel(array $filters): array
    {
        $labels = [];

        if (!empty($filters['search'])) {
            $labels['Busca'] = $filters['search'];
        }

        if (!empty($filters['type'])) {
            $labels['Uso'] = $filters['type'] === 'residential' ? 'Residencial' : 'Comercial';
        }

        if (!empty($filters['unit_model'])) {
            $labels['Modelo'] = UnitModels::label($filters['unit_model']);
        }

        if (!empty($filters['situacao'])) {
            $situacoes = [
                'habitado' => 'Habitado',
                'fechado' => 'Fechado',
                'indisponivel' => 'Indisponível',
                'em_obra' => 'Em Obra',
            ];
            $labels['Situação'] = $situacoes[$filters['situacao']] ?? ucfirst($filters['situacao']);
        }

        if (array_key_exists('possui_dividas', $filters) && $filters['possui_dividas'] !== null && $filters['possui_dividas'] !== '') {
            $labels['Dívidas'] = filter_var($filters['possui_dividas'], FILTER_VALIDATE_BOOLEAN)
                ? 'Com dívidas'
                : 'Sem dívidas';
        }

        return $labels;
    }

    /**
     * Gera PDF de relatório de unidades
     */
    protected function generateUnitsPDF(array $data)
    {
        $pdf = Pdf::loadView('reports.units-pdf', $data);
        $pdf->setPaper('a4', 'landscape');
        
        return $pdf->download('relatorio_unidades_' . date('Y-m-d') . '.pdf');
    }

    /**
     * Gera Excel de relatório de unidades
     */
    protected function generateUnitsExcel(array $data)
    {
        return Excel::download(
            new \App\Exports\UnitsExport($data['units']),
            'relatorio_unidades_' . date('Y-m-d') . '.xlsx'
        );
    }

    /**
     * Gera CSV de relatório de unidades
     */
    protected function generateUnitsCSV(array $data)
    {
        return Excel::download(
            new \App\Exports\UnitsExport($data['units']),
            'relatorio_unidades_' . date('Y-m-d') . '.csv',
            \Maatwebsite\Excel\Excel::CSV
        );
    }

    /**
     * Gera relatório de usuários
     *
     * @param Collection $users
     * @param string $format
     * @return mixed
     */
    public function generateUsersReport(Collection $users, string $format = 'pdf')
    {
        $data = [
            'users' => $users,
            'total' => $users->count(),
            'ativos' => $users->where('is_active', true)->count(),
            'com_dividas' => $users->where('possui_dividas', true)->count(),
            'by_role' => $this->countUsersByRole($users),
            'generated_at' => now()->format('d/m/Y H:i:s'),
        ];

        return match($format) {
            'pdf' => $this->generateUsersPDF($data),
            'excel' => $this->generateUsersExcel($data),
            'csv' => $this->generateUsersCSV($data),
            default => $data,
        };
    }

    /**
     * Conta usuários por perfil
     */
    protected function countUsersByRole(Collection $users): array
    {
        $counts = [
            'Administrador' => 0,
            'Síndico' => 0,
            'Morador' => 0,
            'Agregado' => 0,
            'Porteiro' => 0,
            'Conselho Fiscal' => 0,
            'Secretaria' => 0,
        ];

        foreach ($users as $user) {
            foreach ($user->roles as $role) {
                if (isset($counts[$role->name])) {
                    $counts[$role->name]++;
                }
            }
        }

        return $counts;
    }

    /**
     * Gera PDF de relatório de usuários
     */
    protected function generateUsersPDF(array $data)
    {
        $pdf = Pdf::loadView('reports.users-pdf', $data);
        $pdf->setPaper('a4', 'landscape');
        
        return $pdf->download('relatorio_usuarios_' . date('Y-m-d') . '.pdf');
    }

    /**
     * Gera Excel de relatório de usuários
     */
    protected function generateUsersExcel(array $data)
    {
        return Excel::download(
            new \App\Exports\UsersExport($data['users']),
            'relatorio_usuarios_' . date('Y-m-d') . '.xlsx'
        );
    }

    /**
     * Gera CSV de relatório de usuários
     */
    protected function generateUsersCSV(array $data)
    {
        return Excel::download(
            new \App\Exports\UsersExport($data['users']),
            'relatorio_usuarios_' . date('Y-m-d') . '.csv',
            \Maatwebsite\Excel\Excel::CSV
        );
    }
}

