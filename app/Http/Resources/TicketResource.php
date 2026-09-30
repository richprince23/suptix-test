<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class TicketResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        // return parent::toArray($request);

        return [
            'id' => $this->id,
            'ticket_number' => $this->ticket_number,
            'ticket_type' => $this->ticketType?->name,
            'price' => $this->price,
            'event' => $this->event?->title,
            'event_id' => $this->event_id,
            'venue' => $this->event?->venue_name,
            'venue_name' => $this->event?->venue_name,
            'venue_address' => $this->event?->venue_address,
            'organizer' => $this->organizer?->name,
            'order_number' => $this->order?->order_number,
            'attendee_name' => $this->attendee_name,
            'attendee_email' => $this->attendee_email,
            'status' => $this->status,
            'used_at' => $this->used_at?->format('dS F, Y, H:i A'),
            'created_at' => $this->created_at?->format('dS F, Y, H:i A'),
        ];
    }
}
