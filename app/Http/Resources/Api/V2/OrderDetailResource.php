<?php

namespace App\Http\Resources\Api\V2;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class OrderDetailResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'order_number' => $this->order_number,
            'buyer_name' => $this->buyer_name,
            'buyer_email' => $this->buyer_email,
            'buyer_phone' => $this->buyer_phone,
            'total_amount' => $this->total_amount,
            'status' => $this->status,
            'payment_status' => $this->payment_status,
            'event' => new EventDetailResource($this->whenLoaded('event')),
            'organizer' => new OrganizerResource($this->whenLoaded('organizer')),
            'tickets' => TicketResource::collection($this->whenLoaded('tickets')),
            'tickets_summary' => $this->when($this->relationLoaded('tickets'), function () {
                $tickets = $this->tickets->groupBy('ticket_type_id');
                return $tickets->map(function ($typeTickets) {
                    $firstTicket = $typeTickets->first();
                    return [
                        'ticket_type' => new TicketTypeResource($firstTicket->ticketType),
                        'quantity' => $typeTickets->count(),
                        'total_price' => $typeTickets->sum(function ($ticket) {
                            return $ticket->purchases->first()?->price ?? 0;
                        }),
                    ];
                })->values();
            }),
            'purchases' => $this->when($this->relationLoaded('purchases'), function () {
                return $this->purchases->map(function ($purchase) {
                    return [
                        'id' => $purchase->id,
                        'user' => [
                            'id' => $purchase->user->id,
                            'name' => $purchase->user->name,
                            'email' => $purchase->user->email,
                        ],
                        'price' => $purchase->price,
                        'ticket_id' => $purchase->ticket_id,
                    ];
                });
            }),
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}