<?php

namespace App\Http\Resources\Api\V2;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class TicketTypeResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'description' => $this->description,
            'price' => $this->price,
            'max_quantity' => $this->max_quantity,
            'is_available' => $this->is_available,
            'available_quantity' => $this->when($this->max_quantity, function () {
                return max(0, $this->max_quantity - $this->tickets()->count());
            }),
            'sold_count' => $this->when($this->relationLoaded('tickets'), function () {
                return $this->tickets->count();
            }),
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}