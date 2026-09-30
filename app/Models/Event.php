<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;
use Str;

class Event extends Model
{
    protected $fillable = [
        'code',
        'slug',
        'organizer_id',
        'venue_name',
        'venue_address',
        'venue_gps_code',
        'venue_capacity',
        'venue_phone',
        'title',
        'is_rsvp',
        'rsvp_closes_at',
        'mode',
        'platform_name',
        'online_link',
        'is_public_link',
        'event_type',
        'description',
        'cover_image',
        'start_date',
        'start_time',
        'end_date',
        'end_time',
        'status',
        'tags',
    ];

    protected $casts = [
        'tags' => 'array',
        'start_date' => 'date',
        'end_date' => 'date',
        'is_rsvp' => 'boolean',
        'rsvp_closes_at' => 'datetime',
        'is_public_link' =>'boolean'
    ];

    protected $appends = [
        'cover_image_url',
    ];

    public function organizer()
    {
        return $this->belongsTo(Organizer::class);
    }

    public function creditAccount()
    {
        return $this->hasOne(EventCreditAccount::class);
    }

    public function isMultiDay(): bool
    {
        return $this->end_date && $this->end_date->gt($this->start_date);
    }

    public function tickets()
    {
        return $this->hasMany(Ticket::class);
    }

    public function ticketTypes()
    {
        return $this->hasMany(TicketType::class);
    }

    public function orders()
    {
        return $this->hasMany(Order::class);
    }

    public function reviews()
    {
        return $this->hasMany(Review::class);
    }

    public function discounts()
    {
        return $this->hasMany(Discount::class);
    }

    public function reservations()
    {
        return $this->hasMany(Reservation::class);
    }

    public function rsvps()
    {
        return $this->hasMany(RSVP::class);
    }

    public function waitlist()
    {
        return $this->hasMany(RsvpWaitlist::class);
    }

    public function sessions()
    {
        return $this->hasMany(EventSession::class)
            ->orderBy('session_date')
            ->orderBy('sort_order')
            ->orderBy('start_time');
    }

    public function people()
    {
        return $this->hasMany(EventPerson::class)->orderBy('sort_order');
    }

    public function formFields()
    {
        return $this->hasMany(EventFormField::class)
            ->where('is_active', true)
            ->orderBy('sort_order');
    }

    public function allPartners()
    {
        return $this->hasMany(EventPartner::class)->orderBy('sort_order');
    }

    public function partners()
    {
        return $this->hasMany(EventPartner::class)->where('type', 'partner')->orderBy('sort_order');
    }

    public function sponsors()
    {
        return $this->hasMany(EventPartner::class)->where('type', 'sponsor')->orderBy('sort_order');
    }

    // Get the event's cover image URL using proper Laravel Storage methods
    public function getCoverImageUrlAttribute()
    {
        if (!$this->cover_image) {
            return null;
        }

        // Use the default filesystem disk which Laravel Cloud automatically configures
        return Storage::disk(config('filesystems.default'))->url($this->cover_image);

    }

    public function calculateCurrentStatus(): string
    {
        // Don't auto-update certain statuses
        if (in_array($this->status, ['draft', 'canceled'])) {
            return $this->status;
        }

        $now = Carbon::now();
        $eventStart = $this->getEventStartDateTime();

        // Special case: If event has no end time (regardless of end_date), mark as past after 24 hours from start
        if (is_null($this->end_time)) {
            if ($now->gte($eventStart->copy()->addHours(24))) {
                return 'past'; // Event with no end time is considered completed after 24 hours
            }
            if ($now->gte($eventStart)) {
                return 'live'; // Event is live from start until 24 hours later
            }
        } else {
            $eventEnd = $this->getEventEndDateTime();

            // Check if event has ended
            if ($now->gte($eventEnd)) {
                return 'past'; // Event has ended
            }

            // Check if event is currently happening
            if ($now->gte($eventStart) && $now->lt($eventEnd)) {
                return 'live'; // Currently happening
            }
        }

        // Check if event starts within the next 24 hours
        $hoursUntilStart = $now->diffInHours($eventStart, false);
        if ($hoursUntilStart >= 0 && $hoursUntilStart < 24) {
            return 'live'; // Event starts within 24 hours
        }

        // Future event (more than 24 hours away)
        if ($now->lt($eventStart)) {
            return 'published';
        }

        return 'past'; // Fallback
    }

    // Domain Logic: Get the actual start datetime
    public function getEventStartDateTime(): Carbon
    {
        // return Carbon::parse($this->start_date->format('dS F, Y') . ' ' . $this->start_time);
        return Carbon::parse($this->start_date->format('Y-m-d') . ' ' . $this->start_time);
    }

    // Domain Logic: Get the actual end datetime
    public function getEventEndDateTime(): Carbon
    {
        $endDate = $this->end_date ?? $this->start_date;
        $endTime = $this->end_time ?? $this->start_time;

        // return Carbon::parse($endDate->format('dS F, Y') . ' ' . $endTime);
        return Carbon::parse($endDate->format('Y-m-d') . ' ' . $endTime);
    }

    // Accessor: Get status with automatic calculation
    public function getCurrentStatusAttribute(): string
    {
        return $this->calculateCurrentStatus();
    }

    // Domain Logic: Check if status needs updating
    public function needsStatusUpdate(): bool
    {
        return $this->status !== $this->calculateCurrentStatus();
    }

    // Domain Logic: Update status if needed
    public function updateStatusIfNeeded(): bool
    {
        $newStatus = $this->calculateCurrentStatus();

        if ($this->status !== $newStatus) {
            $this->status = $newStatus;

            return $this->save();
        }

        return false; // No update needed
    }

    // Domain Logic: Check if event is purchasable
    public function isPurchasable(): bool
    {
        // Event must be published or live
        if (!in_array($this->status, ['published', 'live'])) {
            return false;
        }

        // Event must not be in the past
        if ($this->status === 'past') {
            return false;
        }

        // Check if event has started but has no end time (use 24-hour rule)
        $now = Carbon::now();
        $eventStart = $this->getEventStartDateTime();

        if (is_null($this->end_time)) {
            // If event started more than 24 hours ago, not purchasable
            if ($now->gte($eventStart->copy()->addHours(24))) {
                return false;
            }
        } else {
            // If event has ended, not purchasable
            $eventEnd = $this->getEventEndDateTime();
            if ($now->gte($eventEnd)) {
                return false;
            }
        }

        // Check if event has available tickets
        return $this->hasAvailableTickets();
    }

    // Domain Logic: Check if event has available tickets
    public function hasAvailableTickets(): bool
    {
        return $this->ticketTypes()
            ->available()
            ->get()
            ->filter(function ($ticketType) {
                return $ticketType->available_quantity > 0;
            })
            ->count() > 0;
    }

    // Domain Logic: Get reason why event is not purchasable
    public function getPurchasabilityReason(): ?string
    {
        if (!in_array($this->status, ['published', 'live'])) {
            return match ($this->status) {
                'draft' => 'This event is still in draft mode.',
                'canceled' => 'This event has been canceled.',
                'past' => 'This event has already ended.',
                default => 'This event is not available for ticket sales.'
            };
        }

        $now = Carbon::now();
        $eventStart = $this->getEventStartDateTime();

        if (is_null($this->end_time)) {
            if ($now->gte($eventStart->copy()->addHours(24))) {
                return 'Ticket sales for this event have ended.';
            }
        } else {
            $eventEnd = $this->getEventEndDateTime();
            if ($now->gte($eventEnd)) {
                return 'This event has already ended.';
            }
        }

        if (!$this->hasAvailableTickets()) {
            return 'No tickets are currently available for this event.';
        }

        return null; // Event is purchasable
    }

    /**
     * Get the route key for the model.
     */
    public function getRouteKeyName(): string
    {
        return 'slug';
    }

    /**
     * Retrieve the model for a bound value.
     */
    public function resolveRouteBinding($value, $field = null)
    {
        // Try to find by slug first (SEO-friendly URLs)
        $event = $this->where('slug', $value)->first();

        // If not found by slug, try event code (short, shareable URLs)
        if (!$event) {
            $event = $this->where('code', strtoupper($value))->first();
        }

        // If not found by slug/code and value is numeric, try ID (backward compatibility)
        if (!$event && is_numeric($value)) {
            $event = $this->where('id', $value)->first();

            // If found by ID, redirect to slug URL for SEO
            if ($event && request()->route()) {
                $routeName = request()->route()->getName();

                // Only redirect for main event routes to maintain SEO-friendly URLs
                if (in_array($routeName, ['events.show', 'events.tickets.show', 'events.rsvp.show'])) {
                    $newUrl = route($routeName, $event->slug);
                    if (request()->fullUrl() !== $newUrl) {
                        abort(redirect($newUrl, 301));
                    }
                }
            }
        }

        return $event;
    }

    /**
     * Generate a unique slug for the event.
     */
    public function generateUniqueSlug(): string
    {
        $baseSlug = Str::slug($this->title);
        $slug = $baseSlug;
        $counter = 1;

        // Check if slug exists and make it unique
        while (static::where('slug', $slug)->where('id', '!=', $this->id ?? 0)->exists()) {
            $slug = $baseSlug . '-' . $counter;
            $counter++;
        }

        return $slug;
    }

    /**
     * Get the SEO-friendly URL for the event.
     */
    public function getUrlAttribute(): string
    {
        return route('events.show', $this->slug);
    }

    /**
     * Get the short URL using event code (for sharing).
     */
    public function getShortUrlAttribute(): string
    {
        return route('events.show', $this->code);
    }

    /**
     * Get the canonical URL (always use slug for SEO).
     */
    public function getCanonicalUrlAttribute(): string
    {
        return route('events.show', $this->slug);
    }

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($model) {
            // Auto-generate code if empty
            if (empty($model->code)) {
                $model->code = strtoupper(Str::random(8));
            }

            // Auto-generate slug if empty
            if (empty($model->slug)) {
                $model->slug = $model->generateUniqueSlug();
            }
        });

        static::updating(function ($model) {
            // Regenerate slug if title has changed
            if ($model->isDirty('title') && !$model->isDirty('slug')) {
                $model->slug = $model->generateUniqueSlug();
            }

            // Delete old cover image if it's being updated
            if ($model->isDirty('cover_image')) {
                $oldImage = $model->getOriginal('cover_image');
                if ($oldImage && $oldImage !== $model->cover_image) {
                    Storage::disk(config('filesystems.default'))->delete($oldImage);
                }
            }
        });

        static::deleted(function ($model) {
            // Delete cover image when event is deleted
            if ($model->cover_image) {
                Storage::disk(config('filesystems.default'))->delete($model->cover_image);
            }
        });

        // // automatically convert status to 'live' if date is today
        // very bad
        // static::retrieved(function ($model) {
        //     if (Carbon::parse($model->start_date)->isToday() || Carbon::parse($model->start_time)->isPast()) {
        //         $model->status = 'live';
        //         $model->save();
        //     }

        //     if (($model->end_date != null && Carbon::parse($model->end_date)->isNowOrPast()) || ($model->end_time != null && Carbon::parse($model->end_time)->isPast())) {

        //         $model->status = 'past';
        //         $model->save();
        //     }
        // });
    }
}
