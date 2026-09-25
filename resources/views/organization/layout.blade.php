@extends('layouts.app')

@push('styles')
    @include('organization.partials.styles')
@endpush

@section('content')
<div class="container-fluid px-4 org-management">
    @include('organization.partials.alerts')
    @yield('org_content')
</div>
@endsection
