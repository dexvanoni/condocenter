<?php

namespace App\Http\Controllers\Finance;

use App\Http\Controllers\Controller;
use App\Models\Charge;
use App\Models\CondominiumAccount;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;

class CondominiumAccountController extends Controller
{
    public function index(Request $request)
    {
        $user = Auth::user();

        if (! $user->can('view_transactions') && ! $user->can('view_own_financial')) {
            abort(403);
        }

        $condominiumId = $user->condominium_id;
        $startDate = $request->filled('start_date')
            ? Carbon::parse($request->input('start_date'))->startOfDay()
            : now()->startOfMonth();
        $endDate = $request->filled('end_date')
            ? Carbon::parse($request->input('end_date'))->endOfDay()
            : now()->endOfMonth();

        $entriesQuery = CondominiumAccount::with('creator')
            ->byCondominium($condominiumId)
            ->whereBetween('transaction_date', [$startDate, $endDate])
            ->orderByDesc('transaction_date')
            ->orderByDesc('created_at');

        $incomeEntries = (clone $entriesQuery)->where('type', 'income')->get();
        $expenseEntries = (clone $entriesQuery)->where('type', 'expense')->get();
        $chargeIncomeEntries = $incomeEntries->filter(fn (CondominiumAccount $entry) => $entry->source_type === 'charge');
        $manualIncomeEntries = $incomeEntries->reject(fn (CondominiumAccount $entry) => $entry->source_type === 'charge');

        $chargeIds = $chargeIncomeEntries->pluck('source_id')->filter()->unique();
        $chargesById = Charge::with(['unit', 'fee'])
            ->whereIn('id', $chargeIds)
            ->get()
            ->keyBy('id');

        $summary = [
            'income_manual' => $manualIncomeEntries->sum('amount'),
            'income_charges' => $chargeIncomeEntries->sum('amount'),
            'expenses_manual' => $expenseEntries->sum('amount'),
        ];

        $summary['total_income'] = $summary['income_manual'] + $summary['income_charges'];
        $summary['balance'] = $summary['total_income'] - $summary['expenses_manual'];

        $openingBalance = $this->calculateBalanceUntil($condominiumId, $startDate->copy()->subDay());

        $taxIncomeTimeline = $chargeIncomeEntries->map(function (CondominiumAccount $entry) use ($chargesById) {
            $charge = $chargesById->get($entry->source_id);

            return [
                'id' => $entry->id,
                'charge_id' => $entry->source_id,
                'title' => $charge?->title ?? $entry->description,
                'amount' => $entry->amount,
                'transaction_date' => $entry->transaction_date,
                'source' => 'charge',
                'unit' => optional($charge?->unit)->full_identifier,
                'details' => $charge?->description,
                'payment_channel' => data_get($charge?->metadata, 'payment_channel', $entry->payment_method),
            ];
        });

        $timelineIncomes = $taxIncomeTimeline->concat(
            $manualIncomeEntries->map(function (CondominiumAccount $account) {
                return [
                    'id' => $account->id,
                    'title' => $account->description,
                    'amount' => $account->amount,
                    'transaction_date' => $account->transaction_date,
                    'source' => 'manual',
                    'unit' => null,
                    'details' => $account->notes,
                    'payment_channel' => $account->payment_method,
                    'document_path' => $account->document_path,
                ];
            })
        )->sortByDesc('transaction_date');

        $timelineExpenses = $expenseEntries->map(function (CondominiumAccount $account) {
            return [
                'id' => $account->id,
                'title' => $account->description,
                'amount' => $account->amount,
                'transaction_date' => $account->transaction_date,
                'source' => 'manual',
                'details' => $account->notes,
                'payment_method' => $account->payment_method,
                'document_path' => $account->document_path,
                'captured_image_path' => $account->captured_image_path,
                'installments_total' => $account->installments_total,
                'installment_number' => $account->installment_number,
                'created_by' => optional($account->creator)->name,
            ];
        });

        return view('finance.accounts.index', [
            'canManage' => $user->can('manage_transactions'),
            'timelineIncomes' => $timelineIncomes,
            'timelineExpenses' => $timelineExpenses,
            'summary' => $summary,
            'startDate' => $startDate,
            'endDate' => $endDate,
            'openingBalance' => $openingBalance,
            'closingBalance' => $openingBalance + $summary['balance'],
            'taxEntries' => $taxIncomeTimeline,
        ]);
    }

    public function storeExpense(Request $request)
    {
        $user = Auth::user();

        if (! $user->can('manage_transactions')) {
            abort(403);
        }

        $validated = $request->validate([
            'description' => ['required', 'string', 'max:255'],
            'amount' => ['required', 'numeric', 'min:0'],
            'transaction_date' => ['required', 'date'],
            'payment_method' => ['nullable', Rule::in(['cash', 'pix', 'bank_transfer', 'credit_card', 'debit_card', 'boleto', 'other'])],
            'installments_total' => ['nullable', 'integer', 'min:1', 'max:24'],
            'installment_number' => ['nullable', 'integer', 'min:1', 'max:24'],
            'notes' => ['nullable', 'string'],
            'document' => ['nullable', 'file', 'mimes:jpg,jpeg,png,pdf', 'max:8192'],
            'captured_image' => ['nullable', 'file', 'image', 'max:8192'],
        ]);

        $documentPath = $this->storeFile($request->file('document'));
        $capturedImagePath = $this->storeFile($request->file('captured_image'));

        CondominiumAccount::create([
            'condominium_id' => $user->condominium_id,
            'type' => 'expense',
            'description' => $validated['description'],
            'amount' => $validated['amount'],
            'transaction_date' => $validated['transaction_date'],
            'payment_method' => $validated['payment_method'] ?? null,
            'installments_total' => $validated['installments_total'] ?? null,
            'installment_number' => $validated['installment_number'] ?? null,
            'notes' => $validated['notes'] ?? null,
            'document_path' => $documentPath,
            'captured_image_path' => $capturedImagePath,
            'created_by' => $user->id,
        ]);

        return redirect()
            ->back()
            ->with('success', 'Pagamento registrado com sucesso!');
    }

    public function storeIncome(Request $request)
    {
        $user = Auth::user();

        if (! $user->can('manage_transactions')) {
            abort(403);
        }

        $validated = $request->validate([
            'description' => ['required', 'string', 'max:255'],
            'amount' => ['required', 'numeric', 'min:0'],
            'transaction_date' => ['required', 'date'],
            'payment_method' => ['nullable', Rule::in(['cash', 'pix', 'bank_transfer', 'credit_card', 'debit_card', 'boleto', 'other'])],
            'notes' => ['nullable', 'string'],
            'document' => ['nullable', 'file', 'mimes:jpg,jpeg,png,pdf', 'max:8192'],
        ]);

        $documentPath = $this->storeFile($request->file('document'));

        CondominiumAccount::create([
            'condominium_id' => $user->condominium_id,
            'type' => 'income',
            'source_type' => 'manual_income',
            'description' => $validated['description'],
            'amount' => $validated['amount'],
            'transaction_date' => $validated['transaction_date'],
            'payment_method' => $validated['payment_method'] ?? null,
            'notes' => $validated['notes'] ?? null,
            'document_path' => $documentPath,
            'created_by' => $user->id,
        ]);

        return redirect()
            ->back()
            ->with('success', 'Recebimento registrado com sucesso!');
    }

    protected function calculateBalanceUntil(int $condominiumId, Carbon $date): float
    {
        if ($date->isPast() === false) {
            return 0.0;
        }

        $income = CondominiumAccount::byCondominium($condominiumId)
            ->where('type', 'income')
            ->where('transaction_date', '<=', $date)
            ->sum('amount');

        $expenses = CondominiumAccount::byCondominium($condominiumId)
            ->where('type', 'expense')
            ->where('transaction_date', '<=', $date)
            ->sum('amount');

        return $income - $expenses;
    }

    protected function storeFile($file): ?string
    {
        if (! $file) {
            return null;
        }

        return $file->store('condominium/accounts', 'public');
    }
}

