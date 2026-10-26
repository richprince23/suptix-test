<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class EventCreditPurchase extends Model
{
    protected $fillable = [
        'event_id',
        'organizer_id',
        'paystack_reference',
        'amount',
        'pack_key',
        'quantity',
        'attendee_slots_granted',
        'email_credits_granted',
        'sms_credits_granted',
        'status',
        'metadata',
        'completed_at',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'metadata' => 'array',
        'completed_at' => 'datetime',
    ];

    public function event()
    {
        return $this->belongsTo(Event::class);
    }

    public function organizer()
    {
        return $this->belongsTo(Organizer::class);
    }

    public function logs()
    {
        return $this->hasMany(EventCreditLog::class, 'purchase_id');
    }
}
