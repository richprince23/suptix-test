<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class EventSession extends Model
{
    protected $fillable = [
        'event_id',
        'organizer_id',
        'title',
        'description',
        'speaker',
        'session_date',
        'start_time',
        'end_time',
        'location',
        'sort_order',
    ];

    protected $casts = [
        'session_date' => 'date',
    ];

    public function event()
    {
        return $this->belongsTo(Event::class);
    }
}
