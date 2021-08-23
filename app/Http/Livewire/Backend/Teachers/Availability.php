<?php

namespace App\Http\Livewire\Backend\Teachers;

use App\Helpers\General\Calendar;
use App\Models\Service;
use Livewire\Component;

class Availability extends Component
{

    public $data = [
        'day_of_week' => Calendar::MONDAY,
        'from_hour'   => 0,
        'from_minute' => 0,
        'to_hour'     => null,
        'to_minute'   => null,
        'services'    => [],
    ];

    public $teacher;

    public $showModal = false;

    public $services = [];

    public function mount()
    {
        $this->init();
        $this->services = Service::all();
    }

    public function render()
    {
        return view('livewire.backend.teachers.availability');
    }

    public function init()
    {
        $this->data = [
            'day_of_week' => Calendar::MONDAY,
            'from_hour'   => 0,
            'from_minute' => 0,
            'to_hour'     => null,
            'to_minute'   => null,
            'services'    => [],
        ];
    }

    public function save()
    {
        $availability = \App\Models\Availability::make([
            'start_hour'   => $this->data['from_hour'],
            'end_hour'     => $this->data['to_hour'],
            'start_minute' => $this->data['from_minute'],
            'end_minute'   => $this->data['to_minute'],
            'day_of_week'  => $this->data['day_of_week'],
            'user_id'      => $this->teacher->id,
        ]);

        $availability->save();

        $availability->services()->sync($this->data['services']);

        $this->hideModal();
        $this->init();
    }

    public function remove($availabilityId)
    {
        $availability = \App\Models\Availability::find($availabilityId);
        if($availability) {
            $availability->delete();
        }
    }

    public function canSave()
    {
        return count($this->services)!==0
            && count($this->data['services']) > 0
            && !(is_null($this->data['to_hour']) || $this->data['to_hour'] === "" )
            && !(is_null($this->data['to_minute']) || $this->data['to_minute'] === "" )
            && !empty($this->data['day_of_week']);
    }

    public function addWorkHour($dayOfWeekNumber)
    {
        $this->data['day_of_week'] = $dayOfWeekNumber;
        $this->showModal();
    }

    public function getSelectedDayOfWeekNameProperty()
    {
        return Calendar::daysOfWeek()[ $this->data['day_of_week'] ];
    }

    public function showModal()
    {
        $this->showModal = true;
    }

    public function hideModal()
    {
        $this->showModal = false;
    }

    public function isToHourValid($hour)
    {
        return $this->data['from_hour'] <= $hour;
    }

    public function isToMinuteValid($minute)
    {
        return $this->data['from_hour'] !== $this->data['to_hour'] || $this->data['from_minute'] <= $minute;
    }

    public function getAvailabilitiesByDay($dayOfWeek)
    {
        return \App\Models\Availability::where('user_id', $this->teacher->id)
            ->where('day_of_week', $dayOfWeek)
            ->get();

    }
}
