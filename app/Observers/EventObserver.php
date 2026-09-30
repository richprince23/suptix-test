<?php

namespace App\Observers;

use App\Models\Event;
use App\Services\EventCreditService;

class EventObserver
{
    public function __construct(protected EventCreditService $creditService) {}

    public function saved(Event $event): void
    {
        if ($event->is_rsvp) {
            $this->creditService->ensureAccountExists($event);
        }
    }
}
