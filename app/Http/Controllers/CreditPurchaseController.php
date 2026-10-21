<?php

namespace App\Http\Controllers;

use App\Models\Event;
use App\Models\EventCreditPurchase;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class CreditPurchaseController extends Controller
{
    public function store(Request $request, Event $event)
    {
        $packs = config('rsvp_credits.packs');

        $request->validate([
            'pack_key'  => ['required', 'string', 'in:' . implode(',', array_keys($packs))],
            'quantity'  => ['required', 'integer', 'min:' . config('rsvp_credits.min_quantity'), 'max:' . config('rsvp_credits.max_quantity')],
        ]);

        $pack     = $packs[$request->pack_key];
        $quantity = (int) $request->quantity;
        $total    = round($pack['price_ghs'] * $quantity, 2);
        $reference = 'CRDT-' . strtoupper(Str::random(12));

        $purchase = EventCreditPurchase::create([
            'event_id'              => $event->id,
            'organizer_id'          => $event->organizer_id,
            'paystack_reference'    => $reference,
            'amount'                => $total,
            'pack_key'              => $request->pack_key,
            'quantity'              => $quantity,
            'attendee_slots_granted' => $pack['slots'] * $quantity,
            'email_credits_granted' => $pack['emails'] * $quantity,
            'sms_credits_granted'   => $pack['sms'] * $quantity,
            'status'                => 'pending',
            'metadata'              => [
                'type'     => 'rsvp_credit_purchase',
                'pack'     => $pack,
                'quantity' => $quantity,
            ],
        ]);

        $secretKey = config('services.paystack.secret_key');

        $response = Http::withHeaders([
            'Authorization' => 'Bearer ' . $secretKey,
            'Content-Type'  => 'application/json',
        ])->post('https://api.paystack.co/transaction/initialize', [
            'email'        => auth()->user()->email,
            'amount'       => (int) ($total * 100), // pesewas
            'currency'     => 'GHS',
            'reference'    => $reference,
            'callback_url' => route('credits.verify', $event),
            'metadata'     => [
                'type'        => 'rsvp_credit_purchase',
                'event_id'    => $event->id,
                'purchase_id' => $purchase->id,
                'pack_key'    => $request->pack_key,
                'quantity'    => $quantity,
            ],
        ]);

        $data = $response->json();

        if (! $response->successful() || ! ($data['status'] ?? false)) {
            $purchase->update(['status' => 'failed']);
            Log::error('Credit purchase Paystack init failed', ['event_id' => $event->id, 'response' => $data]);

            return back()->withErrors(['payment' => 'Failed to initialize payment. Please try again.']);
        }

        Log::info('Credit purchase initiated', [
            'event_id'    => $event->id,
            'purchase_id' => $purchase->id,
            'reference'   => $reference,
            'amount'      => $total,
        ]);

        return redirect($data['data']['authorization_url']);
    }

    public function verify(Request $request, Event $event)
    {
        $reference = $request->query('reference') ?? $request->query('trxref');

        if (! $reference) {
            return redirect()->route('filament.admin.pages.event-credits-page', ['tenant' => $event->organizer_id, 'event' => $event->id])
                ->with('error', 'No payment reference found.');
        }

        $purchase = EventCreditPurchase::where('paystack_reference', $reference)
            ->where('event_id', $event->id)
            ->first();

        if (! $purchase || $purchase->status === 'completed') {
            return redirect()->route('filament.admin.pages.event-credits-page', ['tenant' => $event->organizer_id, 'event' => $event->id])
                ->with('message', 'Payment already processed.');
        }

        return redirect()->route('filament.admin.pages.event-credits-page', ['tenant' => $event->organizer_id, 'event' => $event->id])
            ->with('message', 'Payment is being processed. Your credits will be added shortly.');
    }
}
