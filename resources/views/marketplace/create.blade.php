@extends('layouts.app')

@section('title', 'Novo Anúncio')

@section('content')
@include('marketplace.partials.form-body', ['isEdit' => false])
@endsection

@push('styles')
@include('marketplace.partials.form-styles')
@endpush

@push('scripts')
@include('marketplace.partials.form-scripts', [
    'isEdit' => false,
    'imagesInputId' => 'marketplaceImagesInput',
    'formId' => 'marketplaceCreateForm',
    'submitBtnId' => 'marketplaceSubmitBtn',
    'feedbackId' => 'marketplaceCreateFeedback',
    'submitUrl' => '/api/marketplace',
    'submitMethod' => 'POST',
    'redirectUrl' => route('marketplace.index'),
    'successFallback' => 'Anúncio publicado com sucesso!',
    'loadingLabel' => 'Publicando...',
])
@endpush
