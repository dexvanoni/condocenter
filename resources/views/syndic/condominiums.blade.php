@extends('layouts.app')

@section('title', 'Meus condomínios')

@section('content')
<div class="container-fluid px-4">
    <div class="mb-4">
        <h1 class="mb-1"><i class="bi bi-buildings"></i> Meus condomínios</h1>
        <p class="text-muted mb-0">Selecione o condomínio para operar com o acesso de síndico.</p>
    </div>

    <div class="card shadow-sm">
        <div class="table-responsive">
            <table class="table table-hover mb-0 align-middle">
                <thead class="table-light">
                    <tr>
                        <th>Condomínio</th>
                        <th>Cidade/UF</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($condominiums as $condo)
                    <tr>
                        <td class="fw-semibold">
                            {{ $condo->name }}
                            @if((int) $activeId === (int) $condo->id)
                                <span class="badge bg-success">Ativo</span>
                            @endif
                        </td>
                        <td>{{ $condo->city }}{{ $condo->city && $condo->state ? ' / ' : '' }}{{ $condo->state }}</td>
                        <td class="text-end">
                            <form method="POST" action="{{ route('syndic.condominiums.enter') }}">
                                @csrf
                                <input type="hidden" name="condominium_id" value="{{ $condo->id }}">
                                <button class="btn btn-sm btn-success">Entrar</button>
                            </form>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="3" class="text-center text-muted py-4">Nenhum condomínio vinculado.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
