<x-mail::message>
# 🔔 Event Reminder

Hello **{{ $guestName }}**,

This is a friendly reminder that **{{ $eventTitle }}** is happening tomorrow!

## Event Details

**📅 Date:** {{ \Carbon\Carbon::parse($eventDate)->format('l, F j, Y') }}
**⏰ Time:** {{ $eventTime ? \Carbon\Carbon::parse($eventTime)->format('g:i A') : 'TBA' }}
@if($event->mode === 'online' || $event->mode === 'hybrid')
**🌐 Platform:** {{ $event->platform_name }}
@if($event->online_link)
**🔗 Meeting Link:** [{{ $event->online_link }}]({{ $event->online_link }})
@endif
@endif
@if($venue && $venue->name)
**📍 Location:** {{ $venue->name }}
@if($venue->address)
{{ $venue->address }}
@endif
@endif

## Your RSVP Information

**Guest Code:** `{{ $guestCode }}`  
**Email:** {{ $rsvp->email }}

<x-mail::panel>
**Important:** Please bring your guest code or show this email at the event for entry.
</x-mail::panel>

## What to Expect

- **Arrive early:** We recommend arriving 15-30 minutes before the start time
- **Bring your guest code:** You'll need this for check-in
- **Contact info:** If you have any last-minute questions, reach out to the organizer

@if($organizer && $organizer->email)
**Event Organizer:** {{ $organizer->name }}  
**Contact:** {{ $organizer->email }}
@endif

<x-mail::button :url="route('events.show', $event->slug)">
View Event Details
</x-mail::button>

Can't make it? Please let the organizer know as soon as possible so they can offer your spot to someone else.

Looking forward to seeing you tomorrow!

Best regards,  
The {{ config('app.name') }} Team
</x-mail::message>
