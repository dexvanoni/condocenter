@extends('layouts.app')

@section('title', 'Entrada de Encomendas')

@section('content')
<div class="container-fluid py-3" style="max-width: 640px;">
    <div id="package-intake-app"></div>
</div>
@endsection

@push('scripts')
    @vite(['resources/js/packages-intake.js'])
@endpush
