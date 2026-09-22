@extends('layouts.app')

@section('title', 'Usuários')

@section('content')
@include('users.partials.index-styles')

<div class="users-page">
	<div class="d-flex flex-wrap justify-content-between align-items-start gap-3 mb-3">
		<div>
			<h1 class="mb-1"><i class="bi bi-people text-primary me-1"></i> Usuários</h1>
			@if(!empty($activeCondominium))
				<p class="text-muted mb-0 small">{{ $activeCondominium->name }}</p>
			@endif
		</div>
		@can('manage_users')
			<a href="{{ route('users.create') }}" class="btn btn-primary btn-sm">
				<i class="bi bi-plus-lg"></i> Novo usuário
			</a>
		@endcan
	</div>

	<form method="GET" action="{{ route('users.index') }}" class="users-toolbar mb-3">
		<div class="row g-2 align-items-end">
			<div class="col-lg-4 col-md-5">
				<label class="form-label small text-muted mb-1">Buscar</label>
				<input type="text" name="search" class="form-control" placeholder="Nome, e-mail ou CPF…" value="{{ request('search') }}">
			</div>
			<div class="col-6 col-md-3 col-lg-2">
				<label class="form-label small text-muted mb-1">Perfil</label>
				<select name="role" class="form-select">
					<option value="">Todos</option>
					@foreach($roles as $role)
						<option value="{{ $role->name }}" @selected(request('role') === $role->name)>{{ $role->name }}</option>
					@endforeach
				</select>
			</div>
			<div class="col-6 col-md-4 col-lg-2">
				<label class="form-label small text-muted mb-1">Unidade</label>
				<select name="unit_id" class="form-select">
					<option value="">Todas</option>
					@foreach($units as $unit)
						<option value="{{ $unit->id }}" @selected(request('unit_id') == $unit->id)>{{ $unit->full_identifier }}</option>
					@endforeach
				</select>
			</div>
			<div class="col-6 col-md-3 col-lg-1">
				<label class="form-label small text-muted mb-1">Cadastro</label>
				<select name="status" class="form-select">
					<option value="">Todos</option>
					<option value="pending" @selected(request('status') === 'pending')>Pendentes</option>
				</select>
			</div>
			<div class="col-6 col-md-3 col-lg-1">
				<label class="form-label small text-muted mb-1">Status</label>
				<select name="is_active" class="form-select">
					<option value="">Todos</option>
					<option value="1" @selected(request('is_active') === '1')>Ativos</option>
					<option value="0" @selected(request('is_active') === '0')>Inativos</option>
				</select>
			</div>
			<div class="col-6 col-md-3 col-lg-1">
				<label class="form-label small text-muted mb-1">Dívidas</label>
				<select name="possui_dividas" class="form-select">
					<option value="">Todas</option>
					<option value="1" @selected(request('possui_dividas') === '1')>Com</option>
					<option value="0" @selected(request('possui_dividas') === '0')>Sem</option>
				</select>
			</div>
			<div class="col-12 col-lg-auto d-flex flex-wrap gap-2 pt-lg-0 pt-1">
				<button type="submit" class="btn btn-primary btn-sm">
					<i class="bi bi-search"></i> Filtrar
				</button>
				<a href="{{ route('users.index') }}" class="btn btn-outline-secondary btn-sm">
					Limpar
				</a>
			</div>
		</div>
		<div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mt-2 pt-2 border-top border-light">
			<span class="users-toolbar__meta">{{ $users->total() }} usuário(s)</span>
			<span class="users-legend">
				<span class="users-inquilino-badge" title="Morador inquilino"><i class="bi bi-key-fill"></i></span>
				Inquilino (unidade em aluguel)
			</span>
		</div>
	</form>

	<div class="users-table-wrap">
		<div class="table-responsive">
			<table class="table users-table align-middle">
				<thead>
					<tr>
						<th>Usuário</th>
						<th class="col-email">E-mail</th>
						<th class="col-cpf">CPF</th>
						<th>Unidade</th>
						<th>Perfis</th>
						<th>Status</th>
						<th class="text-end" width="120">Ações</th>
					</tr>
				</thead>
				<tbody>
					@forelse($users as $user)
						<tr>
							<td>
								<div class="users-identity">
									@if($user->photo)
										<img src="{{ Storage::url($user->photo) }}" alt="" class="users-avatar">
									@else
										<span class="users-avatar users-avatar--placeholder"><i class="bi bi-person"></i></span>
									@endif
									<div class="min-w-0">
										<div class="users-identity__name">
											<span class="text-truncate">{{ $user->name }}</span>
											@if($user->isMoradorInquilino())
												<span class="users-inquilino-badge" title="Morador inquilino — unidade em regime de aluguel">
													<i class="bi bi-key-fill"></i>
												</span>
											@endif
										</div>
										<div class="users-identity__sub d-lg-none">
											{{ $user->email }}
										</div>
									</div>
								</div>
							</td>
							<td class="col-email text-muted small">{{ $user->email }}</td>
							<td class="col-cpf text-muted small">{{ $user->cpf ?: '—' }}</td>
							<td class="small">{{ $user->unit?->full_identifier ?? '—' }}</td>
							<td>
								<div class="users-role-chips">
									@foreach($user->roles as $role)
										<span class="badge">{{ $role->name }}</span>
									@endforeach
								</div>
							</td>
							<td>
								<div class="users-status-stack">
									@if($user->isPendingApproval())
										<span class="badge bg-warning text-dark">Pendente</span>
									@elseif($user->isRegistrationRejected())
										<span class="badge bg-danger">Rejeitado</span>
									@elseif($user->is_active)
										<span class="badge bg-success">Ativo</span>
									@else
										<span class="badge bg-secondary">Inativo</span>
									@endif
									@if($user->possui_dividas)
										<span class="badge bg-danger">Dívidas</span>
									@endif
									@if($user->senha_temporaria)
										<span class="badge bg-warning text-dark">Senha temp.</span>
									@endif
								</div>
							</td>
							<td class="text-end">
								<div class="users-actions d-inline-flex flex-wrap justify-content-end gap-1">
									<a href="{{ route('users.show', $user) }}" class="btn btn-sm btn-outline-secondary" title="Ver">
										<i class="bi bi-eye"></i>
									</a>
									@can('update', $user)
										@if($user->isPendingApproval())
											<form action="{{ route('users.approve', $user) }}" method="POST" class="d-inline">
												@csrf
												<button type="submit" class="btn btn-sm btn-outline-success" title="Aprovar"
													onclick="return confirm('Aprovar o cadastro de {{ $user->name }}?')">
													<i class="bi bi-check2"></i>
												</button>
											</form>
											<form action="{{ route('users.reject', $user) }}" method="POST" class="d-inline">
												@csrf
												<button type="submit" class="btn btn-sm btn-outline-danger" title="Rejeitar"
													onclick="return confirm('Rejeitar o cadastro de {{ $user->name }}?')">
													<i class="bi bi-x"></i>
												</button>
											</form>
										@elseif($user->isRegistrationApproved())
											@if($user->is_active)
												<form action="{{ route('users.deactivate', $user) }}" method="POST" class="d-inline">
													@csrf
													<button type="submit" class="btn btn-sm btn-outline-secondary" title="Desativar"
														onclick="return confirm('Desativar {{ $user->name }}?')">
														<i class="bi bi-person-dash"></i>
													</button>
												</form>
											@else
												<form action="{{ route('users.activate', $user) }}" method="POST" class="d-inline">
													@csrf
													<button type="submit" class="btn btn-sm btn-outline-success" title="Ativar"
														onclick="return confirm('Ativar {{ $user->name }}?')">
														<i class="bi bi-person-check"></i>
													</button>
												</form>
											@endif
										@endif
										<a href="{{ route('users.edit', $user) }}" class="btn btn-sm btn-outline-secondary" title="Editar">
											<i class="bi bi-pencil"></i>
										</a>
									@endcan
									@can('viewHistory', $user)
										<a href="{{ route('users.history', $user) }}" class="btn btn-sm btn-outline-secondary" title="Histórico">
											<i class="bi bi-clock-history"></i>
										</a>
									@endcan
									@can('delete', $user)
										<form action="{{ route('users.destroy', $user) }}" method="POST" class="d-inline"
											onsubmit="return confirm('Tem certeza que deseja excluir este usuário?')">
											@csrf
											@method('DELETE')
											<button type="submit" class="btn btn-sm btn-outline-secondary btn-danger-outline" title="Excluir">
												<i class="bi bi-trash"></i>
											</button>
										</form>
									@endcan
								</div>
							</td>
						</tr>
					@empty
						<tr>
							<td colspan="7" class="text-center py-5 text-muted">
								<i class="bi bi-inbox fs-2 d-block mb-2 opacity-50"></i>
								Nenhum usuário encontrado.
							</td>
						</tr>
					@endforelse
				</tbody>
			</table>
		</div>
		@if($users->hasPages())
			<div class="px-3 py-2 border-top bg-light">
				{{ $users->links() }}
			</div>
		@endif
	</div>
</div>
@endsection
