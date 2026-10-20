<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
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
        'used_at',
        'cancelled_at',
        'notes',
    ];

    protected $casts = [
        'price' => 'decimal:2',
        'used_at' => 'datetime',
        'cancelled_at' => 'datetime',
    ];

    /** @return BelongsTo<Order, $this> */
    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    /** @return BelongsTo<Event, $this> */
    public function event(): BelongsTo
    {
        return $this->belongsTo(Event::class);
    }

    /** @return BelongsTo<TicketType, $this> */
    public function ticketType(): BelongsTo
    {
        return $this->belongsTo(TicketType::class);
    }

    /** @return HasMany<TicketResponse, $this> */
    public function responses(): HasMany
    {
        return $this->hasMany(TicketResponse::class);
    }

    /** @return BelongsTo<Organizer, $this> */
    public function organizer(): BelongsTo
    {
        return $this->belongsTo(Organizer::class);
    }

    /** @return HasMany<Purchase, $this> */
    public function purchases(): HasMany
    {
        return $this->hasMany(Purchase::class);
    }

    /** @return HasMany<CheckIn, $this> */
    public function checkIns(): HasMany
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
    /**
     * Admit the ticket. The status change is one conditional update, so when
     * two scans race only one of them changes the row and gets true.
     */
    public function markAsUsed(): bool
    {
        $claimed = static::whereKey($this->getKey())
            ->where('status', 'active')
            ->update(['status' => 'used', 'used_at' => now()]);

        if ($claimed === 1) {
            $this->refresh();
        }

        return $claimed === 1;
    }

    public function getEventNameAttribute(): string
    {
        return $this->event->title;
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
