<?php

namespace App\Http\Controllers\Backend\Admin;

use App\Http\Controllers\Traits\FileUploadTrait;
use App\Models\Auth\User;
use App\Models\ServiceCategory;
use App\Models\Service;
use App\Models\Media;
use App\Models\TeacherProfile;
use Illuminate\Support\Collection;
use function foo\func;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreServicesRequest;
use App\Http\Requests\Admin\UpdateServicesRequest;
use Yajra\DataTables\Facades\DataTables;

class ServicesController extends Controller
{
    use FileUploadTrait;

    /**
     * Display a listing of Service.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        if (!Gate::allows('service_access')) {
            return abort(401);
        }

        return view('backend.services.index');
    }

    /**
     * Display a listing of Services via ajax DataTable.
     *
     * @return \Illuminate\Http\Response
     */
    public function getData(Request $request)
    {
        $has_view = false;
        $has_delete = false;
        $has_edit = false;
        $services = "";

        if (request('show_deleted') == 1) {
            if (!Gate::allows('service_delete')) {
                return abort(401);
            }
            $services = Service::query()->onlyTrashed()->orderBy('created_at', 'desc');
        } else if (request('cat_id') != "") {
            $id = request('cat_id');
            $services = Service::query()
                ->where('service_category_id', '=', $id)->orderBy('created_at', 'desc');
        } else {
            $services = Service::query()
                ->orderBy('created_at', 'desc');
        }


        if (auth()->user()->can('service_view')) {
            $has_view = true;
        }
        if (auth()->user()->can('service_edit')) {
            $has_edit = true;
        }
        if (auth()->user()->can('lesson_delete')) {
            $has_delete = true;
        }

        return DataTables::of($services)
            ->addIndexColumn()
            ->addColumn('actions', function ($q) use ($has_view, $has_edit, $has_delete, $request) {
                $view = "";
                $edit = "";
                $delete = "";
                if ($request->show_deleted == 1) {
                    return view('backend.datatable.action-trashed')->with(['route_label' => 'admin.services', 'label' => 'id', 'value' => $q->id]);
                }
                if ($has_view) {
                    $view = view('backend.datatable.action-view')
                        ->with(['route' => route('admin.services.show', ['service' => $q->id])])->render();
                }
                if ($has_edit) {
                    $edit = view('backend.datatable.action-edit')
                        ->with(['route' => route('admin.services.edit', ['service' => $q->id])])
                        ->render();
                    $view .= $edit;
                }

                if ($has_delete) {
                    $delete = view('backend.datatable.action-delete')
                        ->with(['route' => route('admin.services.destroy', ['service' => $q->id])])
                        ->render();
                    $view .= $delete;
                }

                return $view;
            })
            ->editColumn('price', function ($q) {
                if ($q->free == 1) {
                    return trans('labels.backend.services.fields.free');
                }
                return $q->price;
            })
            ->addColumn('service_category', function ($q) {
                return ServiceCategory::find($q->service_category_id)->name;
            })
            ->addColumn('location', function ($q) {
                return $q->is_online
                    ? "<span class=\"badge badge-success\">" . __('labels.backend.appointments.fields.online') . "(zoom)</span>"
                    : "<span class=\"badge badge-warning\">" . __('labels.backend.appointments.fields.on_site') . "</span>";
            })
            ->rawColumns(['teachers', 'lessons', 'service_image','location', 'actions', 'status'])
            ->make();
    }


    /**
     * Show the form for creating new Service.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        if (!Gate::allows('service_create')) {
            return abort(401);
        }

        $teachers = new Collection();

        foreach ( TeacherProfile::all() as $teacherProfile) {
            $teachers->put($teacherProfile->id, $teacherProfile->user->full_name);
        }

        $serviceCategories = ServiceCategory::pluck('name', 'id');

        return view('backend.services.create', compact('teachers', 'serviceCategories'));
    }

    /**
     * Store a newly created Service in storage.
     *
     * @param \App\Http\Requests\StoreServicesRequest $request
     * @return \Illuminate\Http\Response
     */
    public function store(StoreServicesRequest $request)
    {
        if (!Gate::allows('service_create')) {
            return abort(401);
        }

        $teachers = array_filter((array)$request->input('teachers'));

        $validated = $request->validated();
        unset($validated['teachers']);

        $service = Service::create($validated);

        $service->save();

        $service->teachers()->sync($teachers);


        return redirect()->route('admin.services.index')->withFlashSuccess(trans('alerts.backend.general.created'));
    }


    /**
     * Show the form for editing Service.
     *
     * @param int $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        if (!Gate::allows('service_edit')) {
            return abort(401);
        }

        $teachers = new Collection();

        foreach ( TeacherProfile::all() as $teacherProfile) {
            $teachers->put($teacherProfile->id, $teacherProfile->user->full_name);
        }

        $serviceCategories = ServiceCategory::pluck('name', 'id');


        $service = Service::findOrFail($id);
        return view('backend.services.edit', compact('service', 'teachers', 'serviceCategories'));
    }

    /**
     * Update Service in storage.
     *
     * @param \App\Http\Requests\UpdateServicesRequest $request
     * @param int $id
     * @return \Illuminate\Http\Response
     */
    public function update(UpdateServicesRequest $request, $id)
    {
        if (!Gate::allows('service_edit')) {
            return abort(401);
        }
        $service = Service::findOrFail($id);

        $teachers = array_filter((array)$request->input('teachers'));

        $validated = $request->validated();
        unset($validated['teachers']);

        $validated['is_online'] = $validated['is_online'] ?? false;

        $service->update($validated);

        $service->teachers()->sync($teachers);

        return redirect()->route('admin.services.index')->withFlashSuccess(trans('alerts.backend.general.updated'));
    }


    /**
     * Display Service.
     *
     * @param int $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        if (!Gate::allows('service_view')) {
            return abort(401);
        }

        $service = Service::findOrFail($id);

        //dd($service->serviceCategory);

        return view('backend.services.show', compact('service'));
    }


    /**
     * Remove Service from storage.
     *
     * @param int $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        if (!Gate::allows('service_delete')) {
            return abort(401);
        }
        $service = Service::findOrFail($id);

        $service->delete();


        return redirect()->route('admin.services.index')->withFlashSuccess(trans('alerts.backend.general.deleted'));
    }

    /**
     * Delete all selected Service at once.
     *
     * @param Request $request
     */
    public function massDestroy(Request $request)
    {
        if (!Gate::allows('service_delete')) {
            return abort(401);
        }
        if ($request->input('ids')) {
            $entries = Service::whereIn('id', $request->input('ids'))->get();

            foreach ($entries as $entry) {
                $entry->delete();
            }
        }
    }


    /**
     * Restore Service from storage.
     *
     * @param int $id
     * @return \Illuminate\Http\Response
     */
    public function restore($id)
    {
        if (!Gate::allows('service_delete')) {
            return abort(401);
        }
        $service = Service::onlyTrashed()->findOrFail($id);
        $service->restore();

        return redirect()->route('admin.services.index')->withFlashSuccess(trans('alerts.backend.general.restored'));
    }

    /**
     * Permanently delete Service from storage.
     *
     * @param int $id
     * @return \Illuminate\Http\Response
     */
    public function perma_del($id)
    {
        if (!Gate::allows('service_delete')) {
            return abort(401);
        }
        $service = Service::onlyTrashed()->findOrFail($id);
        $service->forceDelete();

        return redirect()->route('admin.services.index')->withFlashSuccess(trans('alerts.backend.general.deleted'));
    }

    /**
     * Permanently save Sequence from storage.
     *
     * @param Request
     */
    public function saveSequence(Request $request)
    {
        if (!Gate::allows('service_edit')) {
            return abort(401);
        }

        foreach ($request->list as $item) {
            $serviceTimeline = ServiceTimeline::find($item['id']);
            $serviceTimeline->sequence = $item['sequence'];
            $serviceTimeline->save();
        }

        return 'success';
    }


    /**
     * Publish / Unpublish services
     *
     * @param Request
     */
    public function publish($id)
    {
        if (!Gate::allows('service_edit')) {
            return abort(401);
        }

        $service = Service::findOrFail($id);
        if ($service->published == 1) {
            $service->published = 0;
        } else {
            $service->published = 1;
        }
        $service->save();

        return back()->withFlashSuccess(trans('alerts.backend.general.updated'));
    }
}
