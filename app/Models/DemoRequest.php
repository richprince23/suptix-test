<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DemoRequest extends Model
{
    protected $fillable = [
        'first_name',
        'last_name',
        'email',
        'phone',
        'company',
        'job_title',
        'company_size',
        'event_types',
        'events_per_year',
        'average_attendees',
        'current_solution',
        'demo_focus',
        'timeline',
        'preferred_date',
        'preferred_time',
        'newsletter',
        'terms',
        'honeypot',
        'contacted_at',
        'admin_notes'
    ];

    protected $casts = [
        'event_types' => 'array',
        'newsletter' => 'boolean',
        'terms' => 'boolean',
        'preferred_date' => 'date',
        'contacted_at' => 'datetime',
    ];

    public function getFullNameAttribute(): string
    {
        return "{$this->first_name} {$this->last_name}";
    }

    public function getEventTypesListAttribute(): string
    {
        return implode(', ', $this->event_types ?? []);
    }

    public function markAsContacted(): void
    {
        $this->update(['contacted_at' => now()]);
    }

    public function scopeUncontacted($query)
    {
        return $query->whereNull('contacted_at');
    }

    public function scopeContacted($query)
    {
        return $query->whereNotNull('contacted_at');
    }

    public function scopeUrgent($query)
    {
        return $query->where('timeline', 'immediate');
    }
}
