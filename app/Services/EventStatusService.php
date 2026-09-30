<?php

namespace App\Services;

use App\Models\Event;
use Illuminate\Database\Eloquent\Collection;

class EventStatusService
{
    public function updateEventStatuses(Collection $events): int
    {
        $updatedCount = 0;

        foreach ($events as $event) {
            if ($event->updateStatusIfNeeded()) {
                $updatedCount++;
            }
        }

        return $updatedCount;
    }

    public function getEventsNeedingStatusUpdate(): Collection
    {
        return Event::whereIn('status', ['published', 'live'])
            ->get()
            ->filter(fn ($event) => $event->needsStatusUpdate());
    }
}
