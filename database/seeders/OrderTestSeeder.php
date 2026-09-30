<?php

namespace Database\Seeders;

use App\Models\Event;
use App\Models\Order;
use App\Models\Organizer;
use App\Models\Ticket;
use App\Models\TicketType;
use App\Models\Venue;
use Illuminate\Database\Seeder;

class OrderTestSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Get or create organizer
        $organizer = Organizer::first() ?? Organizer::create([
            'name' => 'Test Organizer',
            'email' => 'organizer@test.com',
            'phone' => '+1234567890',
            'website' => 'https://testorganizer.com',
            'description' => 'Test organizer for demo purposes',
        ]);

        // Get or create venue
        $venue = Venue::first() ?? Venue::create([
            'name' => 'Test Venue',
            'address' => '123 Test Street',
            'city' => 'Test City',
            'state' => 'Test State',
            'country' => 'Test Country',
            'postal_code' => '12345',
            'capacity' => 1000,
        ]);

        // Get or create event
        $event = Event::first() ?? Event::create([
            'title' => 'Test Concert Event',
            'description' => 'A fantastic test concert for demonstration purposes.',
            'start_date' => '2024-12-25',
            'start_time' => '19:00:00',
            'end_date' => '2024-12-25',
            'end_time' => '23:00:00',
            'organizer_id' => $organizer->id,
            'venue_id' => $venue->id,
            'status' => 'published',
            'event_type' => 'concert',
        ]);

        // Get or create ticket type
        $ticketType = TicketType::where('event_id', $event->id)->first() ?? TicketType::create([
            'event_id' => $event->id,
            'name' => 'General Admission',
            'description' => 'Standard ticket for general admission',
            'price' => 75.00,
            'quantity' => 100,
            'available_quantity' => 98,
        ]);

        // Create test order if it doesn't exist
        $order = Order::where('order_number', 'ORD-TEST123456')->first();
        if (! $order) {
            $order = Order::create([
                'order_number' => 'ORD-TEST123456',
                'event_id' => $event->id,
                'buyer_email' => 'test@example.com',
                'buyer_phone' => '+1234567890',
                'total_amount' => 150.00,
                'status' => 'confirmed',
                'payment_method' => 'stripe',
                'payment_reference' => 'pi_test123',
                'purchased_at' => now(),
            ]);

            // Create tickets for the order
            Ticket::create([
                'order_id' => $order->id,
                'event_id' => $event->id,
                'ticket_type_id' => $ticketType->id,
                'attendee_name' => 'John Doe',
                'attendee_email' => 'john@example.com',
                'price' => 75.00,
                'status' => 'active',
            ]);

            Ticket::create([
                'order_id' => $order->id,
                'event_id' => $event->id,
                'ticket_type_id' => $ticketType->id,
                'attendee_name' => 'Jane Doe',
                'attendee_email' => 'jane@example.com',
                'price' => 75.00,
                'status' => 'active',
            ]);

            $this->command->info("Test order created: {$order->order_number} for event: {$event->title}");
        } else {
            $this->command->info("Test order already exists: {$order->order_number}");
        }
    }
}
