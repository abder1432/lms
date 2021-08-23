@extends('backend.layouts.app')
@section('title', __('labels.backend.services.title').' | '.app_name())

@section('content')

    {!! Form::open(['method' => 'POST', 'route' => ['admin.services.store'], 'files' => true]) !!}

    <div class="card">
        <div class="card-header">
            <h3 class="page-title float-left">@lang('labels.backend.services.create')</h3>
        </div>

        <div class="card-body">
            <div class="row">
                <div class="col-12 form-group">
                    {!! Form::label('teachers',trans('labels.backend.services.fields.experts'), ['class' => 'control-label']) !!}
                    {!! Form::select('teachers[]', $teachers, old('teachers'), ['class' => 'form-control select2 js-example-placeholder-multiple', 'multiple' => true, 'required' => true]) !!}
                </div>
            </div>

            <div class="row">
                <div class="col-12 form-group">
                    {!! Form::label('service_category_id',trans('labels.backend.services.fields.category'), ['class' => 'control-label']) !!}
                    {!! Form::select('service_category_id', $serviceCategories, old('service_category_id'), ['class' => 'form-control select2 js-example-placeholder-single', 'multiple' => false, 'required' => true]) !!}
                </div>
            </div>

            <div class="row">
                <div class="col-12 col-lg-6 form-group">
                    {!! Form::label('title', trans('labels.backend.services.fields.title').' *', ['class' => 'control-label']) !!}
                    {!! Form::text('title', old('title'), ['class' => 'form-control', 'placeholder' => trans('labels.backend.services.fields.title'), 'required' => false]) !!}
                </div>
                <div class="col-12 col-lg-3 form-group">
                    {!! Form::label('min_capacity',  trans('labels.backend.services.fields.min_capacity'), ['class' => 'control-label']) !!}
                    {!! Form::number('min_capacity', old('min_capacity'), ['class' => 'form-control', 'placeholder' =>  trans('labels.backend.services.fields.min_capacity')]) !!}
                </div>
                <div class="col-12 col-lg-3 form-group">
                    {!! Form::label('max_capacity',  trans('labels.backend.services.fields.max_capacity'), ['class' => 'control-label']) !!}
                    {!! Form::number('max_capacity', old('max_capacity'), ['class' => 'form-control', 'placeholder' =>  trans('labels.backend.services.fields.max_capacity')]) !!}
                </div>
            </div>
            <div class="row">

                <div class="col-12 form-group">
                    {!! Form::label('description',  trans('labels.backend.services.fields.description'), ['class' => 'control-label']) !!}
                    {!! Form::textarea('description', old('description'), ['class' => 'form-control editor', 'placeholder' => trans('labels.backend.services.fields.description')]) !!}

                </div>
            </div>
            <div class="row">
                <div class="col-12 col-lg-4 form-group">
                    {!! Form::label('price',  trans('labels.backend.services.fields.price').' (in '.$appCurrency["symbol"].')', ['class' => 'control-label']) !!}
                    {!! Form::number('price', old('price'), ['class' => 'form-control', 'placeholder' => trans('labels.backend.services.fields.price'),'step' => 'any', 'pattern' => "[0-9]"]) !!}
                </div>
                <div class="col-12 col-lg-4 form-group">
                    {!! Form::label('duration',  trans('labels.backend.services.fields.duration').' (in minutes)', ['class' => 'control-label']) !!}
                    {!! Form::number('duration', old('duration'), ['class' => 'form-control', 'placeholder' => trans('labels.backend.services.fields.duration'),'step' => 'any', 'pattern' => "[0-9]"]) !!}
                </div>
            </div>

            <div class="border rounded bg-light p-4">
                <h3>@lang('labels.backend.appointments.fields.location')</h3>
                <div class="row">
                    <div class="col-12 col-lg-4 form-group">
                        {!! Form::label('is_online',  trans('labels.backend.services.fields.is_online'), ['class' => 'control-label']) !!}
                        {!! Form::checkbox('is_online', true, old('is_online'), []) !!}
                    </div>
                    <div class="col-12 col-lg-4 form-group">
                        {!! Form::label('location_address',  trans('labels.backend.services.fields.location_address'), ['class' => 'control-label']) !!}
                        {!! Form::text('location_address', old('location_address'), ['class' => 'form-control', 'placeholder' => trans('labels.backend.services.fields.location_address')]) !!}
                    </div>
                    <div class="col-12 col-lg-4 form-group">
                        {!! Form::label('location_phone_number',  trans('labels.backend.services.fields.location_phone_number'), ['class' => 'control-label']) !!}
                        {!! Form::text('location_phone_number', old('location_phone_number'), ['class' => 'form-control', 'placeholder' => trans('labels.backend.services.fields.location_phone_number')]) !!}
                    </div>
                    <div class="col-12 col-lg-4 form-group">
                        {!! Form::label('location_description',  trans('labels.backend.services.fields.location_description'), ['class' => 'control-label']) !!}
                        {!! Form::text('location_description', old('location_description'), ['class' => 'form-control', 'placeholder' => trans('labels.backend.services.fields.location_description')]) !!}
                    </div>
                    <div class="col-12 col-lg-4 form-group">
                        {!! Form::label('location_latitude',  trans('labels.backend.services.fields.location_latitude'), ['class' => 'control-label']) !!}
                        {!! Form::text('location_latitude', old('location_latitude'), ['class' => 'form-control', 'placeholder' => trans('labels.backend.services.fields.location_latitude')]) !!}
                    </div>
                    <div class="col-12 col-lg-4 form-group">
                        {!! Form::label('location_longitude',  trans('labels.backend.services.fields.location_longitude'), ['class' => 'control-label']) !!}
                        {!! Form::text('location_longitude', old('location_longitude'), ['class' => 'form-control', 'placeholder' => trans('labels.backend.services.fields.location_longitude')]) !!}
                    </div>
                </div>
            </div>
            <br>
            <div class="row">
                <div class="col-12  text-center form-group">

                    {!! Form::submit(trans('strings.backend.general.app_save'), ['class' => 'btn btn-lg btn-danger']) !!}
                </div>
            </div>
        </div>
    </div>
    {!! Form::close() !!}


@stop

@push('after-scripts')
    <script type="text/javascript" src="{{asset('/vendor/unisharp/laravel-ckeditor/ckeditor.js')}}"></script>
    <script type="text/javascript" src="{{asset('/vendor/unisharp/laravel-ckeditor/adapters/jquery.js')}}"></script>
    <script src="{{asset('/vendor/laravel-filemanager/js/lfm.js')}}"></script>
    <script>
        $('.editor').each(function () {

            CKEDITOR.replace($(this).attr('id'), {
                filebrowserImageBrowseUrl: '/laravel-filemanager?type=Images',
                filebrowserImageUploadUrl: '/laravel-filemanager/upload?type=Images&_token={{csrf_token()}}',
                filebrowserBrowseUrl: '/laravel-filemanager?type=Files',
                filebrowserUploadUrl: '/laravel-filemanager/upload?type=Files&_token={{csrf_token()}}',
                extraPlugins: 'smiley,lineutils,widget,codesnippet,prism,flash,colorbutton,colordialog',
            });

        });

        $(document).ready(function () {
            $('#start_date').datepicker({
                autoclose: true,
                dateFormat: "{{ config('app.date_format_js') }}"
            });

            var dateToday = new Date();
            $('#expire_at').datepicker({
                autoclose: true,
                minDate: dateToday,
                dateFormat: "{{ config('app.date_format_js') }}"
            });

            $(".js-example-placeholder-single").select2({
                placeholder: "{{trans('labels.backend.services.select_category')}}",
            });

            $(".js-example-placeholder-multiple").select2({
                placeholder: "{{trans('labels.backend.services.select_experts')}}",
            });
        });


    </script>

@endpush
