<?php

namespace Tests\Feature;

use App\Models\Event;
use App\Models\EventPartner;
use App\Models\Organizer;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Storage;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class EventPartnerTest extends TestCase
{
    use DatabaseTransactions;

    protected function setUp(): void
    {
        parent::setUp();
        $this->artisan('migrate');
    }

    private function makeOrganizer(): Organizer
    {
        return Organizer::create([
            'name' => 'Test Organizer',
            'email' => fake()->unique()->safeEmail(),
            'phone' => '0244000000',
        ]);
    }

    private function makeEvent(Organizer $organizer): Event
    {
        return Event::create([
            'organizer_id' => $organizer->id,
            'title' => 'Test Event',
            'description' => 'A test event.',
            'start_date' => now()->addDays(7)->toDateString(),
            'start_time' => '18:00:00',
            'status' => 'published',
        ]);
    }

    // -------------------------------------------------------------------------
    // Creation
    // -------------------------------------------------------------------------

    #[Test]
    public function can_create_a_partner_for_an_event(): void
    {
        $organizer = $this->makeOrganizer();
        $event = $this->makeEvent($organizer);

        $partner = EventPartner::create([
            'event_id' => $event->id,
            'organizer_id' => $organizer->id,
            'type' => 'partner',
            'name' => 'Acme Corp',
            'website' => 'https://acme.example.com',
            'is_visible' => true,
            'sort_order' => 0,
        ]);

        $this->assertNotNull($partner->id);
        $this->assertSame('partner', $partner->type);
        $this->assertDatabaseHas('event_partners', [
            'event_id' => $event->id,
            'type' => 'partner',
            'name' => 'Acme Corp',
        ]);
    }

    #[Test]
    public function can_create_a_sponsor_with_a_tier_for_an_event(): void
    {
        $organizer = $this->makeOrganizer();
        $event = $this->makeEvent($organizer);

        $sponsor = EventPartner::create([
            'event_id' => $event->id,
            'organizer_id' => $organizer->id,
            'type' => 'sponsor',
            'name' => 'Big Bank Ltd',
            'tier' => 'gold',
            'website' => 'https://bigbank.example.com',
            'is_visible' => true,
            'sort_order' => 1,
        ]);

        $this->assertSame('sponsor', $sponsor->type);
        $this->assertSame('gold', $sponsor->tier);
        $this->assertDatabaseHas('event_partners', [
            'event_id' => $event->id,
            'type' => 'sponsor',
            'tier' => 'gold',
        ]);
    }

    // -------------------------------------------------------------------------
    // logo_url accessor
    // -------------------------------------------------------------------------

    #[Test]
    public function logo_url_returns_null_when_no_logo_is_set(): void
    {
        $organizer = $this->makeOrganizer();
        $event = $this->makeEvent($organizer);

        $partner = EventPartner::create([
            'event_id' => $event->id,
            'organizer_id' => $organizer->id,
            'type' => 'partner',
            'name' => 'No Logo Corp',
            'logo' => null,
            'is_visible' => true,
            'sort_order' => 0,
        ]);

        $this->assertNull($partner->logo_url);
    }

    #[Test]
    public function logo_url_returns_a_public_storage_url_when_logo_is_set(): void
    {
        // The model reads and deletes logos on the default disk; use the faked public disk whatever FILESYSTEM_DISK is
        config(['filesystems.default' => 'public']);
        Storage::fake('public');

        $organizer = $this->makeOrganizer();
        $event = $this->makeEvent($organizer);

        $partner = EventPartner::create([
            'event_id' => $event->id,
            'organizer_id' => $organizer->id,
            'type' => 'partner',
            'name' => 'Logo Corp',
            'logo' => 'event-partners/logo.png',
            'is_visible' => true,
            'sort_order' => 0,
        ]);

        $this->assertStringContainsString('event-partners/logo.png', $partner->logo_url);
    }

    // -------------------------------------------------------------------------
    // File cleanup
    // -------------------------------------------------------------------------

    #[Test]
    public function deleting_a_partner_removes_its_logo_from_storage(): void
    {
        // The model reads and deletes logos on the default disk; use the faked public disk whatever FILESYSTEM_DISK is
        config(['filesystems.default' => 'public']);
        Storage::fake('public');
        Storage::disk('public')->put('event-partners/delete-me.png', 'fake');

        $organizer = $this->makeOrganizer();
        $event = $this->makeEvent($organizer);

        $partner = EventPartner::create([
            'event_id' => $event->id,
            'organizer_id' => $organizer->id,
            'type' => 'partner',
            'name' => 'Temp Corp',
            'logo' => 'event-partners/delete-me.png',
            'is_visible' => true,
            'sort_order' => 0,
        ]);

        Storage::disk('public')->assertExists('event-partners/delete-me.png');

        $partner->delete();

        Storage::disk('public')->assertMissing('event-partners/delete-me.png');
    }

    #[Test]
    public function updating_a_partner_logo_removes_the_old_logo_from_storage(): void
    {
        // The model reads and deletes logos on the default disk; use the faked public disk whatever FILESYSTEM_DISK is
        config(['filesystems.default' => 'public']);
        Storage::fake('public');
        Storage::disk('public')->put('event-partners/old-logo.png', 'fake');
        Storage::disk('public')->put('event-partners/new-logo.png', 'fake');

        $organizer = $this->makeOrganizer();
        $event = $this->makeEvent($organizer);

        $partner = EventPartner::create([
            'event_id' => $event->id,
            'organizer_id' => $organizer->id,
            'type' => 'partner',
            'name' => 'Swap Corp',
            'logo' => 'event-partners/old-logo.png',
            'is_visible' => true,
            'sort_order' => 0,
        ]);

        $partner->update(['logo' => 'event-partners/new-logo.png']);

        Storage::disk('public')->assertMissing('event-partners/old-logo.png');
        Storage::disk('public')->assertExists('event-partners/new-logo.png');
    }

    // -------------------------------------------------------------------------
    // Event relationships
    // -------------------------------------------------------------------------

    #[Test]
    public function event_partners_relationship_returns_only_partner_type(): void
    {
        $organizer = $this->makeOrganizer();
        $event = $this->makeEvent($organizer);

        EventPartner::create(['event_id' => $event->id, 'organizer_id' => $organizer->id, 'type' => 'partner', 'name' => 'Partner A', 'is_visible' => true, 'sort_order' => 0]);
        EventPartner::create(['event_id' => $event->id, 'organizer_id' => $organizer->id, 'type' => 'sponsor', 'name' => 'Sponsor A', 'is_visible' => true, 'sort_order' => 1]);

        $partners = $event->partners;

        $this->assertCount(1, $partners);
        $this->assertSame('Partner A', $partners->first()->name);
    }

    #[Test]
    public function event_sponsors_relationship_returns_only_sponsor_type(): void
    {
        $organizer = $this->makeOrganizer();
        $event = $this->makeEvent($organizer);

        EventPartner::create(['event_id' => $event->id, 'organizer_id' => $organizer->id, 'type' => 'partner', 'name' => 'Partner A', 'is_visible' => true, 'sort_order' => 0]);
        EventPartner::create(['event_id' => $event->id, 'organizer_id' => $organizer->id, 'type' => 'sponsor', 'name' => 'Sponsor A', 'is_visible' => true, 'sort_order' => 1]);

        $sponsors = $event->sponsors;

        $this->assertCount(1, $sponsors);
        $this->assertSame('Sponsor A', $sponsors->first()->name);
    }

    #[Test]
    public function event_all_partners_relationship_returns_both_types(): void
    {
        $organizer = $this->makeOrganizer();
        $event = $this->makeEvent($organizer);

        EventPartner::create(['event_id' => $event->id, 'organizer_id' => $organizer->id, 'type' => 'partner', 'name' => 'Partner A', 'is_visible' => true, 'sort_order' => 0]);
        EventPartner::create(['event_id' => $event->id, 'organizer_id' => $organizer->id, 'type' => 'sponsor', 'name' => 'Sponsor A', 'is_visible' => true, 'sort_order' => 1]);

        $this->assertCount(2, $event->allPartners);
    }

    #[Test]
    public function all_partners_are_ordered_by_sort_order(): void
    {
        $organizer = $this->makeOrganizer();
        $event = $this->makeEvent($organizer);

        EventPartner::create(['event_id' => $event->id, 'organizer_id' => $organizer->id, 'type' => 'partner', 'name' => 'Second', 'is_visible' => true, 'sort_order' => 2]);
        EventPartner::create(['event_id' => $event->id, 'organizer_id' => $organizer->id, 'type' => 'sponsor', 'name' => 'First', 'is_visible' => true, 'sort_order' => 1]);

        $names = $event->allPartners->pluck('name')->all();

        $this->assertSame(['First', 'Second'], $names);
    }

    // -------------------------------------------------------------------------
    // Cascade delete
    // -------------------------------------------------------------------------

    #[Test]
    public function deleting_an_event_removes_its_partners(): void
    {
        $organizer = $this->makeOrganizer();
        $event = $this->makeEvent($organizer);

        EventPartner::create(['event_id' => $event->id, 'organizer_id' => $organizer->id, 'type' => 'partner', 'name' => 'Orphan Corp', 'is_visible' => true, 'sort_order' => 0]);

        $eventId = $event->id;
        $event->delete();

        $this->assertDatabaseMissing('event_partners', ['event_id' => $eventId]);
    }
}
