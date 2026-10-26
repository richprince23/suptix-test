<?php

namespace App\Console\Commands;

use App\Mail\RsvpConfirmationMail;
use App\Models\Event;
use App\Models\RSVP;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Mail;

class TestRsvpEmailCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'rsvp:test-email {email} {--event-id=1}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Test RSVP confirmation email functionality';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $email = $this->argument('email');
        $eventId = $this->option('event-id');

        $this->info('Testing RSVP email functionality...');

        // Get or create test event
        $event = Event::with('organizer')->find($eventId);

        if (! $event) {
            $this->error("Event with ID {$eventId} not found.");

            return 1;
        }

        // Create test RSVP
        $testRsvp = new RSVP;
        $testRsvp->guest_name = 'Test User';
        $testRsvp->email = $email;
        $testRsvp->phone = '+1234567890';
        $testRsvp->event_id = $event->id;
        $testRsvp->organizer_id = $event->organizer_id;
        $testRsvp->guest_code = 'TEST1234';

        try {
            $this->info("Sending test email to: {$email}");
            $this->info("Event: {$event->title}");

            // Send test email
            Mail::to($email)->sendNow(new RsvpConfirmationMail($testRsvp, $event));

            $this->info('✅ Test email sent successfully!');

        } catch (\Exception $e) {
            $this->error("❌ Failed to send test email: {$e->getMessage()}");

            return 1;
        }

        return 0;
    }
}
