@extends('layouts.app')

@push('styles')
    @include('organization.partials.styles')
@endpush

@section('content')
<div class="container-fluid px-4 org-management">
    @yield('org_content')
</div>
@endsection
