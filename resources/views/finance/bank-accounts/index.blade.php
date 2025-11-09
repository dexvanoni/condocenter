@extends('layouts.app')

@section('title', 'Contas bancárias')

@section('content')
<div class="row mb-4">
    <div class="col-12">
        <div class="d-flex justify-content-between align-items-center flex-wrap gap-2">
            <div>
                <h2 class="mb-0">Contas bancárias</h2>
                <p class="text-muted mb-0">Gerencie as contas utilizadas para recebimentos e pagamentos do condomínio.</p>
            </div>
            @can('manage_bank_statements')
                <a href="{{ route('financial.bank-accounts.create') }}" class="btn btn-primary">
                    <i class="bi bi-plus-circle"></i> Nova conta
                </a>
            @endcan
        </div>
    </div>
</div>

@if(session('success'))
    <div class="alert alert-success">{{ session('success') }}</div>
@endif

<div class="card shadow-sm">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover mb-0 align-middle">
                <thead class="table-light">
                    <tr>
                        <th>Nome</th>
                        <th>Banco</th>
                        <th>Titular</th>
                        <th>CPF/CNPJ</th>
                        <th class="text-end">Saldo atual</th>
                        <th>Atualizado em</th>
                        <th>Status</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($accounts as $account)
                        <tr>
                            <td>
                                <div class="fw-semibold">{{ $account->name }}</div>
                                <small class="text-muted">{{ $account->institution ?? 'Instituição não informada' }}</small>
                            </td>
                            <td>{{ $account->bank_name ?? '—' }}</td>
                            <td>{{ $account->holder_name ?? '—' }}</td>
                            <td>{{ $account->document_number ?? '—' }}</td>
                            <td class="text-end">
                                <span class="fw-semibold {{ $account->current_balance >= 0 ? 'text-success' : 'text-danger' }}">
                                    R$ {{ number_format($account->current_balance, 2, ',', '.') }}
                                </span>
                            </td>
                            <td>{{ optional($account->balance_updated_at)->format('d/m/Y H:i') ?? '—' }}</td>
                            <td>
                                <span class="badge {{ $account->active ? 'bg-success' : 'bg-secondary' }}">
                                    {{ $account->active ? 'Ativa' : 'Inativa' }}
                                </span>
                            </td>
                            <td class="text-end">
                                @can('manage_bank_statements')
                                    <div class="btn-group">
                                        <a href="{{ route('financial.bank-accounts.edit', $account) }}" class="btn btn-sm btn-outline-primary">
                                            <i class="bi bi-pencil"></i>
                                        </a>
                                        <form action="{{ route('financial.bank-accounts.destroy', $account) }}" method="POST"
                                              onsubmit="return confirm('Deseja remover esta conta?');">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-sm btn-outline-danger">
                                                <i class="bi bi-trash"></i>
                                            </button>
                                        </form>
                                    </div>
                                @endcan
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="text-center text-muted py-4">Nenhuma conta cadastrada.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
    @if($accounts->hasPages())
        <div class="card-footer">
            {{ $accounts->links('pagination::bootstrap-5') }}
        </div>
    @endif
</div>
@endsection

