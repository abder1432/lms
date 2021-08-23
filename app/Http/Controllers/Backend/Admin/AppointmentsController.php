<?php

namespace App\Http\Controllers\Backend\Admin;

use App\Helpers\General\EarningHelper;
use App\Mail\Frontend\Appointment\StudentAppointmentMail;
use App\Models\Auth\User;
use App\Models\Bundle;
use App\Models\Course;
use App\Models\Appointment;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Service;
use App\Models\TeacherProfile;
use Carbon\Carbon;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Gate;
use MacsiDigital\Zoom\Facades\Zoom;
use Yajra\DataTables\Facades\DataTables;
use Illuminate\Support\Facades\Response;

class AppointmentsController extends Controller
{

    /**
     * Display a listing of Appointments.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $appointments = Appointment::get();
        return view('backend.appointments.index', compact('appointments'));
    }

    /**
     * Display a listing of Appointments via ajax DataTable.
     *
     * @return \Illuminate\Http\Response
     */
    public function getData(Request $request)
    {

        $appointments = Appointment::query()->orderBy('updated_at', 'desc');

        if (!auth()->user()->isAdmin()) {
            $appointments->where('user_id', '=', auth()->id());
        }

        return DataTables::of($appointments)
            ->addIndexColumn()
            ->addColumn('actions', function ($q) use ($request) {
                $view = "";

                if (Gate::allows('appointment_view')) {
                    $view .= view('backend.datatable.action-view')
                        ->with(['route' => route('admin.appointments.show', ['appointment' => $q->id])])->render();
                }

                if (Gate::allows('appointment_edit')) {
                    $view .= view('backend.datatable.action-edit')
                        ->with(['route' => route('admin.appointment.edit', ['appointment' => $q->id])])->render();
                }

                if (Gate::allows('appointment_delete')) {
                    $delete = view('backend.datatable.action-delete')
                        ->with(['route' => route('admin.appointments.destroy', ['appointment' => $q->id])])
                        ->render();

                    $view .= $delete;
                }


                return $view;

            })
            ->addColumn('teacher', function ($q) {
                return '<a href="' . route('admin.teachers.show', $q->teacherProfile->user_id) . '">' . $q->teacherProfile->user->full_name . '</a>';
            })
            ->addColumn('student', function ($q) {
                return '<a href="' . route('admin.auth.user.show', $q->user->id) . '">' . $q->user->full_name . '</a>';

            })
            ->addColumn('service', function ($q) {
                return $q->service->title;
            })
            ->addColumn('date', function ($q) {
                return $q->date->format('Y/m/d');
            })
            ->addColumn('time', function ($q) {
                return $q->date->format('H:i');
            })
            ->editColumn('price', function ($q) {
                return '$' . floatval($q->price);
            })->addColumn('start_url', function ($appointment) {
                if ($appointment->service->is_online) {
                    $link = '<span class="badge badge-info">' . __('labels.backend.appointments.fields.online') . '</span><br>';
                    if ($appointment->date->timezone(config('zoom.timezone'))->lt(Carbon::now(new \DateTimeZone(config('zoom.timezone'))))) {
                        return $link . '<span class="badge badge-dark">' . trans('labels.backend.live_lesson_slots.closed') . '</span>';
                    } else {
                        return $link . '<a href="' . $appointment->meeting_start_url . '" class="btn btn-success btn-sm mb-1">' . trans('labels.backend.live_lesson_slots.start_url') . '</a>';
                    }
                } else {
                    //'https://www.google.com/maps/@'.$content['location_latitude'].','.$content['location_longitude'].',16z'
                    $link = '<span class="badge badge-warning">' . __('labels.backend.appointments.fields.on_site') . '</span><br>';

                    return $link . '<a href="https://www.google.com/maps/@'
                        . $appointment->service->location_latitude . ','
                        . $appointment->service->location_longitude . ',16z" class="btn btn-primary btn-sm mb-1">view on maps</a>';

                }
            })
            ->addColumn('payment', function ($q) {
                $orderItem = OrderItem::where('item_type', Appointment::class)->where('item_id', $q->id)->first();

                $payment_status = trans('labels.backend.orders.fields.payment_status.failed');

                $payment_type = '';

                if ($orderItem) {
                    if ($orderItem->order->status == 0) {
                        $payment_status = trans('labels.backend.orders.fields.payment_status.pending');
                    } else if ($orderItem->order->status == 1) {
                        $payment_status = trans('labels.backend.orders.fields.payment_status.completed');
                    }

                    if (!empty($orderItem->order->payment_type)) {
                        if ($orderItem->order->payment_type == 1) {
                            $payment_type = trans('labels.backend.orders.fields.payment_type.stripe');
                        } else if ($orderItem->order->payment_type == 2) {
                            $payment_type = trans('labels.backend.orders.fields.payment_type.paypal');
                        } else if ($orderItem->order->payment_type == 3) {
                            $payment_type = trans('labels.backend.orders.fields.payment_type.tap');
                        } else {
                            $payment_type = trans('labels.backend.orders.fields.payment_type.offline');
                        }
                    }
                }

                return $payment_status . ($payment_type ? ' (' . $payment_type . ')' : '');
            })
            ->rawColumns(['student', 'teacher', 'actions', 'start_url'])
            ->make();
    }

    /**
     * Complete Appointment manually once payment received.
     *
     * @param Request $request
     * @return \Illuminate\Http\Response
     */

    /**
     * Show Appointment from storage.
     *
     * @param int $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        if (!Gate::allows('appointment_view')) {
            return abort(401);
        }

        $appointment = Appointment::findOrFail($id);

        $orderItem = OrderItem::where('item_type', Appointment::class)->where('item_id', $id)->first();

        return view('backend.appointments.show', compact('appointment', 'orderItem'));
    }

    public function create()
    {
        if (!Gate::allows('appointment_create')) {
            return abort(401);
        }

        $teachers = User::role('teacher')->pluck('email', 'id');
        $students = User::role('student')->pluck('email', 'id');

        $services = Service::pluck('title', 'id');
        return view('backend.appointments.create', compact('teachers', 'services', 'students'));
    }

    public function store()
    {
        if (!Gate::allows('appointment_create')) {
            return abort(401);
        }

        $validated = request()->validate([
            'service_id' => ['required', 'exists:services,id'],
            'teacher_id' => ['required', 'exists:users,id'],
            'user_id'    => ['required', 'exists:users,id'],
            'date'       => ['required', 'date'],
            'time'       => ['required', 'date_format:H:i'],
        ]);

        $service = Service::find($validated['service_id']);

        $teacher = TeacherProfile::where('user_id', '=', $validated['teacher_id'])->first();

        $validated['teacher_profile_id'] = $teacher->id;

        $student = User::find($validated['user_id']);

        $date = Carbon::make($validated['date'] . ' ' . $validated['time']);

        $appointment = Appointment::make($validated);
        $appointment->date = $date;
        $appointment->status = Appointment::APPOINTMENT_STATUS_COMPLETE;


        if ($service->is_online) {
            $meeting_password = substr(md5(time()), 6, 6);
            $meeting = $this->meetingCreateOrUpdate([
                'topic'                  => 'Appointment',
                'description'            => 'Online appointment',
                'duration'               => ($service->duration ?? 0) * 60,
                'password'               => $meeting_password,
                'start_at'               => $date,
                'change_default_setting' => false,
            ]);

            $appointment->meeting_id = $meeting->id;
            $appointment->meeting_password = $meeting_password;
            $appointment->meeting_start_url = $meeting->start_url;
            $appointment->meeting_join_url = $meeting->join_url;

            try {
                \Mail::to($student->email)->send(new StudentAppointmentMail([
                    'full_name'        => $student->full_name,
                    'service_name'     => $service->title,
                    'teacher_name'     => $teacher->user->full_name,
                    'date'             => $appointment->date,
                    'meeting_join_url' => $appointment->meeting_join_url,
                    'meeting_id'       => $appointment->meeting_id,
                    'meeting_password' => $appointment->meeting_password,
                ]));
            } catch (\Exception $e) {
                $appointment->save();
                \Log::info($e->getMessage() . ' for appointment ' . $appointment->id);
            }
        }

        $appointment->save();

        $order = Order::create([
            'user_id'      => $student->id,
            'reference_no' => substr(md5(time()), 6, 8),
            'amount'       => $appointment->price,
            'payment_type' => -1,
            'status'       => 1,
            'order_type'   => 0,
        ]);

        OrderItem::create([
            'order_id'  => $order->id,
            'item_type' => Appointment::class,
            'item_id'   => $appointment->id,
            'price'     => $appointment->price,
            'type'      => 0,
        ]);

        return redirect()->route('admin.appointments.index')->withFlashSuccess(trans('alerts.backend.general.created'));
    }

    public function edit($id)
    {
        if (!Gate::allows('appointment_edit')) {
            return abort(401);
        }

        $appointment = Appointment::findOrFail($id);

        $orderItem = OrderItem::where('item_type', Appointment::class)->where('item_id', $id)->first();

        $teachers = User::role('teacher')->pluck('email', 'id');
        $students = User::role('student')->pluck('email', 'id');

        $services = Service::pluck('title', 'id');

        return view('backend.appointments.edit', compact('teachers', 'services', 'students', 'appointment', 'orderItem'));
    }

    public function update(Appointment $appointment)
    {
        if (!Gate::allows('appointment_edit')) {
            return abort(401);
        }

        $validated = request()->validate([
            'service_id' => ['required', 'exists:services,id'],
            'teacher_id' => ['required', 'exists:users,id'],
            'user_id'    => ['required', 'exists:users,id'],
            'date'       => ['required', 'date'],
            'time'       => ['required', 'date_format:H:i'],
        ]);


        $date = Carbon::make($validated['date'] . ' ' . $validated['time']);
        $validated['date'] = $date;

        $appointment->update($validated);

        return redirect()->route('admin.appointments.index')->withFlashSuccess(trans('alerts.backend.general.updated'));
    }

    /**
     * Remove Appointment from storage.
     *
     * @param int $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        if (!Gate::allows('appointment_delete')) {
            return abort(401);
        }
        $appointment = Appointment::findOrFail($id);
        $orderItem = OrderItem::where('item_type', '=', Appointment::class)
            ->where('item_id', '=', $appointment->id)->first();
        if ($orderItem) {
            $order = Order::find($orderItem->order_id);
            $orderItem->delete();
            $order->delete();
        }
        $appointment->delete();
        return redirect()->route('admin.appointments.index')->withFlashSuccess(trans('alerts.backend.general.deleted'));
    }

    /**
     * Delete all selected Appointments at once.
     *
     * @param Request $request
     */
    public function massDestroy(Request $request)
    {
        if (!Gate::allows('appointment_delete')) {
            return abort(401);
        }
        if ($request->input('ids')) {
            $entries = Appointment::whereIn('id', $request->input('ids'))->get();
            foreach ($entries as $entry) {
                if ($entry->status = 1) {
                    foreach ($entry->items as $item) {
                        $item->course->students()->detach($entry->user_id);
                    }
                    $entry->items()->delete();
                    $entry->delete();
                }
            }
        }
    }

    private function meetingCreateOrUpdate($parameters)
    {
        $user = Zoom::user()->get()->first();
        $meetingData = [
            'topic'      => $parameters['topic'],
            'type'       => 2,
            'agenda'     => $parameters['description'],
            'duration'   => $parameters['duration'],
            'password'   => $parameters['password'],
            'start_time' => $parameters['start_at'],
            'timezone'   => config('zoom.timezone'),
        ];

        $meeting = Zoom::meeting()->make($meetingData);

        $meeting->settings()->make([
            'join_before_host'  => $parameters['change_default_setting'] ? $parameters['join_before_host'] ? true : false : config('zoom.join_before_host') ? true : false,
            'host_video'        => $parameters['change_default_setting'] ? $parameters['host_video'] ? true : false : config('zoom.host_video') ? true : false,
            'participant_video' => $parameters['change_default_setting'] ? $parameters['participant_video'] ? true : false : config('zoom.participant_video') ? true : false,
            'mute_upon_entry'   => $parameters['change_default_setting'] ? $parameters['participant_mic_mute'] ? true : false : config('zoom.mute_upon_entry') ? true : false,
            'waiting_room'      => $parameters['change_default_setting'] ? $parameters['waiting_room'] ? true : false : config('zoom.waiting_room') ? true : false,
            'approval_type'     => $parameters['change_default_setting'] ? $parameters['approval_type'] : config('zoom.approval_type'),
            'audio'             => $parameters['change_default_setting'] ? $parameters['audio_option'] : config('zoom.audio'),
            'auto_recording'    => config('zoom.auto_recording'),
        ]);

        return $user->meetings()->save($meeting);
    }
}
