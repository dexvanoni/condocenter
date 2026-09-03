<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Concerns\ResolvesActiveCondominium;
use App\Http\Requests\CancelFineRequest;
use App\Http\Requests\StoreFineRequest;
use App\Models\Fine;
use App\Models\Condominium;
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

        $query = Fine::with(['appliedBy', 'recipients.charge'])
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

        $condominium = Condominium::query()->find($condominiumId);
        $onlinePaymentsEnabled = $condominium?->acceptsOnlinePayments() ?? false;
        $isMoradorView = !$user->can('manage_fines')
            && !($user->can('view_fines') && $user->hasRole('Conselho Fiscal'));

        if ($isMoradorView) {
            $fines->getCollection()->transform(function (Fine $fine) use ($user, $onlinePaymentsEnabled) {
                $fine->setAttribute('resident_context', $this->resolveResidentContext($fine, $user, $onlinePaymentsEnabled));

                return $fine;
            });
        }

        return view('fines.index', compact('fines', 'isMoradorView', 'onlinePaymentsEnabled'));
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

        $user = Auth::user();
        $condominium = $fine->condominium ?? Condominium::query()->find($fine->condominium_id);
        $onlinePaymentsEnabled = $condominium?->acceptsOnlinePayments() ?? false;
        $isMoradorView = !$user->can('manage_fines')
            && !($user->can('view_fines') && $user->hasRole('Conselho Fiscal'));
        $residentContext = $isMoradorView
            ? $this->resolveResidentContext($fine, $user, $onlinePaymentsEnabled)
            : null;

        return view('fines.show', compact('fine', 'isMoradorView', 'onlinePaymentsEnabled', 'residentContext'));
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

    /**
     * @return array{
     *     charge_id: int|null,
     *     charge_status: string|null,
     *     payment_status_label: string,
     *     payment_status_color: string,
     *     can_pay_online: bool,
     *     paid_at: \Illuminate\Support\Carbon|null
     * }
     */
    protected function resolveResidentContext(Fine $fine, $user, bool $onlinePaymentsEnabled): array
    {
        $recipient = $fine->recipients->first(function ($row) use ($user) {
            return (int) $row->notified_user_id === (int) $user->id
                || (int) $row->user_id === (int) $user->id;
        });

        $charge = $recipient?->charge;
        $chargeStatus = $charge?->status;

        if ($fine->isCancelled() && (!$charge || $chargeStatus === 'cancelled')) {
            return [
                'charge_id' => $charge?->id,
                'charge_status' => 'cancelled',
                'payment_status_label' => 'Cancelada',
                'payment_status_color' => 'secondary',
                'can_pay_online' => false,
                'paid_at' => null,
            ];
        }

        $statusMeta = match ($chargeStatus) {
            'pending' => ['label' => 'Pendente', 'color' => 'warning'],
            'overdue' => ['label' => 'Em atraso', 'color' => 'danger'],
            'paid' => ['label' => 'Pago', 'color' => 'success'],
            'cancelled' => ['label' => 'Cancelada', 'color' => 'secondary'],
            default => ['label' => 'Sem cobrança', 'color' => 'secondary'],
        };

        $canPayOnline = $onlinePaymentsEnabled
            && $charge
            && in_array($chargeStatus, ['pending', 'overdue'], true)
            && filled($user->unit_id)
            && (int) $charge->unit_id === (int) $user->unit_id;

        return [
            'charge_id' => $charge?->id,
            'charge_status' => $chargeStatus,
            'payment_status_label' => $statusMeta['label'],
            'payment_status_color' => $statusMeta['color'],
            'can_pay_online' => $canPayOnline,
            'paid_at' => $charge?->paid_at,
        ];
    }
}
