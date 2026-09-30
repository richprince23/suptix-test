<?php

namespace App\Http\Controllers\Api\V2;

use App\Models\Event;
use Illuminate\Http\Request;
use App\Http\Resources\Api\V2\EventResource;
use App\Http\Resources\Api\V2\EventDetailResource;
use App\Http\Resources\Api\V2\TicketTypeResource;

class EventController extends BaseController
{
    public function index(Request $request)
    {
        $query = Event::query()
            ->with(['organizer'])
            ->where('status', 'published')
            ->where('start_date', '>=', now());

        if ($request->has('search')) {
            $search = $request->get('search');
            $query->where(function ($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                  ->orWhere('description', 'like', "%{$search}%")
                  ->orWhere('tags', 'like', "%{$search}%");
            });
        }

        if ($request->has('type')) {
            $query->where('type', $request->get('type'));
        }

        if ($request->has('organizer_id')) {
            $query->where('organizer_id', $request->get('organizer_id'));
        }

        if ($request->has('location')) {
            $location = $request->get('location');
            $query->where(function ($q) use ($location) {
                $q->where('venue_name', 'like', "%{$location}%")
                  ->orWhere('venue_address', 'like', "%{$location}%");
            });
        }

        $perPage = $request->get('per_page', 15);
        $events = $query->orderBy('start_date', 'asc')->paginate($perPage);

        return $this->successResponse([
            'events' => EventResource::collection($events->items()),
            'pagination' => [
                'current_page' => $events->currentPage(),
                'last_page' => $events->lastPage(),
                'per_page' => $events->perPage(),
                'total' => $events->total(),
                'has_more' => $events->hasMorePages(),
            ],
        ], 'Events retrieved successfully');
    }

    public function show(Event $event)
    {
        if ($event->status !== 'published') {
            return $this->notFoundResponse('Event not found');
        }

        $event->load(['organizer', 'ticketTypes' => function ($query) {
            $query->where('is_available', true);
        }]);

        return $this->successResponse(
            new EventDetailResource($event),
            'Event details retrieved successfully'
        );
    }

    public function getTickets(Event $event)
    {
        if ($event->status !== 'published') {
            return $this->notFoundResponse('Event not found');
        }

        $ticketTypes = $event->ticketTypes()
            ->where('is_available', true)
            ->get();

        return $this->successResponse(
            TicketTypeResource::collection($ticketTypes),
            'Ticket types retrieved successfully'
        );
    }
}
