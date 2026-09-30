<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Str;

class Reservation extends Model
{
    protected $fillable = [
        'organizer_id',
        'event_id',
        'reservation_type_id',
        'reservation_number',
        'guest_name',
        'guest_email',
        'guest_phone',
        'party_size',
        'reserved_at',
        'status',
        'special_requests',
        'notes',
    ];

    protected $casts = [
        'party_size' => 'integer',
        'reserved_at' => 'datetime',
    ];

    public function organizer(): BelongsTo
    {
        return $this->belongsTo(Organizer::class);
    }

    public function event(): BelongsTo
    {
        return $this->belongsTo(Event::class);
    }

    public function reservationType(): BelongsTo
    {
        return $this->belongsTo(ReservationType::class);
    }

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($reservation) {
            if (empty($reservation->reservation_number)) {
                $reservation->reservation_number = 'RSV-'.strtoupper(Str::random(10));
            }
        });
    }
}
