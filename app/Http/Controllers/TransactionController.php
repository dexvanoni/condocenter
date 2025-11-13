<?php

namespace App\Http\Controllers;

use App\Models\Transaction;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class TransactionController extends Controller
{
    public function index(Request $request)
    {
        $user = Auth::user();
        $condominiumId = $user?->condominium_id;

        $query = Transaction::with(['user', 'unit'])
            ->where('condominium_id', $condominiumId);

        if ($request->filled('type')) {
            $query->where('type', $request->input('type'));
        }

        if ($request->filled('status')) {
            $query->where('status', $request->input('status'));
        }

        if ($request->filled('category')) {
            $query->where('category', $request->input('category'));
        }

        if ($request->filled('start_date')) {
            $startDate = Carbon::parse($request->input('start_date'))->startOfDay();
            $query->whereDate('transaction_date', '>=', $startDate);
        }

        if ($request->filled('end_date')) {
            $endDate = Carbon::parse($request->input('end_date'))->endOfDay();
            $query->whereDate('transaction_date', '<=', $endDate);
        }

        $transactions = $query
            ->orderByDesc('transaction_date')
            ->orderByDesc('created_at')
            ->get();

        return view('transactions.index', compact('transactions'));
    }
}
