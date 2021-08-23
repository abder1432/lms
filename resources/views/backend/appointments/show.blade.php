@extends('backend.layouts.app')
@section('title', __('labels.backend.appointments.title').' | '.app_name())

@section('content')

    <div class="card">

        <div class="card-header">
            <h3 class="page-title mb-0 float-left">@lang('labels.backend.appointments.title')</h3>
        </div>
        <div class="card-body">
            <div class="row">
                <div class="col-12">
                    <table class="table table-bordered table-striped">
                        <tr>
                            <th>@lang('labels.backend.appointments.fields.id')</th>
                            <td>
                                {{$appointment->id}}
                            </td>
                        </tr>
                        <tr>
                            <th>@lang('labels.backend.appointments.fields.student')</th>
                            <td>
                                Name : <b>{{$appointment->user->full_name}}</b><br>
                                Email : <b>{{$appointment->user->email}}</b>
                            </td>
                        </tr>
                        <tr>
                            <th>@lang('labels.backend.appointments.fields.date')</th>
                            <th>
                                {{$appointment->date->format('Y/m/d \a\t H:i')}}
                                , {{$appointment->date->diffForHumans()}}
                            </th>
                        </tr>
                        <tr>
                            <th>@lang('labels.backend.appointments.fields.location')</th>
                            <th>
                                @if ($appointment->service->is_online)
                                    <span class="badge badge-info">@lang('labels.backend.appointments.fields.online')</span>
                                    <br>
                                    @if ($appointment->date->timezone(config('zoom.timezone'))->lt(\Carbon\Carbon::now(new \DateTimeZone(config('zoom.timezone')))))
                                        <span class="badge badge-dark">@lang('labels.backend.live_lesson_slots.closed')</span>
                                    @else
                                        <a href="' . $appointment->meeting_start_url . '"
                                           class="btn btn-success btn-sm mb-1">@lang('labels.backend.live_lesson_slots.start_url')</a>
                                    @endif
                                @else
                                    <span class="badge badge-warning">@lang('labels.backend.appointments.fields.on_site')</span>
                                    <p class="bg-light border rounded p-3"> {{ $appointment->service->location_address }} <br>
                                        {{ $appointment->service->location_phone_number }} <br>
                                        {{ $appointment->service->location_description }} <br>
                                        <a href="https://www.google.com/maps/{{'@'.$appointment->service->location_latitude}},{{$appointment->service->location_longitude}},16z"
                                           class="btn btn-primary btn-sm mb-1" target="_blank">view on maps</a>
                                    </p>
                                @endif
                            </th>
                        </tr>
                        <tr>
                            <th>@lang('labels.backend.appointments.fields.teacher')</th>
                            <td>
                                Name : <b>{{$appointment->teacherProfile->user->name}}</b><br>
                                Email : <b>{{$appointment->teacherProfile->user->email}}</b><br>
                            </td>
                        </tr>
                        <tr>
                            <th>@lang('labels.backend.appointments.fields.price')</th>
                            <td>{{ $orderItem->price.' '.$appCurrency['symbol'] }}</td>
                        </tr>
                        <tr>
                            <th>@lang('labels.backend.orders.fields.payment_type.title')</th>
                            <td>

                                @if($orderItem->order->payment_type == 1)
                                    {{trans('labels.backend.orders.fields.payment_type.stripe') }}
                                @elseif($orderItem->order->payment_type == 2)
                                    {{trans('labels.backend.orders.fields.payment_type.paypal')}}
                                @elseif($orderItem->order->payment_type == 3)
                                    {{trans('labels.backend.orders.fields.payment_type.tap')}}
                                @else
                                    {{trans('labels.backend.orders.fields.payment_type.offline')}}
                                @endif

                            </td>
                        </tr>
                        <tr>
                            <th>@lang('labels.backend.orders.fields.payment_status.title')</th>
                            <td>

                                @if($orderItem->order->status == 0)
                                    {{trans('labels.backend.orders.fields.payment_status.pending') }}
                                    <a class="btn btn-xs mb-1 mr-1 btn-success text-white" style="cursor:pointer;"
                                       onclick="$(this).find('form').submit();">
                                        {{trans('labels.backend.orders.complete')}}
                                        <form action="{{route('admin.orders.complete', ['order' => $orderItem->order->id])}}"
                                              method="POST" name="complete" style="display:none">
                                            @csrf
                                        </form>
                                    </a>
                                @elseif($orderItem->order->status == 1)
                                    {{trans('labels.backend.orders.fields.payment_status.completed')}}
                                @else
                                    {{trans('labels.backend.orders.fields.payment_status.failed')}}
                                @endif

                            </td>
                        </tr>


                        <tr>
                            <th>@lang('labels.backend.orders.fields.date')</th>
                            <td>{{ $orderItem->order->created_at->format('d M, Y | h:i A') }}</td>
                        </tr>


                    </table>
                </div>
            </div><!-- Nav tabs -->
            @if(Auth::user()->isAdmin())
                <a href="{{ route('admin.appointments.index') }}"
                   class="btn btn-default border">@lang('strings.backend.general.app_back_to_list')</a>
            @else
                <a href="{{ route('admin.payments') }}"
                   class="btn btn-default border">@lang('strings.backend.general.app_back_to_list')</a>
            @endif
        </div>
    </div>
@stop