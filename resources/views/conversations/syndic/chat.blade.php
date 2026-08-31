@extends('layouts.app')

@section('title', 'Conversa Sigilosa com o Síndico')

@section('content')
@include('conversations.partials.syndic-channel-styles')

<div class="container-fluid py-4">
	<div class="row mb-4">
		<div class="col-12">
			<h2 class="mb-1 fw-bold"><i class="bi bi-shield-lock me-2"></i>Conversa Sigilosa com o Síndico</h2>
			<p class="text-muted mb-0">Canal exclusivo e confidencial. Somente você e o perfil Síndico têm acesso.</p>
		</div>
	</div>

	<div class="alert alert-warning border-0 shadow-sm">
		<i class="bi bi-exclamation-triangle me-2"></i>
		<strong>Sigilo garantido:</strong> estas mensagens não aparecem na Central de Mensagens e não são acessíveis ao Administrador.
	</div>

	@include('conversations.partials.syndic-channel-chat', [
		'rootId' => 'syndicChatRoot',
		'channel' => 'syndic',
		'showAddParticipant' => false,
		'showStats' => false,
	])
</div>
@endsection
