<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Notifications\Notifiable;
use Str;

class Order extends Model
{
    use HasFactory, Notifiable;

    protected $fillable = [
        'order_number',
        'organizer_id',
        'created_by_user_id',
        'is_admin_order',
        'admin_notes',
        'event_id',
        'buyer_email',
        'buyer_phone',
        'subtotal_amount',
        'discount_id',
        'discount_amount',
        'total_amount',
        'status',
        'payment_method',
        'payment_reference',
        'purchased_at',
    ];

    protected $casts = [
        'subtotal_amount' => 'decimal:2',
        'discount_amount' => 'decimal:2',
        'total_amount' => 'decimal:2',
        'purchased_at' => 'datetime',
        'is_admin_order' => 'boolean',
    ];

    /** @return BelongsTo<Organizer, $this> */
    public function organizer(): BelongsTo
    {
        return $this->belongsTo(Organizer::class);
    }

    /** @return BelongsTo<Event, $this> */
    public function event(): BelongsTo
    {
        return $this->belongsTo(Event::class);
    }

    /** @return BelongsTo<Discount, $this> */
    public function discount(): BelongsTo
    {
        return $this->belongsTo(Discount::class);
    }

    /** @return HasMany<Ticket, $this> */
    public function tickets(): HasMany
    {
        return $this->hasMany(Ticket::class);
    }

    /** @return HasMany<Payment, $this> */
    public function payments(): HasMany
    {
        return $this->hasMany(Payment::class);
    }

    /** @return HasMany<Purchase, $this> */
    public function purchases(): HasMany
    {
        return $this->hasMany(Purchase::class);
    }

    /** @return HasMany<DiscountUser, $this> */
    public function discountUsers(): HasMany
    {
        return $this->hasMany(DiscountUser::class);
    }

    /** @return BelongsTo<User, $this> */
    public function createdByUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by_user_id');
    }

    /**
     * Get the total number of tickets in this order
     */
    public function getTotalTicketsAttribute(): int
    {
        return $this->tickets()->count();
    }

    /**
     * Check if order is confirmed
     */
    public function isConfirmed(): bool
    {
        return $this->status === 'confirmed';
    }

    /**
     * Mark order as confirmed
     */
    public function confirm(): void
    {
        $this->update([
            'status' => 'confirmed',
            'purchased_at' => now(),
        ]);
    }

    /**
     * Route notifications for the SMS channel.
     */
    public function routeNotificationForSms($notification)
    {
        return $this->buyer_phone;
    }

    /**
     * Generate order number automatically
     */
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($order) {
            if (empty($order->order_number)) {
                $order->order_number = 'ORD-'.strtoupper(Str::random(10));
            }
        });
    }
}
