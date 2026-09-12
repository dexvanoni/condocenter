<?php

namespace App\Http\Controllers\Finance;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Concerns\ResolvesActiveCondominium;
use App\Http\Requests\CancelEmployeeFinancialEntryRequest;
use App\Http\Requests\StoreEmployeeFinancialEntryRequest;
use App\Http\Requests\StoreEmployeeRequest;
use App\Http\Requests\UpdateEmployeeRequest;
use App\Models\Employee;
use App\Models\EmployeeFinancialEntry;
use App\Services\EmployeeService;
use App\Support\EmployeeEntryTypes;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;

class EmployeeController extends Controller
{
    use ResolvesActiveCondominium;

    public function __construct(
        private readonly EmployeeService $employeeService,
    ) {
    }

    public function index(Request $request)
    {
        $user = Auth::user();
        if (! $user->can('view_employees')) {
            abort(403);
        }

        $condominiumId = $this->activeCondominiumId($user);
        $canManage = $user->can('manage_employees');

        $query = Employee::byCondominium($condominiumId)
            ->withSum(['financialEntries as period_total' => function ($q) use ($request) {
                $start = $request->filled('start_date')
                    ? Carbon::parse($request->input('start_date'))->startOfDay()
                    : now()->startOfMonth();
                $end = $request->filled('end_date')
                    ? Carbon::parse($request->input('end_date'))->endOfDay()
                    : now()->endOfMonth();
                $q->active()->whereBetween('reference_date', [$start, $end]);
            }], 'amount')
            ->orderBy('name');

        if ($request->filled('status')) {
            $query->where('status', $request->input('status'));
        }

        if ($request->filled('search')) {
            $search = $request->input('search');
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('position', 'like', "%{$search}%")
                    ->orWhere('cpf', 'like', "%{$search}%");
            });
        }

        $employees = $query->get();
        $startDate = $request->filled('start_date')
            ? Carbon::parse($request->input('start_date'))->startOfDay()
            : now()->startOfMonth();
        $endDate = $request->filled('end_date')
            ? Carbon::parse($request->input('end_date'))->endOfDay()
            : now()->endOfMonth();

        $summary = $this->employeeService->accountabilitySummary($condominiumId, $startDate, $endDate);

        return view('finance.employees.index', [
            'employees' => $employees,
            'canManage' => $canManage,
            'startDate' => $startDate,
            'endDate' => $endDate,
            'summary' => $summary,
            'entryTypes' => EmployeeEntryTypes::labels(),
        ]);
    }

    public function create()
    {
        if (! Auth::user()->can('manage_employees')) {
            abort(403);
        }

        return view('finance.employees.create', [
            'statusLabels' => Employee::statusLabels(),
        ]);
    }

    public function store(StoreEmployeeRequest $request)
    {
        $user = Auth::user();
        $employee = $this->employeeService->createEmployee(
            $this->activeCondominiumId($user),
            $user,
            $request->validated()
        );

        return redirect()
            ->route('financial.employees.show', $employee)
            ->with('success', 'Funcionário cadastrado com sucesso.');
    }

    public function show(Employee $employee)
    {
        $user = Auth::user();
        if (! $user->can('view_employees')) {
            abort(403);
        }
        $this->ensureResourceBelongsToActiveCondominium($user, (int) $employee->condominium_id);

        $employee->load(['financialEntries' => fn ($q) => $q->with(['creator', 'cancelledBy'])->orderByDesc('reference_date')]);

        return view('finance.employees.show', [
            'employee' => $employee,
            'canManage' => $user->can('manage_employees'),
            'entryTypes' => EmployeeEntryTypes::labels(),
            'statusLabels' => Employee::statusLabels(),
        ]);
    }

    public function edit(Employee $employee)
    {
        $user = Auth::user();
        if (! $user->can('manage_employees')) {
            abort(403);
        }
        $this->ensureResourceBelongsToActiveCondominium($user, (int) $employee->condominium_id);

        return view('finance.employees.edit', [
            'employee' => $employee,
            'statusLabels' => Employee::statusLabels(),
        ]);
    }

    public function update(UpdateEmployeeRequest $request, Employee $employee)
    {
        $user = Auth::user();
        $this->employeeService->updateEmployee($employee, $user, $request->validated());

        return redirect()
            ->route('financial.employees.show', $employee)
            ->with('success', 'Dados do funcionário atualizados.');
    }

    public function storeEntry(StoreEmployeeFinancialEntryRequest $request, Employee $employee)
    {
        $user = Auth::user();
        $this->ensureResourceBelongsToActiveCondominium($user, (int) $employee->condominium_id);

        $this->employeeService->registerFinancialEntry($employee, $user, $request->validated());

        return redirect()
            ->route('financial.employees.show', $employee)
            ->with('success', 'Lançamento registrado e incluído na prestação de contas.');
    }

    public function cancelEntry(CancelEmployeeFinancialEntryRequest $request, EmployeeFinancialEntry $entry)
    {
        $user = Auth::user();
        $this->ensureResourceBelongsToActiveCondominium($user, (int) $entry->condominium_id);

        $this->employeeService->cancelFinancialEntry(
            $entry,
            $user,
            $request->validated('cancellation_reason')
        );

        return redirect()
            ->route('financial.employees.show', $entry->employee_id)
            ->with('success', 'Lançamento cancelado. Permanece visível na prestação de contas como não calculado.');
    }
}
