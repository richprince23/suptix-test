<x-mail::message>
# You're In!

Hello **{{ $guestName }}**,

Great news — your spot at **{{ $eventTitle }}** has been confirmed! The organizer has approved your registration.

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

<x-mail::button :url="route('events.show', $event->slug)">
View Event Details
</x-mail::button>

## Need Help?

@if($organizer && $organizer->email)
**{{ $organizer->name }}**
Email: {{ $organizer->email }}
@endif

---

We look forward to seeing you at the event!

Best regards,
The {{ config('app.name') }} Team
</x-mail::message>
