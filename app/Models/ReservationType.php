<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ReservationType extends Model
{
    protected $fillable = [
        'organizer_id',
        'name',
        'description',
        'max_party_size',
        'is_active',
    ];

    protected $casts = [
        'max_party_size' => 'integer',
        'is_active' => 'boolean',
    ];

    public function organizer(): BelongsTo
    {
        return $this->belongsTo(Organizer::class);
    }

    public function reservations(): HasMany
    {
        return $this->hasMany(Reservation::class);
    }
}
