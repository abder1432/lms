<?php

namespace App\Http\Livewire\Frontend\Appointments;

use App\Mail\Frontend\Appointment\StudentAppointmentMail;
use App\Mail\OfflineOrderMail;
use App\Models\Appointment;
use App\Models\Auth\User;
use App\Models\Availability;
use App\Models\Service;
use App\Models\ServiceCategory;
use App\Models\TeacherProfile;
use Carbon\Carbon;
use Carbon\CarbonTimeZone;
use Cart;
use Livewire\Component;
use MacsiDigital\Zoom\Facades\Zoom;

class BookAnAppointment extends Component
{
    protected $rules = [
        'data.service'              => ['required', 'exists:services,id'],
        'data.teacher'              => ['required', 'exists:teacher_profiles,id'],
        'data.user_info.first_name' => ['required', 'string'],
        'data.user_info.last_name'  => ['required', 'string'],
        'data.user_info.email'      => ['required', 'email'],
        'data.user_info.phone'      => ['required', 'string'],
    ];

    protected $listeners = ['dateUpdated' => 'appointmentDateChanged'];

    protected function getValidationAttributes()
    {
        return [
            'data.service'              => 'service',
            'data.teacher'              => 'expert',
            'data.user_info.first_name' => 'first name',
            'data.user_info.last_name'  => 'last name',
            'data.user_info.email'      => 'email',
            'data.user_info.phone'      => 'phone',
        ];
    }

    public function getSelectedDate()
    {
        $date = Carbon::createFromTimestamp($this->data['date'] / 1000);
        $date->setTimezone(CarbonTimeZone::create(($this->data['timezone_offset'] * -1 / 60)));
        $date->addHours($this->data['time_hour']);
        $date->addMinutes($this->data['time_minute']);

        return $date;
    }

    public $data = [
        'user_info'       => [
            'first_name' => null,
            'last_name'  => null,
            'email'      => null,
            'phone'      => null,
        ],
        'category'        => null,
        'service'         => null,
        'teacher'         => null,
        'date'            => null,
        'time_hour'       => null,
        'time_minute'     => null,
        'timezone_offset' => null,
    ];

    public $step = 1;

    public $availableDays = [2, 3, 4];

    public function mount()
    {
        $now = Carbon::now();
        $now->setTimezone(CarbonTimeZone::create(($this->data['timezone_offset'] * -1 / 60)));
        $this->data['time_hour'] = $now->hour;
        $this->data['time_minute'] = $now->minute;
    }

    public function getStepsProperty()
    {
        return [
            1 => 'Category',
            2 => 'Service&Expert',
            3 => 'Date',
            4 => 'PersonalInfo',
        ];
    }

    public function getTotalStepsProperty()
    {
        return count($this->steps);
    }

    public function render()
    {
        $viewData = [];

        if ($this->isStep(1)) {
            $viewData = $this->prepareSelectCategoryStep();
        } else if ($this->isStep(2)) {
            $viewData = $this->prepareSelectTeacherStep();
        } else if ($this->isStep(3)) {
            $viewData = $this->prepareSelectDateStep();
        } else if ($this->isStep(4)) {
            $viewData = $this->preparePersonalInformationStep();
        }

        return view('livewire.frontend.appointments.book-an-appointment', $viewData);
    }

    public function prepareSelectCategoryStep()
    {
        $categories = ServiceCategory::has('services')->get();

        if (!$this->data['category']) {
            $this->data['category'] = optional($categories->first())->id;
        }
        return [
            'categories' => $categories,
        ];
    }

    public function prepareSelectTeacherStep()
    {
        $services = ServiceCategory::find($this->data['category'])->services;
        $teachers = null;

        if (!$this->data['service']) {
            $this->data['service'] = optional($services->first())->id;
        }

        $selectedService = Service::find($this->data['service']);
        $teachers = $selectedService->teachers;

        if ($teachers->isEmpty()) {
            $this->data['teacher'] = null;
        }

        if (!$this->data['teacher'] || (!in_array($this->data['teacher'], $teachers->pluck('id')->toArray()))) {
            $this->data['teacher'] = optional($teachers->first())->id;
        }

        $this->refreshAvailableDays();

        return [
            'services' => $services,
            'teachers' => $teachers,
        ];
    }

    public function prepareSelectDateStep()
    {
        $date = Carbon::createFromTimestamp($this->data['date'] / 1000);
        $date->setTimezone(CarbonTimeZone::create(($this->data['timezone_offset'] * -1 / 60)));

        return compact('date');
    }

    public function preparePersonalInformationStep()
    {
        $teacher = TeacherProfile::find($this->data['teacher']);
        $service = Service::find($this->data['service']);

        $date = $this->getSelectedDate();

        return compact('teacher', 'service', 'date');
    }

    /*
        public function selectCategory($categoryId)
        {
            $this->data['category'] = $categoryId;
            $this->data['service'] = null;
        }*/

    public function updated($name, $value)
    {
        if ($name === 'data.category') {
            $this->data['service'] = null;
        }
    }

    public function appointmentDateChanged($date)
    {
        $this->data['date'] = $date;
    }

    public function next()
    {
        if ($this->canGoNext()) {
            $this->step++;
        }
    }

    public function back()
    {
        if ($this->canGoBack()) {
            $this->step--;
        }
    }

    public function finish()
    {
        $validated = $this->validate($this->rules);
        $studentInfo = $validated['data']['user_info'];
        $studentInfo['password'] = \Hash::make(time());
        $student = User::firstOrCreate([
            'email' => $validated['data']['user_info']['email'],
        ], $studentInfo);

        if (!auth()->check()) {
            auth()->login($student);
        }

        if (!$student->hasRole('student')) {
            $student->assignRole('student');
        }

        $validated['data']['service_id'] = $validated['data']['service'];
        $validated['data']['teacher_profile_id'] = $validated['data']['teacher'];
        $validated['data']['user_id'] = $student->id;

        unset($validated['data']['service']);
        unset($validated['data']['teacher']);
        unset($validated['data']['user_info']);

        $teacher = TeacherProfile::find($validated['data']['teacher_profile_id']);

        $date = $this->getSelectedDate();

        $appointment = Appointment::make($validated['data']);
        $appointment->date = $date;
        $appointment->status = Appointment::APPOINTMENT_STATUS_PENDING_PAYMENT;

        $service = Service::find($validated['data']['service_id']);

        $meeting_password = substr(md5(time()), 6, 6);
        $meeting = $this->meetingCreateOrUpdate([
            'topic'                  => 'Appointment',
            'description'            => 'Online appointment',
            'duration'               => ($service->duration ?? 0),
            'password'               => $meeting_password,
            'start_at'               => $date,
            'change_default_setting' => false,
        ]);

        $appointment->meeting_id = $meeting->id;
        $appointment->meeting_password = $meeting_password;
        $appointment->meeting_start_url = $meeting->start_url;
        $appointment->meeting_join_url = $meeting->join_url;

        $appointment->save();

        $cart_items = Cart::session(auth()->user()->id)->getContent()->keys()->toArray();

        if (!in_array($appointment->id, $cart_items)) {
            Cart::session(auth()->user()->id)
                ->add($appointment->id, $appointment->service->title, $appointment->service->price, 1,
                    [
                        'user_id'     => auth()->user()->id,
                        'description' => $appointment->description,
                        'image'       => $appointment->course_image,
                        'type'        => 'appointment',
                        'teachers'    => [$teacher->id => $teacher->user->full_name],
                    ]);
        }

        try {
            \Mail::to($student->email)->send(new StudentAppointmentMail([
                'full_name'        => $student->full_name,
                'service_name'     => $service->title,
                'teacher_name'     => $teacher->user->full_name,
                'date'             => $appointment->date,
                'meeting_join_url' => $appointment->meeting_join_url,
                'meeting_id'       => $appointment->meeting_id,
                'meeting_password' => $appointment->meeting_password,
                'is_online'        => $service->is_online,
                'location_address' => $service->location_address,
                'location_phone_number' => $service->location_phone_number,
                'location_description' => $service->location_description,
                'location_latitude' => $service->location_latitude,
                'location_longitude' => $service->location_longitude,
            ]));
        } catch (\Exception $e) {
            \Log::info($e->getMessage() . ' for appointment ' . $appointment->id);
        }

        return redirect()->route('cart.index');
    }

    public function canGoBack()
    {
        return $this->isNotStep(1);
    }

    public function canGoNext()
    {
        return !$this->isFinalStep()
            && (
                ($this->isStep(1) && $this->data['category'] !== null)
                || ($this->isStep(2) && $this->data['teacher'] !== null)
                || ($this->isStep(3) && $this->data['date'] !== null)
            );
    }

    public function isFinalStep(): bool
    {
        return $this->step === $this->totalSteps;
    }

    public function isStep($step)
    {
        return $this->step === $step;
    }

    public function isNotStep($step)
    {
        return !$this->isStep($step);
    }

    public function availableHours()
    {
        $teacher = TeacherProfile::find($this->data['teacher']);
        $service = Service::find($this->data['service']);

        $range = [];
        if ($teacher && $service) {
            $user_id = $teacher->user_id;
            $availabilities = $service->availabilitiesForUser($user_id)
                ->where('day_of_week', '=', $this->getSelectedDate()->dayOfWeek)
                ->get();

            foreach ($availabilities as $availability) {
                $range = array_merge($range, range($availability->start_hour, ($availability->end_hour - (int)($service->duration / 60))));
            }
        }

        $range = array_unique($range);

        if (!$this->data['time_hour'] || !in_array($this->data['time_hour'], $range)) {
            $this->data['time_hour'] = $range[0] ?? null;
            $this->data['time_minute'] = null;
        }


        $appointments = $teacher->appointments()
            ->where('service_id', '=', $service->id)
            ->where(\DB::raw('YEAR(date)'), '=', $this->getSelectedDate()->year)
            ->where(\DB::raw('MONTH(date)'), '=', $this->getSelectedDate()->month)
            ->where(\DB::raw('DAY(date)'), '=', $this->getSelectedDate()->day)
            ->get();

        $cleanRange = [];
        if (count($appointments) > 0) {
            foreach ($appointments as $appointment) {
                $start = $appointment->date->hour;
                $end = ($appointment->date->hour + (int)($appointment->service->duration / 60));
                foreach ($range as $hour) {
                    if ($hour < $start || $hour >= $end) {
                        $cleanRange[] = $hour;
                    }
                }
            }
        } else {
            $cleanRange = $range;
        }


        $cleanRange = array_unique($cleanRange);
        return $cleanRange;
    }

    public function availableMinutes()
    {
        $teacher = TeacherProfile::find($this->data['teacher']);
        $service = Service::find($this->data['service']);

        $range = [];
        if ($teacher && $service) {
            $user_id = $teacher->user_id;
            $availabilities = $service->availabilitiesForUser($user_id)
                ->where('start_hour', '<=', $this->data['time_hour'] ?? -1)
                ->where('end_hour', '>=', $this->data['time_hour'] ?? -1)
                ->where('day_of_week', '=', $this->getSelectedDate()->dayOfWeek)
                ->get();
            foreach ($availabilities as $availability) {
                $range = array_merge($range, range($availability->start_minute, $availability->end_minute));
            }
        }

        $range = array_unique($range);

        if (!$this->data['time_minute'] || !in_array($this->data['time_minute'], $range)) {
            $this->data['time_minute'] = $range[0] ?? null;
        }
        $appointments = $teacher->appointments()
            ->where('service_id', '=', $service->id)
            ->where(\DB::raw('YEAR(date)'), '=', $this->getSelectedDate()->year)
            ->where(\DB::raw('MONTH(date)'), '=', $this->getSelectedDate()->month)
            ->where(\DB::raw('DAY(date)'), '=', $this->getSelectedDate()->day)
            ->where(\DB::raw('HOUR(date)'), '=', $this->getSelectedDate()->hour - (int)($service->duration / 60))
            ->get();

        $cleanRange = [];

        if (count($appointments) > 0) {
            foreach ($appointments as $appointment) {
                $end = ($appointment->date->minute + ($appointment->service->duration % 60));
                foreach ($range as $minute) {
                    if ($minute >= $end) {
                        $cleanRange[] = $minute;
                    }
                }
            }
        } else {
            $cleanRange = $range;
        }

        $cleanRange = array_unique($cleanRange);
        return $cleanRange;
    }

    private function refreshAvailableDays()
    {
        $teacher = TeacherProfile::find($this->data['teacher']);
        $service = Service::find($this->data['service']);

        if ($teacher && $service) {
            $user_id = $teacher->user_id;
            $availabilities = $service->availabilitiesForUser($user_id)->pluck('day_of_week');
            $this->availableDays = $availabilities;
        } else {
            $this->availableDays = [];
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
