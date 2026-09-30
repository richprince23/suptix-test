<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

class EventPerson extends Model
{
    protected $fillable = [
        'event_id',
        'session_id',
        'organizer_id',
        'name',
        'role',
        'bio',
        'photo',
        'is_visible',
        'sort_order',
    ];

    protected $casts = [
        'is_visible' => 'boolean',
    ];

    protected $appends = ['photo_url'];

    public function event()
    {
        return $this->belongsTo(Event::class);
    }

    public function session()
    {
        return $this->belongsTo(EventSession::class);
    }

    public function organizer()
    {
        return $this->belongsTo(Organizer::class);
    }

    public function getPhotoUrlAttribute(): ?string
    {
        if (! $this->photo) {
            return null;
        }

        return Storage::disk(config('filesystems.default'))->url($this->photo);
    }

    protected static function booted(): void
    {
        static::updating(function (EventPerson $person) {
            if ($person->isDirty('photo') && $person->getOriginal('photo')) {
                Storage::disk(config('filesystems.default'))->delete($person->getOriginal('photo'));
            }
        });

        static::deleted(function (EventPerson $person) {
            if ($person->photo) {
                Storage::disk(config('filesystems.default'))->delete($person->photo);
            }
        });
    }
}
