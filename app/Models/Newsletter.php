<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class Newsletter extends Model
{
    protected $fillable = [
        'email',
        'honeypot',
        'subscribed_at',
        'unsubscribed_at',
        'unsubscribe_token',
        'ip_address',
        'user_agent',
        'admin_notes'
    ];

    protected $casts = [
        'subscribed_at' => 'datetime',
        'unsubscribed_at' => 'datetime',
    ];

    protected static function boot()
    {
        parent::boot();
        
        static::creating(function ($newsletter) {
            if (!$newsletter->subscribed_at) {
                $newsletter->subscribed_at = now();
            }
            if (!$newsletter->unsubscribe_token) {
                $newsletter->unsubscribe_token = Str::random(64);
            }
        });
    }

    public function scopeActive($query)
    {
        return $query->whereNull('unsubscribed_at');
    }

    public function scopeUnsubscribed($query)
    {
        return $query->whereNotNull('unsubscribed_at');
    }

    public function unsubscribe(): void
    {
        $this->update(['unsubscribed_at' => now()]);
    }

    public function resubscribe(): void
    {
        $this->update([
            'unsubscribed_at' => null,
            'subscribed_at' => now(),
        ]);
    }

    public function isActive(): bool
    {
        return $this->unsubscribed_at === null;
    }

    public function getUnsubscribeUrlAttribute(): string
    {
        return route('newsletter.unsubscribe', ['token' => $this->unsubscribe_token]);
    }
}
