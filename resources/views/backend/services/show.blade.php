@extends('backend.layouts.app')
@section('title', __('labels.backend.services.title').' | '.app_name())

@push('after-styles')
@endpush

@section('content')

    <div class="card">

        <div class="card-header">
            <h3 class="page-title mb-0">@lang('labels.backend.services.title')</h3>
        </div>
        <div class="card-body">
            <div class="row">
                <div class="col-12">
                    <table class="table table-bordered table-striped">
                        <tr>
                            <th>@lang('labels.backend.services.fields.experts')</th>
                            <td>
                                @foreach ($service->teachers as $singleTeachers)
                                    <a href="{{ route('admin.auth.user.show', $singleTeachers->user_id) }}">{{ $singleTeachers->user->full_name }}</a>
                                    <br>
                                @endforeach
                            </td>
                        </tr>
                        <tr>
                            <th>@lang('labels.backend.services.fields.title')</th>
                            <td>
                                {{ $service->title }}
                            </td>
                        </tr>
                        <tr>
                            <th>@lang('labels.backend.services.fields.category')</th>
                            <td>{{ $service->serviceCategory->name }}</td>
                            <td></td>
                        </tr>
                        <tr>
                            <th>@lang('labels.backend.services.fields.description')</th>
                            <td>{!! $service->description !!}</td>
                        </tr>
                        <tr>
                            <th>@lang('labels.backend.services.fields.price')</th>
                            <td>{{ $service->price.' '.$appCurrency['symbol'] }}</td>
                        </tr>
                        {{--<tr>
                            <th>@lang('labels.backend.services.fields.service_image')</th>
                            <td>@if($service->service_image)<a
                                        href="{{ asset('storage/uploads/' . $service->service_image) }}"
                                        target="_blank"><img
                                            src="{{ asset('storage/uploads/' . $service->service_image) }}"
                                            height="50px"/></a>@endif</td>
                        </tr>--}}
                        <tr>
                            <th>@lang('labels.backend.services.fields.min_capacity')</th>
                            <td>{{ $service->min_capacity }}</td>
                        </tr>
                        <tr>
                            <th>@lang('labels.backend.services.fields.max_capacity')</th>
                            <td>{{ $service->max_capacity }}</td>
                        </tr>
                        <tr>
                            <th>@lang('labels.backend.services.fields.duration')</th>
                            <td>{{ $service->duration }} Hrs</td>
                        </tr>
                    </table>
                </div>
            </div><!-- Nav tabs -->

        </div>
    </div>
@stop

@push('after-scripts')
@endpush
