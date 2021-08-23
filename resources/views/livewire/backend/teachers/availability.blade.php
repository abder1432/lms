<div>
    <style>
        .availability-modal {
            position: absolute;
            top: 0;
            bottom: 0;
            left: 0;
            right: 0;
            background: rgba(0, 0, 0, .35);
            display: flex;
            align-items: start;
            justify-content: center;
        }

    </style>

    @foreach(App\Helpers\General\Calendar::daysOfWeek() as $dayOfWeekIndex => $dayOfWeek)
        <div class="card">
            <div class="card-header d-flex justify-content-between">
                <strong>{{ $dayOfWeek }}</strong>
                <strong><i wire:click="addWorkHour({{$dayOfWeekIndex}})" class="fa fa-plus" style="cursor:pointer;"></i></strong>
            </div>
            <div class="card-body">
                @php($availabilites = $this->getAvailabilitiesByDay($dayOfWeekIndex))
                @if(count($availabilites) > 0)
                    <div class="list-group">
                        @foreach($availabilites as $availability)
                            <div class="list-group-item d-flex justify-content-between align-items-center">
                                <small>
                                    <i class="fa fa-clock-o"></i>
                                    @lang('labels.backend.availabilities.from')
                                    <span class="badge badge-primary">{{ $availability->formattedStartTime() }}</span>
                                    <i class="fa fa-arrow-right"></i>
                                    @lang('labels.backend.availabilities.to')
                                    <span class="badge badge-primary">{{ $availability->formattedEndTime() }}</span>
                                    <br>
                                    @lang('labels.backend.services.title')
                                    @foreach($availability->services as $service)
                                        <span class="badge badge-info">{{ $service->title }}</span>
                                    @endforeach
                                </small>
                                <button class="btn" type="button" wire:click="remove({{ $availability->id }})"><i
                                            class="fa fa-trash"></i></button>
                            </div>
                        @endforeach
                    </div>
                @else
                    <p class="font-italic">No work hours available</p>
                @endif
            </div>
        </div>
    @endforeach

    @if($showModal)
        <div class="availability-modal rounded">
            <div class="availability-form col-xl-6 col-md-8 col-sm-10 bg-light m-1 m-auto py-3 rounded shadow">
                <div class="availability-header d-flex justify-content-between m-2 mb-4">
                    <h3>@lang('labels.backend.availabilities.working_hours')</h3>
                    <strong><i wire:click="hideModal()" class="fa fa-times" style="cursor:pointer;"></i></strong>
                </div>
                <div class="row bg-white m-2 p-2 rounded">
                    <div class="badge badge-info col-12 mb-4 mt-2 p-3 text-center">
                        <span>{{ $this->selectedDayOfWeekName }}</span>
                        <span>@lang('labels.backend.availabilities.from')
                            {{ str_pad($this->data['from_hour'],2,'0', STR_PAD_LEFT) }}:{{
                            str_pad($this->data['from_minute'],2,'0', STR_PAD_LEFT) }}</span>
                        <span>@lang('labels.backend.availabilities.to')
                            {{ str_pad($this->data['to_hour'],2,'0', STR_PAD_LEFT) }}:{{
                            str_pad($this->data['to_minute'],2,'0', STR_PAD_LEFT) }}</span>
                    </div>
                    <div class="form-group px-5 col-md-6">
                        <label class="font-weight-bold" for="from_time">
                            @lang('labels.backend.availabilities.from')</label>
                        <div class="d-flex align-items-center">
                            <div class="m-2"><i class="fa fa-clock-o font-2xl"></i></div>
                            <select wire:model="data.from_hour" name="from_time" id="from_time"
                                    class="border rounded p-1 m-1" wire:loading.attr="disabled">
                                @foreach(range(0,23) as $hour)
                                    <option value="{{ $hour }}">{{ str_pad($hour,2,'0', STR_PAD_LEFT) }}</option>
                                @endforeach
                            </select>
                            <strong>:</strong>
                            <select wire:model="data.from_minute" name="from_time" id="from_time"
                                    class="border rounded p-1 m-1" wire:loading.attr="disabled">
                                @foreach(range(0,59, 5) as $minute)
                                    <option value="{{ $minute }}">{{ str_pad($minute,2,'0', STR_PAD_LEFT) }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    <div class="form-group px-5 col-md-6">
                        <label class="font-weight-bold" for="to_time">
                            @lang('labels.backend.availabilities.to')</label>
                        <div class="d-flex align-items-center">
                            <div class="m-2"><i class="fa fa-clock-o font-2xl"></i></div>
                            <select wire:model="data.to_hour" name="to_time" id="to_time"
                                    class="border rounded p-1 m-1" wire:loading.attr="disabled">
                                <option value=""></option>
                                @foreach(range(0,23) as $hour)
                                    @if($this->isToHourValid($hour))
                                        <option value="{{ $hour }}">
                                            {{ str_pad($hour,2,'0', STR_PAD_LEFT) }}
                                        </option>
                                    @endif
                                @endforeach
                            </select>
                            <strong>:</strong>
                            <select wire:model="data.to_minute" name="to_time" id="to_time" wire:loading.attr="disabled"
                                    class="border rounded p-1 m-1">
                                <option value=""></option>
                                @foreach(range(0,59, 5) as $minute)
                                    @if($this->isToMinuteValid($minute))
                                        <option value="{{ $minute }}">
                                            {{ str_pad($minute,2,'0', STR_PAD_LEFT) }}
                                        </option>
                                    @endif
                                @endforeach
                            </select>
                        </div>
                    </div>
                    <div class="form-group px-5 col-md-12 mt-4">
                        <p class="font-weight-bold">
                            @lang('labels.backend.services.title')</p>
                        @if(count($services) > 0)
                            <div class="d-flex">
                                @foreach($services as $service)
                                    <span class="form-group d-flex justify-content-center align-items-center m-2">
                                    <label for="services_{{ $service->id}}">
                                        <input type="checkbox" wire:model="data.services"
                                               wire:loading.attr="disabled"
                                               id="services_{{ $service->id}}"
                                               value="{{ $service->id}}">
                                    {{ $service->title }}</label>
                                </span>
                                @endforeach
                            </div>
                        @else
                            <p class="font-italic text-sm">No services are available</p>
                        @endif
                    </div>

                    <div class="form-group col-md-12 mt-4 text-right">
                        <button type="button" wire:click="hideModal()" class="btn btn-light"
                                wire:loading.attr="disabled">@lang('buttons.general.cancel')</button>
                        <button type="button" class="btn btn-primary"
                                wire:loading.attr="disabled"
                                @if( !$this->canSave() )
                                disabled
                                @endif
                                wire:click="save()">
                            @lang('buttons.general.save')

                        </button>

                    </div>
                </div>
            </div>
        </div>
    @endif

</div>
