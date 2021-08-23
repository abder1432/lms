@extends('backend.layouts.app')
@section('title', __('labels.backend.service_categories.title').' | '.app_name())

@push('after-styles')

@endpush
@section('content')
    {!! Form::model($serviceCategory, ['method' => 'PUT', 'route' => ['admin.service_categories.update', $serviceCategory->id]]) !!}

    <div class="alert alert-danger d-none" role="alert">
        <button type="button" class="close" data-dismiss="alert" aria-label="Close">
            <span aria-hidden="true">×</span>
        </button>
        <div class="error-list">
        </div>
    </div>
    <div class="card">
        <div class="card-header">
            <h3 class="page-title d-inline">@lang('labels.backend.categories.edit')</h3>
            <div class="float-right">
                <a href="{{ route('admin.service_categories.index') }}"
                   class="btn btn-success">@lang('labels.backend.categories.view')</a>
            </div>
        </div>
        <div class="card-body">
            <div class="row justify-content-center">
            <div class="col-12 col-lg-4 form-group">
                {!! Form::label('title', trans('labels.backend.categories.fields.name').' *', ['class' => 'control-label']) !!}
                {!! Form::text('name', old('name'), ['class' => 'form-control', 'placeholder' => 'Enter Category Name', 'required' => false]) !!}

            </div>

            <div class="col-12 form-group text-center">

                {!! Form::submit(trans('strings.backend.general.app_save'), ['class' => 'btn mt-auto  btn-danger']) !!}
            </div>
        </div>
        </div>
    </div>
    {{ html()->form()->close() }}
@endsection

@push('after-scripts')

@endpush


