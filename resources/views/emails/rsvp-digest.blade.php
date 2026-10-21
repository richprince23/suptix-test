<x-mail::message>
# New RSVPs for Your Events

Hi {{ $organizer->name }},

You have **{{ $totalRsvps }}** new {{ Str::plural('RSVP', $totalRsvps) }} across **{{ $eventCount }}** {{ Str::plural('event', $eventCount) }}!

@foreach($rsvpsByEvent as $eventId => $notifications)
@php
    $event = $notifications->first()->event;
    $rsvpCount = $notifications->count();
    $totalRsvpsForEvent = $event->rsvps()->count();
    $capacity = $event->venue_capacity;
@endphp

---

## {{ $event->title }}

**{{ $rsvpCount }}** new {{ Str::plural('RSVP', $rsvpCount) }} • **Total RSVPs:** {{ $totalRsvpsForEvent }}@if($capacity) / {{ number_format($capacity) }} capacity @endif

**New Guests:**
@foreach($notifications as $notification)
- {{ $notification->rsvp->guest_name }} ({{ $notification->rsvp->email }})@if($notification->rsvp->phone), {{ $notification->rsvp->phone }}@endif - {{ $notification->rsvp->created_at->format('M j, g:i A') }}
@endforeach

<x-mail::button :url="config('app.url') . '/admin/' . $organizer->id . '/rsvps?event_id=' . $event->id">
View All RSVPs for {{ $event->title }}
</x-mail::button>

@endforeach

---

<x-mail::button :url="config('app.url') . '/admin/' . $organizer->id . '/rsvps'">
View All RSVPs
</x-mail::button>

You're receiving this digest because you have RSVP notifications set to **{{ ucfirst($organizer->rsvp_notification_frequency) }}**. You can change this preference in your organizer settings.

Thanks,<br>
{{ config('app.name') }} Team
</x-mail::message>
