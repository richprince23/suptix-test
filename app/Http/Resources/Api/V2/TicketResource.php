<?php

namespace App\Http\Resources\Api\V2;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class TicketResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'ticket_number' => $this->ticket_number,
            'status' => $this->status,
            'is_used' => $this->is_used,
            'used_at' => $this->used_at,
            'event' => new EventResource($this->whenLoaded('event')),
            'ticket_type' => new TicketTypeResource($this->whenLoaded('ticketType')),
            'order' => $this->when($this->relationLoaded('order'), function () {
                return [
                    'id' => $this->order->id,
                    'order_number' => $this->order->order_number,
                    'buyer_name' => $this->order->buyer_name,
                    'buyer_email' => $this->order->buyer_email,
                ];
            }),
            'qr_data' => [
                'ticket_number' => $this->ticket_number,
                'event_id' => $this->event_id,
                'ticket_type_id' => $this->ticket_type_id,
            ],
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}