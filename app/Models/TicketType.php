<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;

class TicketType extends Model
{
    protected $fillable = [
        'event_id',
        'organizer_id',
        'name',
        'description',
        'price',
        'quantity',
        'sales_start',
        'sales_end',
    ];

    protected $casts = [
        'sales_start' => 'datetime',
        'sales_end' => 'datetime',
    ];

    protected $appends = [
        'available_quantity',
        'sold_tickets_count',
        'total_capacity',
    ];

    public function event()
    {
        return $this->belongsTo(Event::class);
    }

    public function organizer()
    {
        return $this->belongsTo(Organizer::class);
    }

    public function tickets()
    {
        return $this->hasMany(Ticket::class);
    }

    // Scope for available tickets (within sales date range)
    public function scopeAvailable($query)
    {
        $now = Carbon::now();

        return $query->where(function ($q) use ($now) {
            $q->where(function ($subQ) use ($now) {
                $subQ->whereNull('sales_start')
                    ->orWhere('sales_start', '<=', $now);
            })->where(function ($subQ) use ($now) {
                $subQ->whereNull('sales_end')
                    ->orWhere('sales_end', '>=', $now);
            });
        });
    }

    // Scope for tickets that haven't expired
    public function scopeNotExpired($query)
    {
        $now = Carbon::now();

        return $query->where(function ($q) use ($now) {
            $q->whereNull('sales_end')
                ->orWhere('sales_end', '>=', $now);
        });
    }

    // Check if ticket sales have started
    public function salesStarted(): bool
    {
        return $this->sales_start === null || Carbon::now()->gte($this->sales_start);
    }

    // Check if ticket sales have ended
    public function salesEnded(): bool
    {
        return $this->sales_end !== null && Carbon::now()->gt($this->sales_end);
    }

    // Check if ticket is purchasable right now
    public function isPurchasable(): bool
    {
        return $this->salesStarted() && ! $this->salesEnded();
    }

    // Get the actual available quantity (excluding cancelled tickets)
    public function getAvailableQuantityAttribute(): int
    {
        // Start with the stored quantity (which represents remaining after purchases)
        $baseQuantity = $this->quantity;

        // Add back cancelled tickets that should be available again
        $cancelledTickets = $this->tickets()
            ->where('status', 'cancelled')
            ->count();

        return $baseQuantity + $cancelledTickets;
    }

    // Get sold tickets count (active + used, excluding cancelled)
    public function getSoldTicketsCountAttribute(): int
    {
        return $this->tickets()
            ->whereIn('status', ['active', 'used'])
            ->count();
    }

    // Get total original capacity
    public function getTotalCapacityAttribute(): int
    {
        return $this->quantity + $this->sold_tickets_count;
    }
}
