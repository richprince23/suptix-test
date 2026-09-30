<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class EventReport extends Model
{
    use HasFactory;

    protected $fillable = [
        'event_id',
        'organizer_id',
        'reporter_name',
        'reporter_email',
        'report_type',
        'subject',
        'description',
        'priority',
        'status',
        'metadata',
        'organizer_notified_at',
        'admin_notified_at',
        'resolved_at',
        'organizer_notes',
        'admin_notes',
        'honeypot',
    ];

    protected $casts = [
        'metadata' => 'array',
        'organizer_notified_at' => 'datetime',
        'admin_notified_at' => 'datetime',
        'resolved_at' => 'datetime',
    ];

    // Relationships
    public function event(): BelongsTo
    {
        return $this->belongsTo(Event::class);
    }

    public function organizer(): BelongsTo
    {
        return $this->belongsTo(Organizer::class);
    }



    // Helper methods
    public function markAsResolved(): void
    {
        $this->update([
            'status' => 'resolved',
            'resolved_at' => now(),
        ]);
    }

    public function markOrganizerNotified(): void
    {
        $this->update(['organizer_notified_at' => now()]);
    }

    public function markAdminNotified(): void
    {
        $this->update(['admin_notified_at' => now()]);
    }

    public function getReporterAttribute(): string
    {
        return $this->organizer ? $this->organizer->name : ($this->reporter_name ?? 'Anonymous');
    }

    public function getReporterEmailAddressAttribute(): string
    {
        return $this->organizer ? $this->organizer->email : ($this->reporter_email ?? 'N/A');
    }

    // Scopes
    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    public function scopeResolved($query)
    {
        return $query->where('status', 'resolved');
    }

    public function scopeByType($query, string $type)
    {
        return $query->where('report_type', $type);
    }

    public function scopeByPriority($query, string $priority)
    {
        return $query->where('priority', $priority);
    }

    public function scopeByEvent($query, $eventId)
    {
        return $query->where('event_id', $eventId);
    }

    public function scopeByEventName($query, string $eventName)
    {
        return $query->whereHas('event', function ($q) use ($eventName) {
            $q->where('title', 'like', '%' . $eventName . '%');
        });
    }

    public function scopeForOrganizer($query, $organizerId)
    {
        return $query->whereHas('event', function ($q) use ($organizerId) {
            $q->where('organizer_id', $organizerId);
        });
    }

    public function scopeEscalated($query)
    {
        return $query->whereNotNull('admin_notified_at');
    }

    public function scopeUnescalated($query)
    {
        return $query->whereNull('admin_notified_at');
    }
}
