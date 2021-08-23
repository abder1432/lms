<?php

namespace App\Http\Controllers\Backend\Admin;

use App\Http\Controllers\Traits\FileUploadTrait;
use App\Http\Requests\Admin\StoreServiceCategoriesRequest;
use App\Http\Requests\Admin\UpdateCategoriesRequest;
use App\Http\Requests\Admin\UpdateServiceCategoriesRequest;
use App\Models\ServiceCategory;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Gate;
use Yajra\DataTables\DataTables;

class ServiceCategoriesController extends Controller
{
    use FileUploadTrait;

    /**
     * Display a listing of Category.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        if (!Gate::allows('service_category_access')) {
            return abort(401);
        }

        return view('backend.service-categories.index');
    }

    /**
     * Display a listing of Courses via ajax DataTable.
     *
     * @return \Illuminate\Http\Response
     */
    public function getData(Request $request)
    {
        $has_view = false;
        $has_delete = false;
        $has_edit = false;
        $categories = "";


        if (request('show_deleted') == 1) {
            if (!Gate::allows('service_category_delete')) {
                return abort(401);
            }
            $categories = ServiceCategory::query()->onlyTrashed()
                ->orderBy('created_at', 'desc');
        } else {
            $categories = ServiceCategory::query()->orderBy('created_at', 'desc');
        }

        if (auth()->user()->can('service_category_view')) {
            $has_view = true;
        }
        if (auth()->user()->can('service_category_edit')) {
            $has_edit = true;
        }
        if (auth()->user()->can('service_category_delete')) {
            $has_delete = true;
        }

        return DataTables::of($categories)
            ->addIndexColumn()
            ->addColumn('actions', function ($q) use ($has_view, $has_edit, $has_delete, $request) {
                $view = "";
                $edit = "";
                $delete = "";
                $allow_delete = false;

                if ($request->show_deleted == 1) {
                    return view('backend.datatable.action-trashed')->with(['route_label' => 'admin.service_categories', 'label' => 'id', 'value' => $q->id]);
                }

                if ($has_edit) {
                    $edit = view('backend.datatable.action-edit')
                        ->with(['route' => route('admin.service_categories.edit', ['service_category' => $q->id])])
                        ->render();
                    $view .= $edit;
                }

                if ($has_delete) {
                    $data = $q->services->count();
                    if ($data == 0) {
                        $allow_delete = true;
                    }
                    $delete = view('backend.datatable.action-delete')
                        ->with(['route' => route('admin.service_categories.destroy', ['service_category' => $q->id]), 'allow_delete' => $allow_delete])
                        ->render();
                    $view .= $delete;
                }

                return $view;
            })
            ->addColumn('services', function ($q) {
                return $q->services->count();
            })
            ->rawColumns(['actions'])
            ->make();
    }

    /**
     * Show the form for creating new ServiceCategory.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        if (!Gate::allows('service_category_create')) {
            return abort(401);
        }
        $courses = \App\Models\Course::ofTeacher()->get();
        $courses_ids = $courses->pluck('id');
        $courses = $courses->pluck('title', 'id')->prepend('Please select', '');
        $lessons = \App\Models\Lesson::whereIn('course_id', $courses_ids)->get()->pluck('title', 'id')->prepend('Please select', '');

        return view('backend.service-categories.create', compact('courses', 'lessons'));
    }

    /**
     * Store a newly created ServiceCategory in storage.
     *
     * @param \App\Http\Requests\StoreServiceCategorysRequest $request
     * @return \Illuminate\Http\Response
     */
    public function store(StoreServiceCategoriesRequest $request)
    {
        if (!Gate::allows('service_category_create')) {
            return abort(401);
        }

        $serviceCategory = new  ServiceCategory();

        $serviceCategory->name = $request->name;

        $serviceCategory->save();

        return redirect()->route('admin.service_categories.index')->withFlashSuccess(trans('alerts.backend.general.created'));
    }


    /**
     * Show the form for editing ServiceCategory.
     *
     * @param int $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        if (!Gate::allows('service_category_edit')) {
            return abort(401);
        }

        $serviceCategory = ServiceCategory::findOrFail($id);

        return view('backend.service-categories.edit', compact('serviceCategory'));
    }

    /**
     * Update ServiceCategory in storage.
     *
     * @param \App\Http\Requests\UpdateCategorysRequest $request
     * @param int $id
     * @return \Illuminate\Http\Response
     */
    public function update(UpdateCategoriesRequest $request, $id)
    {
        if (!Gate::allows('service_category_edit')) {
            return abort(401);
        }

        $serviceCategory = ServiceCategory::findOrFail($id);
        $serviceCategory->name = $request->name;
        $serviceCategory->save();

        return redirect()->route('admin.service_categories.index')->withFlashSuccess(trans('alerts.backend.general.updated'));
    }


    /**
     * Display ServiceCategory.
     *
     * @param int $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        if (!Gate::allows('category_view')) {
            return abort(401);
        }
        $serviceCategory = ServiceCategory::findOrFail($id);

        return view('backend.service-categories.show', compact('serviceCategory'));
    }


    /**
     * Remove ServiceCategory from storage.
     *
     * @param int $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        if (!Gate::allows('category_delete')) {
            return abort(401);
        }
        $serviceCategory = ServiceCategory::findOrFail($id);
        $serviceCategory->delete();

        return redirect()->route('admin.service_categories.index')->withFlashSuccess(trans('alerts.backend.general.deleted'));
    }

    /**
     * Delete all selected ServiceCategory at once.
     *
     * @param Request $request
     */
    public function massDestroy(Request $request)
    {
        if (!Gate::allows('category_delete')) {
            return abort(401);
        }
        if ($request->input('ids')) {
            $entries = ServiceCategory::whereIn('id', $request->input('ids'))->get();

            foreach ($entries as $entry) {
                $entry->delete();
            }
        }
    }


    /**
     * Restore ServiceCategory from storage.
     *
     * @param int $id
     * @return \Illuminate\Http\Response
     */
    public function restore($id)
    {
        if (!Gate::allows('category_delete')) {
            return abort(401);
        }
        $serviceCategory = ServiceCategory::onlyTrashed()->findOrFail($id);
        $serviceCategory->restore();

        return redirect()->route('admin.service_categories.index')->withFlashSuccess(trans('alerts.backend.general.restored'));
    }

    /**
     * Permanently delete ServiceCategory from storage.
     *
     * @param int $id
     * @return \Illuminate\Http\Response
     */
    public function perma_del($id)
    {
        if (!Gate::allows('service_category_delete')) {
            return abort(401);
        }
        $serviceCategory = ServiceCategory::onlyTrashed()->findOrFail($id);
        $serviceCategory->forceDelete();

        return redirect()->route('admin.service_categories.index')->withFlashSuccess(trans('alerts.backend.general.deleted'));
    }
}
