<?php

namespace App\Jobs;

use App\Mail\RsvpDigestMail;
use App\Models\Organizer;
use App\Models\PendingRsvpNotification;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class SendRsvpDigestNotifications implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(
        public string $frequency
    ) {
        //
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        Log::info('Starting RSVP digest notifications', [
            'frequency' => $this->frequency,
        ]);

        // Get organizers with this notification frequency
        $organizers = Organizer::where('rsvp_notification_frequency', $this->frequency)->get();

        foreach ($organizers as $organizer) {
            $this->sendDigestForOrganizer($organizer);
        }

        Log::info('Completed RSVP digest notifications', [
            'frequency' => $this->frequency,
            'organizers_processed' => $organizers->count(),
        ]);
    }

    private function sendDigestForOrganizer(Organizer $organizer): void
    {
        // Get pending notifications for this organizer
        $pendingNotifications = PendingRsvpNotification::pending()
            ->forOrganizer($organizer->id)
            ->with(['rsvp', 'event'])
            ->get();

        if ($pendingNotifications->isEmpty()) {
            Log::debug('No pending RSVP notifications for organizer', [
                'organizer_id' => $organizer->id,
                'organizer_name' => $organizer->name,
            ]);
            return;
        }

        try {
            // Group RSVPs by event
            $rsvpsByEvent = $pendingNotifications->groupBy('event_id');

            // Send digest email
            Mail::to($organizer->email)->sendNow(new RsvpDigestMail($organizer, $rsvpsByEvent));

            // Mark all notifications as sent
            foreach ($pendingNotifications as $notification) {
                $notification->markAsSent();
            }

            Log::info('RSVP digest email sent successfully', [
                'organizer_id' => $organizer->id,
                'organizer_email' => $organizer->email,
                'rsvp_count' => $pendingNotifications->count(),
                'event_count' => $rsvpsByEvent->count(),
                'frequency' => $this->frequency,
            ]);

        } catch (\Exception $e) {
            Log::error('Failed to send RSVP digest email', [
                'organizer_id' => $organizer->id,
                'organizer_email' => $organizer->email,
                'error' => $e->getMessage(),
                'frequency' => $this->frequency,
            ]);
        }
    }
}
