<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RsvpCreditSettings extends Model
{
    protected $fillable = [
        'free_attendee_slots_default',
        'free_email_credits_default',
        'free_sms_credits_default',
        'brevo_daily_limit',
    ];

    public static function current(): self
    {
        return static::firstOrCreate([], [
            'free_attendee_slots_default' => 100,
            'free_email_credits_default' => 100,
            'free_sms_credits_default' => 0,
            'brevo_daily_limit' => 270,
        ]);
    }
}
