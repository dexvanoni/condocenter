<?php

namespace App\Http\Controllers\Finance;

use App\Helpers\SidebarHelper;
use App\Http\Controllers\Concerns\ResolvesActiveCondominium;
use App\Http\Controllers\Controller;
use App\Http\Requests\AnalyzeFinanceAiRequest;
use App\Services\Finance\FinanceAiAdvisorService;
use App\Services\Finance\FinanceAiQuotaService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class FinanceAiAdvisorController extends Controller
{
    use ResolvesActiveCondominium;

    public function __construct(
        private FinanceAiAdvisorService $advisor,
        private FinanceAiQuotaService $quota,
    ) {}

    public function index(Request $request): View
    {
        $user = $request->user();
        abort_unless($user && $this->userCanAccessAdvisor($user), 403);
        abort_if(SidebarHelper::isFinancialSimplified($user), 403, 'Este recurso não está disponível no ambiente financeiro simplificado.');

        $condominiumId = $this->activeCondominiumId($user);
        $quota = $this->quota->quotaForCondominium($condominiumId);

        return view('finance.ai-advisor.index', [
            'questions' => config('finance_ai.questions', []),
            'analyzeUrl' => route('financial.ai-advisor.analyze'),
            'quota' => [
                'limit' => $quota['limit'],
                'used' => $quota['used'],
                'remaining' => $quota['remaining'],
                'shared' => $quota['shared'],
                'period_label' => $quota['period_label'],
                'allowed' => $quota['allowed'],
                'message' => $quota['message'],
            ],
        ]);
    }

    public function analyze(AnalyzeFinanceAiRequest $request): JsonResponse
    {
        $user = $request->user();
        abort_unless($this->userCanAccessAdvisor($user), 403);
        abort_if(SidebarHelper::isFinancialSimplified($user), 403, 'Este recurso não está disponível no ambiente financeiro simplificado.');

        $condominiumId = $this->activeCondominiumId($user);
        $questionKey = (string) $request->validated('question');

        // Ignora qualquer condominium_id enviado pelo cliente
        $result = $this->advisor->analyze($user, $condominiumId, $questionKey);

        $status = $result['ok'] ? 200 : 503;

        return response()->json([
            'ok' => $result['ok'],
            'from_cache' => $result['from_cache'],
            'question_key' => $result['question_key'],
            'question_title' => $result['question_title'],
            'analysis' => $result['analysis'],
            'message' => $result['message'],
            'quota' => $result['quota'] ?? null,
        ], $status);
    }

    private function userCanAccessAdvisor($user): bool
    {
        return $user->isSindico()
            && session('active_role') === 'Síndico';
    }
}
