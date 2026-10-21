<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

class EventPartner extends Model
{
    protected $fillable = [
        'event_id',
        'organizer_id',
        'type',
        'name',
        'logo',
        'website',
        'description',
        'tier',
        'is_visible',
        'sort_order',
    ];

    protected $casts = [
        'is_visible' => 'boolean',
    ];

    protected $appends = ['logo_url'];

    public function event()
    {
        return $this->belongsTo(Event::class);
    }

    public function organizer()
    {
        return $this->belongsTo(Organizer::class);
    }

    public function getLogoUrlAttribute(): ?string
    {
        if (! $this->logo) {
            return null;
        }

        return Storage::disk(config('filesystems.default'))->url($this->logo);
    }

    protected static function booted(): void
    {
        static::updating(function (EventPartner $partner) {
            if ($partner->isDirty('logo') && $partner->getOriginal('logo')) {
                Storage::disk(config('filesystems.default'))->delete($partner->getOriginal('logo'));
            }
        });

        static::deleted(function (EventPartner $partner) {
            if ($partner->logo) {
                Storage::disk(config('filesystems.default'))->delete($partner->logo);
            }
        });
    }
}
