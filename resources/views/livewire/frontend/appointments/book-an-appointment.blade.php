<div class="col-md-8 mx-auto m-4">
    <div wire:loading>
        <div class="d-flex align-items-center justify-content-center position-absolute top-0
        left-0 right-0 bottom-0 h-100 w-100 text-black rounded"
             style="z-index: 1;">
            <span><i class="fas fa-spinner fa-pulse"></i> loading...</span>
        </div>
    </div>
    <div class="card text-dark rounded" wire:loading.class="loading-container">
        <div class="card-header">
            <div class="lead font-weight-bold text-center">Book an appointment ({{$step}}/{{$this->totalSteps}})</div>
            <div class="d-flex align-items-center justify-content-center my-4">
                @foreach(range(1, $this->totalSteps) as $index)
                    <span class="d-flex flex-column align-items-center">
                        @if($step > $index)
                            <i class="fa fa-check-circle text-success" style="font-size:1.5rem;"></i>
                        @elseif($step === $index)
                            <i class="fas fa-dot-circle text-primary font-xl" style="font-size:1.5rem;"></i>
                        @else
                            <i class="far fa-circle text-primary" style="opacity: .6"></i>
                        @endif
                    </span>

                    @if(!$loop->last)
                        <div style="width: {{ 1/$this->totalSteps*100 }}%;
                                border-bottom:2px {{ $step > $index ? 'solid' : 'dashed' }};
                                opacity: .6;
                                "
                             class="border-{{ $step > $index ? 'success' : 'primary' }}"
                        ></div>
                    @endif
                @endforeach
            </div>
            <div class="d-flex align-items-center justify-content-around my-4">
                @foreach(range(1, $this->totalSteps) as $index)
                    <small class="{{ $step !== $index ? 'text-muted': 'font-weight-bold' }}">{{ $this->steps[$index] }}</small>
                    @if(!$loop->last)
                        <small><i class="fa fa-arrow-right text-muted"></i></small>
                    @endif
                @endforeach
            </div>
        </div>

        <div class="card-body">
            @if($this->isStep(1))
                <div id="step1">
                    @if($categories && count($categories) > 0)
                        <p class="text-center">
                            Please chose a category of services you want to browse
                        </p>
                        <div class="my-4 form-group">
                            <label for="category_id">Category</label>
                            <select id="category_id" class="form-control" wire:model="data.category">
                                @foreach($categories as $category)
                                    <option value="{{ $category->id }}">
                                        {{ $category->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                    @else
                        <p class="font-italic text-sm">No categories are available.</p>
                    @endif

                </div>
            @elseif ($this->isStep(2))
                <div id="step2">
                    <div class="my-4 form-group">
                        @if($services && count($services) > 0)
                            <label for="service_id">Service</label>
                            <select id="service_id" class="form-control" wire:model="data.service">
                                @foreach($services as $service)
                                    <option value="{{ $service->id }}">
                                        {{ $service->title }}
                                        <small>(@lang('labels.backend.services.fields.duration')
                                            : {{ $service->duration }}
                                            min(s) {{ $service->is_online ? '- ' . __('labels.backend.appointments.fields.online') : '- ' . __('labels.backend.appointments.fields.on_site') }}
                                            )</small>
                                    </option>
                                @endforeach
                            </select>
                        @else
                            <p class="font-italic text-sm">No services are available.</p>
                        @endif
                    </div>
                    <div class="my-4 form-group">
                        @if($teachers && count($teachers) > 0)
                            <label for="teacher_profile_id">Expert</label>
                            <select id="teacher_profile_id" class="form-control" wire:model="data.teacher">
                                @foreach($teachers as $teacher)
                                    <option value="{{ $teacher->id }}">{{ $teacher->user->full_name }}</option>
                                @endforeach
                            </select>
                        @else
                            <p class="font-italic text-sm">No experts are available.</p>
                        @endif
                    </div>
                </div>
            @elseif ($this->isStep(3))
                <div id="step3">
                    <div class="d-flex align-items-center justify-content-center">
                        @if(count($this->availableHours()) > 0)
                            <select class="border d-inline-block rounded text-center m-1 py-2"
                                    wire:model="data.time_hour"
                                    style="font-size: 2rem;">
                                @foreach(array_sort($this->availableHours()) as $value)
                                    <option value="{{ $value }}">
                                        {{ str_pad($value, 2,'0',STR_PAD_LEFT) }}
                                    </option>
                                @endforeach
                            </select>
                            <span>:</span>
                            <select class="border d-inline-block rounded text-center m-1 py-2"
                                    wire:model="data.time_minute"
                                    style="font-size: 2rem;">

                                @foreach(array_sort($this->availableMinutes()) as $value)
                                    <option value="{{ $value }}">
                                        {{ str_pad($value, 2,'0',STR_PAD_LEFT) }}
                                    </option>
                                @endforeach
                            </select>
                        @else
                            <span class="d-inline-block rounded text-center m-1 py-2">- - : - -</span>
                        @endif
                    </div>
                    <div>
                        @error('data.time_hour') <span class="error">{{ $message }}</span> @enderror
                        @error('data.time_minute') <span class="error">{{ $message }}</span> @enderror
                    </div>
                    <div id="myCalendarWrapper" data-teacher="1" data-service="1"></div>
                </div>
            @elseif ($this->isStep(4))
                <div id="step4">
                    <div class="d-flex align-items-center justify-content-around">
                        <small><span class="font-weight-bold">Expert:</span> {{ $teacher->user->full_name }}</small>
                        <small>
                            <span class="font-weight-bold">Date:</span> {{ $date->toDateString() }}
                        </small>
                        <small>
                            <span class="font-weight-bold">Time:</span> {{ $date->toTimeString("minute") }}
                        </small>
                    </div>
                    <div class="font-italic text-right my-3">

                    </div>
                    <hr>
                    <div class="d-flex align-items-start justify-content-between px-3 bg-light border rounded p-3">
                        <small><span class="font-weight-bold">Location:</span>
                            @if($service->is_online)
                                <span class="badge badge-success">@lang('labels.backend.appointments.fields.online') (zoom)</span>
                            @else
                                <span class="badge badge-warning">@lang('labels.backend.appointments.fields.on_site')</span>
                                <p>
                                    {{ $service->location_address }}<br>
                                    {{ $service->location_phone_number }}<br>
                                    {{ $service->location_description }}<br>
                                    GPS: (lat:{{ $service->location_latitude }}, lon:{{ $service->location_longitude }})
                                </p>
                            @endif
                        </small>
                        <small class="badge badge-info"><i class="far fa-clock"></i> {{ $date->diffForHumans() }}
                            - {{ strtolower(__('labels.backend.services.fields.duration')) }}
                            {{$service->duration}}min(s)</small>
                    </div>
                    <hr>
                    <h6 class="my-3">Personal information</h6>
                    <div class="row">
                        <div class="col-md-6 form-group">
                            <label for="first_name"><small>First Name</small></label>
                            <input style="font-size: .8rem" placeholder="First Name" class="form-control" type="text"
                                   id="first_name" name="first_name" wire:model.lazy="data.user_info.first_name">
                            @error('data.user_info.first_name')<p><small class="text-danger text">{{ $message }}</small>
                            </p>@enderror
                        </div>
                        <div class="col-md-6 form-group">
                            <label for="last_name"><small>Last Name</small></label>
                            <input style="font-size: .8rem" placeholder="Last Name" class="form-control" type="text"
                                   id="last_name" name="last_name" wire:model.lazy="data.user_info.last_name">
                            @error('data.user_info.last_name')<p><small class="text-danger text">{{ $message }}</small>
                            </p>@enderror
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6 form-group">
                            <label for="email"><small>Email</small></label>
                            <input style="font-size: .8rem" placeholder="Email" class="form-control" type="text"
                                   id="email" name="email" wire:model.lazy="data.user_info.email">
                            @error('data.user_info.email')<p><small class="text-danger text">{{ $message }}</small>
                            </p>@enderror
                        </div>
                        <div class="col-md-6 form-group">
                            <label for="phone"><small>Phone</small></label>
                            <input style="font-size: .8rem" placeholder="Phone" class="form-control" type="text"
                                   id="phone" name="phone" wire:model.lazy="data.user_info.phone">
                            @error('data.user_info.phone')<p><small class="text-danger text">{{ $message }}</small>
                            </p>@enderror
                        </div>
                    </div>
                    <hr>
                    <div class="d-flex align-items-center justify-content-end">
                        <span><span class="font-weight-bold">Price:</span> {{ $service->price }}$</span>
                    </div>
                    <hr>
                    @error('data.service')<p><small class="text-danger text">{{ $message }}</small></p>@enderror
                    @error('data.teacher')<p><small class="text-danger text">{{ $message }}</small></p>@enderror
                </div>
            @endif

        </div>

        <div class="card-footer d-flex justify-content-between">
            <button class="btn btn-light mx-1" wire:click="back()"
                    wire:loading.attr="disabled" {{ !$this->canGoBack() ? 'disabled' : '' }}>
                <i class="fa fa-arrow-left"></i> Back
            </button>
            @if(!$this->isFinalStep())
                <button class="btn btn-primary" wire:click="next()"
                        wire:loading.attr="disabled" {{ !$this->canGoNext() ? 'disabled' : '' }}>
                    Next <i class="fa fa-arrow-right"></i>
                </button>
            @else
                <button class="btn btn-success" wire:click="finish()"
                        wire:loading.attr="disabled">
                    Proceed to payment &nbsp;<small><i class="fas fa-external-link-alt"> </i></small>
                </button>
            @endif
        </div>
    </div>
</div>
