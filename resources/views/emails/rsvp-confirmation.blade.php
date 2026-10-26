<x-mail::message>
# 🎉 RSVP Confirmed!

Hello **{{ $guestName }}**,

Thank you for reserving your spot at **{{ $eventTitle }}**! We're excited to have you join us.

## Event Details

**Date:** {{ \Carbon\Carbon::parse($eventDate)->format('l, F j, Y') }}

**Time:** {{ $eventTime ? \Carbon\Carbon::parse($eventTime)->format('g:i A') : 'TBA' }}

@if($event->mode === 'online' || $event->mode === 'hybrid')
**Platform:** {{ $event->platform_name }}

@if($event->online_link)
**Meeting Link:** [{{ $event->online_link }}]({{ $event->online_link }})
@endif
@endif

@if($event->venue_name)
**Location:** {{ $event->venue_name }}

@if($event->venue_address)
{{ $event->venue_address }}
@endif
@endif

## Your RSVP Information

**Guest Code:** `{{ $guestCode }}`

**Email:** {{ $rsvp->email }}

@if($rsvp->phone)
**Phone:** {{ $rsvp->phone }}
@endif

<x-mail::panel>
**Important:** Please bring your guest code or show this email at the event for entry.
</x-mail::panel>

@if($responses && $responses->count() > 0)
## Your Responses

@foreach($responses as $response)
**{{ $response->field->label }}:** {{ $response->value }}
@endforeach

@endif

@if($event->description)
## About This Event

{!! $event->description !!}
@endif

<x-mail::button :url="route('events.show', $event->slug)">
View Event Details
</x-mail::button>

## Need Help?

If you have any questions or need to make changes to your RSVP, please contact the event organizer:

@if($organizer && $organizer->email)
**{{ $organizer->name }}**
Email: {{ $organizer->email }}
@endif

---

We look forward to seeing you at the event!

Best regards,
The {{ config('app.name') }} Team
</x-mail::message>
