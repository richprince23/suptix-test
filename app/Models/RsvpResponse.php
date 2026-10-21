<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RsvpResponse extends Model
{
    protected $fillable = [
        'rsvp_id',
        'event_form_field_id',
        'value',
    ];

    public function rsvp()
    {
        return $this->belongsTo(RSVP::class);
    }

    public function field()
    {
        return $this->belongsTo(EventFormField::class, 'event_form_field_id');
    }
}
