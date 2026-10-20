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

    public function organizer(): BelongsTo
    {
        return $this->belongsTo(Organizer::class);
    }

    public function event(): BelongsTo
    {
        return $this->belongsTo(Event::class);
    }

    public function discount(): BelongsTo
    {
        return $this->belongsTo(Discount::class);
    }

    public function tickets(): HasMany
    {
        return $this->hasMany(Ticket::class);
    }

    public function payments(): HasMany
    {
        return $this->hasMany(Payment::class);
    }

    public function purchases(): HasMany
    {
        return $this->hasMany(Purchase::class);
    }

    public function discountUsers(): HasMany
    {
        return $this->hasMany(DiscountUser::class);
    }

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
