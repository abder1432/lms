@inject('request', 'Illuminate\Http\Request')
@extends('backend.layouts.app')
@section('title', __('labels.backend.appointments.title').' | '.app_name())


@section('content')


    <div class="card">
        <div class="card-header">
            <h3 class="page-title d-inline mb-0">@lang('labels.backend.appointments.title')</h3>
            @can('appointment_create')
                <div class="float-right">
                    <a href="{{ route('admin.appointment.create') }}"
                       class="btn btn-success">@lang('strings.backend.general.app_add_new')</a>
                </div>
            @endcan
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table id="myTable" class="table table-bappointmented table-striped">
                    <thead>
                    <tr>
                        <th style="text-align:center;">
                            <input type="checkbox" class="mass" id="select-all"/>
                        </th>
                        <th>@lang('labels.general.sr_no')</th>
                        <th>@lang('labels.backend.appointments.fields.teacher')</th>
                        <th>@lang('labels.backend.appointments.fields.student')</th>
                        <th>@lang('labels.backend.appointments.fields.service')</th>
                        <th>@lang('labels.backend.appointments.fields.date')</th>
                        <th>@lang('labels.backend.appointments.fields.time')</th>
                        <th>@lang('labels.backend.appointments.fields.price')</th>
                        <th>@lang('labels.backend.appointments.fields.payment')</th>
                        <th>@lang('labels.backend.appointments.fields.location')</th>
                        <th>&nbsp; @lang('strings.backend.general.actions')</th>
                    </tr>
                    </thead>
                    <tbody>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
@stop

@push('after-scripts')
    <script>
        $(document).ready(function () {
            var route = '{{route('admin.appointments.get_data')}}';

            $('#myTable').DataTable({
                processing: true,
                search:false,
                serverSide: true,
                iDisplayLength: 10,
                retrieve: true,
                dom: 'lfBrtip<"actions">',
                buttons: [
                    {
                        extend: 'csv',
                        exportOptions: {
                            columns: [ 1, 2, 3, 4, 5, 6, 7,8 ]
                        }
                    },
                    {
                        extend: 'pdf',
                        exportOptions: {
                            columns: [ 1, 2, 3, 4, 5, 6, 7,8 ]
                        }
                    },
                    'colvis'
                ],
                ajax: route,
                columns: [
                    {
                        data: function (data) {
                            return '<input type="checkbox" class="single" name="id[]" value="' + data.id + '" />';
                        }, "appointmentable": false, "searchable": false, "name": "id"
                    },
                    {data: "DT_RowIndex", name: 'DT_RowIndex', searchable: false},
                    {data: "teacher", name: 'teacher'},
                    {data: "student", name: 'student'},
                    {data: "service", name: 'service'},
                    {data: "date", name: 'date'},
                    {data: "time", name: 'time', searchable: false},
                    {data: "price", name: 'price', searchable: false},
                    {data: "payment", name: 'payment', searchable: false},
                    {data: "start_url", name: 'start_url', searchable: false},
                    {data: "actions", name: "actions", searchable: false}
                ],
                @if(request('show_deleted') != 1)
                columnDefs: [
                    {"width": "5%", "targets": 0},
                    {"className": "text-center", "targets": [0]}
                ],
                @endif

                createdRow: function (row, data, dataIndex) {
                    $(row).attr('data-entry-id', data.id);
                },
                language:{
                    url : "//cdn.datatables.net/plug-ins/9dcbecd42ad/i18n/{{$locale_full_name}}.json",
                    buttons :{
                        colvis : '{{trans("datatable.colvis")}}',
                        pdf : '{{trans("datatable.pdf")}}',
                        csv : '{{trans("datatable.csv")}}',
                    }
                }
            });
            @can('course_delete')
            @if(request('show_deleted') != 1)
            $('.actions').html('<a href="' + '{{ route('admin.appointments.mass_destroy') }}' + '" class="btn btn-xs btn-danger js-delete-selected" style="margin-top:0.755em;margin-left: 20px;">Delete selected</a>');
            @endif
            @endcan
        });
    </script>
@endpush