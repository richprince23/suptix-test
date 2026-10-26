<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class RsvpWaitlist extends Model
{
    protected $table = 'rsvp_waitlist';

    protected $attributes = [
        'status' => 'pending',
    ];

    protected $fillable = [
        'event_id',
        'organizer_id',
        'guest_name',
        'email',
        'phone',
        'referral_source',
        'notes',
        'custom_fields',
        'waitlist_code',
        'position',
        'status',
        'actioned_by',
        'actioned_at',
    ];

    protected $casts = [
        'custom_fields' => 'array',
        'actioned_at' => 'datetime',
    ];

    public function event()
    {
        return $this->belongsTo(Event::class);
    }

    public function organizer()
    {
        return $this->belongsTo(Organizer::class);
    }

    public function actionedBy()
    {
        return $this->belongsTo(User::class, 'actioned_by');
    }

    public function maskedEmail(): string
    {
        $parts = explode('@', $this->email);
        $local = $parts[0];
        $domain = $parts[1] ?? '';
        $masked = substr($local, 0, 1) . str_repeat('*', max(3, strlen($local) - 1));

        return $masked . '@' . $domain;
    }

    public function maskedPhone(): ?string
    {
        if (! $this->phone) {
            return null;
        }

        $digits = preg_replace('/\D/', '', $this->phone);
        $last4 = substr($digits, -4);

        return '*** *** ' . $last4;
    }

    protected static function boot()
    {
        parent::boot();

        static::creating(function (RsvpWaitlist $entry) {
            $entry->waitlist_code = strtoupper(Str::random(10));
            $entry->position = static::where('event_id', $entry->event_id)->count() + 1;
        });
    }
}
