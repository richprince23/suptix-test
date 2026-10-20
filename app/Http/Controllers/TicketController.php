<?php

namespace App\Http\Controllers;

use App\Http\Resources\TicketResource;
use App\Models\Event;
use App\Models\Organizer;
use App\Models\Ticket;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;
use Inertia\Response;

class TicketController extends Controller
{
    /**
     * Verify a ticket by ticket number (for QR code scanning)
     */
    public function verify(string $ticketNumber): Response
    {
        $ticket = Ticket::with([
            'order',
            'responses.field',
            'event' => function ($query) {
                $query->select('id', 'title', 'start_date', 'start_time', 'venue_name', 'venue_address', 'organizer_id');
            },
            'event.organizer' => function ($query) {
                $query->select('id', 'name');
            },
            'ticketType',
        ])
            ->where('ticket_number', $ticketNumber)
            ->first();

        // Check if authenticated user can mark this ticket as used
        $canMarkAsUsed = false;
        if (Auth::check() && $ticket) {
            $user = Auth::user();
            $eventOrganizerId = $ticket->event->organizer_id;
            $canMarkAsUsed = $user->organizers->contains('id', $eventOrganizerId);
        }

        return Inertia::render('TicketVerification', [
            'ticket' => $ticket ? [
                'id' => $ticket->id,
                'ticket_number' => $ticket->ticket_number,
                'attendee_name' => $ticket->attendee_name,
                ...($canMarkAsUsed ? ['attendee_email' => $ticket->attendee_email] : []),
                'price' => $ticket->price,
                'status' => $ticket->status,
                'used_at' => $ticket->used_at,
                'event' => [
                    'title' => $ticket->event->title,
                    'start_date' => $ticket->event->start_date,
                    'start_time' => $ticket->event->start_time,
                    'venue_name' => $ticket->event->venue_name,
                    'venue_address' => $ticket->event->venue_address,
                    'organizer' => $ticket->event->organizer ? [
                        'name' => $ticket->event->organizer->name,
                    ] : null,
                ],
                'ticket_type' => $ticket->ticketType ? [
                    'name' => $ticket->ticketType->name,
                    'description' => $ticket->ticketType->description,
                ] : null,
                'order' => [
                    'order_number' => $ticket->order->order_number,
                    'status' => $ticket->order->status,
                ],
            ] : null,
            'ticketNumber' => $ticketNumber,
            'canMarkAsUsed' => $canMarkAsUsed,
        ]);
    }

    /**
     * Verify ticket by ticket number (for check-in purposes)
     */
    public function verifyByNumber(Request $request): JsonResponse
    {
        $request->validate([
            'ticket_number' => 'required|string',
        ]);

        try {
            $ticket = Ticket::with([
                'order',
                'responses.field',
                'event' => function ($query) {
                    $query->select('id', 'title', 'start_date', 'start_time', 'venue_name', 'venue_address', 'organizer_id');
                },
                'event.organizer' => function ($query) {
                    $query->select('id', 'name');
                },
                'ticketType',
            ])
                ->where('ticket_number', strtoupper($request->ticket_number))
                ->first();

            if (! $ticket) {
                return response()->json([
                    'success' => false,
                    'message' => 'Ticket not found',
                ], 404);
            }

            // Check if user is authorized for this ticket's event organizer (only if authenticated)
            if (Auth::check()) {
                $user = Auth::user();
                if (! $user->organizers->contains('id', $ticket->event->organizer_id)) {
                    return response()->json([
                        'success' => false,
                        'message' => 'You are not authorized to view this ticket',
                    ], 403);
                }
            }

            return response()->json([
                'success' => true,
                'message' => 'Ticket found successfully',
                'ticket' => $this->formatTicketResponse($ticket),
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error retrieving ticket: '.$e->getMessage(),
            ], 500);
        }
    }

    /**
     * Mark a ticket as used (for event organizers)
     */
    public function markAsUsed(Request $request, Ticket $ticket)
    {
        // Check if user is authenticated
        if (! Auth::check()) {
            return response()->json([
                'success' => false,
                'message' => 'Authentication required to mark tickets as used.',
            ], 401);
        }

        // Check if the authenticated user belongs to the event's organizer
        $user = Auth::user();
        $eventOrganizerId = $ticket->event->organizer_id;

        if (! $user->organizers->contains('id', $eventOrganizerId)) {
            return response()->json([
                'success' => false,
                'message' => 'You are not authorized to mark this ticket as used.',
            ], 403);
        }

        if ($ticket->status !== 'active') {
            return response()->json([
                'success' => false,
                'message' => 'This ticket is not active and cannot be used.',
            ], 400);
        }

        if (! $ticket->markAsUsed()) {
            return response()->json([
                'success' => false,
                'message' => 'This ticket has already been used.',
            ], 400);
        }

        $ticket->load(['order', 'responses.field', 'ticketType', 'event.organizer']);

        return response()->json([
            'success' => true,
            'message' => 'Ticket marked as used successfully.',
            'ticket' => $this->formatTicketResponse($ticket),
        ]);
    }

    /**
     * Scan and validate ticket for mobile apps (supports both QR data and ticket number)
     */
    public function scanQr(Request $request)
    {
        $request->validate([
            'qr_data' => 'required_without:ticket_number|string',
            'ticket_number' => 'required_without:qr_data|string',
        ]);

        try {
            $ticketNumber = null;
            $qrData = null;

            // Handle ticket number directly (mobile sends just ticket number)
            if ($request->has('ticket_number')) {
                $ticketNumber = strtoupper(trim($request->ticket_number));
            }
            // Handle legacy QR data format
            elseif ($request->has('qr_data')) {
                // Try to decode as JSON first
                $decoded = json_decode($request->qr_data, true);

                if (json_last_error() === JSON_ERROR_NONE && isset($decoded['ticket_number'])) {
                    // Valid JSON QR data format
                    $qrData = $decoded;
                    $ticketNumber = $qrData['ticket_number'];
                } else {
                    // Assume QR data is just the ticket number as plain text
                    $ticketNumber = strtoupper(trim($request->qr_data));
                }
            }

            if (! $ticketNumber) {
                return response()->json([
                    'success' => false,
                    'message' => 'Ticket number is required',
                ], 400);
            }

            // Find the ticket in database
            $ticket = Ticket::with([
                'order',
                'responses.field',
                'event' => function ($query) {
                    $query->select('id', 'title', 'start_date', 'start_time', 'venue_name', 'venue_address', 'organizer_id');
                },
                'event.organizer' => function ($query) {
                    $query->select('id', 'name');
                },
                'ticketType',
            ])
                ->where('ticket_number', $ticketNumber)
                ->first();

            if (! $ticket) {
                return response()->json([
                    'success' => false,
                    'message' => 'Ticket not found',
                ], 404);
            }

            // If we have QR data, validate it against database records
            if ($qrData && ! $this->validateQrData($qrData, $ticket)) {
                return response()->json([
                    'success' => false,
                    'message' => 'QR code data does not match database records',
                ], 400);
            }

            // Only an active ticket admits its holder
            $statusProblem = match ($ticket->status) {
                'active' => null,
                'used' => 'This ticket has already been used.',
                'pending' => 'This ticket has not been paid for.',
                'cancelled' => 'This ticket has been cancelled.',
                default => 'This ticket is not valid.',
            };

            // Personal data is for staff of the ticket's organiser only
            $user = $request->user('sanctum') ?? $request->user();
            $isStaff = $user && $user->organizers->contains('id', $ticket->event->organizer_id);

            if ($statusProblem) {
                return response()->json([
                    'success' => false,
                    'message' => $statusProblem,
                    'ticket' => $this->formatTicketResponse($ticket, $isStaff),
                ], 400);
            }

            return response()->json([
                'success' => true,
                'message' => 'Ticket verification successful',
                'ticket' => $this->formatTicketResponse($ticket, $isStaff),
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error processing ticket verification: '.$e->getMessage(),
            ], 500);
        }
    }

    /**
     * Validate QR data against database ticket
     */
    private function validateQrData(array $qrData, Ticket $ticket): bool
    {
        // Check core ticket details
        if (($qrData['ticket_number'] ?? null) !== $ticket->ticket_number ||
            ($qrData['attendee_name'] ?? null) !== $ticket->attendee_name ||
            ! isset($qrData['price']) || $qrData['price'] != $ticket->price) {
            return false;
        }

        // Check event details
        if (isset($qrData['event'])) {
            if (($qrData['event']['title'] ?? null) !== $ticket->event->title ||
                ($qrData['event']['start_date'] ?? null) !== $ticket->event->start_date) {
                return false;
            }
        }

        // Check order details
        if (isset($qrData['order'])) {
            if (($qrData['order']['order_number'] ?? null) !== $ticket->order->order_number) {
                return false;
            }
        }

        return true;
    }

    /**
     * Get all tickets for an event
     *
     * @return AnonymousResourceCollection
     */
    public function getEventTickets(Organizer $organizer, Event $event): AnonymousResourceCollection|JsonResponse
    {
        // Check if user is authenticated and authorized
        $user = Auth::user();
        if (! $user->organizers->contains('id', $organizer->id)) {
            return response()->json([
                'success' => false,
                'message' => 'You are not authorized to view this data.',
            ], 403);
        }

        // Check if event belongs to organizer
        if ($event->organizer_id !== $organizer->id) {
            return response()->json([
                'success' => false,
                'message' => 'This event does not belong to the specified organizer.',
            ], 400);
        }

        // The resource reads the event and organizer of every ticket, so load them in bulk
        $tickets = $event->tickets()->with(['order', 'ticketType', 'event', 'organizer'])->get();

        return response()->json([
            'success' => true,
            'data' => TicketResource::collection($tickets),
            'meta' => [
                'total' => $tickets->count(),
                'event_id' => $event->id,
                'organizer_id' => $organizer->id,
            ],
        ]);
    }

    public function verifyTicket(Organizer $organizer, Event $event, Request $request)
    {
        $request->validate([
            'ticket_number' => 'required_without:qr_data|string',
            'qr_data' => 'required_without:ticket_number|string',
            'verification_method' => 'sometimes|string|in:manual,qr_scan',
        ]);

        $ticket = null;

        // Handle QR data verification
        if ($request->has('qr_data')) {
            try {
                $qrData = json_decode($request->qr_data, true);
                if (json_last_error() !== JSON_ERROR_NONE) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Invalid QR code format',
                    ], 400);
                }

                if (! isset($qrData['ticket_number'])) {
                    return response()->json([
                        'success' => false,
                        'message' => 'QR code missing ticket number',
                    ], 400);
                }

                $ticketNumber = $qrData['ticket_number'];
            } catch (\Exception $e) {
                return response()->json([
                    'success' => false,
                    'message' => 'Error processing QR code data',
                ], 400);
            }
        } else {
            $ticketNumber = $request->ticket_number;
        }

        // Find ticket with relationships
        $ticket = Ticket::with([
            'order',
            'responses.field',
            'event' => function ($query) {
                $query->select('id', 'title', 'start_date', 'start_time', 'venue_name', 'venue_address', 'organizer_id');
            },
            'event.organizer' => function ($query) {
                $query->select('id', 'name');
            },
            'ticketType',
        ])->where('ticket_number', $ticketNumber)->first();

        if (! $ticket) {
            return response()->json([
                'success' => false,
                'message' => 'Ticket not found',
                'ticket' => null,
            ], 404);
        }

        // Check if ticket belongs to event
        if ($ticket->event_id != $event->id) {
            return response()->json([
                'success' => false,
                'message' => 'This ticket is not valid for this event',
                'ticket' => $this->formatTicketResponse($ticket),
            ], 403);
        }

        // Check if event belongs to organizer
        if ($event->organizer_id != $organizer->id) {
            return response()->json([
                'success' => false,
                'message' => 'This ticket\'s event is not valid for this organizer',
                'ticket' => $this->formatTicketResponse($ticket),
            ], 403);
        }

        // Check ticket status
        if ($ticket->status === 'used') {
            return response()->json([
                'success' => false,
                'message' => 'Ticket has already been used',
                'ticket' => $this->formatTicketResponse($ticket),
            ], 400);
        }

        if ($ticket->status !== 'active') {
            return response()->json([
                'success' => false,
                'message' => 'Ticket is not active: '.$ticket->status,
                'ticket' => $this->formatTicketResponse($ticket),
            ], 400);
        }

        return response()->json([
            'success' => true,
            'message' => 'Ticket verified successfully',
            'ticket' => $this->formatTicketResponse($ticket),
        ], 200);
    }

    /**
     * Show ticket details page (for email links)
     */
    public function details(string $ticketNumber): Response
    {
        $ticket = Ticket::with([
            'order',
            'responses.field',
            'event' => function ($query) {
                $query->select('id', 'title', 'start_date', 'start_time', 'venue_name', 'venue_address', 'organizer_id');
            },
            'event.organizer' => function ($query) {
                $query->select('id', 'name');
            },
            'ticketType',
        ])
            ->where('ticket_number', $ticketNumber)
            ->first();

        return Inertia::render('TicketDetails', [
            'ticket' => $ticket ? [
                'id' => $ticket->id,
                'ticket_number' => $ticket->ticket_number,
                'attendee_name' => $ticket->attendee_name,
                'attendee_email' => $ticket->attendee_email,
                'price' => $ticket->price,
                'status' => $ticket->status,
                'used_at' => $ticket->used_at,
                'event' => [
                    'title' => $ticket->event->title,
                    'start_date' => $ticket->event->start_date,
                    'start_time' => $ticket->event->start_time,
                    'venue_name' => $ticket->event->venue_name,
                    'venue_address' => $ticket->event->venue_address,
                    'organizer' => $ticket->event->organizer ? [
                        'name' => $ticket->event->organizer->name,
                    ] : null,
                ],
                'ticket_type' => $ticket->ticketType ? [
                    'name' => $ticket->ticketType->name,
                    'description' => $ticket->ticketType->description,
                ] : null,
                'order' => [
                    'order_number' => $ticket->order->order_number,
                    'status' => $ticket->order->status,
                ],
                'custom_field_responses' => $ticket->responses
                    ->map(fn ($r) => [
                        'label' => $r->field?->label,
                        'value' => $r->value,
                    ])
                    ->filter(fn ($r) => $r['label'] !== null)
                    ->values()
                    ->toArray(),
            ] : null,
            'ticketNumber' => $ticketNumber,
        ]);
    }

    /**
     * Download ticket as PDF
     */
    public function downloadPdf(Ticket $ticket)
    {
        // Load necessary relationships
        $ticket->load([
            'order',
            'event.organizer',
            'ticketType',
        ]);

        try {
            // Create comprehensive QR code data
            $qrData = [
                'ticket_number' => $ticket->ticket_number,
                'attendee_name' => $ticket->attendee_name,
                'price' => (float) $ticket->price,
                'event' => [
                    'title' => $ticket->event->title,
                    'start_date' => $ticket->event->start_date,
                    'start_time' => $ticket->event->start_time,
                ],
                'order' => [
                    'order_number' => $ticket->order->order_number,
                ],
                'verification_url' => route('tickets.verify', $ticket->ticket_number),
            ];

            // Generate high-quality QR code (400x400 for better resolution)
            $qrCodeUrl = 'https://api.qrserver.com/v1/create-qr-code/?size=400x400&format=png&ecc=M&margin=10&data='.urlencode(json_encode($qrData));

            // Get QR code image data with error handling
            $qrCodeData = @file_get_contents($qrCodeUrl);
            $qrCodeBase64 = $qrCodeData ? 'data:image/png;base64,'.base64_encode($qrCodeData) : null;

            // Ensure price is properly set (fallback to ticket type price if needed)
            $ticketPrice = $ticket->price ?? $ticket->ticketType->price ?? 0;

            // Generate PDF using dompdf with higher quality settings
            $pdf = app('dompdf.wrapper');
            $pdf->loadView('pdf.ticket', compact('ticket', 'qrCodeBase64', 'ticketPrice'));

            // Set higher quality options
            $pdf->setPaper('A4', 'portrait');
            $pdf->getDomPDF()->getOptions()->setChroot(public_path());
            $pdf->getDomPDF()->getOptions()->setIsRemoteEnabled(true);
            $pdf->getDomPDF()->getOptions()->setDefaultFont('Arial');
            $pdf->getDomPDF()->getOptions()->setDpi(150); // Higher DPI for better quality

            // Download the PDF with a proper filename
            return $pdf->download("suptix-ticket-{$ticket->ticket_number}.pdf");

        } catch (\Exception $e) {
            \Log::error('PDF generation error: '.$e->getMessage(), [
                'ticket_id' => $ticket->id,
                'ticket_number' => $ticket->ticket_number,
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Error generating PDF: '.$e->getMessage(),
            ], 500);
        }
    }

    /**
     * Mark ticket as used for specific event (mobile app endpoint)
     */
    public function markTicketAsUsedForEvent(Organizer $organizer, Event $event, Ticket $ticket)
    {
        // Check if user is authenticated
        if (! Auth::check()) {
            return response()->json([
                'success' => false,
                'message' => 'Authentication required to mark tickets as used.',
            ], 401);
        }

        // Check if the authenticated user belongs to the event's organizer
        $user = Auth::user();
        if (! $user->organizers->contains('id', $organizer->id)) {
            return response()->json([
                'success' => false,
                'message' => 'You are not authorized to mark this ticket as used.',
            ], 403);
        }

        // Check if ticket belongs to the event
        if ($ticket->event_id !== $event->id) {
            return response()->json([
                'success' => false,
                'message' => 'This ticket does not belong to the specified event.',
            ], 400);
        }

        // Check if event belongs to organizer
        if ($event->organizer_id !== $organizer->id) {
            return response()->json([
                'success' => false,
                'message' => 'This event does not belong to the specified organizer.',
            ], 400);
        }

        if ($ticket->status !== 'active') {
            return response()->json([
                'success' => false,
                'message' => 'This ticket is not active and cannot be used.',
                'ticket' => $this->formatTicketResponse($ticket),
            ], 400);
        }

        if (! $ticket->markAsUsed()) {
            return response()->json([
                'success' => false,
                'message' => 'This ticket has already been used.',
            ], 400);
        }

        return response()->json([
            'success' => true,
            'message' => 'Ticket marked as used successfully.',
            'ticket' => $this->formatTicketResponse($ticket),
        ]);
    }

    /**
     * Get ticket entry history for an event
     */
    public function getTicketEntryHistory(Organizer $organizer, Event $event, Request $request)
    {
        // Check if user is authenticated and authorized
        $user = Auth::user();
        if (! $user->organizers->contains('id', $organizer->id)) {
            return response()->json([
                'success' => false,
                'message' => 'You are not authorized to view this data.',
            ], 403);
        }

        $limit = $request->get('limit', 50);
        $offset = $request->get('offset', 0);

        $tickets = Ticket::with(['order', 'ticketType'])
            ->where('event_id', $event->id)
            ->where('status', 'used')
            ->orderBy('used_at', 'desc')
            ->limit($limit)
            ->offset($offset)
            ->get();

        $entries = $tickets->map(function ($ticket) {
            return [
                'id' => $ticket->id,
                'ticket_number' => $ticket->ticket_number,
                'attendee_name' => $ticket->attendee_name,
                'used_at' => $ticket->used_at,
                'ticket_type' => $ticket->ticketType ? $ticket->ticketType->name : null,
                'order_number' => $ticket->order->order_number,
            ];
        });

        return response()->json([
            'success' => true,
            'data' => $entries,
            'meta' => [
                'total' => $tickets->count(),
                'limit' => $limit,
                'offset' => $offset,
            ],
        ]);
    }

    /**
     * Get event statistics
     */
    public function getEventStatistics(Organizer $organizer, Event $event)
    {
        // Check if user is authenticated and authorized
        $user = Auth::user();
        if (! $user->organizers->contains('id', $organizer->id)) {
            return response()->json([
                'success' => false,
                'message' => 'You are not authorized to view this data.',
            ], 403);
        }

        $totalTickets = Ticket::where('event_id', $event->id)->count();
        $usedTickets = Ticket::where('event_id', $event->id)->where('status', 'used')->count();
        $activeTickets = Ticket::where('event_id', $event->id)->where('status', 'active')->count();
        $cancelledTickets = Ticket::where('event_id', $event->id)->where('status', 'cancelled')->count();

        return response()->json([
            'success' => true,
            'data' => [
                'total_tickets' => $totalTickets,
                'used_tickets' => $usedTickets,
                'active_tickets' => $activeTickets,
                'cancelled_tickets' => $cancelledTickets,
                'usage_rate' => $totalTickets > 0 ? round(($usedTickets / $totalTickets) * 100, 2) : 0,
            ],
        ]);
    }

    /**
     * Get ticket scanning statistics
     */
    public function getTicketScanStatistics(Organizer $organizer, Event $event, Request $request)
    {
        // Check if user is authenticated and authorized
        $user = Auth::user();
        if (! $user->organizers->contains('id', $organizer->id)) {
            return response()->json([
                'success' => false,
                'message' => 'You are not authorized to view this data.',
            ], 403);
        }

        $dateFrom = $request->get('date_from');
        $dateTo = $request->get('date_to');

        $query = Ticket::where('event_id', $event->id)->where('status', 'used');

        if ($dateFrom) {
            $query->where('used_at', '>=', $dateFrom);
        }

        if ($dateTo) {
            $query->where('used_at', '<=', $dateTo);
        }

        $scansByHour = (clone $query)->selectRaw('HOUR(used_at) as hour, COUNT(*) as count')
            ->groupBy('hour')
            ->orderBy('hour')
            ->get();

        $scansByDay = (clone $query)->selectRaw('DATE(used_at) as date, COUNT(*) as count')
            ->groupBy('date')
            ->orderBy('date')
            ->get();

        return response()->json([
            'success' => true,
            'data' => [
                'scans_by_hour' => $scansByHour,
                'scans_by_day' => $scansByDay,
                'total_scans' => $query->count(),
            ],
        ]);
    }

    /**
     * Format ticket response for consistent API output
     */
    private function formatTicketResponse(Ticket $ticket, bool $includePersonalData = true): array
    {
        $response = [
            'id' => $ticket->id,
            'ticket_number' => $ticket->ticket_number,
            'attendee_name' => $ticket->attendee_name,
            'price' => $ticket->price,
            'status' => $ticket->status,
            'used_at' => $ticket->used_at,
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
            'ticket_type' => $ticket->ticketType ? [
                'name' => $ticket->ticketType->name,
                'description' => $ticket->ticketType->description,
            ] : null,
            'order' => [
                'order_number' => $ticket->order->order_number,
                'status' => $ticket->order->status,
            ],
            'custom_field_responses' => $ticket->responses
                ->map(fn ($r) => [
                    'label' => $r->field?->label,
                    'value' => $r->value,
                ])
                ->filter(fn ($r) => $r['label'] !== null)
                ->values()
                ->toArray(),
        ];

        if ($includePersonalData) {
            $response['attendee_email'] = $ticket->attendee_email;
        }

        return $response;
    }
}
