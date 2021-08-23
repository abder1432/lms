@extends('backend.layouts.app')
@section('title', __('labels.backend.teachers.title').' | '.app_name())

@section('content')
    {{ html()->modelForm($teacher, 'POST', route('admin.teachers.availability', $teacher->id))->class('form-horizontal')->acceptsFiles()->open() }}

    <div class="card">
        <div class="card-header">
            <h3 class="page-title d-inline">@lang('labels.backend.teachers.edit')</h3>
            <div class="float-right">
                <a href="{{ route('admin.teachers.index') }}"
                   class="btn btn-success">@lang('labels.backend.teachers.view')</a>
            </div>
        </div>
        <div class="card-body">
            <div class="row">
                <div class="col-12 p-3">
                    <livewire:backend.teachers.availability :teacher="$teacher"/>
                </div>
            </div>
        </div>

    </div>
    {{ html()->closeModelForm() }}
@endsection

@push('after-scripts')
@endpush