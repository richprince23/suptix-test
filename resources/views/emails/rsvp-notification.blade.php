<x-mail::message>
# 📋 New RSVP Received

Hello,

You have received a new RSVP for your event **{{ $eventTitle }}**.

## Guest Information

**Name:** {{ $guestName }}  
**Email:** {{ $rsvp->email }}
@if($rsvp->phone)
**Phone:** {{ $rsvp->phone }}
@endif
@if($rsvp->referral_source)
**How they heard about the event:** {{ $rsvp->referral_source }}
@endif
@if($rsvp->notes)
**Notes:** {{ $rsvp->notes }}
@endif

**Guest Code:** `{{ $rsvp->guest_code }}`

## Event Status

**Total RSVPs:** {{ $totalRsvps }}
@if($venueCapacity)
**Venue Capacity:** {{ $venueCapacity }}  
**Remaining Spots:** {{ max(0, $venueCapacity - $totalRsvps) }}

@if($totalRsvps >= $venueCapacity)
<x-mail::panel>
🚨 **Event is now at full capacity!** No more RSVPs will be accepted.
</x-mail::panel>
@elseif(($venueCapacity - $totalRsvps) <= 10)
<x-mail::panel>
⚠️ **Only {{ $venueCapacity - $totalRsvps }} spots remaining** for this event.
</x-mail::panel>
@endif
@endif

<x-mail::button :url="route('events.show', $event->slug)">
View Event Details
</x-mail::button>

## Next Steps

- The guest has been sent a confirmation email with their guest code
- You can manage all RSVPs through your organizer dashboard
- Remember to check in guests using their guest codes at the event

Best regards,  
{{ config('app.name') }} Team
</x-mail::message>
