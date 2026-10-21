<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Contact extends Model
{
    protected $fillable = [
        'first_name',
        'last_name', 
        'email',
        'phone',
        'subject',
        'message',
        'newsletter',
        'honeypot',
        'responded_at',
        'admin_notes'
    ];

    protected $casts = [
        'newsletter' => 'boolean',
        'responded_at' => 'datetime',
    ];

    public function getFullNameAttribute(): string
    {
        return "{$this->first_name} {$this->last_name}";
    }

    public function markAsResponded(): void
    {
        $this->update(['responded_at' => now()]);
    }

    public function scopeUnresponded($query)
    {
        return $query->whereNull('responded_at');
    }

    public function scopeResponded($query)
    {
        return $query->whereNotNull('responded_at');
    }
}
