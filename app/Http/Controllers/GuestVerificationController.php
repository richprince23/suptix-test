<?php

namespace App\Http\Controllers;

use App\Models\Event;
use App\Models\Organizer;
use App\Models\RSVP;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class GuestVerificationController extends Controller
{
    public function guestList(Organizer $organizer, Event $event): JsonResponse
    {
        // Check if the event belongs to the organizer
        if ($event->organizer_id !== $organizer->id) {
            return response()->json([
                'success' => false,
                'message' => 'Event does not belong to this organizer',
            ], 403);
        }

        // Check if the user has access to this organizer
        $user = Auth::user();
        if (! $user || ! $user->organizers->contains('id', $organizer->id)) {
            return response()->json([
                'success' => false,
                'message' => 'You are not authorized to view the guest list for this event',
            ], 403);
        }

        $event->load('organizer:id,name');

        $rsvps = RSVP::with(['checkedInBy:id,name'])
            ->where('event_id', $event->id)
            ->get()
            ->each(fn (RSVP $rsvp) => $rsvp->setRelation('event', $event))
            ->map(fn (RSVP $rsvp) => $this->formatRsvpResponse($rsvp))
            ->values();

        return response()->json([
            'success' => true,
            'data' => $rsvps,
        ]);
    }

    /**
     * Verify guest code for RSVP access
     */
    public function verifyGuestCode(Request $request): JsonResponse
    {
        $request->validate([
            'guest_code' => 'required|string',
            'guest_identifier' => 'sometimes|string', // email or name
        ]);

        try {
            // Find RSVP by guest code
            $rsvp = RSVP::with([
                'event' => function ($query) {
                    $query->select('id', 'title', 'start_date', 'start_time', 'venue_name', 'venue_address', 'organizer_id', 'code');
                },
                'event.organizer' => function ($query) {
                    $query->select('id', 'name');
                },
                'checkedInBy' => function ($query) {
                    $query->select('id', 'name');
                },
            ])->where('guest_code', strtoupper($request->guest_code))->first();

            if (! $rsvp) {
                return response()->json([
                    'success' => false,
                    'message' => 'Invalid guest code',
                ], 404);
            }

            // Optional: Additional verification with guest identifier
            if ($request->has('guest_identifier')) {
                $identifier = $request->guest_identifier;
                if (strtolower($rsvp->email) !== strtolower($identifier) &&
                    strtolower($rsvp->guest_name) !== strtolower($identifier)) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Guest code does not match the provided information',
                    ], 403);
                }
            }

            return response()->json([
                'success' => true,
                'message' => 'Guest verified successfully',
                'data' => [
                    'rsvp' => $this->formatRsvpResponse($rsvp),
                    'guest_access' => true,
                ],
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error verifying guest: '.$e->getMessage(),
            ], 500);
        }
    }

    /**
     * Verify guest for specific event using event code
     */
    public function verifyGuestForEvent(Request $request, string $eventCode): JsonResponse
    {
        $request->validate([
            'guest_identifier' => 'required|string', // email or name
        ]);

        try {
            // Find event by event code
            $event = Event::with([
                'organizer' => function ($query) {
                    $query->select('id', 'name');
                },
            ])->where('code', strtoupper($eventCode))->first();

            if (! $event) {
                return response()->json([
                    'success' => false,
                    'message' => 'Event not found',
                ], 404);
            }

            // Check if event is RSVP-based
            if (! $event->is_rsvp) {
                return response()->json([
                    'success' => false,
                    'message' => 'This event does not support RSVP guest verification',
                ], 400);
            }

            // Find RSVPs for this guest and event
            $rsvps = RSVP::with(['checkedInBy' => function ($query) {
                $query->select('id', 'name');
            }])
                ->where('event_id', $event->id)
                ->where(function ($query) use ($request) {
                    $identifier = $request->guest_identifier;
                    $query->where('email', 'LIKE', '%'.$identifier.'%')
                        ->orWhere('guest_name', 'LIKE', '%'.$identifier.'%');
                })
                ->get();

            if ($rsvps->isEmpty()) {
                return response()->json([
                    'success' => false,
                    'message' => 'No RSVP found for this guest at this event',
                ], 404);
            }

            return response()->json([
                'success' => true,
                'message' => 'Guest verified successfully',
                'data' => [
                    'event' => [
                        'id' => $event->id,
                        'title' => $event->title,
                        'start_date' => $event->start_date,
                        'start_time' => $event->start_time,
                        'venue_name' => $event->venue_name,
                        'venue_address' => $event->venue_address,
                        'organizer' => $event->organizer ? [
                            'name' => $event->organizer->name,
                        ] : null,
                    ],
                    'rsvps' => $rsvps->map(function ($rsvp) use ($event) {
                        return [
                            'id' => $rsvp->id,
                            'event_id' => $event->id,
                            'organizer_id' => $event->organizer_id,
                            'created_at' => $rsvp->created_at,
                            'guest_code' => $rsvp->guest_code,
                            'guest_name' => $rsvp->guest_name,
                            'email' => $rsvp->email,
                            'phone' => $rsvp->phone,
                            'status' => $rsvp->status,
                            'checked_in' => $rsvp->checked_in,
                            'checked_in_at' => $rsvp->checked_in_at,
                            'checked_in_by' => $rsvp->checkedInBy ? [
                                'name' => $rsvp->checkedInBy->name,
                            ] : null,
                            'notes' => $rsvp->notes,
                            'referral_source' => $rsvp->referral_source,
                        ];
                    }),
                    'guest_access' => true,
                ],
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error verifying guest: '.$e->getMessage(),
            ], 500);
        }
    }

    /**
     * Check in an RSVP guest (requires authentication)
     */
    public function checkInGuest(Request $request): JsonResponse
    {
        $request->validate([
            'guest_code' => 'required|string',
        ]);

        if (! Auth::check()) {
            return response()->json([
                'success' => false,
                'message' => 'Authentication required to check in guests',
            ], 401);
        }

        try {
            $rsvp = RSVP::with(['event.organizer'])
                ->where('guest_code', strtoupper($request->guest_code))
                ->first();

            if (! $rsvp) {
                return response()->json([
                    'success' => false,
                    'message' => 'Invalid guest code',
                ], 404);
            }

            // Check if user is authorized for this event's organizer
            $user = Auth::user();
            if (! $user->organizers->contains('id', $rsvp->event->organizer_id)) {
                return response()->json([
                    'success' => false,
                    'message' => 'You are not authorized to check in guests for this event',
                ], 403);
            }

            if (! $rsvp->canCheckIn()) {
                $message = $rsvp->checked_in
                    ? 'Guest is already checked in'
                    : 'Guest status does not allow check-in';

                return response()->json([
                    'success' => false,
                    'message' => $message,
                    'rsvp' => $this->formatRsvpResponse($rsvp),
                ], 400);
            }

            $rsvp->markAsCheckedIn($user->id);
            $rsvp->load(['checkedInBy']);

            $formatted = $this->formatRsvpResponse($rsvp);

            return response()->json([
                'success' => true,
                'message' => 'Guest checked in successfully',
                // 'rsvp' is kept at the top level for older app builds; new clients read data.rsvp
                'rsvp' => $formatted,
                'data' => [
                    'rsvp' => $formatted,
                ],
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error checking in guest: '.$e->getMessage(),
            ], 500);
        }
    }

    /**
     * Get event RSVP statistics
     */
    public function getEventRsvpStatistics(Request $request, string $eventCode): JsonResponse
    {
        if (! Auth::check()) {
            return response()->json([
                'success' => false,
                'message' => 'Authentication required',
            ], 401);
        }

        try {
            $event = Event::where('code', strtoupper($eventCode))->first();

            if (! $event) {
                return response()->json([
                    'success' => false,
                    'message' => 'Event not found',
                ], 404);
            }

            // Check authorization
            $user = Auth::user();
            if (! $user->organizers->contains('id', $event->organizer_id)) {
                return response()->json([
                    'success' => false,
                    'message' => 'You are not authorized to view statistics for this event',
                ], 403);
            }

            $totalRsvps = RSVP::where('event_id', $event->id)->count();
            $attendingRsvps = RSVP::where('event_id', $event->id)->where('status', 'attending')->count();
            $checkedInRsvps = RSVP::where('event_id', $event->id)->where('checked_in', true)->count();
            $maybeRsvps = RSVP::where('event_id', $event->id)->where('status', 'maybe')->count();
            $notAttendingRsvps = RSVP::where('event_id', $event->id)->where('status', 'not_attending')->count();

            return response()->json([
                'success' => true,
                'data' => [
                    'total_rsvps' => $totalRsvps,
                    'attending_rsvps' => $attendingRsvps,
                    'checked_in_rsvps' => $checkedInRsvps,
                    'maybe_rsvps' => $maybeRsvps,
                    'not_attending_rsvps' => $notAttendingRsvps,
                    'check_in_rate' => $attendingRsvps > 0 ? round(($checkedInRsvps / $attendingRsvps) * 100, 2) : 0,
                ],
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error retrieving statistics: '.$e->getMessage(),
            ], 500);
        }
    }

    /**
     * Format RSVP response for consistent API output
     */
    private function formatRsvpResponse(RSVP $rsvp): array
    {
        return [
            'id' => $rsvp->id,
            'event_id' => $rsvp->event_id,
            'organizer_id' => $rsvp->event?->organizer_id,
            'guest_code' => $rsvp->guest_code,
            'guest_name' => $rsvp->guest_name,
            'email' => $rsvp->email,
            'phone' => $rsvp->phone,
            'status' => $rsvp->status,
            'checked_in' => $rsvp->checked_in,
            'checked_in_at' => $rsvp->checked_in_at,
            'checked_in_by' => $rsvp->checkedInBy ? [
                'name' => $rsvp->checkedInBy->name,
            ] : null,
            'notes' => $rsvp->notes,
            'referral_source' => $rsvp->referral_source,
            'created_at' => $rsvp->created_at,
            'event' => $rsvp->event ? [
                'id' => $rsvp->event->id,
                'title' => $rsvp->event->title,
                'start_date' => $rsvp->event->start_date,
                'start_time' => $rsvp->event->start_time,
                'venue_name' => $rsvp->event->venue_name,
                'venue_address' => $rsvp->event->venue_address,
                'organizer' => $rsvp->event->organizer ? [
                    'id' => $rsvp->event->organizer->id,
                    'name' => $rsvp->event->organizer->name,
                ] : null,
            ] : null,
        ];
    }
}
