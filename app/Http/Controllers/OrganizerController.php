<?php

namespace App\Http\Controllers;

use App\Models\Organizer;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Inertia\Inertia;

class OrganizerController extends Controller
{
    /**
     * Display the specified organizer's profile with events
     */
    public function show(Request $request, Organizer $organizer)
    {
        // Load organizer with events and their relationships
        $organizer->load([
            'events' => function ($query) {
                $query->with(['ticketTypes'])
                    ->whereIn('status', ['published', 'live'])
                    ->orderBy('start_date', 'asc')
                    ->orderBy('start_time', 'asc');
            }
        ]);

        // Get upcoming events for calendar
        $upcomingEvents = $organizer->events()
            ->with(['ticketTypes'])
            ->whereIn('status', ['published', 'live'])
            ->where('start_date', '>=', now()->toDateString())
            ->orderBy('start_date', 'asc')
            ->orderBy('start_time', 'asc')
            ->limit(10)
            ->get()
            ->map(function ($event) {
                return [
                    'id' => $event->id,
                    'title' => $event->title,
                    'slug' => $event->slug,
                    'start_date' => $event->start_date,
                    'start_time' => $event->start_time,
                    'end_date' => $event->end_date,
                    'end_time' => $event->end_time,
                    'venue_name' => $event->venue_name,
                    'venue_address' => $event->venue_address,
                    'cover_image_url' => $event->cover_image_url,
                    'ticket_types_count' => $event->ticketTypes->count(),
                    'min_price' => $event->ticketTypes->min('price'),
                ];
            });

        // Prepare calendar data for upcoming events
        $calendarEvents = $upcomingEvents->map(function ($event) {
            return [
                'id' => $event['id'],
                'title' => $event['title'],
                'start' => $event['start_date'],
                'url' => route('events.show', $event['slug'] ?? $event['id']),
                'color' => '#ff6b35', // Primary color
            ];
        });

        // Get event statistics
        $stats = [
            'total_events' => $organizer->events()->count(),
            'upcoming_events' => $organizer->events()
                ->whereIn('status', ['published', 'live'])
                ->where('start_date', '>=', now()->toDateString())
                ->count(),
            'past_events' => $organizer->events()
                ->where('status', 'completed')
                ->orWhere(function ($query) {
                    $query->whereIn('status', ['published', 'live'])
                        ->where('start_date', '<', now()->toDateString());
                })
                ->count(),
            'total_venues' => "",
        ];

        // Format events for timeline view
        $timelineEvents = $organizer->events->map(function ($event) {
            return [
                'id' => $event->id,
                'title' => $event->title,
                'slug' => $event->slug,
                'description' => $event->description,
                'start_date' => $event->start_date,
                'start_time' => $event->start_time,
                'end_date' => $event->end_date,
                'end_time' => $event->end_time,
                'status' => $event->status,
                'cover_image_url' => $event->cover_image_url,
                    'venue_name' => $event->venue_name,
                    'venue_address' => $event->venue_address,
                'ticket_types' => $event->ticketTypes->map(function ($type) {
                    return [
                        'name' => $type->name,
                        'price' => $type->price,
                        'available_quantity' => $type->available_quantity,
                    ];
                }),
                'is_upcoming' => Carbon::parse($event->start_date)->isFuture(),
                'formatted_date' => Carbon::parse($event->start_date)->format('M d, Y'),
                'formatted_time' => $event->start_time ? Carbon::parse($event->start_time)->format('g:i A') : null,
            ];
        });

        return Inertia::render('OrganizerProfile', [
            'organizer' => array_merge($organizer->toArray(), [
                'logo_url' => $organizer->logo_url,
                'banner_url' => $organizer->banner_url,
            ]),
            'events' => $timelineEvents,
            'upcomingEvents' => $upcomingEvents,
            'calendarEvents' => $calendarEvents,
            'stats' => $stats,
        ]);
    }

    /**
     * Store a subscription to an organizer
     */
    public function subscribe(Request $request, Organizer $organizer)
    {
        $request->validate([
            'email' => 'required|email|max:255',
            'name' => 'nullable|string|max:255',
        ]);

        // Check if already subscribed
        $existingSubscription = $organizer->subscriptions()
            ->where('email', $request->email)
            ->first();

        if ($existingSubscription) {
            return response()->json([
                'message' => 'You are already subscribed to this organizer.',
                'subscribed' => true,
            ], 422);
        }

        // Create subscription
        $organizer->subscriptions()->create([
            'email' => $request->email,
            'name' => $request->name,
            'subscribed_at' => now(),
        ]);

        return response()->json([
            'message' => 'Successfully subscribed! You will receive updates about new events.',
            'subscribed' => true,
        ]);
    }

    /**
     * Remove subscription from an organizer
     */
    public function unsubscribe(Request $request, Organizer $organizer)
    {
        $request->validate([
            'email' => 'required|email',
        ]);

        $deleted = $organizer->subscriptions()
            ->where('email', $request->email)
            ->delete();

        if ($deleted) {
            return response()->json([
                'message' => 'Successfully unsubscribed.',
                'subscribed' => false,
            ]);
        }

        return response()->json([
            'message' => 'Subscription not found.',
            'subscribed' => false,
        ], 404);
    }
}
