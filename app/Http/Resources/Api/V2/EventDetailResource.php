<?php

namespace App\Http\Resources\Api\V2;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class EventDetailResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'title' => $this->title,
            'slug' => $this->slug,
            'description' => $this->description,
            'cover_image' => $this->cover_image_url,
            'start_date' => $this->start_date,
            'start_time' => $this->start_time,
            'end_date' => $this->end_date,
            'end_time' => $this->end_time,
            'event_type' => $this->event_type,
            'tags' => $this->tags ?? [],
            'status' => $this->status,
            'mode' => $this->mode,
            'is_rsvp' => $this->is_rsvp,
            'organizer' => new OrganizerResource($this->whenLoaded('organizer')),
            'venue_name' => $this->venue_name,
            'venue_address' => $this->venue_address,
            'venue_gps_code' => $this->venue_gps_code,
            'venue_capacity' => $this->venue_capacity,
            'venue_phone' => $this->venue_phone,
            'ticket_types' => TicketTypeResource::collection($this->whenLoaded('ticketTypes')),
            'statistics' => $this->when($this->relationLoaded('tickets'), function () {
                return [
                    'total_tickets' => $this->tickets->count(),
                    'used_tickets' => $this->tickets->where('is_used', true)->count(),
                    'available_tickets' => $this->ticketTypes->sum(function ($type) {
                        return $type->max_quantity - $type->tickets()->count();
                    }),
                ];
            }),
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
