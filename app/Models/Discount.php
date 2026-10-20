<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Discount extends Model
{
    use HasFactory;

    protected $fillable = [
        'organizer_id',
        'event_id',
        'code',
        'name',
        'description',
        'type',
        'value',
        'usage_limit',
        'used_count',
        'valid_from',
        'valid_until',
        'is_active',
    ];

    protected $casts = [
        'value' => 'decimal:2',
        'usage_limit' => 'integer',
        'used_count' => 'integer',
        'valid_from' => 'date',
        'valid_until' => 'date',
        'is_active' => 'boolean',
    ];

    public function organizer(): BelongsTo
    {
        return $this->belongsTo(Organizer::class);
    }

    public function event(): BelongsTo
    {
        return $this->belongsTo(Event::class);
    }

    /**
     * The discount for a subtotal, rounded to two decimals and never more than
     * the subtotal itself (so a percentage above 100 cannot exceed the order).
     */
    public function amountFor(float $subtotal): float
    {
        $subtotal = max(0.0, $subtotal);

        $amount = match ($this->type) {
            'percentage' => $subtotal * min((float) $this->value, 100.0) / 100,
            'fixed_amount' => (float) $this->value,
            default => 0.0,
        };

        return round(min($amount, $subtotal), 2);
    }

    /**
     * Why the discount cannot be used today, or null when its dates allow it.
     * Both dates are inclusive: a code is valid for the whole of valid_until.
     */
    public function dateProblem(): ?string
    {
        $today = now()->startOfDay();

        if ($this->valid_from && $today->lt($this->valid_from->copy()->startOfDay())) {
            return 'not_started';
        }

        if ($this->valid_until && $today->gt($this->valid_until->copy()->startOfDay())) {
            return 'expired';
        }

        return null;
    }

    public function discountUsers(): HasMany
    {
        return $this->hasMany(DiscountUser::class);
    }

    protected static function boot()
    {
        parent::boot();
        static::creating(function ($model) {
            $model->code = strtoupper(str_replace(' ', '', $model->code));
        });

        static::updating(function ($model) {
            $model->code = strtoupper(str_replace(' ', '', $model->code));
        });
    }
}
