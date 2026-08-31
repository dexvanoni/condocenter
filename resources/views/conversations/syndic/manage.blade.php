@extends('layouts.app')

@section('title', 'Atendimento Sigiloso')

@section('content')
@include('conversations.partials.syndic-channel-styles')

<div class="container-fluid py-4">
	<div class="row mb-4">
		<div class="col-12 d-flex flex-wrap justify-content-between align-items-start gap-3">
			<div>
				<h2 class="mb-1 fw-bold"><i class="bi bi-clipboard-data me-2"></i>Atendimento Sigiloso</h2>
				<p class="text-muted mb-0">Gerencie conversas confidenciais recebidas dos moradores.</p>
			</div>
			<div class="badge bg-dark fs-6 px-3 py-2">
				<i class="bi bi-shield-lock me-1"></i> Acesso restrito ao perfil Síndico
			</div>
		</div>
	</div>

	<div class="row g-3 mb-4" id="syndicStatsCards">
		<div class="col-6 col-lg-3">
			<div class="card border-0 shadow-sm h-100">
				<div class="card-body">
					<div class="text-muted small">Total de conversas</div>
					<div class="fs-3 fw-bold" id="statTotal">-</div>
				</div>
			</div>
		</div>
		<div class="col-6 col-lg-3">
			<div class="card border-0 shadow-sm h-100">
				<div class="card-body">
					<div class="text-muted small">Aguardando resposta</div>
					<div class="fs-3 fw-bold text-danger" id="statPending">-</div>
				</div>
			</div>
		</div>
		<div class="col-6 col-lg-3">
			<div class="card border-0 shadow-sm h-100">
				<div class="card-body">
					<div class="text-muted small">Tempo médio de resposta</div>
					<div class="fs-5 fw-bold" id="statAvgResponse">-</div>
				</div>
			</div>
		</div>
		<div class="col-6 col-lg-3">
			<div class="card border-0 shadow-sm h-100">
				<div class="card-body">
					<div class="text-muted small">Respondidas em até 24h</div>
					<div class="fs-3 fw-bold text-success" id="statUnder24h">-</div>
				</div>
			</div>
		</div>
	</div>

	@include('conversations.partials.syndic-channel-chat', [
		'rootId' => 'syndicManageRoot',
		'channel' => 'syndic',
		'showAddParticipant' => true,
		'showStats' => true,
	])
</div>
@endsection
