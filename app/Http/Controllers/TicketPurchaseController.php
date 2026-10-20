<?php

namespace App\Http\Controllers;

use App\Models\Discount;
use App\Models\Event;
use App\Models\Order;
use App\Models\Ticket;
use App\Models\TicketResponse;
use App\Models\TicketType;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Inertia\Inertia;

class TicketPurchaseController extends Controller
{
    /**
     * Show event details and ticket selection page
     * Redirect to the merged event details page
     */
    public function show(Event $event)
    {
        // Redirect to the event details page which now includes ticket purchase functionality
        return redirect()->route('events.show', $event);
    }

    /**
     * Show buyer and attendee form page
     */
    public function showBuyerForm(Request $request, Event $event)
    {
        // Check if event is purchasable
        if (! $event->isPurchasable()) {
            return redirect()->route('events.show', $event)
                ->withErrors(['error' => $event->getPurchasabilityReason()]);
        }

        // Validate that we have ticket selections
        $validator = Validator::make($request->all(), [
            'ticket_selections' => 'required|array|min:1',
            'ticket_selections.*.ticket_type_id' => 'required|exists:ticket_types,id',
            'ticket_selections.*.quantity' => 'required|integer|min:1|max:10',
            'discount_id' => 'nullable|exists:discounts,id',
        ]);

        if ($validator->fails()) {
            return redirect()->route('events.show', $event)
                ->withErrors(['error' => 'Please select tickets before proceeding.']);
        }

        // Get Custom form fields

        $customFields = $event->formFields()->get();

        // Get ticket types for this event
        $ticketTypes = $event->ticketTypes()->available()->get();

        // Validate that all selected ticket types belong to this event
        foreach ($request->ticket_selections as $selection) {
            $ticketType = $ticketTypes->find($selection['ticket_type_id']);
            if (! $ticketType) {
                return redirect()->route('events.show', $event)
                    ->withErrors(['error' => 'Invalid ticket selection.']);
            }

            // Check availability
            if ($ticketType->available_quantity < $selection['quantity']) {
                return redirect()->route('events.show', $event)
                    ->withErrors(['error' => "Not enough tickets available for {$ticketType->name}."]);
            }
        }

        // Get discount if provided
        $appliedDiscount = null;
        if ($request->discount_id) {
            $appliedDiscount = \App\Models\Discount::find($request->discount_id);
            if ($appliedDiscount && $appliedDiscount->is_active) {
                // Calculate discount amount for display
                $subtotal = collect($request->ticket_selections)->sum(function ($selection) use ($ticketTypes) {
                    $ticketType = $ticketTypes->find($selection['ticket_type_id']);

                    return $ticketType ? $ticketType->price * $selection['quantity'] : 0;
                });

                $appliedDiscount->amount = $appliedDiscount->amountFor((float) $subtotal);
            } else {
                $appliedDiscount = null;
            }
        }

        return Inertia::render('TicketBuyerForm', [
            'event' => $event->load('organizer'),
            'ticketTypes' => $ticketTypes,
            'ticketSelections' => $request->ticket_selections,
            'appliedDiscount' => $appliedDiscount,
            'custom_fields' => $customFields
        ]);
    }

    /**
     * Process ticket purchase
     */
    public function purchase(Request $request, Event $event)
    {
        // Honeypot protection
        if (!empty($request->input('honeypot'))) {
            Log::warning('Ticket purchase spam attempt detected', [
                'event_id' => $event->id,
                'honeypot_value' => $request->input('honeypot'),
                'ip' => $request->ip(),
                'buyer_email' => $request->input('buyer_email'),
            ]);

            // Return back with generic error to avoid revealing honeypot
            return back()->withErrors(['error' => 'Unable to process your request at this time. Please try again.'])->withInput();
        }

        $validator = Validator::make($request->all(), [
            'buyer_email' => 'required|email',
            'buyer_phone' => 'nullable|string',
            'discount_id' => 'nullable|exists:discounts,id',
            'tickets' => 'required|array|min:1',
            'tickets.*.ticket_type_id' => 'required|exists:ticket_types,id',
            'tickets.*.quantity' => 'required|integer|min:1|max:10',
            'tickets.*.attendees' => 'required|array',
            'tickets.*.attendees.*.name' => 'required|string|max:255',
            'tickets.*.attendees.*.email' => 'nullable|email',
            'tickets.*.attendees.*.custom_fields' => 'nullable|array',
            'tickets.*.attendees.*.custom_fields.*' => 'nullable|string|max:1000',
            'honeypot' => 'nullable|string|max:0', // Should be empty
        ]);

        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput();
        }

        try {
            DB::beginTransaction();

            // Validate event is still purchasable before processing
            if (! $event->isPurchasable()) {
                throw new \Exception($event->getPurchasabilityReason() ?? 'Tickets are no longer available for this event.');
            }

            // Calculate total amount and validate availability
            $totalAmount = 0;
            $ticketDetails = [];
            $discount = null;
            $discountAmount = 0;

            foreach ($request->tickets as $ticketRequest) {
                // Use pessimistic locking to prevent race conditions
                $ticketType = TicketType::lockForUpdate()->findOrFail($ticketRequest['ticket_type_id']);

                // Check if ticket type belongs to this event
                if ($ticketType->event_id !== $event->id) {
                    throw new \Exception('Invalid ticket type for this event');
                }

                // Check if ticket is purchasable (within date range)
                if (! $ticketType->isPurchasable()) {
                    if (! $ticketType->salesStarted()) {
                        throw new \Exception("Sales for {$ticketType->name} have not started yet");
                    }
                    if ($ticketType->salesEnded()) {
                        throw new \Exception("Sales for {$ticketType->name} have ended");
                    }
                }

                // Check availability using calculated available quantity (excludes cancelled tickets)
                $availableQuantity = $ticketType->available_quantity;
                if ($availableQuantity < $ticketRequest['quantity']) {
                    throw new \Exception("Not enough tickets available for {$ticketType->name}. Only {$availableQuantity} tickets remaining.");
                }

                // Check if attendees count matches quantity
                if (count($ticketRequest['attendees']) !== $ticketRequest['quantity']) {
                    throw new \Exception("Number of attendees must match ticket quantity for {$ticketType->name}");
                }

                $ticketDetails[] = [
                    'ticket_type' => $ticketType,
                    'quantity' => $ticketRequest['quantity'],
                    'attendees' => $ticketRequest['attendees'],
                    'subtotal' => $ticketType->price * $ticketRequest['quantity'],
                ];

                $totalAmount += $ticketType->price * $ticketRequest['quantity'];
            }

            // Handle discount if provided
            if ($request->discount_id) {
                $discount = Discount::findOrFail($request->discount_id);

                // Validate discount is still usable
                if (! $discount->is_active) {
                    throw new \Exception('Discount code is no longer active');
                }

                if ($discount->usage_limit && $discount->used_count >= $discount->usage_limit) {
                    throw new \Exception('Discount code has reached its usage limit');
                }

                if ($discount->event_id && $discount->event_id !== $event->id) {
                    throw new \Exception('Discount code is not valid for this event');
                }

                // Calculate discount amount
                $discountAmount = $discount->amountFor((float) $totalAmount);

                // Apply discount
                $totalAmount = max(0, $totalAmount - $discountAmount);
            }

            Log::info($event);
            // Create order
            $order = Order::create([
                'event_id' => $event->id,
                'organizer_id' => $event->organizer_id,
                'buyer_email' => $request->buyer_email,
                'buyer_phone' => $request->buyer_phone,
                'subtotal_amount' => $totalAmount + $discountAmount, // Original amount before discount
                'discount_id' => $discount ? $discount->id : null,
                'discount_amount' => $discountAmount,
                'total_amount' => $totalAmount,
                'status' => 'pending',
            ]);

            // Create tickets with attendee information
            foreach ($ticketDetails as $detail) {
                $ticketType = $detail['ticket_type'];

                // Atomically reduce available quantity with safety check
                $updated = TicketType::where('id', $ticketType->id)
                    ->where('quantity', '>=', $detail['quantity'])
                    ->decrement('quantity', $detail['quantity']);

                if (! $updated) {
                    throw new \Exception("Unable to reserve {$detail['quantity']} tickets for {$ticketType->name}. Another customer may have purchased these tickets.");
                }

                // Create individual tickets for each attendee
                foreach ($detail['attendees'] as $attendee) {
                    $ticket = Ticket::create([
                        'order_id' => $order->id,
                        'event_id' => $event->id,
                        'organizer_id' => $event->organizer_id,
                        'ticket_type_id' => $ticketType->id,
                        'attendee_name' => $attendee['name'],
                        'attendee_email' => $attendee['email'] ?? null,
                        'price' => $ticketType->price,
                        'status' => 'pending',
                    ]);

                    if (!empty($attendee['custom_fields'])) {
                        foreach ($attendee['custom_fields'] as $fieldId => $value) {
                            if (!empty($value)) {
                                TicketResponse::create([
                                    'ticket_id' => $ticket->id,
                                    'event_form_field_id' => $fieldId,
                                    'value' => is_array($value) ? implode(', ', $value) : $value,
                                ]);
                            }
                        }
                    }
                }
            }

            // Increment discount usage count if discount was applied
            if ($discount) {
                $discount->increment('used_count');
            }

            DB::commit();

            // Initialize payment immediately
            try {
                $paystackService = app(\App\Services\PaystackService::class);
                $paymentResult = $paystackService->initializePayment($order);

                if ($paymentResult['success']) {
                    // Use Inertia::location for external redirects
                    return \Inertia\Inertia::location($paymentResult['data']['authorization_url']);
                } else {
                    // If payment initialization fails, redirect to confirmation with error
                    return redirect()->route('orders.confirmation', $order->order_number)
                        ->withErrors(['payment' => $paymentResult['message']]);
                }
            } catch (\Exception $e) {
                Log::error('Payment initialization failed: '.$e->getMessage());

                // Fallback to confirmation page if payment fails
                return redirect()->route('orders.confirmation', $order->order_number)
                    ->withErrors(['payment' => 'Payment initialization failed. Please contact support.']);
            }

        } catch (\Exception $e) {
            DB::rollback();
            Log::error($e);

            return back()->withErrors(['error' => $e->getMessage()])->withInput();
        }
    }

    /**
     * Show order confirmation
     */
    public function confirmation(string $orderNumber)
    {
        $order = Order::where('order_number', $orderNumber)
            ->with(['event', 'tickets.ticketType', 'discount'])
            ->firstOrFail();

        return Inertia::render('TicketConfirmation', [
            'order' => $order->toArray(),
            'tickets' => $order->tickets->toArray(),
        ]);
    }

    /**
     * Get available ticket types for an event (API endpoint)
     */
    public function getAvailableTickets(Event $event)
    {
        // Check if event is purchasable
        if (! $event->isPurchasable()) {
            return response()->json([
                'error' => $event->getPurchasabilityReason(),
                'purchasable' => false,
                'ticket_types' => [],
                'event' => $event->only(['id', 'title', 'start_date', 'start_time', 'status']),
            ], 422);
        }

        $ticketTypes = $event->ticketTypes()
            ->available()
            ->get()
            ->filter(function ($ticketType) {
                // Filter out ticket types with no available quantity (including cancelled tickets)
                return $ticketType->available_quantity > 0;
            })
            ->values(); // Reset array keys

        return response()->json([
            'purchasable' => true,
            'ticket_types' => $ticketTypes,
            'event' => $event->only(['id', 'title', 'start_date', 'start_time', 'status']),
        ]);
    }

    /**
     * Download tickets for an order (future implementation)
     */
    public function downloadTickets(string $orderNumber)
    {
        $order = Order::where('order_number', $orderNumber)
            ->with(['tickets.ticketType', 'event'])
            ->firstOrFail();

        // TODO: Generate PDF with QR codes
        // For now, return JSON response
        return response()->json([
            'message' => 'Ticket download functionality to be implemented',
            'order' => $order,
        ]);
    }
}
