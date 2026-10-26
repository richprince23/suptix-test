<?php

namespace App\Http\Controllers;

use App\Models\Event;
use App\Services\SeoService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;
use Inertia\Response;

class CheckInController extends Controller
{
    /**
     * Show the attendee check-in page
     */
    public function index(Request $request): Response
    {
        $eventId = $request->query('event');
        $selectedEvent = null;

        if ($eventId) {
            $event = Event::with('organizer')->find($eventId);

            // Verify the user has access to this event
            $user = Auth::user();
            if ($event && $user->organizers->contains('id', $event->organizer_id)) {
                $selectedEvent = [
                    'id' => $event->id,
                    'title' => $event->title,
                    'start_date' => $event->start_date,
                    'start_time' => $event->start_time,
                    'venue_name' => $event->venue_name,
                    'venue_address' => $event->venue_address,
                    'organizer' => $event->organizer ? [
                        'id' => $event->organizer->id,
                        'name' => $event->organizer->name,
                    ] : null,
                    'is_rsvp' => $event->is_rsvp,
                ];
            }
        }

        SeoService::setAttendeeCheckInSEO();

        return Inertia::render('AttendeeCheckIn', [
            'selectedEvent' => $selectedEvent,
            'seo' => SeoService::attendeeCheckIn(),
        ]);
    }
}
