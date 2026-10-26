<?php

namespace App\Console\Commands;

use App\Mail\RsvpDigestMail;
use App\Models\Organizer;
use Illuminate\Console\Command;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Mail;

class SendMockRsvpDigest extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'rsvp:send-mock-digest {--email=test@example.com} {--organizer_id=}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Send a test RSVP digest email with mock data';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $email = $this->option('email');
        $organizerId = $this->option('organizer_id');

        // Get organizer or use first available
        if ($organizerId) {
            $organizer = Organizer::find($organizerId);
            if (!$organizer) {
                $this->error("Organizer with ID {$organizerId} not found.");
                return 1;
            }
        } else {
            $organizer = Organizer::first();
            if (!$organizer) {
                $this->error("No organizers found in database.");
                return 1;
            }
        }

        // Create mock data
        $mockRsvpsByEvent = $this->createMockData($organizer);

        $this->info("Sending mock RSVP digest email:");
        $this->info("- Organizer: {$organizer->name} (ID: {$organizer->id})");
        $this->info("- To: {$email}");
        $this->info("- Events: {$mockRsvpsByEvent->count()}");
        $this->info("- Total RSVPs: {$mockRsvpsByEvent->flatten(1)->count()}");

        if (!$this->confirm('Send test email?', true)) {
            $this->info('Cancelled.');
            return 0;
        }

        try {
            // Send immediately (not queued) to avoid serialization issues with mock data
            Mail::to($email)->sendNow(new RsvpDigestMail($organizer, $mockRsvpsByEvent));
            $this->info("Test email sent successfully to {$email}!");
            $this->info("Check your inbox to verify the multitenancy URLs are correct.");
            $this->info("URLs should include: /admin/{$organizer->id}/rsvps");
            return 0;
        } catch (\Exception $e) {
            $this->error("Failed to send email: {$e->getMessage()}");
            $this->error($e->getTraceAsString());
            return 1;
        }
    }

    private function createMockData(Organizer $organizer): Collection
    {
        // Create mock event 1
        $event1 = $this->createMockEvent(101, 'Summer Tech Conference 2025', 500, 45);

        // Create mock notifications for event 1
        $notifications1 = collect([
            $this->createMockNotification($event1, 'John Doe', 'john@example.com', '+1234567890', '2025-01-15 10:30 AM'),
            $this->createMockNotification($event1, 'Jane Smith', 'jane@example.com', '+0987654321', '2025-01-15 11:45 AM'),
            $this->createMockNotification($event1, 'Bob Johnson', 'bob@example.com', null, '2025-01-15 02:15 PM'),
        ]);

        // Create mock event 2
        $event2 = $this->createMockEvent(102, 'Product Launch Workshop', null, 12);

        // Create mock notifications for event 2
        $notifications2 = collect([
            $this->createMockNotification($event2, 'Alice Williams', 'alice@example.com', '+1122334455', '2025-01-15 03:20 PM'),
            $this->createMockNotification($event2, 'Charlie Brown', 'charlie@example.com', '+5544332211', '2025-01-15 04:00 PM'),
        ]);

        // Return grouped by event ID
        return collect([
            $event1->id => $notifications1,
            $event2->id => $notifications2,
        ]);
    }

    private function createMockEvent($id, $name, $capacity, $totalRsvps): object
    {
        return new class($id, $name, $capacity, $totalRsvps) {
            public $id;
            public $name;
            public $venue_capacity;
            private $totalRsvps;

            public function __construct($id, $name, $capacity, $totalRsvps) {
                $this->id = $id;
                $this->name = $name;
                $this->totalRsvps = $totalRsvps;
                $this->venue_capacity = $capacity ?: null;
            }

            public function rsvps() {
                return new class($this->totalRsvps) {
                    private $total;

                    public function __construct($total) {
                        $this->total = $total;
                    }

                    public function count() {
                        return $this->total;
                    }
                };
            }
        };
    }

    private function createMockNotification($event, $name, $email, $phone, $time): \stdClass
    {
        $notification = new \stdClass();
        $notification->event_id = $event->id;
        $notification->event = $event;

        $rsvp = new \stdClass();
        $rsvp->name = $name;
        $rsvp->email = $email;
        $rsvp->phone = $phone;
        $rsvp->created_at = new \DateTime($time);

        $notification->rsvp = $rsvp;

        return $notification;
    }
}
