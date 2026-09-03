<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Concerns\ResolvesActiveCondominium;
use App\Http\Requests\CancelFineRequest;
use App\Http\Requests\StoreFineRequest;
use App\Models\Fine;
use App\Services\FineNoticeService;
use App\Services\FineService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class FineController extends Controller
{
    use ResolvesActiveCondominium;

    public function __construct(
        private readonly FineService $fineService,
        private readonly FineNoticeService $noticeService,
    ) {
        $this->authorizeResource(Fine::class, 'fine', [
            'except' => ['exportPdf'],
        ]);
    }

    public function index(Request $request)
    {
        $user = Auth::user();
        $condominiumId = $this->activeCondominiumId($user);

        $query = Fine::with(['appliedBy', 'recipients'])
            ->byCondominium($condominiumId)
            ->orderByDesc('applied_at');

        if (!$user->can('manage_fines') && !($user->can('view_fines') && $user->hasRole('Conselho Fiscal'))) {
            $query->whereHas('recipients', function ($recipientQuery) use ($user) {
                $recipientQuery->where('notified_user_id', $user->id)
                    ->orWhere('user_id', $user->id);
            });
        }

        if ($request->filled('status')) {
            $query->where('status', $request->input('status'));
        }

        if ($request->filled('search')) {
            $term = trim($request->input('search'));
            $query->where(function ($q) use ($term) {
                $q->where('reference', 'like', "%{$term}%")
                    ->orWhere('motivo', 'like', "%{$term}%")
                    ->orWhere('enquadramento', 'like', "%{$term}%");
            });
        }

        $fines = $query->paginate(15)->withQueryString();

        return view('fines.index', compact('fines'));
    }

    public function create()
    {
        $eligibleUsers = $this->fineService->eligibleInfractors($this->activeCondominiumId(Auth::user()));

        return view('fines.create', compact('eligibleUsers'));
    }

    public function store(StoreFineRequest $request)
    {
        $fine = $this->fineService->issue(Auth::user(), $request->validated());

        return redirect()
            ->route('fines.show', $fine)
            ->with('success', 'Multa aplicada com sucesso. Notificações enviadas aos responsáveis.');
    }

    public function show(Fine $fine)
    {
        $fine->load([
            'appliedBy',
            'cancelledBy',
            'recipients.user.roles',
            'recipients.unit',
            'recipients.notifiedUser',
            'recipients.charge',
        ]);

        return view('fines.show', compact('fine'));
    }

    public function exportPdf(Fine $fine)
    {
        $this->authorize('export', $fine);

        return $this->noticeService->download($fine);
    }

    public function cancel(CancelFineRequest $request, Fine $fine)
    {
        $this->authorize('cancel', $fine);

        $this->fineService->cancel($fine, Auth::user(), $request->validated('reason'));

        return redirect()
            ->route('fines.show', $fine)
            ->with('success', 'Multa cancelada com sucesso.');
    }
}
