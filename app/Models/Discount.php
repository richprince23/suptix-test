<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Discount extends Model
{
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

    public function discountUsers(): HasMany
    {
        return $this->hasMany(DiscountUser::class);
    }

    protected static function boot(){
        parent::boot();
        static::creating(function($model){
            $model->code = strtoupper(str_replace(' ', '', $model->code));
        });

        static::updating(function($model){
            $model->code = strtoupper(str_replace(' ', '', $model->code));
        });
    }
}
