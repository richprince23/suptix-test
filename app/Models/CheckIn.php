<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CheckIn extends Model
{
    protected $fillable = [
        'ticket_id',
        'event_id',
        'checked_in_by',
        'checked_in_at',
    ];

    protected $casts = [
        'checked_in_at' => 'datetime',
    ];

    public function organizer()
    {
        return $this->belongsTo(Organizer::class);
    }

    public function event()
    {
        return $this->belongsTo(Event::class);
    }

    public function ticket()
    {
        return $this->belongsTo(Ticket::class);
    }

    public function checkedInBy()
    {
        return $this->belongsTo(User::class, 'checked_in_by');
    }
}
