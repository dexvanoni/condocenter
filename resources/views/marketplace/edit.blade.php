@extends('layouts.app')

@section('title', 'Editar Anúncio')

@section('content')
@include('marketplace.partials.form-body', ['isEdit' => true])
@endsection

@push('styles')
@include('marketplace.partials.form-styles')
@endpush

@push('scripts')
@include('marketplace.partials.form-scripts', [
    'isEdit' => true,
    'imagesInputId' => 'marketplaceImagesInputEdit',
    'formId' => 'marketplaceEditForm',
    'submitBtnId' => 'marketplaceEditSubmitBtn',
    'feedbackId' => 'marketplaceEditFeedback',
    'submitUrl' => '/api/marketplace/' . $item->id,
    'submitMethod' => 'PUT',
    'redirectUrl' => route('marketplace.index'),
    'successFallback' => 'Anúncio atualizado com sucesso!',
    'loadingLabel' => 'Salvando...',
])
@endpush
