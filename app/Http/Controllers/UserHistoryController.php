<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Services\UserHistoryService;
use App\Services\ReportGeneratorService;
use Illuminate\Http\Request;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Support\Facades\Auth;

class UserHistoryController extends Controller
{
    use AuthorizesRequests;

    protected UserHistoryService $historyService;
    protected ReportGeneratorService $reportService;

    public function __construct(
        UserHistoryService $historyService,
        ReportGeneratorService $reportService
    ) {
        $this->historyService = $historyService;
        $this->reportService = $reportService;
    }

    /**
     * Get authenticated user with proper type hint
     * @return User
     */
    private function authUser(): User
    {
        /** @var User $user */
        $user = Auth::user();
        return $user;
    }

    /**
     * Exibe o histórico completo do usuário
     */
    public function show(User $user)
    {
        // Verifica autorização
        $authUser = $this->authUser();
        if (!$authUser->can('viewHistory', $user)) {
            abort(403, 'Você não tem permissão para visualizar este histórico.');
        }

        $history = $this->historyService->getCompleteHistory($user);

        return view('users.history', compact('user', 'history'));
    }

    /**
     * Exporta histórico em PDF
     */
    public function exportPdf(User $user)
    {
        // Verifica autorização
        $authUser = $this->authUser();
        if (!$authUser->can('exportHistory', $user)) {
            abort(403, 'Você não tem permissão para exportar este histórico.');
        }

        // Log da atividade
        $authUser->logActivity(
            'export_pdf',
            'user_history',
            "Exportou histórico PDF do usuário {$user->name}",
            ['user_id' => $user->id]
        );

        return $this->reportService->generateUserHistoryPDF($user);
    }

    /**
     * Exporta histórico em Excel
     */
    public function exportExcel(User $user)
    {
        // Verifica autorização
        $authUser = $this->authUser();
        if (!$authUser->can('exportHistory', $user)) {
            abort(403, 'Você não tem permissão para exportar este histórico.');
        }

        // Log da atividade
        $authUser->logActivity(
            'export_excel',
            'user_history',
            "Exportou histórico Excel do usuário {$user->name}",
            ['user_id' => $user->id]
        );

        return $this->reportService->generateUserHistoryExcel($user);
    }

    /**
     * Retorna dados para impressão
     */
    public function print(User $user)
    {
        // Verifica autorização
        $authUser = $this->authUser();
        if (!$authUser->can('viewHistory', $user)) {
            abort(403, 'Você não tem permissão para visualizar este histórico.');
        }

        $history = $this->historyService->getCompleteHistory($user);

        return view('users.history-print', compact('user', 'history'));
    }
}

