<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class EventCreditLog extends Model
{
    public const UPDATED_AT = null;

    protected $fillable = [
        'event_id',
        'rsvp_id',
        'purchase_id',
        'type',
        'source',
        'quantity',
        'balance_before',
        'balance_after',
        'note',
    ];

    public function event()
    {
        return $this->belongsTo(Event::class);
    }

    public function rsvp()
    {
        return $this->belongsTo(RSVP::class);
    }

    public function purchase()
    {
        return $this->belongsTo(EventCreditPurchase::class);
    }
}
