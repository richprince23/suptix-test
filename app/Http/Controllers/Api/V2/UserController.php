<?php

namespace App\Http\Controllers\Api\V2;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use App\Http\Resources\Api\V2\UserResource;
use App\Http\Resources\Api\V2\OrderResource;
use App\Http\Resources\Api\V2\TicketResource;

class UserController extends BaseController
{
    public function profile(Request $request)
    {
        return $this->successResponse(
            new UserResource($request->user()),
            'User profile retrieved successfully'
        );
    }

    public function updateProfile(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'sometimes|required|string|max:255',
            'phone' => 'sometimes|nullable|string|max:15',
            'current_password' => 'required_with:password|string',
            'password' => 'sometimes|string|min:8|confirmed',
        ]);

        if ($validator->fails()) {
            return $this->validationErrorResponse($validator->errors());
        }

        $user = $request->user();

        if ($request->has('password')) {
            if (!Hash::check($request->current_password, $user->password)) {
                return $this->errorResponse('Current password is incorrect', 422);
            }
            $user->password = Hash::make($request->password);
        }

        if ($request->has('name')) {
            $user->name = $request->name;
        }

        if ($request->has('phone')) {
            $user->phone = $request->phone;
        }

        $user->save();

        return $this->successResponse(
            new UserResource($user),
            'Profile updated successfully'
        );
    }

    public function orders(Request $request)
    {
        $perPage = $request->get('per_page', 15);
        
        $orders = $request->user()
            ->purchases()
            ->with(['order.event', 'order.organizer'])
            ->whereHas('order')
            ->latest()
            ->paginate($perPage);

        $orderData = $orders->groupBy('order_id')->map(fn($purchases) => $purchases->first()->order)->values();

        return $this->successResponse([
            'orders' => OrderResource::collection($orderData),
            'pagination' => [
                'current_page' => $orders->currentPage(),
                'last_page' => $orders->lastPage(),
                'per_page' => $orders->perPage(),
                'total' => $orders->total(),
                'has_more' => $orders->hasMorePages(),
            ],
        ], 'User orders retrieved successfully');
    }

    public function tickets(Request $request)
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
        ], 'User tickets retrieved successfully');
    }
}