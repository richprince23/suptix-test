<?php

namespace Tests\Feature;

use App\Models\Event;
use App\Models\EventCreditAccount;
use App\Models\EventCreditPurchase;
use App\Models\Organizer;
use App\Models\RSVP;
use App\Models\RsvpCreditSettings;
use App\Services\CreditCheckResult;
use App\Services\EventCreditService;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

class EventCreditTest extends TestCase
{
    use DatabaseTransactions;

    private EventCreditService $service;

    private Organizer $organizer;

    private Event $event;

    protected function setUp(): void
    {
        parent::setUp();

        $this->service = app(EventCreditService::class);

        $this->organizer = Organizer::create([
            'name' => 'Test Org',
            'email' => 'org@example.com',
            'phone' => '0300000001',
            'address' => '1 Test St',
            'city' => 'Accra',
            'state' => 'greater_accra_region',
            'country' => 'ghana',
        ]);

        $this->event = Event::create([
            'organizer_id' => $this->organizer->id,
            'title' => 'Test RSVP Event',
            'slug' => 'test-rsvp-event-'.uniqid(),
            'code' => strtoupper(uniqid()),
            'is_rsvp' => true,
            'status' => 'published',
            'start_date' => now()->addDays(7)->toDateString(),
            'start_time' => '09:00:00',
            'event_type' => 'Conference',
            'description' => 'Test event description',
        ]);
    }

    // -----------------------------------------------------------------------
    // ensureAccountExists
    // -----------------------------------------------------------------------

    public function test_ensure_account_exists_creates_account_with_defaults(): void
    {
        $account = $this->service->ensureAccountExists($this->event);

        $this->assertInstanceOf(EventCreditAccount::class, $account);
        $this->assertEquals($this->event->id, $account->event_id);
        $this->assertEquals(100, $account->free_attendee_slots);
        $this->assertEquals(100, $account->free_email_credits);
        $this->assertEquals(0, $account->free_sms_credits);
    }

    public function test_ensure_account_exists_is_idempotent(): void
    {
        $first = $this->service->ensureAccountExists($this->event);
        $second = $this->service->ensureAccountExists($this->event);

        $this->assertEquals($first->id, $second->id);
        $this->assertDatabaseCount('event_credit_accounts', 1);
    }

    public function test_observer_creates_account_when_rsvp_event_saved(): void
    {
        $this->assertDatabaseHas('event_credit_accounts', ['event_id' => $this->event->id]);
    }

    // -----------------------------------------------------------------------
    // canAcceptNewRsvp
    // -----------------------------------------------------------------------

    public function test_can_accept_new_rsvp_when_slots_available(): void
    {
        $result = $this->service->canAcceptNewRsvp($this->event);

        $this->assertInstanceOf(CreditCheckResult::class, $result);
        $this->assertTrue($result->allowed);
    }

    public function test_blocks_new_rsvp_when_slots_exhausted(): void
    {
        $account = EventCreditAccount::where('event_id', $this->event->id)->first();
        $account->update(['used_attendee_slots' => 100]);

        $result = $this->service->canAcceptNewRsvp($this->event);

        $this->assertFalse($result->allowed);
        $this->assertStringContainsString('fully booked', $result->reason);
    }

    // -----------------------------------------------------------------------
    // consumeAttendeeSlot
    // -----------------------------------------------------------------------

    public function test_consume_attendee_slot_increments_used_count(): void
    {
        $rsvp = $this->makeRsvp();

        $this->service->consumeAttendeeSlot($this->event, $rsvp);

        $account = EventCreditAccount::where('event_id', $this->event->id)->first();
        $this->assertEquals(1, $account->used_attendee_slots);
    }

    public function test_consume_attendee_slot_writes_log_entry(): void
    {
        $rsvp = $this->makeRsvp();

        $this->service->consumeAttendeeSlot($this->event, $rsvp);

        $this->assertDatabaseHas('event_credit_logs', [
            'event_id' => $this->event->id,
            'rsvp_id' => $rsvp->id,
            'type' => 'attendee_consumed',
            'source' => 'free_tier',
        ]);
    }

    // -----------------------------------------------------------------------
    // canSendConfirmationEmail
    // -----------------------------------------------------------------------

    public function test_can_send_email_when_credits_available(): void
    {
        $result = $this->service->canSendConfirmationEmail($this->event);
        $this->assertTrue($result->allowed);
    }

    public function test_blocks_email_when_email_credits_exhausted(): void
    {
        $account = EventCreditAccount::where('event_id', $this->event->id)->first();
        $account->update(['used_email_credits' => 100]);

        $result = $this->service->canSendConfirmationEmail($this->event);
        $this->assertFalse($result->allowed);
    }

    public function test_blocks_email_when_brevo_daily_quota_reached(): void
    {
        RsvpCreditSettings::current()->update(['brevo_daily_limit' => 5]);

        // Simulate 5 other events that have each sent 1 email today
        for ($i = 0; $i < 5; $i++) {
            $ev = Event::create([
                'organizer_id' => $this->organizer->id,
                'title' => 'Aux Event '.$i,
                'slug' => 'aux-event-'.$i.'-'.uniqid(),
                'code' => strtoupper(uniqid()),
                'is_rsvp' => true,
                'status' => 'published',
                'start_date' => now()->addDays(7)->toDateString(),
                'start_time' => '09:00:00',
                'event_type' => 'Conference',
                'description' => 'Aux event description',
            ]);

            EventCreditAccount::where('event_id', $ev->id)->update([
                'global_daily_email_date' => now()->toDateString(),
                'global_daily_email_count' => 1,
            ]);
        }

        $result = $this->service->canSendConfirmationEmail($this->event);
        $this->assertFalse($result->allowed);
    }

    // -----------------------------------------------------------------------
    // grantCreditsFromPurchase
    // -----------------------------------------------------------------------

    public function test_grant_credits_from_purchase_increments_purchased_columns(): void
    {
        $purchase = EventCreditPurchase::create([
            'event_id' => $this->event->id,
            'organizer_id' => $this->organizer->id,
            'paystack_reference' => 'TEST-REF-'.uniqid(),
            'amount' => 45.00,
            'pack_key' => 'slots_100',
            'quantity' => 1,
            'attendee_slots_granted' => 100,
            'email_credits_granted' => 100,
            'sms_credits_granted' => 0,
            'status' => 'pending',
        ]);

        $this->service->grantCreditsFromPurchase($purchase);

        $account = EventCreditAccount::where('event_id', $this->event->id)->first();
        $this->assertEquals(100, $account->purchased_attendee_slots);
        $this->assertEquals(100, $account->purchased_email_credits);

        $purchase->refresh();
        $this->assertEquals('completed', $purchase->status);
    }

    public function test_grant_credits_writes_log_entries(): void
    {
        $purchase = EventCreditPurchase::create([
            'event_id' => $this->event->id,
            'organizer_id' => $this->organizer->id,
            'paystack_reference' => 'TEST-REF-'.uniqid(),
            'amount' => 45.00,
            'pack_key' => 'slots_100',
            'quantity' => 1,
            'attendee_slots_granted' => 100,
            'email_credits_granted' => 100,
            'sms_credits_granted' => 0,
            'status' => 'pending',
        ]);

        $this->service->grantCreditsFromPurchase($purchase);

        $this->assertDatabaseHas('event_credit_logs', [
            'event_id' => $this->event->id,
            'purchase_id' => $purchase->id,
            'type' => 'attendee_granted',
            'source' => 'purchase',
        ]);
    }

    // -----------------------------------------------------------------------
    // grantAdminCredits
    // -----------------------------------------------------------------------

    public function test_admin_grant_writes_log_with_admin_grant_source(): void
    {
        $account = EventCreditAccount::where('event_id', $this->event->id)->first();

        $this->service->grantAdminCredits($account, 50, 50, 0, 'Testing comp grant');

        $this->assertDatabaseHas('event_credit_logs', [
            'event_id' => $this->event->id,
            'type' => 'attendee_granted',
            'source' => 'admin_grant',
            'note' => 'Testing comp grant',
        ]);

        $account->refresh();
        $this->assertEquals(150, $account->free_attendee_slots);
    }

    // -----------------------------------------------------------------------
    // Helpers
    // -----------------------------------------------------------------------

    private function makeRsvp(): RSVP
    {
        return RSVP::create([
            'event_id' => $this->event->id,
            'organizer_id' => $this->organizer->id,
            'guest_name' => 'Test Guest',
            'email' => 'guest@example.com',
        ]);
    }
}
