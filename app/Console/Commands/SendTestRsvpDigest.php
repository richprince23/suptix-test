<?php

namespace App\Console\Commands;

use App\Mail\RsvpDigestMail;
use App\Models\Event;
use App\Models\Organizer;
use App\Models\PendingRsvpNotification;
use App\Models\RSVP;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Mail;

class SendTestRsvpDigest extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'rsvp:send-test-digest {organizer_id} {--email=}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Send a test RSVP digest email to an organizer';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $organizerId = $this->argument('organizer_id');
        $organizer = Organizer::find($organizerId);

        if (!$organizer) {
            $this->error("Organizer with ID {$organizerId} not found.");
            return 1;
        }

        // Get actual pending notifications if they exist
        $pendingNotifications = PendingRsvpNotification::pending()
            ->forOrganizer($organizer->id)
            ->with(['rsvp', 'event'])
            ->get();

        if ($pendingNotifications->isEmpty()) {
            $this->warn("No pending RSVP notifications found for {$organizer->name}.");

            // Create fake data for testing
            $this->info("Creating test data...");

            // Get or create a test event
            $event = $organizer->events()->first();

            if (!$event) {
                $this->error("No events found for this organizer. Please create an event first.");
                return 1;
            }

            // Create test RSVPs if needed
            $rsvps = $organizer->rsvps()->where('event_id', $event->id)->take(3)->get();

            if ($rsvps->isEmpty()) {
                $this->error("No RSVPs found for this organizer. Please create some RSVPs first.");
                return 1;
            }

            // Create test pending notifications
            foreach ($rsvps as $rsvp) {
                PendingRsvpNotification::create([
                    'rsvp_id' => $rsvp->id,
                    'event_id' => $event->id,
                    'organizer_id' => $organizer->id,
                    'sent' => false,
                ]);
            }

            // Reload pending notifications
            $pendingNotifications = PendingRsvpNotification::pending()
                ->forOrganizer($organizer->id)
                ->with(['rsvp', 'event'])
                ->get();
        }

        // Group RSVPs by event
        $rsvpsByEvent = $pendingNotifications->groupBy('event_id');

        $this->info("Preparing to send digest email:");
        $this->info("- Organizer: {$organizer->name}");
        $this->info("- Email: " . ($this->option('email') ?: $organizer->email));
        $this->info("- Total RSVPs: {$pendingNotifications->count()}");
        $this->info("- Events: {$rsvpsByEvent->count()}");

        if (!$this->confirm('Do you want to send this test email?', true)) {
            $this->info('Test cancelled.');
            return 0;
        }

        try {
            // Send to custom email or organizer's email
            $email = $this->option('email') ?: $organizer->email;

            // Send immediately (not queued) for testing
            Mail::to($email)->sendNow(new RsvpDigestMail($organizer, $rsvpsByEvent));

            $this->info("Test RSVP digest email sent successfully to {$email}!");
            $this->info("Check inbox to verify multitenancy URLs: /admin/{$organizer->id}/rsvps");

            // Ask if we should mark as sent
            if ($this->confirm('Mark these notifications as sent?', false)) {
                foreach ($pendingNotifications as $notification) {
                    $notification->markAsSent();
                }
                $this->info('Notifications marked as sent.');
            }

            return 0;
        } catch (\Exception $e) {
            $this->error("Failed to send email: {$e->getMessage()}");
            $this->error($e->getTraceAsString());
            return 1;
        }
    }
}
