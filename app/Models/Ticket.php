<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Str;

class Ticket extends Model
{
    use HasFactory;

    protected $fillable = [
        'ticket_number',
        'organizer_id',
        'order_id',
        'event_id',
        'ticket_type_id',
        'attendee_name',
        'attendee_email',
        'price',
        'status',
        'is_used',
        'used_at',
        'cancelled_at',
        'notes',
    ];

    protected $casts = [
        'price' => 'decimal:2',
        'is_used' => 'boolean',
        'used_at' => 'datetime',
        'cancelled_at' => 'datetime',
    ];

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    public function event(): BelongsTo
    {
        return $this->belongsTo(Event::class);
    }

    public function ticketType(): BelongsTo
    {
        return $this->belongsTo(TicketType::class);
    }

    public function responses()
    {
        return $this->hasMany(TicketResponse::class);
    }

    public function organizer(): BelongsTo
    {
        return $this->belongsTo(Organizer::class);
    }

    public function purchases()
    {
        return $this->hasMany(Purchase::class);
    }

    public function checkIns()
    {
        return $this->hasMany(CheckIn::class);
    }

    // Removed redundant accessor since attendee_name is a direct database field

    /**
     * Check if ticket is active
     */
    public function isActive(): bool
    {
        return $this->status === 'active';
    }

    /**
     * Mark ticket as used
     */
    public function markAsUsed(): void
    {
        $this->update([
            'status' => 'used',
            'used_at' => now(),
        ]);
    }

    public function getEventNameAttribute(): string
    {
        return $this->event->event_name;
    }

    /**
     * Generate ticket number automatically
     */
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($ticket) {
            if (empty($ticket->ticket_number)) {
                $ticket->ticket_number = 'TKT-'.strtoupper(Str::random(12));
            }
        });

        // static::created(function ($ticket) {
        //     // relaod tickts for event id cache after creation
        //     Cache::forget("event_{$ticket->event_id}_tickets");
        // });

        // static::retrieved(function ($ticket) {
        // cache tickets for event id after retreival
        // bad: this will call the cache method several time for the number of models,
        //  there being counter productive
        // Cache::put("event_{$ticket->event_id}_tickets", $ticket);
        // });
    }
}
