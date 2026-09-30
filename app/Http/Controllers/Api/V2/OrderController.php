<?php

namespace App\Http\Controllers\Api\V2;

use App\Models\Order;
use App\Models\Event;
use App\Models\TicketType;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use App\Http\Resources\Api\V2\OrderResource;
use App\Http\Resources\Api\V2\OrderDetailResource;

class OrderController extends BaseController
{
    public function index(Request $request)
    {
        $perPage = $request->get('per_page', 15);
        
        $orders = $request->user()
            ->purchases()
            ->with(['order.event', 'order.organizer'])
            ->whereHas('order')
            ->latest()
            ->paginate($perPage);

        $orderData = $orders->groupBy('order_id')->map(function ($purchases) {
            return $purchases->first()->order;
        })->values();

        return $this->successResponse([
            'orders' => OrderResource::collection($orderData),
            'pagination' => [
                'current_page' => $orders->currentPage(),
                'last_page' => $orders->lastPage(),
                'per_page' => $orders->perPage(),
                'total' => $orders->total(),
                'has_more' => $orders->hasMorePages(),
            ],
        ], 'Orders retrieved successfully');
    }

    public function show(Order $order)
    {
        $user = auth()->user();
        
        $hasAccess = $order->purchases()
            ->where('user_id', $user->id)
            ->exists();

        if (!$hasAccess) {
            return $this->forbiddenResponse('You do not have access to this order');
        }

        $order->load(['event', 'organizer', 'tickets.ticketType', 'purchases.user']);

        return $this->successResponse(
            new OrderDetailResource($order),
            'Order details retrieved successfully'
        );
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'event_id' => 'required|exists:events,id',
            'tickets' => 'required|array|min:1',
            'tickets.*.ticket_type_id' => 'required|exists:ticket_types,id',
            'tickets.*.quantity' => 'required|integer|min:1|max:10',
            'buyer_name' => 'required|string|max:255',
            'buyer_email' => 'required|email|max:255',
            'buyer_phone' => 'required|string|max:15',
            'honeypot' => 'nullable|max:0',
        ]);

        if ($validator->fails()) {
            return $this->validationErrorResponse($validator->errors());
        }

        if ($request->filled('honeypot')) {
            return $this->errorResponse('Invalid submission detected', 422);
        }

        $event = Event::findOrFail($request->event_id);

        if ($event->status !== 'published') {
            return $this->errorResponse('Event is not available for booking', 400);
        }

        if ($event->event_date < now()) {
            return $this->errorResponse('Cannot book tickets for past events', 400);
        }

        return DB::transaction(function () use ($request, $event) {
            $user = $request->user();
            $totalAmount = 0;
            $orderItems = [];

            foreach ($request->tickets as $ticketData) {
                $ticketType = TicketType::findOrFail($ticketData['ticket_type_id']);
                
                if ($ticketType->event_id !== $event->id) {
                    throw new \Exception('Invalid ticket type for this event');
                }

                if (!$ticketType->is_available) {
                    throw new \Exception("Ticket type '{$ticketType->name}' is not available");
                }

                $quantity = $ticketData['quantity'];
                $ticketPrice = $ticketType->price;
                $subtotal = $ticketPrice * $quantity;
                $totalAmount += $subtotal;

                $orderItems[] = [
                    'ticket_type' => $ticketType,
                    'quantity' => $quantity,
                    'price' => $ticketPrice,
                    'subtotal' => $subtotal,
                ];
            }

            $order = Order::create([
                'order_number' => 'ORD-' . strtoupper(uniqid()),
                'event_id' => $event->id,
                'organizer_id' => $event->organizer_id,
                'buyer_name' => $request->buyer_name,
                'buyer_email' => $request->buyer_email,
                'buyer_phone' => $request->buyer_phone,
                'total_amount' => $totalAmount,
                'status' => 'pending',
                'payment_status' => 'pending',
            ]);

            foreach ($orderItems as $item) {
                for ($i = 0; $i < $item['quantity']; $i++) {
                    $ticket = $order->tickets()->create([
                        'ticket_number' => 'TKT-' . strtoupper(uniqid()),
                        'event_id' => $event->id,
                        'organizer_id' => $event->organizer_id,
                        'ticket_type_id' => $item['ticket_type']->id,
                        'status' => 'valid',
                        'is_used' => false,
                    ]);

                    $order->purchases()->create([
                        'user_id' => $user->id,
                        'ticket_id' => $ticket->id,
                        'price' => $item['price'],
                    ]);
                }
            }

            $order->load(['event', 'organizer', 'tickets.ticketType', 'purchases.user']);

            return $this->successResponse(
                new OrderDetailResource($order),
                'Order created successfully. Complete payment to confirm your tickets.',
                201
            );
        });
    }
}