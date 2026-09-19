<?php

namespace App\Http\Controllers;

use App\Models\ServiceOrder;
use App\Models\Unit;
use App\Services\ServiceOrderService;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ServiceOrderController extends Controller
{
    use AuthorizesRequests;

    public function __construct(
        private readonly ServiceOrderService $serviceOrderService,
    ) {
    }

    public function index(Request $request)
    {
        $this->authorize('viewAny', ServiceOrder::class);

        /** @var \App\Models\User $user */
        $user = Auth::user();
        $condominiumId = $user->tenantCondominiumId();

        $query = ServiceOrder::with(['unit', 'requester'])
            ->byCondominium($condominiumId)
            ->where(function ($q) use ($user) {
                $q->where('user_id', $user->id);

                if ($user->isMorador() && $user->unit_id) {
                    $q->orWhere(function ($inner) use ($user) {
                        $inner->where('visible_to_tenant', true)
                            ->where('unit_id', $user->unit_id);
                    });
                }

                if ($user->isProprietario()) {
                    $ownedIds = app(\App\Services\UnitOccupancyService::class)
                        ->ownedUnitIds($user, (int) $condominiumId);

                    if ($ownedIds !== []) {
                        $q->orWhereIn('unit_id', $ownedIds);
                    }
                }
            })
            ->latest();

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('protocol', 'like', "%{$search}%")
                    ->orWhere('title', 'like', "%{$search}%")
                    ->orWhere('description', 'like', "%{$search}%");
            });
        }

        $orders = $query->paginate(12)->withQueryString();

        return view('service-orders.index', compact('orders'));
    }

    public function create()
    {
        $this->authorize('create', ServiceOrder::class);

        /** @var \App\Models\User $user */
        $user = Auth::user();

        $occupancy = app(\App\Services\UnitOccupancyService::class);
        $condominiumId = (int) $user->tenantCondominiumId();

        $units = Unit::byCondominium($condominiumId)
            ->active()
            ->orderBy('block')
            ->orderBy('number')
            ->get()
            ->filter(function (Unit $unit) use ($user, $occupancy) {
                if ($occupancy->isRental($unit)) {
                    return $user->isProprietario() && $occupancy->userOwnsUnit($user, $unit);
                }

                return $user->isMorador() && (int) $user->unit_id === (int) $unit->id;
            })
            ->values();

        return view('service-orders.create', [
            'user' => $user,
            'units' => $units,
            'prefilledUnit' => $user->isProprietario()
                ? $units->first()
                : $user->unit,
            'canSetTenantVisibility' => $user->isProprietario(),
        ]);
    }

    public function store(Request $request)
    {
        $this->authorize('create', ServiceOrder::class);

        /** @var \App\Models\User $user */
        $user = Auth::user();

        $validated = $request->validate([
            'type' => 'required|in:maintenance,repair,inspection',
            'location_type' => 'required|in:unit,common_area',
            'location_detail' => 'nullable|string|max:255',
            'unit_id' => 'nullable|exists:units,id',
            'title' => 'required|string|max:150',
            'description' => 'required|string|max:5000',
            'urgency' => 'required|in:low,medium,high,urgent',
            'preferred_date' => 'nullable|date|after_or_equal:today',
            'preferred_time_start' => 'nullable|date_format:H:i',
            'preferred_time_end' => 'nullable|date_format:H:i|after:preferred_time_start',
            'availability_notes' => 'nullable|string|max:2000',
            'whatsapp_notify' => 'nullable|boolean',
            'visible_to_tenant' => 'nullable|boolean',
        ]);

        if ($validated['location_type'] === 'common_area' && empty($validated['location_detail'])) {
            return back()->withErrors(['location_detail' => 'Informe qual área comum precisa de atendimento.'])->withInput();
        }

        $order = $this->serviceOrderService->create($user, array_merge($validated, [
            'whatsapp_notify' => $request->boolean('whatsapp_notify'),
            'visible_to_tenant' => $request->boolean('visible_to_tenant'),
        ]));

        return redirect()
            ->route('service-orders.show', $order)
            ->with('success', "Ordem de serviço {$order->protocol} registrada com sucesso!");
    }

    public function show(ServiceOrder $serviceOrder)
    {
        $this->authorize('view', $serviceOrder);

        $serviceOrder->load([
            'unit',
            'requester',
            'assignee',
            'charges',
            'items.charge',
            'messages' => fn ($q) => $q->where('is_internal', false)->with('author'),
        ]);

        return view('service-orders.show', [
            'order' => $serviceOrder,
        ]);
    }

    public function storeMessage(Request $request, ServiceOrder $serviceOrder)
    {
        $this->authorize('message', $serviceOrder);

        $validated = $request->validate([
            'message' => 'required|string|max:3000',
        ]);

        $this->serviceOrderService->addMessage(
            $serviceOrder,
            Auth::user(),
            $validated['message'],
        );

        return back()->with('success', 'Mensagem enviada.');
    }
}
