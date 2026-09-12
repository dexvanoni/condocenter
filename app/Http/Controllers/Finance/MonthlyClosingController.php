<?php

namespace App\Http\Controllers\Finance;

use App\Http\Controllers\Concerns\ResolvesActiveCondominium;
use App\Http\Controllers\Controller;
use App\Http\Requests\CompleteMonthlyClosingRequest;
use App\Http\Requests\ConfirmMonthlyClosingStepRequest;
use App\Services\MonthlyClosingChecklistService;
use App\Services\MonthlyClosingService;
use App\Support\MonthlyClosingSteps;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;

class MonthlyClosingController extends Controller
{
    use ResolvesActiveCondominium;

    public function __construct(
        private readonly MonthlyClosingChecklistService $checklistService,
        private readonly MonthlyClosingService $closingService
    ) {
    }

    public function index(Request $request)
    {
        $user = Auth::user();

        abort_unless($user->can('view_financial_reports'), 403);

        $month = $this->resolveMonth($request);
        $condominium = $this->activeCondominium($user);
        $checklist = $this->checklistService->build($condominium->id, $month);

        return view('finance.monthly-closing.index', [
            'condominium' => $condominium,
            'checklist' => $checklist,
            'prevMonth' => $month->copy()->subMonth()->format('Y-m'),
            'nextMonth' => $month->copy()->addMonth()->format('Y-m'),
            'isCurrentMonth' => $month->isSameMonth(now()),
            'canManageClosing' => $user->can('manage_transactions'),
        ]);
    }

    public function confirmStep(ConfirmMonthlyClosingStepRequest $request, string $stepKey): RedirectResponse
    {
        abort_unless(MonthlyClosingSteps::isValid($stepKey), 404);

        $user = Auth::user();
        $month = $this->resolveMonth($request);
        $condominiumId = $this->activeCondominiumId($user);
        $closing = $this->closingService->findOrCreate($condominiumId, $month);

        $this->closingService->confirmStep(
            $closing,
            $stepKey,
            $user,
            $request->input('notes')
        );

        return redirect()
            ->route('monthly-closing.index', ['month' => $month->format('Y-m')])
            ->with('success', 'Passo marcado como conferido.');
    }

    public function unconfirmStep(Request $request, string $stepKey): RedirectResponse
    {
        abort_unless(Auth::user()?->can('manage_transactions'), 403);
        abort_unless(MonthlyClosingSteps::isValid($stepKey), 404);

        $request->validate(['month' => ['required', 'date_format:Y-m']]);

        $month = $this->resolveMonth($request);
        $condominiumId = $this->activeCondominiumId(Auth::user());
        $closing = $this->closingService->findOrCreate($condominiumId, $month);

        $this->closingService->unconfirmStep($closing, $stepKey);

        return redirect()
            ->route('monthly-closing.index', ['month' => $month->format('Y-m')])
            ->with('success', 'Confirmação do passo removida.');
    }

    public function complete(CompleteMonthlyClosingRequest $request): RedirectResponse
    {
        $user = Auth::user();
        $month = $this->resolveMonth($request);
        $condominiumId = $this->activeCondominiumId($user);
        $checklist = $this->checklistService->build($condominiumId, $month);

        $this->checklistService->assertReadyToComplete($checklist);

        $closing = $this->closingService->findOrCreate($condominiumId, $month);
        $this->closingService->complete($closing, $user, $request->input('closing_notes'));

        return redirect()
            ->route('monthly-closing.index', ['month' => $month->format('Y-m')])
            ->with('success', 'Fechamento do mês encerrado com sucesso.');
    }

    public function reopen(Request $request): RedirectResponse
    {
        abort_unless(Auth::user()?->can('manage_transactions'), 403);

        $request->validate(['month' => ['required', 'date_format:Y-m']]);

        $month = $this->resolveMonth($request);
        $condominiumId = $this->activeCondominiumId(Auth::user());
        $closing = $this->closingService->findOrCreate($condominiumId, $month);

        $this->closingService->reopen($closing);

        return redirect()
            ->route('monthly-closing.index', ['month' => $month->format('Y-m')])
            ->with('success', 'Fechamento reaberto para novas conferências.');
    }

    private function resolveMonth(Request $request): Carbon
    {
        if (! $request->filled('month')) {
            return now()->startOfMonth();
        }

        $month = Carbon::createFromFormat('Y-m', $request->input('month'));

        if (! $month) {
            return now()->startOfMonth();
        }

        return $month->startOfMonth();
    }
}
