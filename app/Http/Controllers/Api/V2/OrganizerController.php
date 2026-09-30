<?php

namespace App\Http\Controllers\Api\V2;

use App\Models\Event;
use App\Models\Ticket;
use App\Models\CheckIn;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Http\Resources\Api\V2\OrganizerResource;
use App\Http\Resources\Api\V2\EventResource;
use App\Http\Resources\Api\V2\EventDetailResource;
use App\Http\Resources\Api\V2\TicketResource;
use App\Http\Resources\Api\V2\OrderResource;

class OrganizerController extends BaseController
{
    public function profile(Request $request)
    {
        $user = $request->user();
        $organizers = $user->organizers()->with(['events', 'users'])->get();

        if ($organizers->isEmpty()) {
            return $this->errorResponse('User is not associated with any organizer', 404);
        }

        return $this->successResponse(
            OrganizerResource::collection($organizers),
            'Organizer profile retrieved successfully'
        );
    }

    public function events(Request $request)
    {
        $user = $request->user();
        $organizerIds = $user->organizers()->pluck('organizers.id');

        if ($organizerIds->isEmpty()) {
            return $this->errorResponse('User is not associated with any organizer', 404);
        }

        $perPage = $request->get('per_page', 15);
        
        $query = Event::whereIn('organizer_id', $organizerIds)
            ->with(['organizer']);

        if ($request->has('status')) {
            $query->where('status', $request->get('status'));
        }

        if ($request->has('search')) {
            $search = $request->get('search');
            $query->where(function ($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                  ->orWhere('description', 'like', "%{$search}%");
            });
        }

        $events = $query->orderBy('start_date', 'desc')->paginate($perPage);

        return $this->successResponse([
            'events' => EventResource::collection($events->items()),
            'pagination' => [
                'current_page' => $events->currentPage(),
                'last_page' => $events->lastPage(),
                'per_page' => $events->perPage(),
                'total' => $events->total(),
                'has_more' => $events->hasMorePages(),
            ],
        ], 'Organizer events retrieved successfully');
    }

    public function event(Event $event, Request $request)
    {
        $user = $request->user();
        $organizerIds = $user->organizers()->pluck('organizers.id');

        if (!$organizerIds->contains($event->organizer_id)) {
            return $this->forbiddenResponse('You do not have access to this event');
        }

        $event->load(['organizer', 'ticketTypes']);

        return $this->successResponse(
            new EventDetailResource($event),
            'Event details retrieved successfully'
        );
    }

    public function eventStatistics(Event $event, Request $request)
    {
        $user = $request->user();
        $organizerIds = $user->organizers()->pluck('organizers.id');

        if (!$organizerIds->contains($event->organizer_id)) {
            return $this->forbiddenResponse('You do not have access to this event');
        }

        $totalTickets = $event->tickets()->count();
        $usedTickets = $event->tickets()->where('is_used', true)->count();
        $totalRevenue = $event->orders()
            ->where('payment_status', 'completed')
            ->sum('total_amount');

        $ticketTypeStats = $event->ticketTypes()
            ->withCount(['tickets', 'tickets as used_tickets_count' => function ($query) {
                $query->where('is_used', true);
            }])
            ->get()
            ->map(function ($ticketType) {
                return [
                    'id' => $ticketType->id,
                    'name' => $ticketType->name,
                    'price' => $ticketType->price,
                    'total_sold' => $ticketType->tickets_count,
                    'total_used' => $ticketType->used_tickets_count,
                    'revenue' => $ticketType->price * $ticketType->tickets_count,
                ];
            });

        $recentCheckIns = CheckIn::where('event_id', $event->id)
            ->with(['ticket.ticketType'])
            ->latest()
            ->limit(10)
            ->get()
            ->map(function ($checkIn) {
                return [
                    'id' => $checkIn->id,
                    'ticket_number' => $checkIn->ticket->ticket_number,
                    'ticket_type' => $checkIn->ticket->ticketType->name,
                    'checked_in_at' => $checkIn->checked_in_at,
                ];
            });

        return $this->successResponse([
            'overview' => [
                'total_tickets' => $totalTickets,
                'used_tickets' => $usedTickets,
                'unused_tickets' => $totalTickets - $usedTickets,
                'attendance_rate' => $totalTickets > 0 ? round(($usedTickets / $totalTickets) * 100, 2) : 0,
                'total_revenue' => $totalRevenue,
            ],
            'ticket_types' => $ticketTypeStats,
            'recent_check_ins' => $recentCheckIns,
        ], 'Event statistics retrieved successfully');
    }

    public function eventTickets(Event $event, Request $request)
    {
        $user = $request->user();
        $organizerIds = $user->organizers()->pluck('organizers.id');

        if (!$organizerIds->contains($event->organizer_id)) {
            return $this->forbiddenResponse('You do not have access to this event');
        }

        $perPage = $request->get('per_page', 15);
        
        $query = $event->tickets()->with(['ticketType', 'order']);

        if ($request->has('status')) {
            if ($request->get('status') === 'used') {
                $query->where('is_used', true);
            } elseif ($request->get('status') === 'unused') {
                $query->where('is_used', false);
            }
        }

        if ($request->has('ticket_type_id')) {
            $query->where('ticket_type_id', $request->get('ticket_type_id'));
        }

        if ($request->has('search')) {
            $search = $request->get('search');
            $query->where(function ($q) use ($search) {
                $q->where('ticket_number', 'like', "%{$search}%")
                  ->orWhereHas('order', function ($orderQuery) use ($search) {
                      $orderQuery->where('buyer_name', 'like', "%{$search}%")
                                 ->orWhere('buyer_email', 'like', "%{$search}%");
                  });
            });
        }

        $tickets = $query->orderBy('created_at', 'desc')->paginate($perPage);

        return $this->successResponse([
            'tickets' => TicketResource::collection($tickets->items()),
            'pagination' => [
                'current_page' => $tickets->currentPage(),
                'last_page' => $tickets->lastPage(),
                'per_page' => $tickets->perPage(),
                'total' => $tickets->total(),
                'has_more' => $tickets->hasMorePages(),
            ],
        ], 'Event tickets retrieved successfully');
    }

    public function eventOrders(Event $event, Request $request)
    {
        $user = $request->user();
        $organizerIds = $user->organizers()->pluck('organizers.id');

        if (!$organizerIds->contains($event->organizer_id)) {
            return $this->forbiddenResponse('You do not have access to this event');
        }

        $perPage = $request->get('per_page', 15);
        
        $query = $event->orders()->with(['event', 'organizer']);

        if ($request->has('status')) {
            $query->where('status', $request->get('status'));
        }

        if ($request->has('payment_status')) {
            $query->where('payment_status', $request->get('payment_status'));
        }

        if ($request->has('search')) {
            $search = $request->get('search');
            $query->where(function ($q) use ($search) {
                $q->where('order_number', 'like', "%{$search}%")
                  ->orWhere('buyer_name', 'like', "%{$search}%")
                  ->orWhere('buyer_email', 'like', "%{$search}%");
            });
        }

        $orders = $query->orderBy('created_at', 'desc')->paginate($perPage);

        return $this->successResponse([
            'orders' => OrderResource::collection($orders->items()),
            'pagination' => [
                'current_page' => $orders->currentPage(),
                'last_page' => $orders->lastPage(),
                'per_page' => $orders->perPage(),
                'total' => $orders->total(),
                'has_more' => $orders->hasMorePages(),
            ],
        ], 'Event orders retrieved successfully');
    }

    public function checkInTicket(Event $event, Ticket $ticket, Request $request)
    {
        $user = $request->user();
        $organizerIds = $user->organizers()->pluck('organizers.id');

        if (!$organizerIds->contains($event->organizer_id)) {
            return $this->forbiddenResponse('You do not have access to this event');
        }

        if ($ticket->event_id !== $event->id) {
            return $this->errorResponse('Ticket does not belong to this event', 400);
        }

        if ($ticket->status !== 'valid') {
            return $this->errorResponse('Ticket is not valid for check-in', 400);
        }

        if ($ticket->is_used) {
            return $this->errorResponse('Ticket has already been used', 400);
        }

        $checkIn = CheckIn::create([
            'ticket_id' => $ticket->id,
            'event_id' => $event->id,
            'checked_in_by' => $user->id,
            'checked_in_at' => now(),
        ]);

        $ticket->update([
            'is_used' => true,
            'used_at' => now(),
        ]);

        $ticket->load(['event', 'ticketType', 'order', 'checkIns']);

        return $this->successResponse([
            'ticket' => new TicketResource($ticket),
            'check_in' => [
                'id' => $checkIn->id,
                'checked_in_at' => $checkIn->checked_in_at,
                'checked_in_by' => $user->name,
            ],
        ], 'Ticket checked in successfully');
    }
}