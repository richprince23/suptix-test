<?php

namespace Tests\Feature;

use App\Exceptions\InsufficientCreditsException;
use App\Models\Event;
use App\Models\EventCreditAccount;
use App\Models\Organizer;
use App\Models\RSVP;
use App\Models\RsvpCreditSettings;
use App\Models\RsvpWaitlist;
use App\Services\EventCreditService;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

class RsvpWaitlistTest extends TestCase
{
    use DatabaseTransactions;

    private EventCreditService $service;

    private Organizer $organizer;

    private Event $event;

    private EventCreditAccount $account;

    protected function setUp(): void
    {
        parent::setUp();

        $this->service = app(EventCreditService::class);

        $this->organizer = Organizer::create([
            'name'    => 'Test Org',
            'email'   => 'org@example.com',
            'phone'   => '0300000001',
            'address' => '1 Test St',
            'city'    => 'Accra',
            'state'   => 'greater_accra_region',
            'country' => 'ghana',
        ]);

        $this->event = Event::create([
            'organizer_id' => $this->organizer->id,
            'title'        => 'Test RSVP Event',
            'slug'         => 'test-rsvp-event-' . uniqid(),
            'code'         => strtoupper(uniqid()),
            'is_rsvp'      => true,
            'status'       => 'published',
            'start_date'   => now()->addDays(7)->toDateString(),
            'start_time'   => '09:00:00',
            'event_type'   => 'Conference',
            'description'  => 'Test event description',
        ]);

        $this->account = $this->service->ensureAccountExists($this->event);
    }

    public function test_waitlist_entry_is_created_with_auto_position(): void
    {
        $entry = RsvpWaitlist::create([
            'event_id'     => $this->event->id,
            'organizer_id' => $this->organizer->id,
            'guest_name'   => 'Alice Smith',
            'email'        => 'alice@example.com',
        ]);

        $this->assertEquals(1, $entry->position);
        $this->assertEquals('pending', $entry->status);
        $this->assertNotEmpty($entry->waitlist_code);
        $this->assertEquals(10, strlen($entry->waitlist_code));
    }

    public function test_waitlist_positions_increment_per_event(): void
    {
        RsvpWaitlist::create([
            'event_id' => $this->event->id, 'organizer_id' => $this->organizer->id,
            'guest_name' => 'Alice', 'email' => 'alice@example.com',
        ]);

        $second = RsvpWaitlist::create([
            'event_id' => $this->event->id, 'organizer_id' => $this->organizer->id,
            'guest_name' => 'Bob', 'email' => 'bob@example.com',
        ]);

        $this->assertEquals(2, $second->position);
    }

    public function test_masked_email_hides_local_part(): void
    {
        $entry = new RsvpWaitlist(['email' => 'richard@gmail.com']);

        $this->assertStringStartsWith('r', $entry->maskedEmail());
        $this->assertStringContainsString('@gmail.com', $entry->maskedEmail());
        $this->assertStringNotContainsString('ichard', $entry->maskedEmail());
    }

    public function test_masked_phone_shows_only_last_four_digits(): void
    {
        $entry = new RsvpWaitlist(['phone' => '0244123456']);

        $masked = $entry->maskedPhone();
        $this->assertStringEndsWith('3456', $masked);
        $this->assertStringNotContainsString('0244', $masked);
    }

    public function test_masked_phone_returns_null_when_no_phone(): void
    {
        $entry = new RsvpWaitlist(['phone' => null]);

        $this->assertNull($entry->maskedPhone());
    }

    public function test_approve_waitlist_entry_creates_rsvp(): void
    {
        $entry = RsvpWaitlist::create([
            'event_id'     => $this->event->id,
            'organizer_id' => $this->organizer->id,
            'guest_name'   => 'Alice Smith',
            'email'        => 'alice@example.com',
            'phone'        => '0244123456',
        ]);

        $rsvp = $this->service->approveWaitlistEntry($entry);

        $this->assertInstanceOf(RSVP::class, $rsvp);
        $this->assertEquals('alice@example.com', $rsvp->email);
        $this->assertEquals('Alice Smith', $rsvp->guest_name);
        $this->assertEquals($this->event->id, $rsvp->event_id);
        $this->assertEquals('attending', $rsvp->status);
        $this->assertNotEmpty($rsvp->guest_code);
    }

    public function test_approve_waitlist_entry_marks_entry_approved(): void
    {
        $entry = RsvpWaitlist::create([
            'event_id'     => $this->event->id,
            'organizer_id' => $this->organizer->id,
            'guest_name'   => 'Alice Smith',
            'email'        => 'alice@example.com',
        ]);

        $this->service->approveWaitlistEntry($entry);

        $entry->refresh();
        $this->assertEquals('approved', $entry->status);
        $this->assertNotNull($entry->actioned_at);
    }

    public function test_approve_waitlist_entry_consumes_credit_slot(): void
    {
        $before = $this->account->used_attendee_slots;

        $entry = RsvpWaitlist::create([
            'event_id'     => $this->event->id,
            'organizer_id' => $this->organizer->id,
            'guest_name'   => 'Alice Smith',
            'email'        => 'alice@example.com',
        ]);

        $this->service->approveWaitlistEntry($entry);

        $this->account->refresh();
        $this->assertEquals($before + 1, $this->account->used_attendee_slots);
    }

    public function test_approve_waitlist_entry_throws_when_no_slots(): void
    {
        $this->account->update(['used_attendee_slots' => 100]);

        $entry = RsvpWaitlist::create([
            'event_id'     => $this->event->id,
            'organizer_id' => $this->organizer->id,
            'guest_name'   => 'Alice Smith',
            'email'        => 'alice@example.com',
        ]);

        $this->expectException(InsufficientCreditsException::class);

        $this->service->approveWaitlistEntry($entry);
    }

    public function test_approve_waitlist_entry_transfers_custom_fields_to_rsvp_responses(): void
    {
        // Create a real EventFormField to satisfy the FK constraint
        $field = \App\Models\EventFormField::create([
            'event_id'    => $this->event->id,
            'organizer_id' => $this->organizer->id,
            'label'       => 'Dietary Preference',
            'type'        => 'text',
            'is_required' => false,
            'sort_order'  => 1,
        ]);

        $entry = RsvpWaitlist::create([
            'event_id'      => $this->event->id,
            'organizer_id'  => $this->organizer->id,
            'guest_name'    => 'Alice Smith',
            'email'         => 'alice@example.com',
            'custom_fields' => [$field->id => 'Vegan'],
        ]);

        $rsvp = $this->service->approveWaitlistEntry($entry);

        $this->assertDatabaseHas('rsvp_responses', [
            'rsvp_id'             => $rsvp->id,
            'event_form_field_id' => $field->id,
            'value'               => 'Vegan',
        ]);
    }

    public function test_waitlist_confirmation_mail_is_sent_on_waitlist_add(): void
    {
        Mail::fake();

        // Exhaust all slots
        $this->account->update(['used_attendee_slots' => 100]);

        $this->post("/events/{$this->event->slug}/rsvp", [
            'guest_name' => 'Alice Smith',
            'email'      => 'alice@example.com',
            'phone'      => '0244123456',
        ]);

        Mail::assertQueued(\App\Mail\WaitlistConfirmationMail::class, function ($mail) {
            return $mail->hasTo('alice@example.com');
        });
    }

    public function test_duplicate_waitlist_entry_is_rejected(): void
    {
        $this->account->update(['used_attendee_slots' => 100]);

        $this->post("/events/{$this->event->slug}/rsvp", [
            'guest_name' => 'Alice Smith',
            'email'      => 'alice@example.com',
            'phone'      => '0244123456',
        ]);

        $response = $this->post("/events/{$this->event->slug}/rsvp", [
            'guest_name' => 'Alice Smith',
            'email'      => 'alice@example.com',
            'phone'      => '0244123456',
        ]);

        $response->assertSessionHasErrors('venue_full');
        $this->assertDatabaseCount('rsvp_waitlist', 1);
    }

    public function test_normal_rsvp_flow_unaffected_when_slots_available(): void
    {
        $response = $this->post("/events/{$this->event->slug}/rsvp", [
            'guest_name' => 'Bob Jones',
            'email'      => 'bob@example.com',
            'phone'      => '0244123456',
        ]);

        $response->assertSessionHasNoErrors();
        $this->assertDatabaseHas('rsvps', ['email' => 'bob@example.com', 'event_id' => $this->event->id]);
        $this->assertDatabaseMissing('rsvp_waitlist', ['email' => 'bob@example.com']);
    }
}
