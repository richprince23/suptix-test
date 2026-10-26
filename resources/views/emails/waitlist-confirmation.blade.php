<x-mail::message>
# You're on the Waitlist

Hello **{{ $guestName }}**,

Thank you for your interest in **{{ $eventTitle }}**! The event has reached its current capacity, but you've been added to the waitlist.

## Event Details

**Date:** {{ \Carbon\Carbon::parse($eventDate)->format('l, F j, Y') }}
**Time:** {{ $eventTime ? \Carbon\Carbon::parse($eventTime)->format('g:i A') : 'TBA' }}
@if($event->venue_name)
**Location:** {{ $event->venue_name }}
@endif

## Your Waitlist Position

<x-mail::panel>
You are **#{{ $position }}** on the waitlist. If a spot opens up, the organizer will review and approve your registration — you'll receive a confirmation email with your entry details.
</x-mail::panel>

**What happens next?**

- The organizer will review waitlisted guests as slots become available
- If approved, you'll receive a separate confirmation email with your guest code
- No action is needed from you at this time

## Need Help?

@if($organizer && $organizer->email)
**{{ $organizer->name }}**
Email: {{ $organizer->email }}
@endif

---

Best regards,
The {{ config('app.name') }} Team
</x-mail::message>
