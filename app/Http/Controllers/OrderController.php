<?php

namespace App\Http\Controllers;

use App\Mail\OrderEmail;
use App\Mail\TicketEmail;
use App\Models\Order;
use App\Services\SeoService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;
use Inertia\Inertia;
use Inertia\Response;

class OrderController extends Controller
{
    /**
     * Show the order search page
     */
    public function searchPage(Request $request): Response
    {
        $orderNumber = $request->query('order');

        SeoService::setOrderSearchSEO();

        return Inertia::render('OrderSearch', [
            'initialOrderNumber' => $orderNumber,
            'seo' => SeoService::orderSearch(),
        ]);
    }

    /**
     * Search for an order by order number
     */
    public function search(Request $request)
    {
        $request->validate([
            'order_number' => 'required|string|max:255',
            'verification_email' => 'nullable|email|required_without:verification_phone',
            'verification_phone' => 'nullable|string|max:20|required_without:verification_email',
        ]);

        $orderNumber = strtoupper(trim($request->order_number));
        $verificationEmail = $request->verification_email ? trim($request->verification_email) : null;
        $verificationPhone = $request->verification_phone ? trim($request->verification_phone) : null;

        // Add basic rate limiting to prevent abuse
        if (strlen($orderNumber) < 3) {
            return response()->json([
                'success' => false,
                'message' => 'Order number must be at least 3 characters long.',
            ], 400);
        }

        // Ensure at least one verification method is provided
        if (! $verificationEmail && ! $verificationPhone) {
            return response()->json([
                'success' => false,
                'message' => 'Please provide either email address or phone number for verification.',
            ], 400);
        }

        // Find the order with its relationships
        $query = Order::with([
            'event' => function ($query) {
                $query->select('id', 'title', 'description', 'start_date', 'start_time', 'end_date', 'end_time', 'cover_image', 'venue_name', 'venue_address', 'organizer_id');
            },
            'event.organizer' => function ($query) {
                $query->select('id', 'name', 'email', 'phone');
            },
            'tickets' => function ($query) {
                $query->select('id', 'order_id', 'ticket_number', 'ticket_type_id', 'attendee_name', 'attendee_email', 'price', 'status', 'used_at');
            },
            'tickets.ticketType' => function ($query) {
                $query->select('id', 'name', 'description', 'price');
            },
        ])
            ->where('order_number', $orderNumber);

        // Add email or phone verification
        if ($verificationEmail && $verificationPhone) {
            // If both are provided, match either one
            $query->where(function ($subQuery) use ($verificationEmail, $verificationPhone) {
                $subQuery->where('buyer_email', $verificationEmail)
                    ->orWhere('buyer_phone', $verificationPhone);
            });
        } elseif ($verificationEmail) {
            $query->where('buyer_email', $verificationEmail);
        } elseif ($verificationPhone) {
            $query->where('buyer_phone', $verificationPhone);
        }

        $order = $query->first();

        if (! $order) {
            return response()->json([
                'success' => false,
                'message' => 'Order not found or verification details do not match. Please check your order number and email/phone number.',
            ], 404);
        }

        // Return the order data
        return response()->json([
            'success' => true,
            'order' => [
                'id' => $order->id,
                'order_number' => $order->order_number,
                'buyer_email' => $order->buyer_email,
                'buyer_phone' => $order->buyer_phone,
                'total_amount' => $order->total_amount,
                'status' => $order->status,
                'payment_method' => $order->payment_method,
                'purchased_at' => $order->purchased_at,
                'created_at' => $order->created_at,
                'total_tickets' => $order->tickets->count(),
                'event' => [
                    'id' => $order->event->id,
                    'title' => $order->event->title,
                    'description' => $order->event->description,
                    'start_date' => $order->event->start_date,
                    'start_time' => $order->event->start_time,
                    'end_date' => $order->event->end_date,
                    'end_time' => $order->event->end_time,
                    'cover_image' => $order->event->cover_image,
                    'venue_name' => $order->event->venue_name,
                    'venue_address' => $order->event->venue_address,
                    'organizer' => $order->event->organizer ? [
                        'name' => $order->event->organizer->name,
                        'email' => $order->event->organizer->email,
                        'phone' => $order->event->organizer->phone,
                    ] : null,
                ],
                'tickets' => $order->tickets->map(function ($ticket) {
                    return [
                        'id'             => $ticket->id,
                        'ticket_number'  => $ticket->ticket_number,
                        'attendee_name'  => $ticket->attendee_name,
                        'attendee_email' => $ticket->attendee_email,
                        'price'          => $ticket->price,
                        'status'         => $ticket->status,
                        'used_at'        => $ticket->used_at,
                        'ticket_type'    => $ticket->ticketType ? [
                            'name'        => $ticket->ticketType->name,
                            'description' => $ticket->ticketType->description,
                            'price'       => $ticket->ticketType->price,
                        ] : null,
                        'custom_field_responses' => $ticket->responses
                            ->map(fn ($r) => [
                                'label' => $r->field?->label,
                                'value' => $r->value,
                            ])
                            ->filter(fn ($r) => $r['label'] !== null)
                            ->values()
                            ->toArray(),
                    ];
                }),
            ],
        ]);
    }

    /**
     * Share tickets via email to attendees
     */
    public function shareTickets(Request $request)
    {
        $request->validate([
            'order_id' => 'required|exists:orders,id',
            'order_number' => 'required|string',
        ]);

        try {
            $order = Order::with([
                'event' => function ($query) {
                    $query->select('id', 'title', 'description', 'start_date', 'start_time', 'end_date', 'end_time', 'cover_image', 'venue_name', 'venue_address', 'organizer_id');
                },
                'event.organizer' => function ($query) {
                    $query->select('id', 'name', 'email', 'phone');
                },
                'tickets' => function ($query) {
                    $query->select('id', 'order_id', 'ticket_number', 'ticket_type_id', 'attendee_name', 'attendee_email', 'price', 'status', 'used_at');
                },
                'tickets.ticketType' => function ($query) {
                    $query->select('id', 'name', 'description', 'price');
                },
            ])->findOrFail($request->order_id);

            $sentCount = 0;
            $errors = [];

            foreach ($order->tickets as $ticket) {
                try {
                    // Only send to active tickets
                    if ($ticket->status === 'active' && $ticket->attendee_email && $ticket->attendee_email != $order->buyer_email) {
                        Mail::to($ticket->attendee_email)->sendNow(new TicketEmail($ticket, $order));
                        $sentCount++;
                    }
                } catch (\Exception $e) {
                    $errors[] = "Failed to send to {$ticket->attendee_email}: ".$e->getMessage();
                }
            }

            if ($sentCount > 0) {
                return response()->json([
                    'success' => true,
                    'message' => "Successfully sent {$sentCount} ticket(s) to attendees.",
                    'sent_count' => $sentCount,
                    'errors' => $errors,
                ]);
            } else {
                return response()->json([
                    'success' => false,
                    'message' => 'No tickets were sent. Please check that tickets are active and have valid email addresses.',
                    'errors' => $errors,
                ], 400);
            }

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'An error occurred while sending tickets: '.$e->getMessage(),
            ], 500);
        }
    }

    /**
     * Send order confirmation email to buyer
     */
    public function sendOrderConfirmation(Request $request)
    {
        $request->validate([
            'order_id' => 'required|exists:orders,id',
            'order_number' => 'required|string',
        ]);

        try {
            $order = Order::with([
                'event' => function ($query) {
                    $query->select('id', 'title', 'description', 'start_date', 'start_time', 'end_date', 'end_time', 'cover_image', 'venue_name', 'venue_address', 'organizer_id');
                },
                'event.organizer' => function ($query) {
                    $query->select('id', 'name', 'email', 'phone');
                },
                'tickets' => function ($query) {
                    $query->select('id', 'order_id', 'ticket_number', 'ticket_type_id', 'attendee_name', 'attendee_email', 'price', 'status', 'used_at');
                },
                'tickets.ticketType' => function ($query) {
                    $query->select('id', 'name', 'description', 'price');
                },
            ])->findOrFail($request->order_id);

            // Send order confirmation email to buyer
            Mail::to($order->buyer_email)->sendNow(new OrderEmail($order));

            return response()->json([
                'success' => true,
                'message' => 'Order confirmation email sent successfully!',
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to send order confirmation email: '.$e->getMessage(),
            ], 500);
        }
    }

    /**
     * Send both order confirmation and individual ticket emails
     */
    public function sendAllEmails(Request $request)
    {
        $request->validate([
            'order_id' => 'required|exists:orders,id',
            'order_number' => 'required|string',
        ]);

        try {
            $order = Order::with([
                'event' => function ($query) {
                    $query->select('id', 'title', 'description', 'start_date', 'start_time', 'end_date', 'end_time', 'cover_image', 'venue_name', 'venue_address', 'organizer_id');
                },
                'event.organizer' => function ($query) {
                    $query->select('id', 'name', 'email', 'phone');
                },
                'tickets' => function ($query) {
                    $query->select('id', 'order_id', 'ticket_number', 'ticket_type_id', 'attendee_name', 'attendee_email', 'price', 'status', 'used_at');
                },
                'tickets.ticketType' => function ($query) {
                    $query->select('id', 'name', 'description', 'price');
                },
            ])->findOrFail($request->order_id);

            $sentEmails = 0;
            $errors = [];

            // Send order confirmation email to buyer
            try {
                Mail::to($order->buyer_email)->sendNow(new OrderEmail($order));
                $sentEmails++;
            } catch (\Exception $e) {
                $errors[] = "Failed to send order confirmation to {$order->buyer_email}: ".$e->getMessage();
            }

            // Send individual ticket emails to attendees
            foreach ($order->tickets as $ticket) {
                try {
                    if ($ticket->status === 'active' && $ticket->attendee_email && $ticket->attendee_email != $order->buyer_email) {
                        Mail::to($ticket->attendee_email)->sendNow(new TicketEmail($ticket, $order));
                        $sentEmails++;
                    }
                } catch (\Exception $e) {
                    $errors[] = "Failed to send ticket to {$ticket->attendee_email}: ".$e->getMessage();
                }
            }

            return response()->json([
                'success' => $sentEmails > 0,
                'message' => $sentEmails > 0
                    ? "Successfully sent {$sentEmails} email(s) (1 order confirmation + ".($sentEmails - 1).' tickets)'
                    : 'No emails were sent.',
                'sent_count' => $sentEmails,
                'errors' => $errors,
            ], $sentEmails > 0 ? 200 : 400);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'An error occurred while sending emails: '.$e->getMessage(),
            ], 500);
        }
    }

    /**
     * Get order details by order number (for check-in purposes)
     */
    public function getOrderByNumber(Request $request, string $orderNumber): JsonResponse
    {
        if (! Auth::check()) {
            return response()->json([
                'success' => false,
                'message' => 'Authentication required',
            ], 401);
        }

        try {
            $order = Order::with([
                'event' => function ($query) {
                    $query->select('id', 'title', 'start_date', 'start_time', 'venue_name', 'venue_address', 'organizer_id');
                },
                'event.organizer' => function ($query) {
                    $query->select('id', 'name');
                },
                'tickets' => function ($query) {
                    $query->select('id', 'order_id', 'ticket_number', 'attendee_name', 'attendee_email', 'price', 'status', 'used_at', 'event_id', 'ticket_type_id');
                },
                'tickets.responses.field',
                'tickets.ticketType' => function ($query) {
                    $query->select('id', 'name', 'description');
                },
                'tickets.event' => function ($query) {
                    $query->select('id', 'title', 'start_date', 'start_time', 'venue_name', 'venue_address', 'organizer_id');
                },
                'tickets.event.organizer' => function ($query) {
                    $query->select('id', 'name');
                },
            ])->where('order_number', strtoupper($orderNumber))->first();

            if (! $order) {
                return response()->json([
                    'success' => false,
                    'message' => 'Order not found',
                ], 404);
            }

            // Check if user is authorized for this order's event organizer
            $user = Auth::user();
            if (! $user->organizers->contains('id', $order->event->organizer_id)) {
                return response()->json([
                    'success' => false,
                    'message' => 'You are not authorized to view this order',
                ], 403);
            }

            return response()->json([
                'success' => true,
                'order' => $this->formatOrderForCheckIn($order),
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error retrieving order: '.$e->getMessage(),
            ], 500);
        }
    }

    /**
     * Format order response for check-in purposes
     */
    private function formatOrderForCheckIn(Order $order): array
    {
        return [
            'id' => $order->id,
            'order_number' => $order->order_number,
            'buyer_email' => $order->buyer_email,
            'buyer_phone' => $order->buyer_phone,
            'status' => $order->status,
            'total_amount' => $order->total_amount,
            'purchased_at' => $order->purchased_at,
            'event' => [
                'id' => $order->event->id,
                'title' => $order->event->title,
                'start_date' => $order->event->start_date,
                'start_time' => $order->event->start_time,
                'venue_name' => $order->event->venue_name,
                'venue_address' => $order->event->venue_address,
                'organizer' => $order->event->organizer ? [
                    'id' => $order->event->organizer->id,
                    'name' => $order->event->organizer->name,
                ] : null,
            ],
            'tickets' => $order->tickets->map(function ($ticket) use ($order) {
                return [
                    'id' => $ticket->id,
                    'ticket_number' => $ticket->ticket_number,
                    'attendee_name' => $ticket->attendee_name,
                    'attendee_email' => $ticket->attendee_email,
                    'price' => $ticket->price,
                    'status' => $ticket->status,
                    'used_at' => $ticket->used_at,
                    'custom_field_responses' => $ticket->responses
                        ->map(fn ($r) => [
                            'label' => $r->field?->label,
                            'value' => $r->value,
                        ])
                        ->filter(fn ($r) => $r['label'] !== null)
                        ->values()
                        ->toArray(),
                    'ticket_type' => $ticket->ticketType ? [
                        'id' => $ticket->ticketType->id,
                        'name' => $ticket->ticketType->name,
                        'description' => $ticket->ticketType->description,
                    ] : null,
                    'event' => [
                        'id' => $ticket->event->id,
                        'title' => $ticket->event->title,
                        'start_date' => $ticket->event->start_date,
                        'start_time' => $ticket->event->start_time,
                        'venue_name' => $ticket->event->venue_name,
                        'venue_address' => $ticket->event->venue_address,
                        'organizer' => $ticket->event->organizer ? [
                            'id' => $ticket->event->organizer->id,
                            'name' => $ticket->event->organizer->name,
                        ] : null,
                    ],
                    'order' => [
                        'order_number' => $order->order_number,
                        'status' => $order->status,
                    ],
                ];
            }),
        ];
    }

    /**
     * Show order details page (if needed for future use)
     */
    public function show($orderNumber): Response
    {

        $order = Order::where('order_number', $orderNumber)->firstOrFail();
        // dd($order);
        $order->load([
            'event.organizer',
            'tickets.ticketType',
            'tickets.responses.field',
            'discount',
        ]);

        return Inertia::render('OrderDetails', [
            'order'   => $order->toArray(),
            'tickets' => $order->tickets->map(function ($ticket) {
                $ticketArray = $ticket->toArray();
                $ticketArray['custom_field_responses'] = $ticket->responses
                    ->map(fn ($r) => [
                        'label' => $r->field?->label,
                        'value' => $r->value,
                    ])
                    ->filter(fn ($r) => $r['label'] !== null)
                    ->values()
                    ->toArray();
                return $ticketArray;
            }),
            'seo' => SeoService::orderDetails($order),
        ]);
    }
}
