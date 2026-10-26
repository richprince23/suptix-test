<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Str;

class RSVP extends Model
{
    protected $table = 'rsvps';

    protected $fillable = [
        'event_id',
        'organizer_id',
        'guest_name',
        'email',
        'phone',
        'guest_code',
        'status',
        'notes',
        'referral_source',
        'checked_in',
        'checked_in_at',
        'checked_in_by',
    ];

    protected $casts = [
        'checked_in' => 'boolean',
        'checked_in_at' => 'datetime',
    ];

    public function organizer()
    {
        return $this->belongsTo(Organizer::class);

    }

    public function event()
    {
        return $this->belongsTo(Event::class);
    }

    public function checkedInBy()
    {
        return $this->belongsTo(User::class, 'checked_in_by');
    }

    public function responses()
    {
        return $this->hasMany(RsvpResponse::class, 'rsvp_id');
    }

    /**
     * Mark RSVP as checked in
     */
    public function markAsCheckedIn(?int $checkedInByUserId = null): bool
    {
        if ($this->checked_in) {
            return false; // Already checked in
        }

        $this->checked_in = true;
        $this->checked_in_at = now();
        if ($checkedInByUserId) {
            $this->checked_in_by = $checkedInByUserId;
        }

        return $this->save();
    }

    /**
     * Check if guest can be checked in
     */
    public function canCheckIn(): bool
    {
        return ! $this->checked_in && $this->status === 'attending';
    }

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($rsvp) {
            $rsvp->guest_code = strtoupper(Str::random(8));
        });
    }

    // public function hasRsvped($email, $event_id){
    //     $q = $this->query();
    //     return $q->where('email', $email)->where('event_id', $event_id)->count() !== 0;
    // }

    //  // check if venue is not full
    //  public function isVenueFull(){
    //     return $this->count() == $this->event->venue->capacity;
    // }
}
