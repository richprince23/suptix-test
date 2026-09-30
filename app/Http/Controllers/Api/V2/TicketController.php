<?php

namespace App\Http\Controllers\Api\V2;

use App\Models\Ticket;
use App\Models\CheckIn;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use App\Http\Resources\Api\V2\TicketResource;
use App\Http\Resources\Api\V2\TicketDetailResource;

class TicketController extends BaseController
{
    public function index(Request $request)
    {
        $perPage = $request->get('per_page', 15);
        
        $tickets = $request->user()
            ->purchases()
            ->with(['ticket.event', 'ticket.ticketType', 'order'])
            ->whereHas('ticket')
            ->latest()
            ->paginate($perPage);

        $ticketData = $tickets->map(function ($purchase) {
            return $purchase->ticket;
        });

        return $this->successResponse([
            'tickets' => TicketResource::collection($ticketData),
            'pagination' => [
                'current_page' => $tickets->currentPage(),
                'last_page' => $tickets->lastPage(),
                'per_page' => $tickets->perPage(),
                'total' => $tickets->total(),
                'has_more' => $tickets->hasMorePages(),
            ],
        ], 'Tickets retrieved successfully');
    }

    public function show(Ticket $ticket)
    {
        $user = auth()->user();
        
        $hasAccess = $ticket->purchases()
            ->where('user_id', $user->id)
            ->exists();

        if (!$hasAccess) {
            return $this->forbiddenResponse('You do not have access to this ticket');
        }

        $ticket->load(['event', 'ticketType', 'order', 'checkIns']);

        return $this->successResponse(
            new TicketDetailResource($ticket),
            'Ticket details retrieved successfully'
        );
    }

    public function scan(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'qr_data' => 'required|string',
        ]);

        if ($validator->fails()) {
            return $this->validationErrorResponse($validator->errors());
        }

        try {
            $qrData = json_decode($request->qr_data, true);
            
            if (!$qrData || !isset($qrData['ticket_number'])) {
                return $this->errorResponse('Invalid QR code format', 400);
            }

            $ticket = Ticket::where('ticket_number', $qrData['ticket_number'])->first();

            if (!$ticket) {
                return $this->errorResponse('Ticket not found', 404);
            }

            $user = $request->user();
            $hasAccess = $ticket->purchases()
                ->where('user_id', $user->id)
                ->exists();

            if (!$hasAccess) {
                return $this->forbiddenResponse('You do not have access to this ticket');
            }

            $ticket->load(['event', 'ticketType', 'order', 'checkIns']);

            return $this->successResponse(
                new TicketDetailResource($ticket),
                'Ticket scanned successfully'
            );

        } catch (\Exception $e) {
            return $this->errorResponse('Invalid QR code', 400);
        }
    }

    public function verify(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'ticket_number' => 'required|string',
        ]);

        if ($validator->fails()) {
            return $this->validationErrorResponse($validator->errors());
        }

        $ticket = Ticket::where('ticket_number', $request->ticket_number)->first();

        if (!$ticket) {
            return $this->errorResponse('Ticket not found', 404);
        }

        $user = $request->user();
        $hasAccess = $ticket->purchases()
            ->where('user_id', $user->id)
            ->exists();

        if (!$hasAccess) {
            return $this->forbiddenResponse('You do not have access to this ticket');
        }

        $ticket->load(['event', 'ticketType', 'order', 'checkIns']);

        return $this->successResponse([
            'ticket' => new TicketDetailResource($ticket),
            'verification' => [
                'is_valid' => $ticket->status === 'valid',
                'is_used' => $ticket->is_used,
                'can_check_in' => $ticket->status === 'valid' && !$ticket->is_used,
                'message' => $this->getTicketStatusMessage($ticket),
            ],
        ], 'Ticket verified successfully');
    }

    public function checkIn(Ticket $ticket, Request $request)
    {
        $user = $request->user();
        
        $hasAccess = $ticket->purchases()
            ->where('user_id', $user->id)
            ->exists();

        if (!$hasAccess) {
            return $this->forbiddenResponse('You do not have access to this ticket');
        }

        if ($ticket->status !== 'valid') {
            return $this->errorResponse('Ticket is not valid for check-in', 400);
        }

        if ($ticket->is_used) {
            return $this->errorResponse('Ticket has already been used', 400);
        }

        $checkIn = CheckIn::create([
            'ticket_id' => $ticket->id,
            'event_id' => $ticket->event_id,
            'checked_in_by' => $user->id,
            'checked_in_at' => now(),
        ]);

        $ticket->update([
            'is_used' => true,
            'used_at' => now(),
        ]);

        $ticket->load(['event', 'ticketType', 'order', 'checkIns']);

        return $this->successResponse(
            new TicketDetailResource($ticket),
            'Ticket checked in successfully'
        );
    }

    private function getTicketStatusMessage(Ticket $ticket): string
    {
        if ($ticket->status !== 'valid') {
            return 'Ticket is not valid';
        }

        if ($ticket->is_used) {
            return 'Ticket has already been used';
        }

        return 'Ticket is valid and ready for check-in';
    }
}