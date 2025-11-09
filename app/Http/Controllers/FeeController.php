<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreFeeRequest;
use App\Http\Requests\UpdateFeeRequest;
use App\Models\BankAccount;
use App\Models\Charge;
use App\Models\Fee;
use App\Models\Unit;
use App\Services\FeeService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;

class FeeController extends Controller
{
    public function __construct(
        private readonly FeeService $feeService
    ) {
        $this->middleware(['can:view_charges'])->only(['index', 'show']);
        $this->middleware(['can:manage_charges'])->except(['index', 'show']);
    }

    public function index()
    {
        $condominiumId = Auth::user()->condominium_id;

        $fees = Fee::withCount([
                'configurations',
                'charges as pending_charges_count' => fn ($query) => $query->where('status', 'pending'),
                'charges as overdue_charges_count' => fn ($query) => $query->where('status', 'overdue'),
            ])
            ->where('condominium_id', $condominiumId)
            ->orderBy('name')
            ->get();

        $summary = [
            'active' => $fees->where('active', true)->count(),
            'inactive' => $fees->where('active', false)->count(),
            'total_configurations' => $fees->sum('configurations_count'),
            'pending_charges' => $fees->sum('pending_charges_count'),
            'overdue_charges' => $fees->sum('overdue_charges_count'),
        ];

        return view('fees.index', compact('fees', 'summary'));
    }

    public function create()
    {
        $condominiumId = Auth::user()->condominium_id;

        $units = Unit::with('morador')
            ->where('condominium_id', $condominiumId)
            ->orderBy('block')
            ->orderBy('number')
            ->get();

        $bankAccounts = BankAccount::where('condominium_id', $condominiumId)
            ->orderBy('name')
            ->get();

        $fee = new Fee([
            'condominium_id' => $condominiumId,
            'recurrence' => 'monthly',
            'due_day' => 10,
            'due_offset_days' => 0,
            'billing_type' => 'condominium_fee',
            'auto_generate_charges' => true,
            'active' => true,
            'amount' => 0,
        ]);

        return view('fees.create', compact('fee', 'units', 'bankAccounts'));
    }

    public function store(StoreFeeRequest $request)
    {
        $fee = $this->feeService->createFee($request->user(), $request->validated());

        return redirect()
            ->route('fees.show', $fee)
            ->with('success', 'Taxa criada com sucesso!');
    }

    public function show(Fee $fee)
    {
        $this->authorizeFee($fee);

        $fee->load([
            'bankAccount',
            'configurations.unit.morador',
        ]);

        $orderedConfigurations = $fee->configurations->sortBy(function ($configuration) {
            $block = $configuration->unit->block ?? '';
            $number = $configuration->unit->number ?? '';

            return sprintf('%s-%s', $block, str_pad($number, 4, '0', STR_PAD_LEFT));
        })->values();

        $fee->setRelation('configurations', $orderedConfigurations);

        $charges = Charge::with('unit')
            ->where('fee_id', $fee->id)
            ->orderByDesc('due_date')
            ->get();

        return view('fees.show', compact('fee', 'charges'));
    }

    public function edit(Fee $fee)
    {
        $this->authorizeFee($fee);

        $condominiumId = Auth::user()->condominium_id;

        $units = Unit::with('morador')
            ->where('condominium_id', $condominiumId)
            ->orderBy('block')
            ->orderBy('number')
            ->get();

        $bankAccounts = BankAccount::where('condominium_id', $condominiumId)
            ->orderBy('name')
            ->get();

        $fee->load('configurations.unit');

        return view('fees.edit', compact('fee', 'units', 'bankAccounts'));
    }

    public function update(UpdateFeeRequest $request, Fee $fee)
    {
        $this->authorizeFee($fee);

        $updatedFee = $this->feeService->updateFee($fee, $request->user(), $request->validated());

        return redirect()
            ->route('fees.show', $updatedFee)
            ->with('success', 'Taxa atualizada com sucesso!');
    }

    public function destroy(Request $request, Fee $fee)
    {
        $this->authorizeFee($fee);

        $this->feeService->deleteFee($fee, $request->user());

        return redirect()
            ->route('fees.index')
            ->with('success', 'Taxa removida com sucesso!');
    }

    public function generateCharges(Fee $fee)
    {
        $this->authorizeFee($fee);

        $count = $this->feeService->generateUpcomingCharges($fee);

        return redirect()
            ->route('fees.show', $fee)
            ->with('success', $count > 0
                ? "{$count} cobrança(s) gerada(s) para o próximo período."
                : 'Nenhuma nova cobrança necessária para o próximo período.');
    }

    private function authorizeFee(Fee $fee): void
    {
        if ($fee->condominium_id !== Auth::user()->condominium_id) {
            throw ValidationException::withMessages([
                'fee' => 'Taxa não pertence ao seu condomínio.',
            ]);
        }
    }
}

