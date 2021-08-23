@component('mail::message')
Dear  **{{$content['full_name'] }}**

You have successfully scheduled **{{ $content['service_name']  }}** appointment with {{ $content['teacher_name']  }}.
We are waiting for you at {{ $content['date']  }}

@if($content['is_online'])
@lang('labels.backend.appointments.fields.location') @lang('labels.backend.appointments.fields.online')

@component('mail::button', ['url' => $content['meeting_join_url'] ])
Join Zoom Meeting
@endcomponent

Zoom Meeting ID **{{ $content['meeting_id']  }}** <br>
Appointment password **{{ $content['meeting_password']  }}** <br>
@else
@lang('labels.backend.appointments.fields.location') @lang('labels.backend.appointments.fields.on_site')

{{ $content['location_address'] }}

{{ $content['location_phone_number'] }}

{{ $content['location_description'] }}

@component('mail::button', ['url' => 'https://www.google.com/maps/@'.$content['location_latitude'].','.$content['location_longitude'].',16z' ])
    view on map
@endcomponent

@endif


Thank you for choosing our company,
{{ config('app.name') }}
@endcomponent
