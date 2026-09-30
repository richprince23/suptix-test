<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PendingRsvpNotification extends Model
{
    protected $fillable = [
        'rsvp_id',
        'event_id',
        'organizer_id',
        'sent',
        'sent_at',
    ];

    protected $casts = [
        'sent' => 'boolean',
        'sent_at' => 'datetime',
    ];

    public function rsvp(): BelongsTo
    {
        return $this->belongsTo(RSVP::class);
    }

    public function event(): BelongsTo
    {
        return $this->belongsTo(Event::class);
    }

    public function organizer(): BelongsTo
    {
        return $this->belongsTo(Organizer::class);
    }

    public function markAsSent(): void
    {
        $this->update([
            'sent' => true,
            'sent_at' => now(),
        ]);
    }

    public function scopePending($query)
    {
        return $query->where('sent', false);
    }

    public function scopeForOrganizer($query, int $organizerId)
    {
        return $query->where('organizer_id', $organizerId);
    }
}
