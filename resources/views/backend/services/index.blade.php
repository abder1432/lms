@inject('request', 'Illuminate\Http\Request')
@extends('backend.layouts.app')
@section('title', __('labels.backend.services.title').' | '.app_name())

@section('content')


    <div class="card">
        <div class="card-header">
            <h3 class="page-title float-left mb-0">@lang('labels.backend.services.title')</h3>
            @can('service_create')
                <div class="float-right">
                    <a href="{{ route('admin.services.create') }}"
                       class="btn btn-success">@lang('strings.backend.general.app_add_new')</a>

                </div>
            @endcan
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <div class="d-block">
                    <ul class="list-inline">
                        <li class="list-inline-item">
                            <a href="{{ route('admin.services.index') }}"
                               style="{{ request('show_deleted') == 1 ? '' : 'font-weight: 700' }}">{{trans('labels.general.all')}}</a>
                        </li>
                        |
                        <li class="list-inline-item">
                            <a href="{{ route('admin.services.index') }}?show_deleted=1"
                               style="{{ request('show_deleted') == 1 ? 'font-weight: 700' : '' }}">{{trans('labels.general.trash')}}</a>
                        </li>
                    </ul>
                </div>
                <div class="d-block">
                    <ul class="list-inline">
                        <li class="font-weight-bold"> @lang('labels.backend.service_categories.title') @lang('labels.backend.reports.filter')
                            :
                        </li>

                        @foreach(\App\Models\ServiceCategory::has('services')->get() as $category)
                            <li class="list-inline-item font-weight-normal">
                                <a href="{{ route('admin.services.index', ['cat_id' => $category->id]) }}"
                                   style="{{ request('cat_id') == $category->id ? 'font-weight: 700' : '' }}">{{ $category->name }}</a>
                            </li>
                        @endforeach
                        <li class="list-inline-item font-weight-normal">
                            <a href="{{ route('admin.services.index') }}">&times; @lang('labels.backend.services.clear_filter')</a>
                        </li>
                    </ul>
                </div>


                <table id="myTable"
                       class="table table-bordered table-striped @can('service_delete') @if ( request('show_deleted') != 1 ) dt-select @endif @endcan">
                    <thead>
                    <tr>
                        @can('service_delete')
                            @if ( request('show_deleted') != 1 )
                                <th style="text-align:center;"><input type="checkbox" class="mass" id="select-all"/>
                                </th>@endif
                        @endcan

                        <th>@lang('labels.general.sr_no')</th>
                        <th>@lang('labels.general.id')

                        <th>@lang('labels.backend.services.fields.name')</th>
                        <th>@lang('labels.backend.services.fields.price')</th>
                        <th>@lang('labels.backend.services.fields.duration')</th>
                        <th>@lang('labels.backend.services.fields.category')</th>
                        <th>@lang('labels.backend.services.fields.location')</th>
                        @if( request('show_deleted') == 1 )
                            <th>&nbsp; @lang('strings.backend.general.actions')</th>
                        @else
                            <th>&nbsp; @lang('strings.backend.general.actions')</th>
                        @endif
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
            var route = '{{route('admin.services.get_data')}}';

            @if(request('show_deleted') == 1)
                route = '{{route('admin.services.get_data',['show_deleted' => 1])}}';
            @endif

                    @if(request('teacher_id') != "")
                route = '{{route('admin.services.get_data',['teacher_id' => request('teacher_id')])}}';
            @endif

                    @if(request('cat_id') != "")
                route = '{{route('admin.services.get_data',['cat_id' => request('cat_id')])}}';
            @endif

            $('#myTable').DataTable({
                processing: true,
                serverSide: true,
                iDisplayLength: 10,
                retrieve: true,
                dom: 'lfBrtip<"actions">',
                buttons: [
                    {
                        extend: 'csv',
                        exportOptions: {
                            columns: [2, 3, 4, 5, 6]
                        }
                    },
                    {
                        extend: 'pdf',
                        exportOptions: {
                            columns: [2, 3, 4, 5, 6]
                        }
                    },
                    'colvis'
                ],
                ajax: route,
                columns: [
                        @if(request('show_deleted') != 1)
                    {
                        "data": function (data) {
                            return '<input type="checkbox" class="single" name="id[]" value="' + data.id + '" />';
                        }, "orderable": false, "searchable": false, "name": "id"
                    },
                        @endif
                    {
                        data: "DT_RowIndex", name: 'DT_RowIndex', searchable: false, orderable: false
                    },
                    {data: "id", name: 'id'},
                    {data: "title", name: 'title'},
                    {data: "price", name: "price"},
                    {data: "duration", name: 'duration'},
                    {data: "service_category", name: 'category'},
                    {data: "location", name: 'location'},
                    {data: "actions", name: "actions"}
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
                language: {
                    url: "//cdn.datatables.net/plug-ins/9dcbecd42ad/i18n/{{$locale_full_name}}.json",
                    buttons: {
                        colvis: '{{trans("datatable.colvis")}}',
                        pdf: '{{trans("datatable.pdf")}}',
                        csv: '{{trans("datatable.csv")}}',
                    }
                }
            });
            {{--@can('service_delete')--}}
            {{--@if(request('show_deleted') != 1)--}}
            {{--$('.actions').html('<a href="' + '{{ route('admin.services.mass_destroy') }}' + '" class="btn btn-xs btn-danger js-delete-selected" style="margin-top:0.755em;margin-left: 20px;">Delete selected</a>');--}}
            {{--@endif--}}
            {{--@endcan--}}
        });

    </script>

@endpush