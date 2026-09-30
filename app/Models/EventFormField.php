<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class EventFormField extends Model
{
    protected $fillable = [
        'event_id',
        'organizer_id',
        'label',
        'field_type',
        'options',
        'placeholder',
        'is_required',
        'sort_order',
        'is_active',
    ];

    protected $casts = [
        'options' => 'array',
        'is_required' => 'boolean',
        'is_active' => 'boolean',
    ];

    public function event()
    {
        return $this->belongsTo(Event::class);
    }

    public function responses()
    {
        return $this->hasMany(RsvpResponse::class);
    }
}
