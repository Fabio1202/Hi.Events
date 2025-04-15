@php /** @var \HiEvents\DomainObjects\EventDomainObject $event */ @endphp
@php /** @var \HiEvents\DomainObjects\EventSettingDomainObject $eventSettings */ @endphp
@php /** @var \HiEvents\DomainObjects\AttendeeDomainObject $attendee */ @endphp

<x-mail::message>
# {{ __('Your ticket for') }} {{ $event->getTitle() }} {{ __('has been canceled') }}
<br>
<br>

{{ __('Hello') }}, {{ $attendee->getFirstName() }}<br>

{{ __('We are sorry to hear that you have canceled your ticket for') }} {{ $event->getTitle() }}. <br>

{{ __('If you have any questions or concerns, please feel free to reach out to us at') }}
<a href="mailto:{{$eventSettings->getSupportEmail()}}">{{$eventSettings->getSupportEmail()}}</a>. <br>

{{ __('Best regards,') }}<br>
{{ config('app.name') }}

</x-mail::message>
