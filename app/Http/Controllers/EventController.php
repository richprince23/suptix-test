<?php

namespace App\Http\Controllers;

use App\Http\Resources\EventResource;
use App\Models\Event;
use App\Models\Organizer;
use App\Services\SeoService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;
use Spatie\QueryBuilder\AllowedFilter;
use Spatie\QueryBuilder\QueryBuilder;

class EventController extends Controller
{
    /**
     * Display a listing of published events
     */
    public function index(Request $request)
    {
        // Build the base query with Spatie Query Builder
        $events = QueryBuilder::for(Event::class)
            ->with(['organizer'])
            ->allowedFilters([
                AllowedFilter::partial('search', 'title'),
                AllowedFilter::partial('search_description', 'description'),
                AllowedFilter::callback('search_global', function ($query, $value) {
                    $query->where(function ($q) use ($value) {
                        $q->where('title', 'like', "%{$value}%")
                            ->orWhere('description', 'like', "%{$value}%")
                            ->orWhere('tags', 'like', "%{$value}%");
                    });
                }),
                AllowedFilter::exact('event_type'),
                AllowedFilter::callback('category', function ($query, $value) {
                    if ($value === 'all') {
                        return $query;
                    }

                    return $query->where('event_type', $value);
                }),
                AllowedFilter::callback('status', function ($query, $value) {
                    if ($value === 'all' || empty($value)) {
                        return $query->whereIn('status', ['published', 'live']);
                    }
                    if (is_array($value)) {
                        return $query->whereIn('status', $value);
                    }

                    return $query->where('status', $value);
                }),
                AllowedFilter::callback('venue', function ($query, $value) {
                    $query->where('venue_name', 'like', "%{$value}%");
                }),
                AllowedFilter::callback('tags', function ($query, $value) {
                    if ($value === 'all') {
                        return $query;
                    }

                    return $query->where('tags', 'like', "%{$value}%");
                }),
                AllowedFilter::scope('date_from', 'fromDate'),
                AllowedFilter::scope('date_to', 'toDate'),
                AllowedFilter::callback('start_date', function ($query, $value) {
                    return $query->where('start_date', '>=', $value);
                }),
                AllowedFilter::callback('end_date', function ($query, $value) {
                    return $query->where('start_date', '<=', $value);
                }),
            ])
            ->allowedSorts(['title', 'start_date', 'start_time', 'created_at', 'event_type'])
            ->defaultSort('start_date', 'start_time')
            ->when(! $request->has('filter.status'), function ($query) {
                // Apply default status filter when no status filter is explicitly provided
                return $query->whereIn('status', ['published', 'live']);
            })
            ->paginate(12)
            ->withQueryString();

        // Get unique event types for filter dropdown, add 'All' option
        $eventTypes = collect(['all' => 'All Categories'])
            ->merge(
                Event::distinct()
                    ->pluck('event_type')
                    ->filter()
                    ->mapWithKeys(function ($type) {
                        return [$type => ucwords(str_replace('_', ' ', $type))];
                    })
            )
            ->toArray();

        // Get unique tags for filter dropdown, add 'All' option
        // Use a more PostgreSQL-compatible approach
        $allTags = Event::whereNotNull('tags')
            ->get()
            ->pluck('tags')
            ->filter(function ($tags) {
                // Handle both string '[]' and array [] cases
                if (is_string($tags)) {
                    return $tags !== '[]' && $tags !== '' && $tags !== null;
                }

                return is_array($tags) && count($tags) > 0;
            })
            ->flatten()
            ->filter() // Remove empty/null values
            ->unique()
            ->values()
            ->toArray();

        $tags = collect(['all' => 'All Tags'])
            ->merge(collect($allTags)->mapWithKeys(function ($tag) {
                return [$tag => ucwords($tag)];
            }))
            ->toArray();

        // Get available statuses for filter dropdown, add 'All' option
        $availableStatuses = [
            'all' => 'All Events',
            'published' => 'Published',
            'live' => 'Live',
            'past' => 'Past',
            'draft' => 'Draft',
            'canceled' => 'Canceled',
        ];

        // Get total events count for SEO
        $totalEvents = Event::whereIn('status', ['published', 'live'])->count();

        // Set events index SEO using SEOTools
        SeoService::setEventsIndexSEO($totalEvents);

        // Get current filters from request
        $currentFilters = collect($request->all())
            ->filter(function ($value, $key) {
                return str_starts_with($key, 'filter.');
            })
            ->mapWithKeys(function ($value, $key) {
                return [str_replace('filter.', '', $key) => $value];
            })
            ->toArray();

        return Inertia::render('EventsPage', [
            'events' => $events,
            'eventTypes' => $eventTypes,
            'tags' => $tags,
            'availableStatuses' => $availableStatuses,
            'filters' => array_merge([
                'search' => $request->get('search'),
                'category' => $request->input('filter.category'),
                'status' => $request->input('filter.status'),
                'venue' => $request->input('filter.venue'),
                'start_date' => $request->input('filter.start_date'),
                'end_date' => $request->input('filter.end_date'),
                'tags' => $request->input('filter.tags'),
            ], $currentFilters),
            'seo' => SeoService::eventsIndex($totalEvents),
        ]);
    }

    /**
     * Display events in calendar format
     */
    public function calendar(Request $request)
    {
        // Get the month and year from request (default to current month)
        $year = $request->get('year', now()->year);
        $month = $request->get('month', now()->month);

        // Calculate start and end dates for the calendar view (show full calendar month + adjacent days)
        $startDate = Carbon::create($year, $month, 1)->startOfMonth()->startOfWeek();
        $endDate = Carbon::create($year, $month, 1)->endOfMonth()->endOfWeek();

        $events = Event::query()
            ->with(['organizer'])
            ->whereBetween('start_date', [$startDate, $endDate])
            ->orderBy('start_date', 'asc')
            ->get()
            ->map(function ($event) {
                // Parse start_date and combine with start_time if available
                $startDate = Carbon::parse($event->start_date);
                if ($event->start_time) {
                    $startTime = Carbon::parse($event->start_time);
                    $startDate = $startDate->setTimeFromTimeString($startTime->format('H:i:s'));
                }

                // Parse end_date and combine with end_time if available
                $endDateTime = null;
                if ($event->end_date) {
                    $endDateTime = Carbon::parse($event->end_date);
                    if ($event->end_time) {
                        $endTime = Carbon::parse($event->end_time);
                        $endDateTime = $endDateTime->setTimeFromTimeString($endTime->format('H:i:s'));
                    }
                }

                return [
                    'id' => $event->id,
                    'title' => $event->title,
                    'start' => $startDate->toISOString(),
                    'end' => $endDateTime?->toISOString(),
                    'description' => $event->description,
                    'mode' => $event->mode,
                    'venue_name' => $event->venue_name,
                    'online_link' => $event->link,
                    'is_public_link' => $event->link,
                    'organizer' => $event->organizer?->name,
                    'event_type' => $event->event_type,
                    'status' => $event->status,
                    'cover_image_url' => $event->cover_image_url,
                    'url' => route('events.show', $event),
                ];
            });

        // Set calendar page SEO using SEOTools
        SeoService::setCalendarSEO();

        return Inertia::render('Calendar', [
            'events' => $events,
            'seo' => SeoService::calendar(),
            'currentYear' => (int) $year,
            'currentMonth' => (int) $month,
        ]);
    }

    /**
     * Show a single event
     */
    public function show(Event $event)
    {
        // Load relationships and filter ticket types by availability
        $event->load([
            'organizer',
            'sessions',
            'formFields',
            'people' => function ($query) {
                $query->where('is_visible', true);
            },
            'allPartners' => function ($query) {
                $query->where('is_visible', true);
            },
            'ticketTypes' => function ($query) {
                $query->available()->where('quantity', '>', 0);
            },
        ]);

        // Filter ticket types to only show those that are currently purchasable
        $availableTicketTypes = $event->ticketTypes->filter(function ($ticketType) {
            return $ticketType->isPurchasable() && $ticketType->available_quantity > 0;
        });

        // Ensure relationships are properly loaded for Inertia
        $mapKey = config('services.google_maps.map_key');
        $mapId = config('services.google_maps.map_id');

        SeoService::setEventSEO($event);

        $eventArray = $event->toArray();
        $eventArray['ticketTypes'] = $availableTicketTypes->values()->toArray();

        return Inertia::render('EventDetails', [
            'event' => $eventArray,
            'seo' => SeoService::eventShow($event),
            'map_key' => $mapKey,
            'map_id' => $mapId,
            'ticketTypes' => $availableTicketTypes->values()->toArray(),
        ]);
    }

    /**
     * Show a single event by ID (backward compatibility)
     */
    public function showById(int $id)
    {
        $event = Event::findOrFail($id);

        // Redirect to new slug-based URL for SEO
        return redirect()->route('events.show', $event->slug, 301);
    }

    // api
    public function getEvents(Organizer $organizer)
    {
        if (! $this->userBelongsToOrganizer($organizer)) {
            return response()->json([
                'success' => false,
                'message' => 'You are not part of this organization!',
            ], 403);
        }

        $events = $organizer->events()->with('organizer')->get();

        return EventResource::collection($events);
    }

    // api - read-only single event lookup for the scanner app
    public function getEvent(Organizer $organizer, Event $event)
    {
        if (! $this->userBelongsToOrganizer($organizer)) {
            return response()->json([
                'success' => false,
                'message' => 'You are not part of this organization!',
            ], 403);
        }

        if ($event->organizer_id !== $organizer->id) {
            return response()->json([
                'success' => false,
                'message' => 'Event does not belong to this organizer',
            ], 404);
        }

        return new EventResource($event->load('organizer'));
    }

    private function userBelongsToOrganizer(Organizer $organizer): bool
    {
        $user = Auth::user();

        return $user && $user->organizers->contains('id', $organizer->id);
    }
}
