<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class EventCreditAccount extends Model
{
    protected $fillable = [
        'event_id',
        'organizer_id',
        'free_attendee_slots',
        'free_email_credits',
        'free_sms_credits',
        'purchased_attendee_slots',
        'purchased_email_credits',
        'purchased_sms_credits',
        'used_attendee_slots',
        'used_email_credits',
        'used_sms_credits',
        'global_daily_email_date',
        'global_daily_email_count',
        'overflow_behavior',
    ];

    protected $casts = [
        'global_daily_email_date' => 'date',
    ];

    public function event()
    {
        return $this->belongsTo(Event::class);
    }

    public function organizer()
    {
        return $this->belongsTo(Organizer::class);
    }

    public function purchases()
    {
        return $this->hasMany(EventCreditPurchase::class, 'event_id', 'event_id');
    }

    public function logs()
    {
        return $this->hasMany(EventCreditLog::class, 'event_id', 'event_id');
    }

    public function getRemainingAttendeeSlots(): int
    {
        return ($this->free_attendee_slots + $this->purchased_attendee_slots) - $this->used_attendee_slots;
    }

    public function getRemainingEmailCredits(): int
    {
        return ($this->free_email_credits + $this->purchased_email_credits) - $this->used_email_credits;
    }

    public function getRemainingSmsCredits(): int
    {
        return ($this->free_sms_credits + $this->purchased_sms_credits) - $this->used_sms_credits;
    }

    public function getTotalAttendeeSlots(): int
    {
        return $this->free_attendee_slots + $this->purchased_attendee_slots;
    }

    public function getTotalEmailCredits(): int
    {
        return $this->free_email_credits + $this->purchased_email_credits;
    }

    public function getTotalSmsCredits(): int
    {
        return $this->free_sms_credits + $this->purchased_sms_credits;
    }
}
