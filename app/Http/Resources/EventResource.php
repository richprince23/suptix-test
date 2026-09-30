<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class EventResource extends JsonResource
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
            'code' => $this->code,
            'title' => $this->title,
            'description' => $this->description,
            'slug' => $this->slug,
            'event_type' => $this->event_type,
            'organizer_id' => $this->organizer_id,
            'organizer_name' => $this->whenLoaded('organizer', fn () => $this->organizer?->name),
            'venue_name' => $this->venue_name,
            'venue_address' => $this->venue_address,
            'venue_gps_code' => $this->venue_gps_code,
            'venue_capacity' => $this->venue_capacity,
            'venue_phone' => $this->venue_phone,
            'start_date' => $this->formatDate($this->start_date),
            'start_time' => $this->formatTime($this->start_time),
            'end_date' => $this->formatDate($this->end_date),
            'end_time' => $this->formatTime($this->end_time),
            'status' => $this->current_status, // show calculated status
            'is_rsvp' => $this->is_rsvp ?? false,
            'mode' => $this->mode ?? 'in-person',
            'platform_name' => $this->platform_name ?? null,
            'online_link' => $this->online_link ?? null,
            'is_public_link' => $this->is_public_link ?? false,
            'tags' => $this->tags ?? [],
            'cover_image_url' => $this->cover_image_url,
        ];
    }

    private function formatDate($date): ?string
    {
        return $date?->format('dS F, Y');
    }

    private function formatTime($time): ?string
    {
        if (! $time) {
            return null;
        }

        try {
            return is_string($time)
                ? date('H:i A', strtotime($time))
                : $time->format('H:i A');
        } catch (\Exception $e) {
            return null;
        }
    }
}
