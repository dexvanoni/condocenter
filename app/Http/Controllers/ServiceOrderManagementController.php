<?php

namespace App\Http\Controllers;

use App\Models\ServiceOrder;
use App\Models\ServiceOrderItem;
use App\Services\ServiceOrderService;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ServiceOrderManagementController extends Controller
{
    use AuthorizesRequests;

    public function __construct(
        private readonly ServiceOrderService $serviceOrderService,
    ) {
        $this->middleware(function ($request, $next) {
            $this->authorize('manage', ServiceOrder::class);

            return $next($request);
        });
    }

    public function index(Request $request)
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();
        $condominiumId = $user->tenantCondominiumId();

        $query = ServiceOrder::with(['unit', 'requester', 'assignee'])
            ->byCondominium($condominiumId)
            ->latest();

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('urgency')) {
            $query->where('urgency', $request->urgency);
        }

        if ($request->filled('type')) {
            $query->where('type', $request->type);
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('protocol', 'like', "%{$search}%")
                    ->orWhere('title', 'like', "%{$search}%")
                    ->orWhereHas('requester', fn ($rq) => $rq->where('name', 'like', "%{$search}%"));
            });
        }

        $orders = $query->paginate(20)->withQueryString();

        $stats = [
            'open' => ServiceOrder::byCondominium($condominiumId)->where('status', 'open')->count(),
            'dispatched' => ServiceOrder::byCondominium($condominiumId)->where('status', 'dispatched')->count(),
            'in_progress' => ServiceOrder::byCondominium($condominiumId)->where('status', 'in_progress')->count(),
            'resolved' => ServiceOrder::byCondominium($condominiumId)->where('status', 'resolved')->count(),
        ];

        return view('service-orders.manage.index', compact('orders', 'stats'));
    }

    public function show(ServiceOrder $serviceOrder)
    {
        $this->authorize('update', $serviceOrder);

        $serviceOrder->load([
            'unit',
            'requester',
            'assignee',
            'charges',
            'items.creator',
            'items.charge',
            'messages.author',
        ]);

        $assignees = $this->serviceOrderService->managersForCondominium($serviceOrder->condominium_id);

        return view('service-orders.manage.show', [
            'order' => $serviceOrder,
            'assignees' => $assignees,
        ]);
    }

    public function updateStatus(Request $request, ServiceOrder $serviceOrder)
    {
        $this->authorize('update', $serviceOrder);

        $validated = $request->validate([
            'status' => 'required|in:open,dispatched,in_progress,resolved,unresolved,cancelled',
            'resolution_notes' => 'nullable|string|max:5000',
            'assigned_to' => 'nullable|exists:users,id',
        ]);

        $this->serviceOrderService->updateStatus($serviceOrder, Auth::user(), $validated);

        return back()->with('success', 'Status da ordem de serviço atualizado.');
    }

    public function storeMessage(Request $request, ServiceOrder $serviceOrder)
    {
        $this->authorize('update', $serviceOrder);

        $validated = $request->validate([
            'message' => 'required|string|max:3000',
            'is_internal' => 'nullable|boolean',
        ]);

        $this->serviceOrderService->addMessage(
            $serviceOrder,
            Auth::user(),
            $validated['message'],
            (bool) ($validated['is_internal'] ?? false),
        );

        return back()->with('success', 'Mensagem registrada.');
    }

    public function storeItem(Request $request, ServiceOrder $serviceOrder)
    {
        $this->authorize('update', $serviceOrder);

        $validated = $request->validate([
            'type' => 'required|in:material,service',
            'description' => 'required|string|max:255',
            'quantity' => 'required|numeric|min:0.01|max:9999',
            'unit_price' => 'required|numeric|min:0.01|max:999999.99',
        ]);

        $this->serviceOrderService->addItem($serviceOrder, Auth::user(), $validated);

        return back()->with('success', 'Item adicionado ao ressarcimento.');
    }

    public function destroyItem(ServiceOrder $serviceOrder, ServiceOrderItem $item)
    {
        $this->authorize('update', $serviceOrder);

        if ($item->service_order_id !== $serviceOrder->id) {
            abort(404);
        }

        $this->serviceOrderService->removeItem($item, Auth::user());

        return back()->with('success', 'Item removido.');
    }

    public function generateCharge(Request $request, ServiceOrder $serviceOrder)
    {
        $this->authorize('update', $serviceOrder);

        $validated = $request->validate([
            'due_date' => 'required|date|after_or_equal:today',
            'fine_percentage' => 'nullable|numeric|min:0|max:100',
            'interest_rate' => 'nullable|numeric|min:0|max:100',
        ]);

        $charge = $this->serviceOrderService->generateReimbursementCharge(
            $serviceOrder,
            Auth::user(),
            $validated,
        );

        return back()->with('success', "Cobrança #{$charge->id} gerada com sucesso para ressarcimento.");
    }
}
