@extends('layouts.app')

@section('title', 'Retirada de Encomenda')

@section('content')
<div class="container-fluid py-3" style="max-width: 640px;">
    <div id="package-pickup-app"></div>
</div>
@endsection

@push('scripts')
    @vite(['resources/js/packages-pickup.js'])
@endpush
