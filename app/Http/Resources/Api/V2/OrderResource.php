<?php

namespace App\Http\Resources\Api\V2;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class OrderResource extends JsonResource
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
            'event' => new EventResource($this->whenLoaded('event')),
            'organizer' => new OrganizerResource($this->whenLoaded('organizer')),
            'tickets_count' => $this->when($this->relationLoaded('tickets'), function () {
                return $this->tickets->count();
            }),
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}