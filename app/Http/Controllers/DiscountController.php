<?php

namespace App\Http\Controllers;

use App\Models\Discount;
use App\Models\Event;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class DiscountController extends Controller
{
    /**
     * Validate and calculate discount for given code and event
     */
    public function validateDiscount(Request $request): JsonResponse
    {
        $request->validate([
            'code' => 'required|string',
            'event_id' => 'required|exists:events,id',
            'subtotal' => 'required|numeric|min:0',
        ]);

        $code = $request->input('code');
        $eventId = $request->input('event_id');
        $subtotal = $request->input('subtotal');

        // Find the discount by code
        $discount = Discount::where('code', $code)
            ->where('is_active', true)
            ->first();

        if (! $discount) {
            return response()->json([
                'valid' => false,
                'message' => 'Invalid discount code',
            ], 422);
        }

        // Check if discount is valid for this event
        if ($discount->event_id && $discount->event_id != $eventId) {
            return response()->json([
                'valid' => false,
                'message' => 'This discount code is not valid for this event',
            ], 422);
        }

        // Check validity period
        $now = Carbon::now();
        if ($discount->valid_from && $now->lt($discount->valid_from)) {
            return response()->json([
                'valid' => false,
                'message' => 'This discount code is not yet active',
            ], 422);
        }

        if ($discount->valid_until && $now->gt($discount->valid_until)) {
            return response()->json([
                'valid' => false,
                'message' => 'This discount code has expired',
            ], 422);
        }

        // Check usage limit
        if ($discount->usage_limit && $discount->used_count >= $discount->usage_limit) {
            return response()->json([
                'valid' => false,
                'message' => 'This discount code has reached its usage limit',
            ], 422);
        }

        // Calculate discount amount
        $discountAmount = $discount->amountFor((float) $subtotal);

        return response()->json([
            'valid' => true,
            'discount' => [
                'id' => $discount->id,
                'code' => $discount->code,
                'name' => $discount->name,
                'type' => $discount->type,
                'value' => $discount->value,
                'amount' => $discountAmount,
                'subtotal' => $subtotal,
                'total' => round(max(0, $subtotal - $discountAmount), 2),
            ],
        ]);
    }
}
